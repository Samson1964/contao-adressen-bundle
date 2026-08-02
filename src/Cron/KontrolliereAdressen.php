<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Cron;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Email;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LoggerInterface;

/**
 * Cronjob "Adressen kontrollieren"
 *
 * Verschickt an alle aktiven, auf der Website eingebundenen Adressen eine
 * E-Mail mit den gespeicherten Daten und der Bitte, diese zu prüfen und
 * Änderungen zu melden.
 *
 * Das Ausführungsintervall wird in Resources/config/services.yaml über den
 * Tag "contao.cronjob" (interval) festgelegt.
 *
 * Alle Einstellungen (Absender, Betreff, Testmodus …) stehen im Backend unter
 * System -> Einstellungen -> "Adressen: Kontroll-E-Mails".
 *
 * ACHTUNG: Dieser Cronjob verschickt E-Mails an echte Empfänger. Er tut das
 * erst, wenn die Einstellung "Kontroll-E-Mails scharfschalten"
 * (adressen_cron_live) gesetzt ist. Ohne diesen Haken laufen alle E-Mails
 * ausschließlich an den eingetragenen Test-Empfänger.
 */
class KontrolliereAdressen
{
	/**
	 * Vorgabe für den Betreff, wenn in den Einstellungen keiner hinterlegt ist
	 */
	private const BETREFF_STANDARD = 'Adressen-Überprüfung';

	/**
	 * Spalten aus tl_adressen, die im ersten Block der E-Mail aufgelistet werden.
	 * Die Beschriftungen stehen in der Sprachdatei unter
	 * $GLOBALS['TL_LANG']['MSC']['adressen_cron_felder'].
	 *
	 * @var list<string>
	 */
	private const FELDER_ADRESSE = array
	(
		'nachname', 'vorname', 'titel', 'firma', 'strasse', 'plz', 'ort',
		'telefon1', 'telefon2', 'telefon3', 'telefon4',
		'telefax1', 'telefax2',
		'email1', 'email2', 'email3', 'email4', 'email5', 'email6',
	);

	/**
	 * Spalten für den zweiten Block (Homepage und soziale Netzwerke).
	 *
	 * @var list<string>
	 */
	private const FELDER_WEB = array
	(
		'homepage', 'facebook', 'twitter', 'instagram',
		'skype', 'whatsapp', 'threema', 'telegram', 'irc',
	);

	/**
	 * Ersatzbeschriftungen, falls die Sprachdatei nicht geladen werden konnte.
	 *
	 * @var array<string, string>
	 */
	private const FELDER_STANDARD = array
	(
		'nachname'  => 'Name',      'vorname'   => 'Vorname',   'titel'     => 'Titel',
		'firma'     => 'Firma',     'strasse'   => 'Straße',    'plz'       => 'PLZ',
		'ort'       => 'Ort',
		'telefon1'  => 'Telefon 1', 'telefon2'  => 'Telefon 2', 'telefon3'  => 'Telefon 3',
		'telefon4'  => 'Telefon 4', 'telefax1'  => 'Fax 1',     'telefax2'  => 'Fax 2',
		'email1'    => 'E-Mail 1',  'email2'    => 'E-Mail 2',  'email3'    => 'E-Mail 3',
		'email4'    => 'E-Mail 4',  'email5'    => 'E-Mail 5',  'email6'    => 'E-Mail 6',
		'homepage'  => 'Homepage',  'facebook'  => 'Facebook',  'twitter'   => 'Twitter',
		'instagram' => 'Instagram', 'skype'     => 'Skype',     'whatsapp'  => 'WhatsApp',
		'threema'   => 'Threema',   'telegram'  => 'Telegram',  'irc'       => 'IRC',
		'foto'      => 'Standardfoto', 'text'   => 'Profiltext',
	);

	public function __construct(
		private readonly ContaoFramework $framework,
		private readonly LoggerInterface|null $logger = null,
	)
	{
	}

