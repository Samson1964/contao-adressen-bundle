<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

use Contao\Backend;
use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;

/**
 * CSV-Import und CSV-Export der Adressen im Backend.
 */
class Adressen_Backend extends Backend
{
	/**
	 * Exportiert alle Adressen als CSV-Datei.
	 *
	 * @return string Leerer String, wenn der Export nicht angefordert wurde
	 *                (die Methode beendet das Skript sonst selbst)
	 */
	public function exportAdressen(DataContainer $dc): string
	{
		if (Input::get('key') !== 'export')
		{
			return '';
		}

		$objRow = Database::getInstance()
			->prepare('SELECT * FROM tl_adressen ORDER BY nachname, vorname, titel')
			->execute();

		if (!$objRow->numRows)
		{
			Message::addError('Es sind keine Adressen vorhanden.');
			$this->redirect(str_replace('&key=export', '', Environment::get('request')));
		}

		$strDatei = 'Adressen-Export_'.date('Ymd-Hi').'.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$strDatei.'"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		$resHandle = fopen('php://output', 'w');

		$blnKopfzeile = true;

		while ($objRow->next())
		{
			$arrZeile = $objRow->row();

			if ($blnKopfzeile)
			{
				fputcsv($resHandle, array_keys($arrZeile));
				$blnKopfzeile = false;
			}

			fputcsv($resHandle, array_map(
				static fn ($varWert): string => \is_scalar($varWert) ? (string) $varWert : '',
				$arrZeile
			));
		}

		fclose($resHandle);
		exit;
	}

	/**
	 * Importiert Adressen aus einer CSV-Datei.
	 *
	 * Die erste Zeile der Datei muss die Spaltennamen enthalten. Es werden
	 * ausschließlich Spalten übernommen, die in der DCA von tl_adressen
	 * definiert sind – so kann über die Datei kein beliebiges SQL eingeschleust
	 * werden.
	 */
	public function importAdressen(DataContainer $dc): string
	{
		if (Input::get('key') !== 'import')
		{
			return '';
		}

		$objUploader = new FileUpload();

		if (Input::post('FORM_SUBMIT') === 'tl_table_import')
		{
			$this->verarbeiteUpload($objUploader, $dc->table);
		}

		return $this->erzeugeFormular($objUploader);
	}

	/**
	 * Liest die hochgeladenen Dateien ein und schreibt sie in die Datenbank.
	 */
	private function verarbeiteUpload(FileUpload $objUploader, string $strTable): void
	{
		$arrUploaded = $objUploader->uploadTo('system/tmp');

		if (empty($arrUploaded))
		{
			Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields'] ?? 'Bitte wählen Sie eine Datei aus.');
			$this->reload();
		}

		$arrErlaubteSpalten = array_keys($GLOBALS['TL_DCA'][$strTable]['fields'] ?? array());
		$strSeparator       = self::getSeparator();

		foreach ($arrUploaded as $strCsvFile)
		{
			$objFile = new File($strCsvFile);

			if ($objFile->extension !== 'csv')
			{
				Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'] ?? 'Dateityp "%s" wird nicht unterstützt.', $objFile->extension));
				continue;
			}

			$arrTabelle = array();
			$resFile    = $objFile->handle;

			while (($arrZeile = fgetcsv($resFile, 0, $strSeparator)) !== false)
			{
				$arrTabelle[] = $arrZeile;
			}

			if (\count($arrTabelle) < 2)
			{
				Message::addError($objFile->name.' enthält keine Datensätze.');
				continue;
			}

			$this->importiereTabelle($objFile->name, $arrTabelle, $arrErlaubteSpalten, $strTable);
		}

		// Zur Adressliste zurückkehren (key=import aus der URL entfernen)
		System::setCookie('BE_PAGE_OFFSET', 0, 0);
		$this->redirect(str_replace('&key=import', '', Environment::get('request')));
	}

