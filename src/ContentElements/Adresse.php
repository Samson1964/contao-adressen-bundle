<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\ContentElements;

use Contao\Config;
use Contao\ContentElement;
use Contao\Database;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoAdressenBundle\Classes\Adressdaten;

/**
 * Inhaltselement "Adresse"
 *
 * Gibt einen Datensatz aus tl_adressen im Frontend aus.
 */
class Adresse extends ContentElement
{
	/**
	 * Template
	 */
	protected $strTemplate = 'ce_adressen';

	/**
	 * Erzeugt die Ausgabe des Inhaltselements.
	 */
	protected function compile(): void
	{
		$intAdresse = (int) $this->adresse_id;

		// Template-Variablen mit definierten Werten vorbelegen, damit die
		// Templates auch ohne Adresse fehlerfrei durchlaufen
		$this->Template->name          = '';
		$this->Template->visitenkarte  = '';
		$this->Template->adresse       = '';
		$this->Template->adressen      = '';
		$this->Template->telefon       = array();
		$this->Template->telefon_fest  = array();
		$this->Template->telefon_mobil = array();
		$this->Template->telefax       = array();
		$this->Template->email         = array();
		$this->Template->viewFoto      = false;
		$this->Template->funktioninfo  = '';
		$this->Template->aktiv         = '';
		$this->Template->id            = 0;

		// Daten aus tl_content
		$this->Template->funktion = (string) $this->adresse_funktion;
		$this->Template->zusatz   = (string) $this->adresse_zusatz;

		if ($intAdresse < 1)
		{
			return;
		}

		$objAdresse = Database::getInstance()
			->prepare('SELECT * FROM tl_adressen WHERE id = ?')
			->execute($intAdresse);

		if (!$objAdresse->numRows)
		{
			return;
		}

		// Erlaubte E-Mail-Adressen ermitteln
		$arrErlaubteMails = null;

		if ($this->adresse_selectmails)
		{
			$arrErlaubteMails = StringUtil::deserialize($this->adresse_mails, true);
		}

		$arrTelefon = Adressdaten::telefonnummern($objAdresse);

		// Aufbereitete Daten in das Template schreiben
		$this->Template->name          = Adressdaten::name($objAdresse);
		$this->Template->visitenkarte  = Adressdaten::visitenkarte($objAdresse);
		$this->Template->adresse       = Adressdaten::anschriftMitKarte($objAdresse);
		// "adressen" bleibt aus Kompatibilitätsgründen zu älteren Templates erhalten
		$this->Template->adressen      = $this->Template->adresse ? '<div class="adr_adresse">'.$this->Template->adresse.'</div>' : '';
		$this->Template->telefon       = $arrTelefon['alle'];
		$this->Template->telefon_fest  = $arrTelefon['fest'];
		$this->Template->telefon_mobil = $arrTelefon['mobil'];
		$this->Template->telefax       = Adressdaten::telefaxnummern($objAdresse);
		$this->Template->email         = Adressdaten::emailadressen($objAdresse, $arrErlaubteMails);

		// Rohdaten aus tl_adressen in das Template schreiben
		foreach (self::getDatenbankFelder() as $strFeld)
		{
			$this->Template->$strFeld = (string) $objAdresse->$strFeld;
		}

		// Foto zusammenbauen
		if ($this->adresse_addImage)
		{
			$this->befuelleFoto($objAdresse);
		}
	}

	/**
	 * Schreibt die Bildinformationen in das Template.
	 *
	 * Quelle ist entweder das im Inhaltselement hinterlegte Ersatzfoto, das
	 * Standardfoto der Adresse oder das global konfigurierte Standardbild.
	 */
	private function befuelleFoto(object $objAdresse): void
	{
		$varUuid = $this->singleSRC ?: ($objAdresse->singleSRC ?: Config::get('adressen_defaultImage'));

		if (!$varUuid)
		{
			return;
		}

		$objFile = FilesModel::findByUuid($varUuid);

		if ($objFile === null || !$objFile->path)
		{
			return;
		}

		// Bildformat entweder aus dem Inhaltselement oder aus den Systemeinstellungen
		$varSize = $this->adresse_altformat
			? StringUtil::deserialize($this->size)
			: StringUtil::deserialize(Config::get('adressen_ImageSize'));

		$figure = System::getContainer()->get('contao.image.studio')
			->createFigureBuilder()
			->fromPath($objFile->path)
			->setSize($varSize)
			->buildIfResourceExists();

		if ($figure === null)
		{
			return;
		}

		// Templatewerte über die Legacy-Struktur erzeugen
		// (Controller::addImageToTemplate() existiert in Contao 5 nicht mehr)
		$objBild = new \stdClass();
		$figure->applyLegacyTemplateData($objBild);

		$this->Template->viewFoto     = true;
		$this->Template->image        = $objBild->singleSRC ?? $objFile->path;
		$this->Template->thumbnail    = $objBild->src ?? '';
		$this->Template->imageSize    = $objBild->imgSize ?? '';
		$this->Template->imageTitle   = $objBild->imageTitle ?? '';
		$this->Template->imageAlt     = $objBild->alt ?? '';
		$this->Template->imageCaption = $objBild->caption ?? '';
	}

	/**
	 * Spalten aus tl_adressen, die unverändert ins Template übernommen werden.
	 *
	 * @return list<string>
	 */
	private static function getDatenbankFelder(): array
	{
		return array
		(
			'id', 'nachname', 'vorname', 'titel', 'firma',
			'strasse', 'plz', 'ort',
			'homepage', 'facebook', 'twitter', 'instagram',
			'skype', 'whatsapp', 'threema', 'telegram', 'irc',
			'text', 'info', 'aktiv'
		);
	}

	/**
	 * Prüft, ob eine Telefonnummer zu einem deutschen Mobilfunknetz gehört.
	 *
	 * @deprecated Wird nur noch aus Kompatibilitätsgründen bereitgestellt,
	 *             bitte Adressdaten::istMobilfunk() verwenden.
	 */
	public static function Mobilfunk(string $strNummer): bool
	{
		return Adressdaten::istMobilfunk($strNummer);
	}
}
