<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\Input;
use Contao\Module;
use Contao\System;

/**
 * Frontend-Modul "Adressensuche"
 *
 * Durchsucht das Feld tl_adressen.searchstring, das beim Speichern einer
 * Adresse aus allen durchsuchbaren Feldern erzeugt wird, und kann zusätzlich
 * auf Kategorien (tl_adressen.funktionen) einschränken.
 *
 * Unterstützte URL-Parameter:
 *   s=<Suchbegriff>
 *   funktion[]=<Kategorie-ID>
 *   join=and|or   – Verknüpfung mehrerer Kategorien
 *   email=1       – nur E-Mail-Adressen ausgeben (Liteversion)
 */
class Suche extends Module
{
	/**
	 * Template
	 */
	protected $strTemplate = 'adresse_ergebnisse';

	/**
	 * Suchbegriff aus der URL
	 */
	private string $strSuchbegriff = '';

	/**
	 * Ausgewählte Kategorien
	 *
	 * @var list<int>
	 */
	private array $arrFunktionen = array();

	/**
	 * Verknüpfung der Kategorien: AND oder OR
	 */
	private string $strVerknuepfung = 'OR';

	/**
	 * Liteversion: nur E-Mail-Adressen ausgeben
	 */
	private bool $blnLiteversion = false;

	/**
	 * Zeigt im Backend einen Platzhalter an und liest im Frontend die
	 * URL-Parameter ein.
	 */
	public function generate(): string
	{
		$objScopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
		$objRequest      = System::getContainer()->get('request_stack')->getCurrentRequest();

		if ($objRequest !== null && $objScopeMatcher->isBackendRequest($objRequest))
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### '.($GLOBALS['TL_LANG']['FMD']['adressen_suche'][0] ?? 'ADRESSENSUCHE').' ###';
			$objTemplate->title    = $this->name;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;

			return $objTemplate->parse();
		}

		// URL-Parameter auswerten
		$this->strSuchbegriff  = trim(self::leseString('s'));
		$this->arrFunktionen   = self::leseFunktionen();
		$this->strVerknuepfung = strtoupper(self::leseString('join')) === 'AND' ? 'AND' : 'OR';
		$this->blnLiteversion  = self::leseString('email') !== '';

		return parent::generate();
	}

	/**
	 * Erzeugt die Ausgabe des Moduls.
	 */
	protected function compile(): void
	{
		$this->Template->Suchbegriff            = $this->strSuchbegriff;
		$this->Template->SuchbegriffModifiziert = '';
		$this->Template->Ergebnisliste          = array();
		$this->Template->Gesucht                = false;
		$this->Template->Funktionen             = Funktionen::getAktiveFunktionen();
		$this->Template->Funktionsauswahl       = $this->arrFunktionen;
		$this->Template->Verknuepfung           = $this->strVerknuepfung;
		$this->Template->Liteversion            = $this->blnLiteversion;

		if ($this->strSuchbegriff === '' && !$this->arrFunktionen)
		{
			return;
		}

		$this->Template->Gesucht = true;

		[$strWhere, $arrParameter] = $this->baueBedingung();

		$objSuche = Database::getInstance()
			->prepare('SELECT * FROM tl_adressen WHERE '.$strWhere.' ORDER BY nachname ASC, vorname ASC')
			->execute(...$arrParameter);

		$arrDaten = array();

		while ($objSuche->next())
		{
			$arrTelefon = array();
			$arrMails   = array();

			for ($i = 1; $i <= 4; $i++)
			{
				$arrTelefon['telefon'.$i]    = (string) $objSuche->{'telefon'.$i};
				$arrTelefon['telefon'.$i.'sel'] = Adressdaten::telefonlink((string) $objSuche->{'telefon'.$i});
			}

			for ($i = 1; $i <= 6; $i++)
			{
				$arrMails['email'.$i] = (string) $objSuche->{'email'.$i};
			}

			$arrDaten[] = array_merge($arrTelefon, $arrMails, array
			(
				'nachname'    => (string) $objSuche->nachname,
				'vorname'     => (string) $objSuche->vorname,
				'titel'       => (string) $objSuche->titel,
				'firma'       => (string) $objSuche->firma,
				'plz'         => (string) $objSuche->plz,
				'ort'         => (string) $objSuche->ort,
				'strasse'     => (string) $objSuche->strasse,
				'email'       => (bool) array_filter($arrMails),
				'telefon'     => (bool) array_filter($arrTelefon),
				'homepage'    => (string) $objSuche->homepage,
				'info'        => (string) $objSuche->info,
				'text'        => (string) $objSuche->text,
				'deaktiviert' => $objSuche->aktiv ? '' : 'deaktiviert ',
				'unverlinkt'  => $objSuche->links ? '' : 'unverlinkt ',
			));
		}

		$this->Template->SuchbegriffModifiziert = Funktionen::generateAlias($this->strSuchbegriff);
		$this->Template->Ergebnisliste          = $arrDaten;
	}

	/**
	 * Baut die WHERE-Bedingung samt Parametern auf.
	 *
	 * Alle Werte werden als Platzhalter übergeben, damit kein Bestandteil der
	 * URL in das SQL-Statement gelangt.
	 *
	 * @return array{0: string, 1: list<string>}
	 */
	private function baueBedingung(): array
	{
		$arrBedingungen = array();
		$arrParameter   = array();

		if ($this->strSuchbegriff !== '')
		{
			$arrBedingungen[] = 'searchstring LIKE ?';
			$arrParameter[]   = '%'.Funktionen::generateAlias($this->strSuchbegriff).'%';
		}

		if ($this->arrFunktionen)
		{
			$arrFunktionsBedingungen = array();

			foreach ($this->arrFunktionen as $intFunktion)
			{
				$arrFunktionsBedingungen[] = 'funktionen LIKE ?';
				// Die Kategorien liegen serialisiert vor, z.B. a:1:{i:0;s:2:"12";}
				$arrParameter[] = '%"'.$intFunktion.'"%';
			}

			$arrBedingungen[] = '('.implode(' '.$this->strVerknuepfung.' ', $arrFunktionsBedingungen).')';
		}

		return array(implode(' AND ', $arrBedingungen), $arrParameter);
	}

	/**
	 * Liest einen skalaren URL-Parameter aus.
	 *
	 * Übergibt jemand den Parameter als Array (z.B. ?s[]=x), wird ein leerer
	 * String zurückgegeben statt eine "Array to string conversion"-Warnung
	 * auszulösen.
	 */
	private static function leseString(string $strName): string
	{
		$varWert = Input::get($strName);

		return \is_scalar($varWert) ? (string) $varWert : '';
	}

	/**
	 * Liest den Parameter funktion[] aus der URL und lässt nur ganze Zahlen zu.
	 *
	 * @return list<int>
	 */
	private static function leseFunktionen(): array
	{
		$varFunktionen = Input::get('funktion');

		if ($varFunktionen === null || $varFunktionen === '')
		{
			return array();
		}

		if (!\is_array($varFunktionen))
		{
			$varFunktionen = array($varFunktionen);
		}

		$arrFunktionen = array();

		foreach ($varFunktionen as $varFunktion)
		{
			if (\is_scalar($varFunktion) && (int) $varFunktion > 0)
			{
				$arrFunktionen[] = (int) $varFunktion;
			}
		}

		return array_values(array_unique($arrFunktionen));
	}
}
