<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

use Contao\Database;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\FrontendTemplate;
use Contao\System;

/**
 * Ersetzt den Insert-Tag {{adresse::ID}} durch die Adresse aus tl_adressen.
 *
 * Unterstützte Schreibweisen:
 *   {{adresse::ID}}
 *   {{adresse::ID::Funktion}}
 *   {{adresse::ID::Funktion::Funktionsinfo}}
 *
 * Zusätzlich kann an beliebiger Stelle der Parameter "foto=" angegeben werden:
 *   foto=0        – Foto ausblenden
 *   foto=120,90   – Foto in abweichender Größe ausgeben
 */
class Adressen_Frontend extends Frontend
{
	/**
	 * Standard-Abmessungen des Vorschaufotos
	 */
	private const FOTO_BREITE = 80;
	private const FOTO_HOEHE  = 56;

	/**
	 * Template
	 */
	protected $strTemplate = 'ce_adressen_inserttag';

	/**
	 * Hook "replaceInsertTags".
	 *
	 * @param string $strTag
	 *
	 * @return string|false Ausgabe des Tags oder false, wenn es nicht zu
	 *                      diesem Bundle gehört
	 */
	public function adresse_ersetzen($strTag)
	{
		$arrSplit = explode('::', (string) $strTag);

		if ($arrSplit[0] !== 'adresse' && $arrSplit[0] !== 'cache_adresse')
		{
			// Nicht unser Insert-Tag
			return false;
		}

		// Spezialparameter foto= herauslösen
		$blnFoto     = true;
		$intBreite   = self::FOTO_BREITE;
		$intHoehe    = self::FOTO_HOEHE;

		foreach ($arrSplit as $intIndex => $strTeil)
		{
			if ($intIndex === 0 || strncmp($strTeil, 'foto=', 5) !== 0)
			{
				continue;
			}

			$arrMasse = explode(',', substr($strTeil, 5));
			array_splice($arrSplit, $intIndex, 1);

			if (!(int) ($arrMasse[0] ?? 0))
			{
				// foto=0 blendet das Foto aus
				$blnFoto = false;
			}
			else
			{
				$intBreite = (int) $arrMasse[0];
				$intHoehe  = (int) ($arrMasse[1] ?? 0) ?: self::FOTO_HOEHE;
			}

			break;
		}

		$objTemplate = new FrontendTemplate($this->strTemplate);

		// Alle Template-Variablen mit einem definierten Wert vorbelegen
		foreach (self::getTemplateFelder() as $strFeld)
		{
			$objTemplate->$strFeld = '';
		}

		// Funktion und Funktionsinfo sind unabhängig von der Adresse
		$objTemplate->funktion     = $arrSplit[2] ?? '';
		$objTemplate->funktioninfo = $arrSplit[3] ?? '';

		$intId = (int) ($arrSplit[1] ?? 0);

		if ($intId > 0)
		{
			$objAdresse = Database::getInstance()
				->prepare('SELECT * FROM tl_adressen WHERE id = ?')
				->execute($intId);

			if ($objAdresse->numRows)
			{
				$this->befuelleTemplate($objTemplate, $objAdresse, $blnFoto, $intBreite, $intHoehe);
			}
		}

		return $objTemplate->parse();
	}

	/**
	 * Überträgt die Daten der Adresse in das Template.
	 */
	private function befuelleTemplate(FrontendTemplate $objTemplate, object $objAdresse, bool $blnFoto, int $intBreite, int $intHoehe): void
	{
		// Foto zusammenbauen
		if ($blnFoto && $objAdresse->singleSRC)
		{
			$this->befuelleFoto($objTemplate, $objAdresse, $intBreite, $intHoehe);
		}

		$objTemplate->name         = Adressdaten::name($objAdresse);
		$objTemplate->visitenkarte = Adressdaten::visitenkarte($objAdresse);
		$objTemplate->adresse      = Adressdaten::anschriftMitKarte($objAdresse);

		// Telefonnummern nach Festnetz und Mobilfunk getrennt ausgeben
		$arrTelefon             = Adressdaten::telefonnummern($objAdresse);
		$objTemplate->telefon   = implode(', ', $arrTelefon['fest']);
		$objTemplate->handy     = implode(', ', $arrTelefon['mobil']);
		$objTemplate->telefax   = implode(', ', Adressdaten::telefaxnummern($objAdresse));

		// E-Mail-Adressen über den Insert-Tag {{email::...}} verschleiern
		$arrMails = array();

		foreach (Adressdaten::emailadressen($objAdresse) as $strMail)
		{
			$arrMails[] = Kompatibilitaet::insertTagsErsetzen('{{email::'.$strMail.'}}');
		}

		$objTemplate->email = implode(', ', $arrMails);

		// Restliche Felder direkt übernehmen
		foreach (self::getDatenbankFelder() as $strFeld)
		{
			$objTemplate->$strFeld = (string) $objAdresse->$strFeld;
		}
	}

	/**
	 * Erzeugt das Vorschaubild über den Image-Studio-Service
	 * (Controller::getImage() existiert in Contao 5 nicht mehr).
	 */
	private function befuelleFoto(FrontendTemplate $objTemplate, object $objAdresse, int $intBreite, int $intHoehe): void
	{
		$objModel = FilesModel::findByUuid($objAdresse->singleSRC);

		if ($objModel === null || !$objModel->path)
		{
			return;
		}

		$strProjectDir = System::getContainer()->getParameter('kernel.project_dir');

		if (!is_file($strProjectDir.'/'.$objModel->path))
		{
			return;
		}

		$figure = System::getContainer()->get('contao.image.studio')
			->createFigureBuilder()
			->fromPath($objModel->path)
			->setSize(array($intBreite, $intHoehe, 'crop'))
			->buildIfResourceExists();

		if ($figure === null)
		{
			return;
		}

		$objTemplate->bildurl  = $objModel->path;
		$objTemplate->thumburl = $figure->getImage()->getImageSrc();
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
			'telefon1', 'telefon2', 'telefon3', 'telefon4',
			'telefax1', 'telefax2',
			'email1', 'email2', 'email3', 'email4', 'email5', 'email6',
			'homepage', 'facebook', 'twitter', 'instagram',
			'skype', 'whatsapp', 'threema', 'telegram', 'irc',
			'text', 'info', 'aktiv'
		);
	}

	/**
	 * Alle Variablen, die das Template kennt.
	 *
	 * @return list<string>
	 */
	private static function getTemplateFelder(): array
	{
		return array_merge(
			self::getDatenbankFelder(),
			array('funktion', 'funktioninfo', 'name', 'adresse', 'telefon', 'handy', 'telefax', 'email', 'bildurl', 'thumburl', 'visitenkarte')
		);
	}
}
