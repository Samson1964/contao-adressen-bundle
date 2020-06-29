<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   fh-counter
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

/**
 * Class CounterRegister
 *
 * @copyright  Frank Hoppe 2014
 * @author     Frank Hoppe
 *
 * Basisklasse vom FH-Counter
 * Erledigt die Zählung der jeweiligen Contenttypen und schreibt die Zählerwerte in $GLOBALS
 */
class Suche extends \Module
{

	var $suchbegriff;
	var $funktion;
	var $liteversion;
	
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'adresse_ergebnisse';
	 
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ADRESSENSUCHE ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		else
		{
			// FE-Modus: URL mit allen möglichen Parametern auflösen
			$this->suchbegriff = trim(strtolower(\Input::get('s')));
			$this->funktion = \Input::get('funktion');
			$this->linken = strtoupper(\Input::get('join'));
			$this->liteversion = strtoupper(\Input::get('email'));
		}
		
		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{

		// Funktionsparameter prüfen und anpassen
		if(!$this->funktion) $this->funktion = array();
		switch($this->linken)
		{
			case 'AND':
				break;
			case 'OR':
				break;
			default:
				$this->linken = 'OR';
		}
		$this->liteversion = $this->liteversion ? true : false;
		
		if($this->suchbegriff || $this->funktion)
		{
			// Suchbegriff modifizieren
			$s = '%'.\StringUtil::generateAlias($this->suchbegriff).'%';

			// Abfrage zusammenbauen je nach Parametern
			if($this->suchbegriff && !$this->funktion)
			{
				// Suchbegriff vorhanden, aber keine Funktion
				$objSuche = \Database::getInstance()->prepare('SELECT * FROM tl_adressen WHERE searchstring LIKE ? ORDER BY nachname ASC, vorname ASC')
				                                    ->execute($s);
			}
			elseif(!$this->suchbegriff && $this->funktion)
			{
				// Suchbegriff nicht vorhanden, aber mind. eine Funktion
				// Funktionen-SQL bauen
				$sql = '';
				foreach($this->funktion as $item)
				{
					if($sql) $sql .= ' '.$this->linken.' funktionen LIKE ';
					else $sql .= 'funktionen LIKE ';
					$sql .= '\'%"'.$item.'"%\'';
				}
				$objSuche = \Database::getInstance()->prepare('SELECT * FROM tl_adressen WHERE '.$sql.' ORDER BY nachname ASC, vorname ASC')
				                                    ->execute();
			}
			elseif($this->suchbegriff && $this->funktion)
			{
				// Suchbegriff vorhanden und auch mind. eine Funktion
				// Funktionen-SQL bauen
				$sql = '';
				foreach($this->funktion as $item)
				{
					if($sql) $sql .= ' '.$this->linken.' funktionen LIKE ';
					else $sql .= 'funktionen LIKE ';
					$sql .= '\'%"'.$item.'"%\'';
				}
				$objSuche = \Database::getInstance()->prepare('SELECT * FROM tl_adressen WHERE searchstring LIKE ? AND ('.$sql.') ORDER BY nachname ASC, vorname ASC')
				                                    ->execute($s);
			}

			$daten = array();
			if($objSuche->numRows)
			{
				// Datensätze anzeigen
				while($objSuche->next())
				{
					if($objSuche->telefon1 || $objSuche->telefon2 || $objSuche->telefon3 || $objSuche->telefon4) $telefon = true;
					else $telefon = false;
					if($objSuche->email1 || $objSuche->email2 || $objSuche->email3 || $objSuche->email4 || $objSuche->email5 || $objSuche->email6) $email = true;
					else $email = false;
					$daten[] = array
					(
						'nachname'    => $objSuche->nachname,
						'vorname'     => $objSuche->vorname,
						'titel'       => $objSuche->titel,
						'firma'       => $objSuche->firma,
						'plz'         => $objSuche->plz,
						'ort'         => $objSuche->ort,
						'strasse'     => $objSuche->strasse,
						'email'       => $email,
						'email1'      => $objSuche->email1,
						'email2'      => $objSuche->email2,
						'email3'      => $objSuche->email3,
						'email4'      => $objSuche->email4,
						'email5'      => $objSuche->email5,
						'email6'      => $objSuche->email6,
						'telefon'     => $telefon,
						'telefon1'    => $objSuche->telefon1,
						'telefon2'    => $objSuche->telefon2,
						'telefon3'    => $objSuche->telefon3,
						'telefon4'    => $objSuche->telefon4,
						'telefon1sel' => $this->Telefonlink($objSuche->telefon1),
						'telefon2sel' => $this->Telefonlink($objSuche->telefon2),
						'telefon3sel' => $this->Telefonlink($objSuche->telefon3),
						'telefon4sel' => $this->Telefonlink($objSuche->telefon4),
						'homepage'    => $objSuche->homepage,
						'info'        => $objSuche->info,
						'text'        => $objSuche->text,
						'deaktiviert' => $objSuche->aktiv ? '' : 'deaktiviert ',
						'unverlinkt'  => $objSuche->links ? '' : 'unverlinkt ',
					);
				}
			}
			//print_r($daten);
			$this->Template->SuchbegriffModifiziert = $s;
			$this->Template->Ergebnisliste = $daten;
			$this->Template->Gesucht = true;
		}
		else
		{
			$this->Template->Gesucht = false;
		}

		// Weitere Templatevariablen
		$this->Template->Suchbegriff = $this->suchbegriff;
		$this->Template->Funktionen = \Schachbulle\ContaoAdressenBundle\Classes\Funktionen::getFunktionen(false);
		$this->Template->Funktionsauswahl = $this->funktion;
		$this->Template->Verknuepfung = $this->linken;
		$this->Template->Liteversion = $this->liteversion;

		return;
	}

	/**
	 * Konvertiert eine Telefonnummer in einen mobilfähigen Link
	 * Aus z.B. +43 (0)699 11112222 wird +4369911112222
	 */
	public function Telefonlink($nummer)
	{
		$nummer = trim($nummer);
		if($nummer == '') return $nummer;
		
		// Zeichen in ihre ursprüngliche Form umwandeln
		$nummer = html_entity_decode($nummer);
		// Sonderzeichen und Buchstaben entfernen
		$nummer = str_replace('(0)','',$nummer);
		$nummer = preg_replace('/[^0-9]/','',$nummer);
		// Prüfen auf führende 0
		if(substr($nummer,0,1) == '0')
		{
			// Prüfen auf 0 an zweiter Stelle
			if(substr($nummer,0,2) == '0')
			{
				// Durch + ersetzen
				$nummer = '+'.substr($nummer,2);
			}
			else
			{
				// Durch +49 ersetzen
				$nummer = '+49'.substr($nummer,1);
			}
		}
		else
		{
			// Keine 0 am Anfang, dann wohl Ländervorwahl
			$nummer = '+'.$nummer;
		}
		return $nummer;
	}

}
