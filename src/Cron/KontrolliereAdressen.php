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
	 * Felder, die in der E-Mail aufgelistet werden: [Beschriftung => Spalte]
	 */
	private const FELDER_ADRESSE = array
	(
		'Name'       => 'nachname',
		'Vorname'    => 'vorname',
		'Titel'      => 'titel',
		'Firma'      => 'firma',
		'Straße'     => 'strasse',
		'PLZ'        => 'plz',
		'Ort'        => 'ort',
		'Telefon 1'  => 'telefon1',
		'Telefon 2'  => 'telefon2',
		'Telefon 3'  => 'telefon3',
		'Telefon 4'  => 'telefon4',
		'Fax 1'      => 'telefax1',
		'Fax 2'      => 'telefax2',
		'E-Mail 1'   => 'email1',
		'E-Mail 2'   => 'email2',
		'E-Mail 3'   => 'email3',
		'E-Mail 4'   => 'email4',
		'E-Mail 5'   => 'email5',
		'E-Mail 6'   => 'email6',
	);

	private const FELDER_WEB = array
	(
		'Homepage'  => 'homepage',
		'Facebook'  => 'facebook',
		'Twitter'   => 'twitter',
		'Instagram' => 'instagram',
		'Skype'     => 'skype',
		'WhatsApp'  => 'whatsapp',
		'Threema'   => 'threema',
		'Telegram'  => 'telegram',
		'IRC'       => 'irc',
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
		$strText .= '<p><i>(E-Mail-Adressen werden für Spambots nicht lesbar dargestellt!)</i></p>'."\n";

		$strHinweis = '';

		$strText .= '<ul>';
		$strText .= self::erzeugeEintraege($objAdresse, self::FELDER_WEB);
		$strText .= $this->erzeugeFotoEintrag($objAdresse, $strHinweis);
		$strText .= '</ul>';
		$strText .= '<p><i>'.$strHinweis.'</i></p>'."\n";

		$strText .= '<ul>';
		$strText .= '<li>Profiltext: <b>'.StringUtil::specialchars((string) $objAdresse->text).'</b></li>'."\n";
		$strText .= '</ul>';

		// Einbindungen (Spalte links) als HTML-Liste ausgeben
		$strText .= '<p>Ihre Adresse wird auf folgenden Seiten angezeigt:</p>';
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

		$strText .= '<p><i>Dies ist eine automatisch generierte E-Mail.</i></p>';
		$strText .= '</body>';
		$strText .= '</html>';

		return $strText;
	}

	/**
	 * Erzeugt eine vollständige <ul>-Liste aus den angegebenen Feldern.
	 *
	 * @param array<string, string> $arrFelder
	 */
	private static function erzeugeListe(object $objAdresse, array $arrFelder): string
	{
		return '<ul>'.self::erzeugeEintraege($objAdresse, $arrFelder).'</ul>';
	}

	/**
	 * Erzeugt die <li>-Einträge aus den angegebenen Feldern.
	 *
	 * @param array<string, string> $arrFelder
	 */
	private static function erzeugeEintraege(object $objAdresse, array $arrFelder): string
	{
		$strText = '';

		foreach ($arrFelder as $strLabel => $strFeld)
		{
			$strText .= '<li>'.$strLabel.': <b>'.StringUtil::specialchars((string) $objAdresse->$strFeld).'</b></li>'."\n";
		}

		return $strText;
	}

	/**
	 * Erzeugt den Listeneintrag für das Standardfoto und den passenden Hinweistext.
	 *
	 * @param string $strHinweis Wird mit dem passenden Hinweistext befüllt
	 */
	private function erzeugeFotoEintrag(object $objAdresse, string &$strHinweis): string
	{
		$strHinweis = 'Bitte senden Sie uns ein Foto oder einen Link zu einem Foto, welches wir verwenden dürfen.';

		if (!$objAdresse->singleSRC)
		{
			return '<li>Standardfoto: <b>-</b></li>'."\n";
		}

		// Ohne Basis-URL lässt sich das Foto in der E-Mail nicht anzeigen
		$strBasisUrl = self::einstellung('adressen_cron_fotourl');

		if ($strBasisUrl === '')
		{
			return '<li>Standardfoto: <b>-</b></li>'."\n";
		}

		$objModel      = FilesModel::findByUuid($objAdresse->singleSRC);
		$strProjectDir = System::getContainer()->getParameter('kernel.project_dir');

		if ($objModel === null || !$objModel->path || !is_file($strProjectDir.'/'.$objModel->path))
		{
			return '<li>Standardfoto: <b>-</b></li>'."\n";
		}

		$strHinweis = 'Das Standardfoto wird wie im Vorschaubild verkleinert angezeigt, wenn die Fotoanzeige aktiviert ist. Statt des Standardfotos kann auf den jeweiligen Seiten auch ein anderes Foto eingebunden sein.';

		$strFoto = StringUtil::specialchars(rtrim($strBasisUrl, '/').'/'.$objModel->path);

		return '<li>Standardfoto: <a href="'.$strFoto.'"><img src="'.$strFoto.'" height="80" alt="Standardfoto"></a></li>'."\n";
	}
}
