<?php

namespace Schachbulle\ContaoAdressenBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Psr\Log\LoggerInterface;

/**
 * Cronjob "Adressen kontrollieren"
 *
 * Verschickt an alle aktiven, auf der Website eingebundenen Adressen eine E-Mail
 * mit den gespeicherten Daten und der Bitte, diese zu prüfen und Änderungen zu melden.
 *
 * Ersetzt das frühere Standalone-Skript src/Resources/public/check.php.
 * Das Ausführungsintervall wird in Resources/config/services.yml über den
 * Tag "contao.cronjob" (interval) festgelegt.
 *
 * ACHTUNG: Dieser Cronjob verschickt E-Mails an echte Empfänger. Solange die
 * Konstante TESTMODUS auf true steht, gehen alle E-Mails ausschließlich an den
 * unten definierten Test-Empfänger. Zum Scharfschalten TESTMODUS auf false setzen.
 */
class KontrolliereAdressen
{
	/**
	 * Testmodus: true = E-Mails gehen NUR an den Test-Empfänger,
	 * false = E-Mails gehen an die jeweiligen Kontakte (Live-Betrieb).
	 */
	private const TESTMODUS = false;

	/**
	 * Empfänger im Testmodus
	 */
	private const TEST_EMPFAENGER = 'Frank Binding <webmaster@schachbund.com>';

	public function __construct(
		private readonly ContaoFramework $framework,
		private readonly LoggerInterface|null $logger = null,
	)
	{
	}

	public function __invoke(): void
	{
		// Contao-Framework initialisieren (Legacy-Klassen Database, FilesModel, Email)
		$this->framework->initialize();

		$db         = \Contao\Database::getInstance();
		$projectDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');

		$arrSearch  = array('ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', 'é', 'ß');
		$arrReplace = array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'e', 'ss');

		// Alle aktiven Adressen mit mindestens einer E-Mail-Adresse laden
		$objAdressen = $db->prepare("SELECT * FROM tl_adressen WHERE (email1 != '' OR email2 != '' OR email3 != '' OR email4 != '' OR email5 != '' OR email6 != '') AND aktiv = '1'")
		                  ->execute();

		$intMails = 0;