	public function __invoke(): void
	{
		// Contao-Framework initialisieren (Legacy-Klassen Config, Database, FilesModel, Email)
		$this->framework->initialize();

		$strAbsender = self::einstellung('adressen_cron_absender');

		if ($strAbsender === '')
		{
			$this->logger?->error('[Adressen-Verwaltung] Kontroll-E-Mails übersprungen: In den Einstellungen ist keine Absenderadresse hinterlegt.');

			return;
		}

		// Ohne den Schalter "scharfschalten" bleibt der Cronjob im Testmodus
		$blnLive          = (bool) Config::get('adressen_cron_live');
		$strTestEmpfaenger = self::einstellung('adressen_cron_testempfaenger');

		if (!$blnLive && $strTestEmpfaenger === '')
		{
			$this->logger?->error('[Adressen-Verwaltung] Kontroll-E-Mails übersprungen: Der Cronjob läuft im Testmodus, es ist aber kein Test-Empfänger hinterlegt.');

			return;
		}

		$strBetreff = self::einstellung('adressen_cron_betreff') ?: self::BETREFF_STANDARD;
		$strReplyTo = self::einstellung('adressen_cron_replyto') ?: $strAbsender;
		$strName    = self::einstellung('adressen_cron_absendername');

		$objAdressen = Database::getInstance()
			->prepare("SELECT * FROM tl_adressen WHERE (email1 != '' OR email2 != '' OR email3 != '' OR email4 != '' OR email5 != '' OR email6 != '') AND aktiv = '1' AND links != ''")
			->execute();

		$intMails = 0;

		while ($objAdressen->next())
		{
			$arrEmpfaenger = $this->getEmpfaenger($objAdressen);

			if (!$arrEmpfaenger)
			{
				continue;
			}

			$objEmail = new Email();
			$objEmail->from     = $strAbsender;
			$objEmail->charset  = 'utf-8';
			$objEmail->subject  = $strBetreff;
			$objEmail->html     = $this->erzeugeText($objAdressen, $strBetreff);
			$objEmail->replyTo($strReplyTo);

			if ($strName !== '')
			{
				$objEmail->fromName = $strName;
			}

			// Versenden – im Testmodus ausschließlich an den Test-Empfänger
			$objEmail->sendTo($blnLive ? $arrEmpfaenger : $strTestEmpfaenger);

			$intMails++;
		}

		$this->logger?->info(sprintf(
			'[Adressen-Verwaltung] %d Kontroll-E-Mails verschickt%s',
			$intMails,
			$blnLive ? '' : ' (Testmodus – nur an '.$strTestEmpfaenger.')'
		));
	}

	/**
	 * Liest eine Einstellung aus System -> Einstellungen als getrimmten String.
	 */
	private static function einstellung(string $strKey): string
	{
		return trim((string) Config::get($strKey));
	}

	/**
	 * Stellt die Empfängerliste aus allen hinterlegten E-Mail-Adressen zusammen.
	 *
	 * @return list<string>
	 */
	private function getEmpfaenger(object $objAdresse): array
	{
		// Umlaute im Anzeigenamen ersetzen, damit der Header nicht kodiert werden muss
		$arrSearch  = array('ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', 'é', 'ß');
		$arrReplace = array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'e', 'ss');

		$strName = trim(str_replace($arrSearch, $arrReplace, (string) $objAdresse->vorname.' '.(string) $objAdresse->nachname));

		// Kommas würden die Empfängerliste zerreißen
		$strName = str_replace(array(',', '<', '>'), '', $strName);

		$arrEmpfaenger = array();

		for ($i = 1; $i <= 6; $i++)
		{
			$strMail = trim((string) $objAdresse->{'email'.$i});

			if ($strMail === '')
			{
				continue;
			}

			$arrEmpfaenger[] = $strName !== '' ? $strName.' <'.$strMail.'>' : $strMail;
		}

