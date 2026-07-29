<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Modules;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoAdressenBundle\Classes\Adressdaten;

/**
 * Frontend-Modul "Wertungsreferenten"
 *
 * Gibt zu jedem Verband bzw. Bezirk den zuständigen Wertungsreferenten als
 * Tabelle aus. Die Zuordnung erfolgt über das Feld tl_adressen.wertungsreferent.
 */
class Wertungsreferenten extends Module
{
	/**
	 * Template
	 */
	protected $strTemplate = 'mod_adressen_referenten';

	/**
	 * Zeigt im Backend einen Platzhalter an.
	 */
	public function generate(): string
	{
		$objScopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
		$objRequest      = System::getContainer()->get('request_stack')->getCurrentRequest();

		if ($objRequest !== null && $objScopeMatcher->isBackendRequest($objRequest))
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### '.($GLOBALS['TL_LANG']['FMD']['adressen_wertungsreferenten'][0] ?? 'WERTUNGSREFERENTEN').' ###';
			$objTemplate->title    = $this->name;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	/**
	 * Erzeugt die Ausgabe des Moduls.
	 */
	protected function compile(): void
	{
		// Namen der Verbände/Bezirke aus der Sprachdatei
		$arrVerbaende = $GLOBALS['TL_LANG']['tl_adressen']['verbaende'] ?? array();

		$objAdressen = Database::getInstance()
			->prepare("SELECT * FROM tl_adressen WHERE aktiv = '1' AND wertungsreferent IS NOT NULL")
			->execute();

		// Adressen den Verbänden/Bezirken zuordnen
		$arrZeilen = array();

		while ($objAdressen->next())
		{
			$arrBezirke = StringUtil::deserialize($objAdressen->wertungsreferent, true);

			foreach ($arrBezirke as $strBezirk)
			{
				if (!isset($arrVerbaende[$strBezirk]))
				{
					continue;
				}

				$arrZeilen[$strBezirk] = array
				(
					'referent' => Adressdaten::name($objAdressen),
					'kontakt'  => $this->formatiereKontakt($objAdressen),
				);
			}
		}

		// Tabelle in der Reihenfolge der Sprachdatei aufbauen
		$strContent  = '<table class="ce_table adressen_referenten">';
		$strContent .= '<thead><tr><th>Verband/Bezirk</th><th>Referent</th><th>Kontakt</th></tr></thead>';
		$strContent .= '<tbody>';

		$strClass = '';

		foreach ($arrVerbaende as $strKey => $strName)
		{
			if (!isset($arrZeilen[$strKey]))
			{
				continue;
			}

			$strClass = ($strClass === 'odd') ? 'even' : 'odd';

			$strContent .= '<tr class="'.$strClass.'">';
			$strContent .= '<td>'.StringUtil::specialchars((string) $strName).'</td>';
			$strContent .= '<td>'.StringUtil::specialchars($arrZeilen[$strKey]['referent']).'</td>';
			$strContent .= '<td>'.$arrZeilen[$strKey]['kontakt'].'</td>';
			$strContent .= '</tr>';
		}

		$strContent .= '</tbody></table>';

		$this->Template->daten = $strContent;
	}

	/**
	 * Baut die Kontaktspalte aus Telefonnummern und E-Mail-Adressen zusammen.
	 *
	 * Die E-Mail-Adressen werden über den Insert-Tag {{email::...}} ausgegeben,
	 * damit Contao sie beim Parsen des Templates gegen Spambots verschleiert.
	 */
	private function formatiereKontakt(object $objAdresse): string
	{
		$arrTeile = array_map(
			static fn (string $strNummer): string => StringUtil::specialchars($strNummer),
			Adressdaten::telefonnummern($objAdresse)['alle']
		);

		foreach (Adressdaten::emailadressen($objAdresse) as $strMail)
		{
			$arrTeile[] = '{{email::'.$strMail.'}}';
		}

		return implode(', ', $arrTeile);
	}
}
