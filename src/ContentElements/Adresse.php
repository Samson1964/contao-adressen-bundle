<?php

namespace Schachbulle\ContaoAdressenBundle\ContentElements;

class Adresse extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_adressen_default';

	/**
	 * Generate the module
	 */
	protected function compile()
	{
	
		// Adresse aus Datenbank laden, wenn ID übergeben wurde
		if($this->adresse_id)
		{
			$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen WHERE id=?")->execute($this->adresse_id);

			// Adresse gefunden
			if($objAdresse)
			{
				// Alternatives Template zuweisen
				if($this->adresse_alttemplate) $this->Template = new \FrontendTemplate($this->adresse_tpl);

				// Name zusammenbauen
				$this->Template->name = $objAdresse->nachname;
				if($objAdresse->vorname) $this->Template->name = $objAdresse->vorname." ".$this->Template->name;
				if($objAdresse->titel) $this->Template->name = $objAdresse->titel." ".$this->Template->name;

				// Visitenkarte zusammenbauen
				if($objAdresse->text)
				{
					$this->Template->visitenkarte = str_replace("\r\n","<br />",$objAdresse->text);
					$this->Template->visitenkarte = str_replace("\n","<br />",$this->Template->visitenkarte);
					$this->Template->visitenkarte = str_replace('"',"&quot;",$this->Template->visitenkarte);
				}

				// Adresse zusammenbauen
				if($objAdresse->ort_view && $objAdresse->ort) $this->Template->adresse = $objAdresse->ort;
				if($objAdresse->ort_view && $objAdresse->plz) $this->Template->adresse = $objAdresse->plz." ".$this->Template->adresse;
				if($objAdresse->strasse_view && $objAdresse->ort_view && $objAdresse->strasse) $this->Template->adresse = $objAdresse->strasse.", ".$this->Template->adresse;

				// Telefon-Arrays erstellen
				if($objAdresse->telefon_view)
				{
					$telefon = array();
					$telefon_fest = array();
					$telefon_mobil = array();
					if($objAdresse->telefon1) 
					{
						$telefon[] = $objAdresse->telefon1;
						($this->Mobilfunk($objAdresse->telefon1)) ? $telefon_mobil[] = $objAdresse->telefon1 : $telefon_fest[] = $objAdresse->telefon1;
					}
					if($objAdresse->telefon2) 
					{
						$telefon[] = $objAdresse->telefon2;
						($this->Mobilfunk($objAdresse->telefon2)) ? $telefon_mobil[] = $objAdresse->telefon2 : $telefon_fest[] = $objAdresse->telefon2;
					}
					if($objAdresse->telefon3) 
					{
						$telefon[] = $objAdresse->telefon3;
						($this->Mobilfunk($objAdresse->telefon3)) ? $telefon_mobil[] = $objAdresse->telefon3 : $telefon_fest[] = $objAdresse->telefon3;
					}
					if($objAdresse->telefon4) 
					{
						$telefon[] = $objAdresse->telefon4;
						($this->Mobilfunk($objAdresse->telefon4)) ? $telefon_mobil[] = $objAdresse->telefon4 : $telefon_fest[] = $objAdresse->telefon4;
					}
				}

				// Telefax-Array erstellen
				if($objAdresse->telefax_view)
				{
					$telefax = array();
					if($objAdresse->telefax1) $telefax[] = $objAdresse->telefax1;
					if($objAdresse->telefax2) $telefax[] = $objAdresse->telefax2;
				}

				// Erlaubte E-Mail-Adressen feststellen
				$mailErlaubtArr = array(1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true);
				if($this->adresse_selectmails)
				{
					$erlaubtArr = unserialize($this->adresse_mails);
					if(is_array($erlaubtArr))
					{
						for($x=1; $x<=6; $x++)
						{
							if(in_array($x, $erlaubtArr) == false) 
							{
								$mailErlaubtArr[$x] = false;
							}
						}
					}
				}
					
				// Email-Array erstellen
				if($objAdresse->email_view)
				{
					$email = array();
					if($objAdresse->email1 && $mailErlaubtArr[1]) $email[] = $objAdresse->email1;
					if($objAdresse->email2 && $mailErlaubtArr[2]) $email[] = $objAdresse->email2;
					if($objAdresse->email3 && $mailErlaubtArr[3]) $email[] = $objAdresse->email3;
					if($objAdresse->email4 && $mailErlaubtArr[4]) $email[] = $objAdresse->email4;
					if($objAdresse->email5 && $mailErlaubtArr[5]) $email[] = $objAdresse->email5;
					if($objAdresse->email6 && $mailErlaubtArr[6]) $email[] = $objAdresse->email6;
				}

				// Bild extrahieren
				if($this->singleSRC)
				{
					// Ersatzfoto im Inhaltselement definiert
					$objFile = \FilesModel::findByUuid($this->singleSRC);
				}
				elseif($objAdresse->singleSRC)
				{
					// Foto aus tl_adressen
					$objFile = \FilesModel::findByPk($objAdresse->singleSRC);
				}
				else
				{
					// Standardfoto
					$objFile = \FilesModel::findByUuid($GLOBALS['TL_CONFIG']['adressen_defaultImage']);
				}
				$objBild = new \stdClass();
				\Controller::addImageToTemplate($objBild, array('singleSRC' => $objFile->path, 'size' => unserialize($GLOBALS['TL_CONFIG']['adressen_ImageSize'])), \Config::get('maxImageWidth'), null, $objFile);

				// Daten aus tl_adressen in das Template schreiben
				$this->Template->id            = $objAdresse->id;
				$this->Template->nachname      = $objAdresse->nachname;
				$this->Template->vorname       = $objAdresse->vorname;
				$this->Template->titel         = $objAdresse->titel;
				$this->Template->firma         = $objAdresse->firma;
				$this->Template->adressen      = $this->getAdressen($objAdresse); // Übergabe der Adressen im alten und neuen Format
				$this->Template->strasse       = $objAdresse->strasse;
				$this->Template->plz           = $objAdresse->plz;
				$this->Template->ort           = $objAdresse->ort;
				$this->Template->telefon       = $telefon;
				$this->Template->telefon_fest  = $telefon_fest;
				$this->Template->telefon_mobil = $telefon_mobil;
				$this->Template->telefax       = $telefax;
				$this->Template->email         = $email;
				$this->Template->homepage      = $objAdresse->homepage;
				$this->Template->facebook      = $objAdresse->facebook;
				$this->Template->twitter       = $objAdresse->twitter;
				$this->Template->instagram     = $objAdresse->instagram;
				$this->Template->whatsapp      = $objAdresse->whatsapp;
				$this->Template->threema       = $objAdresse->threema;
				$this->Template->telegram      = $objAdresse->telegram;
				$this->Template->skype         = $objAdresse->skype;
				$this->Template->irc           = $objAdresse->irc;
				$this->Template->text          = $objAdresse->text;
				$this->Template->info          = $objAdresse->info;
				$this->Template->aktiv         = $objAdresse->aktiv;

				// Foto
				$this->Template->viewFoto      = $this->adresse_viewFoto;
				$this->Template->image         = $objBild->singleSRC;
				$this->Template->imageSize     = $objBild->imgSize;
				$this->Template->imageTitle    = $objBild->imageTitle;
				$this->Template->imageAlt      = $objBild->alt;
				$this->Template->imageCaption  = $objBild->caption;
				$this->Template->thumbnail     = $objBild->src;

				// Daten aus tl_content in das Template schreiben
				$this->Template->funktion      = $this->adresse_funktion;
				$this->Template->zusatz        = $this->adresse_zusatz;
			}
		}

		return;

	}

	/**
	 * Funktion getAdressen
	 * @param $objekt: Objekt mit dem Datensatz aus der Datenbank
	 * @return: öffentliche Adressen als HTML-String
	 */
	static function getAdressen($objekt)
	{

		$return = ''; // Rückgabe aller Adressen im HTML-Format
		$prefix = '<div class="adr_adresse">'; // Wird vor die Adresse gesetzt
		$google = array
		(
			0 => '<a class="google" target="_blank" href="https://maps.google.de/maps?hl=de&t=h&iwloc=addr&q=',
			1 => '" title="Adresse in Googlemap suchen">',
			2 => '</a>'
		); // Link GoogleMap. Adresse wird zwischen 0 und 1 sowie 1 und 2 eingesetzt
		$suffix = '</div>'; // Wird an die Adresse angehangen

		// Adresse (altes Format) zusammenbauen, wenn etwas angezeigt werden soll
		if($objekt->ort_view)
		{
			$adresse = ''; // Speichert die reine Adresse
			// PLZ und Ort darf angezeigt werden
			if($objekt->strasse_view)
			{
				// Straße darf angezeigt werden
				$adresse .= $objekt->strasse ? $objekt->strasse.', ' : $adresse;
			}
			$adresse .= $objekt->plz ? $objekt->plz : '';
			$adresse .= $objekt->ort ? ' '.$objekt->ort : '';
			// Adresse speichern für Rückgabe
			$return = $prefix.$google[0].$adresse.$google[1].$adresse.$google[2].$suffix;
		}

		// Adressen (neues Format) zusammenbauen
		$dataArr = unserialize($objekt->adressen);
		if($dataArr)
		{
			foreach($dataArr as $data)
			{
				$adresse = '';
				if($data['public_plzort'])
				{
					// PLZ und Ort darf angezeigt werden
					if($data['public_str'])
					{
						// Straße darf angezeigt werden
						$adresse .= $data['strasse'] ? $data['strasse'].', ' : $adresse;
					}
					$adresse .= $data['plz'] ? $data['plz'] : '';
					$adresse .= $data['ort'] ? ' '.$data['ort'] : '';
					// Adresse speichern für Rückgabe
					if($data['googlemap'] && $adresse) $return .= $prefix.$google[0].$adresse.$google[1].$adresse.$google[2].$suffix;
					elseif($adresse) $return .= $prefix.$adresse.$suffix;
				}
			}
		}

		return $return;

	}

	/**
	 * Funktion Mobilnetz
	 * @param: zu prüfende Nummer
	 * @return: true = ja, Mobilfunknummer
	 */
	static function Mobilfunk($nummer)
	{

		$vorwahl = substr($nummer,0,4);
		switch($vorwahl)
		{
			case '0150':
			case '0151':
			case '0152':
			case '0155':
			case '0156':
			case '0157':
			case '0159':
			case '0160':
			case '0161':
			case '0162':
			case '0163':
			case '0170':
			case '0171':
			case '0172':
			case '0173':
			case '0174':
			case '0175':
			case '0176':
			case '0177':
			case '0178':
			case '0179':
				return true;
			default: 
				return false;
		}
	}

}