<?php
// Melde alle Fehler außer E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);
set_time_limit(0);

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
define('TL_FILES_URL', '');
define('TL_SCRIPT', 'php/adressen/check.php');
require($_SERVER['DOCUMENT_ROOT'].'../../system/initialize.php');

/**
 * extends \System ?
 */
class CheckJob extends \PageRegular
{

	var $aktzeit;
	var $search;
	var $replace;

	/**
	 * Initialize the object (do not remove)
	 */
	public function __construct()
	{
		$this->aktzeit = time();
		$this->search = array('ä', 'Ä',	'ö', 'Ö', 'ü', 'Ü', 'é', 'ß');
		$this->replace = array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'e', 'ss');
		parent::__construct();
	}

	/**
	 * Ausführen
	 */
	public function run()
	{
		$debugmode = false; // Im Debugmodus werden nur Testmails an Webmaster geschickt
		// Alle Datensätze laden, wo mindestens eine E-Mail-Adresse eingetragen ist
		$objAdressen = \Database::getInstance()->prepare("SELECT * FROM tl_adressen WHERE (email1 != '' OR email2 != '' OR email3 != '' OR email4 != '' OR email5 != '' OR email6 != '') AND aktiv = '1'")
		                                       ->execute();
		$mails = 0;
		while($objAdressen->next())
		{
			if($objAdressen->links) // Nur veröffentlichte Adressen prüfen
			{
				// Mail zusammenbauen
				$text = '<html>';
				$text .= '<head>';
				$text .= '<meta charset="utf-8">';
				$text .= '<title>[Deutscher Schachbund] Adressen-Überprüfung</title>';
				$text .= '<style>';
				$text .= 'body {font-family:Verdana; font-size:12px;}';
				$text .= '</style>';
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
				$text .= "<li>Google+: <b>".$objAdressen->google."</b></li>\n";
				$text .= "<li>ICQ: <b>".$objAdressen->icq."</b></li>\n";
				$text .= "<li>Yahoo: <b>".$objAdressen->yahoo."</b></li>\n";
				$text .= "<li>AIM: <b>".$objAdressen->aim."</b></li>\n";
				$text .= "<li>MSN: <b>".$objAdressen->msn."</b></li>\n";
				$text .= "<li>IRC: <b>".$objAdressen->irc."</b></li>\n";
				if($objAdressen->addImage)
				{
					$objModel = \FilesModel::findByUuid($objAdressen->singleSRC);
					if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
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
				// Adressen in HTML umwandeln
				if($objAdressen->links)
				{
					$text .= "<p>Ihre Adresse wird auf folgenden Seiten angezeigt:</p>";
					$adresse = explode("\n",trim($objAdressen->links));
					$text .= '<ul>';
					for($x=0;$x<count($adresse);$x++)
					{
						$text .= '<li><a href="'.$adresse[$x].'">'.$adresse[$x].'</a></li>'."\n";
					}
					$text .= '</ul>';
				}
				else
				{
					$text .= "<p><b>Ihre Adresse wird nicht auf der DSB-Website angezeigt!</b></p>";
				}
				$text .= '<p>Deutscher Schachbund e.V.<br>';
				$text .= 'Öffentlichkeitsarbeit</p>';
				$text .= '<p><i>Dies ist eine automatisch generierte E-Mail.</i></p>';
				$text .= '</body>';
				$text .= '</html>';
				// Mail verschicken
				$mails++;
				$email = new \Email();
				$email->from = 'server@schachbund.de';
				$email->fromName = 'Deutscher Schachbund';
				$email->charset = 'utf-8';
				$email->subject = '[Deutscher Schachbund] Adressen-Überprüfung';
				$email->html = $text;
				$email->replyTo('DSB-Presse <presse@schachbund.de>');
				// Empfänger inkl. seiner Adressen in ein Array übertragen
				$empfaenger = array();
				$empfaengername = str_replace($this->search, $this->replace, $objAdressen->vorname . ' ' . $objAdressen->nachname);
				if($objAdressen->email1) $empfaenger[] = $empfaengername . ' <' . $objAdressen->email1 . '>';
				if($objAdressen->email2) $empfaenger[] = $empfaengername . ' <' . $objAdressen->email2 . '>';
				if($objAdressen->email3) $empfaenger[] = $empfaengername . ' <' . $objAdressen->email3 . '>';
				if($objAdressen->email4) $empfaenger[] = $empfaengername . ' <' . $objAdressen->email4 . '>';
				if($objAdressen->email5) $empfaenger[] = $empfaengername . ' <' . $objAdressen->email5 . '>';
				if($objAdressen->email6) $empfaenger[] = $empfaengername . ' <' . $objAdressen->email6 . '>';
				$cc = implode(',', $empfaenger);
				if($debugmode)
				{
					$email->sendTo('Frank Hoppe <webmaster@schachbund.de>');
				}
				else
				{
					$email->sendTo($cc);
					//$email->sendBcc('Frank Hoppe <webmaster@schachbund.de>');
					//$email->sendTo('DSB-Presse <presse@schachbund.de>');
				}
			}
		}
		echo "$mails Emails verschickt<br>\n";
		echo "Fertig";
	}
}

/**
 * Instantiate controller
 */
$objCheck = new CheckJob();
$objCheck->run();

?>