		return $arrEmpfaenger;
	}

	/**
	 * Baut den HTML-Text der Kontroll-E-Mail zusammen.
	 */
	private function erzeugeText(object $objAdresse, string $strBetreff): string
	{
		$strText  = '<html>';
		$strText .= '<head>';
		$strText .= '<meta charset="utf-8">';
		$strText .= '<title>'.StringUtil::specialchars($strBetreff).'</title>';
		$strText .= '<style>body {font-family:Verdana; font-size:12px;}</style>';
		$strText .= '</head>';
		$strText .= '<body>';

		// Anrede und Einleitung stehen in der Sprachdatei und lassen sich damit
		// projektweise überschreiben, ohne den Cronjob anzufassen
		System::loadLanguageFile('default');

		$strText .= '<p>'.($GLOBALS['TL_LANG']['MSC']['adressen_cron_anrede'] ?? 'Guten Tag,').'</p>';
		$strText .= '<p>'.($GLOBALS['TL_LANG']['MSC']['adressen_cron_einleitung'] ?? '').'</p>';

		$strText .= self::erzeugeListe($objAdresse, self::FELDER_ADRESSE);
		$strText .= '<p><i>'.self::text('spamschutz').'</i></p>'."\n";

		$strHinweis = '';

		$strText .= '<ul>';
		$strText .= self::erzeugeEintraege($objAdresse, self::FELDER_WEB);
		$strText .= $this->erzeugeFotoEintrag($objAdresse, $strHinweis);
		$strText .= '</ul>';
		$strText .= '<p><i>'.$strHinweis.'</i></p>'."\n";

		$strText .= '<ul>';
		$strText .= '<li>'.self::feldname('text').': <b>'.StringUtil::specialchars((string) $objAdresse->text).'</b></li>'."\n";
		$strText .= '</ul>';

		// Einbindungen (Spalte links) als HTML-Liste ausgeben
		$strText .= '<p>'.self::text('seiten').'</p>';
		$strText .= '<ul>';

		foreach (array_filter(array_map('trim', explode("\n", (string) $objAdresse->links))) as $strLink)
		{
			$strLink = StringUtil::specialchars($strLink);
			$strText .= '<li><a href="'.$strLink.'">'.$strLink.'</a></li>'."\n";
		}

		$strText .= '</ul>';

		// Grußformel: bevorzugt das eigene Feld (HTML erlaubt, z.B. mit <br>),
		// ersatzweise der Absendername
		$strSignatur = self::einstellung('adressen_cron_signatur');

		if ($strSignatur !== '')
		{
			$strText .= '<p>'.$strSignatur.'</p>';
		}
		else
		{
			$strName = self::einstellung('adressen_cron_absendername');

			if ($strName !== '')
			{
				$strText .= '<p>'.StringUtil::specialchars($strName).'</p>';
			}
		}

		$strText .= '<p><i>'.self::text('automatisch').'</i></p>';
		$strText .= '</body>';
		$strText .= '</html>';

		return $strText;
	}

	/**
	 * Erzeugt eine vollständige <ul>-Liste aus den angegebenen Spalten.
	 *
	 * @param list<string> $arrFelder
	 */
	private static function erzeugeListe(object $objAdresse, array $arrFelder): string
	{
		return '<ul>'.self::erzeugeEintraege($objAdresse, $arrFelder).'</ul>';
	}

	/**
	 * Erzeugt die <li>-Einträge aus den angegebenen Spalten.
	 *
	 * @param list<string> $arrFelder
	 */
	private static function erzeugeEintraege(object $objAdresse, array $arrFelder): string
	{
		$strText = '';

		foreach ($arrFelder as $strFeld)
		{
			$strText .= '<li>'.self::feldname($strFeld).': <b>'.StringUtil::specialchars((string) $objAdresse->$strFeld).'</b></li>'."\n";
		}

		return $strText;
	}

	/**
	 * Liefert die Beschriftung einer Spalte für die E-Mail.
	 *
	 * Quelle ist $GLOBALS['TL_LANG']['MSC']['adressen_cron_felder'][<Spalte>];
	 * fehlt der Eintrag, greift die eingebaute Vorgabe aus FELDER_STANDARD.
	 */
	private static function feldname(string $strFeld): string
	{
		return (string) ($GLOBALS['TL_LANG']['MSC']['adressen_cron_felder'][$strFeld]
			?? self::FELDER_STANDARD[$strFeld]
			?? $strFeld);
	}

	/**
	 * Liefert einen Textbaustein der E-Mail aus der Sprachdatei.
	 *
	 * Quelle ist $GLOBALS['TL_LANG']['MSC']['adressen_cron_texte'][<Schlüssel>].
	 * Fehlt der Eintrag, wird ein leerer String zurückgegeben – die Sprachdatei
	 * des Bundles liefert alle Schlüssel mit.
	 */
	private static function text(string $strKey): string
	{
		return (string) ($GLOBALS['TL_LANG']['MSC']['adressen_cron_texte'][$strKey] ?? '');
	}

	/**
	 * Erzeugt den Listeneintrag für das Standardfoto und den passenden Hinweistext.
	 *
	 * @param string $strHinweis Wird mit dem passenden Hinweistext befüllt
	 */
	private function erzeugeFotoEintrag(object $objAdresse, string &$strHinweis): string
	{
		$strHinweis = self::text('foto_fehlt');
		$strLeer    = '<li>'.self::feldname('foto').': <b>-</b></li>'."\n";

		if (!$objAdresse->singleSRC)
		{
			return $strLeer;
		}

		// Ohne Basis-URL lässt sich das Foto in der E-Mail nicht anzeigen
		$strBasisUrl = self::einstellung('adressen_cron_fotourl');

		if ($strBasisUrl === '')
		{
			return $strLeer;
		}

		$objModel      = FilesModel::findByUuid($objAdresse->singleSRC);
		$strProjectDir = System::getContainer()->getParameter('kernel.project_dir');

		if ($objModel === null || !$objModel->path || !is_file($strProjectDir.'/'.$objModel->path))
		{
			return $strLeer;
		}

		$strHinweis = self::text('foto_vorhanden');

		$strFoto = StringUtil::specialchars(rtrim($strBasisUrl, '/').'/'.$objModel->path);
		$strName = self::feldname('foto');

		return '<li>'.$strName.': <a href="'.$strFoto.'"><img src="'.$strFoto.'" height="80" alt="'.$strName.'"></a></li>'."\n";
	}
}