		while ($objAdressen->next())
		{
			// Nur veröffentlichte (auf der Website eingebundene) Adressen prüfen
			if (!$objAdressen->links)
			{
				continue;
			}

			// E-Mail-Text zusammenbauen
			$text  = '<html>';
			$text .= '<head>';
			$text .= '<meta charset="utf-8">';
			$text .= '<title>[Deutscher Schachbund] Adressen-Überprüfung</title>';
			$text .= '<style>body {font-family:Verdana; font-size:12px;}</style>';
			$text .= '</head>';
			$text .= '<body>';
			$text .= '<p>Liebe Schachfreundin, lieber Schachfreund,</p>';
			$text .= '<p>in regelmäßigen Abständen werden die in unserer internen Adressen-Datenbank gespeicherten Datensätze automatisch mittels der dort hinterlegten E-Mail-Adresse(n) überprüft.<br>';
			$text .= 'Bitte nehmen Sie sich kurz Zeit und werfen Sie einen Blick auf die nachfolgend aufgeführten Daten. Melden Sie uns Änderungen, indem Sie diese E-Mail beantworten.</p>';
			$text .= '<ul>';
			$text .= "<li>Name: <b>".$objAdressen->nachname."</b></li>\n";
			$text .= "<li>Vorname: <b>".$objAdressen->vorname."</b></li>\n";
			$text .= "<li>Titel: <b>".$objAdressen->titel."</b></li>\n";
			$text .= "<li>Firma: <b>".$objAdressen->firma."</b></li>\n";
			$text .= "<li>Straße: <b>".$objAdressen->strasse."</b></li>\n";
			$text .= "<li>PLZ: <b>".$objAdressen->plz."</b></li>\n";
			$text .= "<li>Ort: <b>".$objAdressen->ort."</b></li>\n";
			$text .= "<li>Telefon 1: <b>".$objAdressen->telefon1."</b></li>\n";
			$text .= "<li>Telefon 2: <b>".$objAdressen->telefon2."</b></li>\n";
			$text .= "<li>Telefon 3: <b>".$objAdressen->telefon3."</b></li>\n";
			$text .= "<li>Telefon 4: <b>".$objAdressen->telefon4."</b></li>\n";
			$text .= "<li>Fax 1: <b>".$objAdressen->telefax1."</b></li>\n";
			$text .= "<li>Fax 2: <b>".$objAdressen->telefax2."</b></li>\n";
			$text .= "<li>E-Mail 1: <b>".$objAdressen->email1."</b></li>\n";
			$text .= "<li>E-Mail 2: <b>".$objAdressen->email2."</b></li>\n";
			$text .= "<li>E-Mail 3: <b>".$objAdressen->email3."</b></li>\n";
			$text .= "<li>E-Mail 4: <b>".$objAdressen->email4."</b></li>\n";
			$text .= "<li>E-Mail 5: <b>".$objAdressen->email5."</b></li>\n";
			$text .= "<li>E-Mail 6: <b>".$objAdressen->email6."</b></li>\n";
			$text .= '</ul>';
			$text .= "<p><i>(E-Mail-Adressen werden für Spambots nicht lesbar dargestellt!)</i></p>\n";
			$text .= '<ul>';
			$text .= "<li>Homepage: <b>".$objAdressen->homepage."</b></li>\n";
			$text .= "<li>Facebook: <b>".$objAdressen->facebook."</b></li>\n";
			$text .= "<li>Twitter: <b>".$objAdressen->twitter."</b></li>\n";
			$text .= "<li>Instagram: <b>".$objAdressen->instagram."</b></li>\n";
			$text .= "<li>Skype: <b>".$objAdressen->skype."</b></li>\n";
			$text .= "<li>WhatsApp: <b>".$objAdressen->whatsapp."</b></li>\n";
			$text .= "<li>Threema: <b>".$objAdressen->threema."</b></li>\n";
			$text .= "<li>Telegram: <b>".$objAdressen->telegram."</b></li>\n";
			$text .= "<li>IRC: <b>".$objAdressen->irc."</b></li>\n";
			if ($objAdressen->addImage)
			{
				$objModel = \Contao\FilesModel::findByUuid($objAdressen->singleSRC);
				if ($objModel !== null && is_file($projectDir . '/' . $objModel->path))
				{
					$foto = 'https://www.schachbund.de/'.$objModel->path;
					$text .= '<li>Standardfoto: <a href="'.$foto.'"><img src="'.$foto.'" height="80"></a></li>'."\n";
					$text .= '</ul>';
					$text .= "<p><i>(Das Standardfoto wird wie im Vorschaubild verkleinert angezeigt, wenn die Fotoanzeige aktiviert ist. Statt des Standardfotos kann auf den jeweiligen Seiten auch ein anderes Foto eingebunden sein.)</i></p>\n";
				}
				else
				{
					$text .= "<li>Standardfoto: <b>-</b></li>\n";
					$text .= '</ul>';
					$text .= "<p><i>(Bitte senden Sie uns ein Foto oder einen Link zu einem Foto, welches wir verwenden dürfen.)</i></p>\n";
				}
			}
			else
			{
				$text .= "<li>Standardfoto: <b>-</b></li>\n";
				$text .= '</ul>';
				$text .= "<p><i>(Bitte senden Sie uns ein Foto oder einen Link zu einem Foto, welches wir verwenden dürfen.)</i></p>\n";
			}
			$text .= '<ul>';
			$text .= "<li>Profiltext: <b>".$objAdressen->text."</b></li>\n\n";
			$text .= '</ul>';
			// Einbindungen (Spalte links) als HTML-Liste ausgeben
			$text .= "<p>Ihre Adresse wird auf folgenden Seiten angezeigt:</p>";
			$arrLinks = explode("\n", trim((string) $objAdressen->links));
			$text .= '<ul>';
			foreach ($arrLinks as $strLink)
			{
				if ($strLink === '')
				{
					continue;
				}
				$text .= '<li><a href="'.$strLink.'">'.$strLink.'</a></li>'."\n";
			}
			$text .= '</ul>';
			$text .= '<p>Deutscher Schachbund e.V.<br>';
			$text .= 'Öffentlichkeitsarbeit</p>';
			$text .= '<p><i>Dies ist eine automatisch generierte E-Mail.</i></p>';
			$text .= '</body>';
			$text .= '</html>';

			// E-Mail aufbauen
			$email = new \Contao\Email();
			$email->from     = 'server@schachbund.de';
			$email->fromName = 'Deutscher Schachbund';
			$email->charset  = 'utf-8';
			$email->subject  = '[Deutscher Schachbund] Adressen-Überprüfung';
			$email->html     = $text;
			$email->replyTo('DSB-Presse <presse@schachbund.com>');

			// Empfänger inkl. aller hinterlegten E-Mail-Adressen zusammenstellen
			$arrEmpfaenger = array();
			$strName       = str_replace($arrSearch, $arrReplace, $objAdressen->vorname . ' ' . $objAdressen->nachname);
			if ($objAdressen->email1) $arrEmpfaenger[] = $strName . ' <' . $objAdressen->email1 . '>';
			if ($objAdressen->email2) $arrEmpfaenger[] = $strName . ' <' . $objAdressen->email2 . '>';
			if ($objAdressen->email3) $arrEmpfaenger[] = $strName . ' <' . $objAdressen->email3 . '>';
			if ($objAdressen->email4) $arrEmpfaenger[] = $strName . ' <' . $objAdressen->email4 . '>';
			if ($objAdressen->email5) $arrEmpfaenger[] = $strName . ' <' . $objAdressen->email5 . '>';
			if ($objAdressen->email6) $arrEmpfaenger[] = $strName . ' <' . $objAdressen->email6 . '>';

			// Versenden – im Testmodus ausschließlich an den Test-Empfänger
			if (self::TESTMODUS)
			{
				$email->sendTo(self::TEST_EMPFAENGER);
			}
			else
			{
				$email->sendTo(implode(',', $arrEmpfaenger));
			}

			$intMails++;
		}

		$this->logger?->info(sprintf('[Adressen-Verwaltung] %d Kontroll-E-Mails verschickt%s', $intMails, self::TESTMODUS ? ' (Testmodus – nur an Test-Empfänger)' : ''));
	}
}
