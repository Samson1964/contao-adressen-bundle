<?php

namespace Schachbulle\ContaoAdressenBundle\Classes;

/*
 * Ersetzt den Tag {{adresse::ID}} bzw. {{adresse::ID::Funktion}}
 * durch die entsprechende Adresse aus tl_adressen
 */

class Adressen_Backend extends \Contao\Backend
{

	public function exportAdressen(\Contao\DataContainer $dc)
	{
		if($this->Input->get('key') != 'export')
		{
			// Export-Befehl fehlt
			return '';
		}

		// Datensätze laden
		$arrExport = array();
		$objRow = $this->Database->prepare("SELECT * FROM tl_adressen ORDER BY nachname,vorname,titel")->execute($dc->id);

		while($objRow->next())
		{
			$arrExport[] = $objRow->row();
		}

		// Ausgabe
		$exportFile =  'Adressen-Export_' . date("Ymd-Hi");

		header('Content-Type: application/csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="' . $exportFile .'.csv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		// Dateihandle für Direktstream öffnen
		$fp = fopen("php://output",'w');
		$array = array();

		// Kopf ausgeben
		foreach($arrExport[0] as $key => $value)
		{
			$array[] = $key;
		}
		fputcsv($fp,$array);

		// Daten ausgeben
		for($x=0;$x<count($arrExport);$x++)
		{
			$array = array();
			foreach($arrExport[$x] as $key => $value)
			{
				$array[] = $value;
			}
			fputcsv($fp,$array);
		}
		fclose($fp);
		exit;
		
	}

	public function importAdressen(\Contao\DataContainer $dc)
	{
		if(\Contao\Input::get('key') != 'import')
		{
			// Beenden, wenn der Parameter nicht übereinstimmt
			return '';
		}

		// Objekt BackendUser importieren
		$this->import('BackendUser','User');
		$class = $this->User->uploader;

		// See #4086
		if (!class_exists($class))
		{
			$class = 'FileUpload';
		}

		$objUploader = new $class();

		// Formular wurde abgeschickt, CSS-Datei importieren
		if (\Contao\Input::post('FORM_SUBMIT') == 'tl_table_import')
		{
			$arrUploaded = $objUploader->uploadTo('system/tmp');

			if(empty($arrUploaded))
			{
				\Contao\Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
				$this->reload();
			}

			$this->import('Database');

			foreach ($arrUploaded as $strCsvFile)
			{
				$objFile = new \File($strCsvFile, true);
				$arrTable = array();

				if ($objFile->extension != 'csv')
				{
					\Contao\Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
					continue;
				}

				// Get separator
				switch (\Contao\Input::post('separator'))
				{
					case 'semicolon':
						$strSeparator = ';';
						break;

					case 'tabulator':
						$strSeparator = "\t";
						break;

					default:
						$strSeparator = ',';
						break;
				}

				$resFile = $objFile->handle;

				while(($arrRow = @fgetcsv($resFile, null, $strSeparator)) !== false)
				{
					$arrTable[] = $arrRow;
				}
				// Feldnamen extrahieren
				$feldnamen = implode(",",$arrTable[0]);
				// ID-Position feststellen
				$idpos = array_search("id",$arrTable[0]);
				// Prüfung auf doppelte Primärschlüssel, wenn Primärschlüssel im Import
				$doppelt = ""; // String für die doppelten ID
				if(isset($idpos))
				{
					for($x=1;$x<count($arrTable);$x++)
					{
						$zeile = array();
						foreach($arrTable[$x] as $wert)
						{
							// Sonderzeichen schützen
							$wert = addslashes($wert);
							$zeile[] = $wert;
						} 
						// Prüfen, wenn ID (Primärschlüssel) mit im Import ist und in Datenbank
						$objErgebnis = $this->Database->prepare("SELECT * FROM ".$dc->table." WHERE id = ?")
						                              ->execute($zeile[$idpos]); 
						if($objErgebnis)
						{
							($doppelt) ? ($doppelt .= ", ".$objErgebnis->id) : ($doppelt = $objErgebnis->id);
							//continue;
						}
					}
				}
				// Daten in MySQL-Tabelle schreiben
				if($doppelt)
				{
					\Contao\Message::addError($objFile->name." wurde nicht importiert. Doppelte ID: ".$doppelt);
				}
				else
				{
					for($x=1;$x<count($arrTable);$x++)
					{
						$zeile = array();
						foreach($arrTable[$x] as $wert)
						{
							// Sonderzeichen schützen
							$wert = addslashes($wert);
							$zeile[] = $wert;
						} 
						// Array trennen
						$values = implode('", "',$zeile);
						$values = '"'.$values.'"';
						// Prüfen, wenn ID (Primärschlüssel) mit im Import ist und in Datenbank
						//$this->Database->prepare("SELECT id FROM ".$dc->table." WHERE id = ?")->execute(\Input::get('id')); 
						$this->Database->prepare("INSERT INTO ".$dc->table." (".$feldnamen.") VALUES (".$values.")")->execute(\Input::get('id')); 
					}
				}
			}

//			$objVersions = new \Versions($dc->table, \Input::get('id'));
//			$objVersions->create();

//			$this->Database->prepare("UPDATE " . $dc->table . " SET tableitems=? WHERE id=?")
//						   ->execute(serialize($arrTable), \Input::get('id'));

			// Cookie setzen und zurückkehren zur Adressenliste (key=import aus URL entfernen)
			\System::setCookie('BE_PAGE_OFFSET', 0, 0);
			$this->redirect(str_replace('&key=import', '', \Contao\Environment::get('request')));
		}

		// Return form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', \Contao\Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['MSC']['tw_import'][1].'</h2>
'.\Contao\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_table_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox">
  <h3><label for="separator">'.$GLOBALS['TL_LANG']['MSC']['separator'][0].'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="comma">'.$GLOBALS['TL_LANG']['MSC']['comma'].'</option>
    <option value="semicolon">'.$GLOBALS['TL_LANG']['MSC']['semicolon'].'</option>
    <option value="tabulator">'.$GLOBALS['TL_LANG']['MSC']['tabulator'].'</option>
  </select>'.(($GLOBALS['TL_LANG']['MSC']['separator'][1] != '') ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  <h3>'.$GLOBALS['TL_LANG']['MSC']['source'][0].'</h3>'.$objUploader->generateMarkup().(isset($GLOBALS['TL_LANG']['MSC']['source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][0]).'">
</div>

</div>
</form>'; 
	}
}