	/**
	 * Schreibt die eingelesenen CSV-Zeilen in die Datenbank.
	 *
	 * @param list<list<string|null>> $arrTabelle
	 * @param list<string>            $arrErlaubteSpalten
	 */
	private function importiereTabelle(string $strDateiname, array $arrTabelle, array $arrErlaubteSpalten, string $strTable): void
	{
		$arrKopf = array_map('trim', array_map('strval', array_shift($arrTabelle)));

		// Nur Spalten übernehmen, die es in der Tabelle wirklich gibt
		$arrSpalten = array();

		foreach ($arrKopf as $intIndex => $strSpalte)
		{
			if (\in_array($strSpalte, $arrErlaubteSpalten, true))
			{
				$arrSpalten[$intIndex] = $strSpalte;
			}
		}

		if (!$arrSpalten)
		{
			Message::addError($strDateiname.' enthält keine bekannten Spalten.');

			return;
		}

		$objDatabase = Database::getInstance();
		$intIdIndex  = array_search('id', $arrSpalten, true);

		// Bei mitgelieferten Primärschlüsseln prüfen, ob diese schon vergeben sind
		if ($intIdIndex !== false)
		{
			$arrDoppelt = array();

			foreach ($arrTabelle as $arrZeile)
			{
				$intId = (int) ($arrZeile[$intIdIndex] ?? 0);

				if ($intId < 1)
				{
					continue;
				}

				$objTreffer = $objDatabase->prepare('SELECT id FROM '.$strTable.' WHERE id = ?')->execute($intId);

				if ($objTreffer->numRows)
				{
					$arrDoppelt[] = $intId;
				}
			}

			if ($arrDoppelt)
			{
				Message::addError($strDateiname.' wurde nicht importiert. Doppelte ID: '.implode(', ', $arrDoppelt));

				return;
			}
		}

		// Spaltennamen sind gegen die DCA geprüft und damit sicher
		$strFelder      = implode(', ', $arrSpalten);
		$strPlatzhalter = implode(', ', array_fill(0, \count($arrSpalten), '?'));
		$objStatement   = $objDatabase->prepare('INSERT INTO '.$strTable.' ('.$strFelder.') VALUES ('.$strPlatzhalter.')');

		$intZeilen = 0;

		foreach ($arrTabelle as $arrZeile)
		{
			$arrWerte = array();

			foreach (array_keys($arrSpalten) as $intIndex)
			{
				$arrWerte[] = (string) ($arrZeile[$intIndex] ?? '');
			}

			$objStatement->execute(...$arrWerte);
			$intZeilen++;
		}

		Message::addConfirmation($strDateiname.': '.$intZeilen.' Datensätze importiert.');
	}

	/**
	 * Ermittelt das im Formular gewählte Trennzeichen.
	 */
	private static function getSeparator(): string
	{
		switch (Input::post('separator'))
		{
			case 'semicolon':
				return ';';

			case 'tabulator':
				return "\t";

			default:
				return ',';
		}
	}

	/**
	 * Erzeugt das Upload-Formular für den Import.
	 */
	private function erzeugeFormular(FileUpload $objUploader): string
	{
		// Request-Token über den Service holen
		// (die Konstante REQUEST_TOKEN existiert in Contao 5 nicht mehr)
		$strToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
		$strBack  = StringUtil::ampersand(str_replace('&key=import', '', Environment::get('request')));

		return '
<div id="tl_buttons">
<a href="'.$strBack.'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle'] ?? '').'" accesskey="b">'.($GLOBALS['TL_LANG']['MSC']['backBT'] ?? 'Zurück').'</a>
</div>

<h2 class="sub_headline">'.($GLOBALS['TL_LANG']['MSC']['tw_import'][1] ?? 'CSV-Import').'</h2>
'.Message::generate().'
<form action="'.StringUtil::ampersand(Environment::get('request')).'" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_table_import">
<input type="hidden" name="REQUEST_TOKEN" value="'.$strToken.'">
<input type="hidden" name="MAX_FILE_SIZE" value="'.Config::get('maxFileSize').'">

<div class="tl_tbox">
  <h3><label for="separator">'.($GLOBALS['TL_LANG']['MSC']['separator'][0] ?? 'Trennzeichen').'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset()">
    <option value="comma">'.($GLOBALS['TL_LANG']['MSC']['comma'] ?? 'Komma').'</option>
    <option value="semicolon">'.($GLOBALS['TL_LANG']['MSC']['semicolon'] ?? 'Semikolon').'</option>
    <option value="tabulator">'.($GLOBALS['TL_LANG']['MSC']['tabulator'] ?? 'Tabulator').'</option>
  </select>'.(!empty($GLOBALS['TL_LANG']['MSC']['separator'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  <h3>'.($GLOBALS['TL_LANG']['MSC']['source'][0] ?? 'Quelldatei').'</h3>'.$objUploader->generateMarkup().(!empty($GLOBALS['TL_LANG']['MSC']['source'][1]) ? '
  <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][0] ?? 'Importieren').'">
</div>

</div>
</form>';
	}
}
