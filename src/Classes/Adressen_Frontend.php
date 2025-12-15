<?php

namespace Schachbulle\ContaoAdressenBundle\Classes;

/*
 * Ersetzt den Tag {{adresse::ID}} bzw. {{adresse::ID::Funktion}}
 * durch die entsprechende Adresse aus tl_adressen
 */

class Adressen_Frontend extends \Contao\Frontend
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_adressen_inserttag';

	public function adresse_ersetzen($strTag)
	{

		$arrSplit = explode('::', $strTag);

		if($arrSplit[0] == 'adresse' || $arrSplit[0] == 'cache_adresse')
		{
			// Foto standardmäßig einschalten
			$fotosichtbar = true;
			$fotobreite = 0;
			$fotohoehe = 0;

			// Template-Objekt erzeugen
			$this->Template = new \Contao\FrontendTemplate($this->strTemplate);

			// Template-Variablen initialisieren
			$this->Template->funktion     = "";
			$this->Template->funktioninfo = "";
			$this->Template->id           = "";
			$this->Template->nachname     = "";
			$this->Template->vorname      = "";
			$this->Template->titel        = "";
			$this->Template->name         = "";
			$this->Template->firma        = "";
			$this->Template->adresse      = "";
			$this->Template->strasse      = "";
			$this->Template->plz          = "";
			$this->Template->ort          = "";
			$this->Template->telefon      = "";
			$this->Template->telefon1     = "";
			$this->Template->telefon2     = "";
			$this->Template->telefon3     = "";
			$this->Template->telefon4     = "";
			$this->Template->handy        = "";
			$this->Template->telefax      = "";
			$this->Template->telefax1     = "";
			$this->Template->telefax2     = "";
			$this->Template->email        = "";
			$this->Template->email1       = "";
			$this->Template->email2       = "";
			$this->Template->email3       = "";
			$this->Template->email4       = "";
			$this->Template->email5       = "";
			$this->Template->email6       = "";
			$this->Template->homepage     = "";
			$this->Template->facebook     = "";
			$this->Template->twitter      = "";
			$this->Template->google       = "";
			$this->Template->icq          = "";
			$this->Template->yahoo        = "";
			$this->Template->aim          = "";
			$this->Template->msn          = "";
			$this->Template->irc          = "";
			$this->Template->bild         = "";
			$this->Template->bildurl      = "";
			$this->Template->thumburl     = "";
			$this->Template->text         = "";
			$this->Template->info         = "";
			$this->Template->visitenkarte = "";
			$this->Template->aktiv        = "";

			// Spezialparameter foto sichern und diesen dann entfernen
			for($x=1;$x<count($arrSplit);$x++)
			{
				if(substr($arrSplit[$x],0,5) == "foto=")
				{
					$temp = explode(",",substr($arrSplit[$x],5));
					array_splice($arrSplit,$x,1);
					// Wenn Parameter 0 = 0 dann Foto abschalten
					if(!$temp[0]) $fotosichtbar = false;
					if($temp[0]) $fotobreite = $temp[0];
					if($temp[1]) $fotohoehe = $temp[1];
					break;
				}
			}

			// Funktion separat zusammenbauen, da allgemeingültig
			if(isset($arrSplit[2])) $this->Template->funktion = $arrSplit[2];
			// Funktionsinfo zusammenbauen, nicht allgemeingültig
			if(isset($arrSplit[3])) $this->Template->funktioninfo = $arrSplit[3];

			if(isset($arrSplit[1]))
			{
				// Adresse laden
				$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen WHERE id=?")->execute($arrSplit[1]);

				if($objAdresse->id)
				{
					// Bild zusammenbauen, Thumbnail generieren
					if($fotosichtbar && $objAdresse->addBild && $objAdresse->bild != '')
					{
						if(is_numeric($objAdresse->bild))
						{
							$objModel = \Contao\FilesModel::findByPk($objAdresse->bild);
							if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
							{
								if($fotobreite && $fotohoehe)
								{
									// Spezialmodus, im Tag stehen eigene Fotomaße
									$ausrichtung = 'center_center';
									$breite = $fotobreite;
									$hoehe = $fotohoehe;
								}
								else
								{
									// Standardmodus, Ausrichtung ermitteln
									($objAdresse->size) ? ($ausrichtung = $objAdresse->size) : ($ausrichtung = 'center_center');
									(isset($objAdresse->prozentx)) ? ($ausrichtungx = $objAdresse->prozentx) : ($ausrichtungx = 50);
									(isset($objAdresse->prozenty)) ? ($ausrichtungy = $objAdresse->prozenty) : ($ausrichtungy = 50);
									$ausrichtung = $ausrichtungx."_".$ausrichtungy;
									$breite = 80;
									$hoehe = 56;
								}
								$this->Template->bildurl = $objModel->path;
								$this->Template->thumburl = $this->getImage($this->urlEncode($objModel->path),$breite,$hoehe,$ausrichtung);
							}
						}
					}
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
					if(trim($this->Template->adresse) != '')
					{
						$this->Template->adresse = '<a class="google" title="Adresse in Googlemap suchen" href="http://maps.google.de/maps?hl=de&t=h&iwloc=addr&q='.$this->Template->adresse.'" target="_blank">'.$this->Template->adresse.'</a>';
					}
					// Telefon zusammenbauen, Festnetz/Mobil separat
					if($objAdresse->telefon_view)
					{
						if($objAdresse->telefon1)
						{
							if($this->Handy($objAdresse->telefon1)) $this->Template->handy .= $objAdresse->telefon1;
							else $this->Template->telefon .= $objAdresse->telefon1;
						}
						if($objAdresse->telefon2)
						{
							if($this->Handy($objAdresse->telefon2)) ($this->Template->handy) ? ($this->Template->handy .=  ", ".$objAdresse->telefon2) : ($this->Template->handy .=  $objAdresse->telefon2);
							else ($this->Template->telefon) ? ($this->Template->telefon .=  ", ".$objAdresse->telefon2) : ($this->Template->telefon .=  $objAdresse->telefon2);
						}
						if($objAdresse->telefon3)
						{
							if($this->Handy($objAdresse->telefon3)) ($this->Template->handy) ? ($this->Template->handy .=  ", ".$objAdresse->telefon3) : ($this->Template->handy .=  $objAdresse->telefon3);
							else ($this->Template->telefon) ? ($this->Template->telefon .=  ", ".$objAdresse->telefon3) : ($this->Template->telefon .=  $objAdresse->telefon3);
						}
						if($objAdresse->telefon4)
						{
							if($this->Handy($objAdresse->telefon4)) ($this->Template->handy) ? ($this->Template->handy .=  ", ".$objAdresse->telefon4) : ($this->Template->handy .=  $objAdresse->telefon4);
							else ($this->Template->telefon) ? ($this->Template->telefon .=  ", ".$objAdresse->telefon4) : ($this->Template->telefon .=  $objAdresse->telefon4);
						}
					}
					// Telefax zusammenbauen
					if($objAdresse->telefax_view)
					{
						if($objAdresse->telefax1)
						{
							$this->Template->telefax .= $objAdresse->telefax1;
						}
						if($objAdresse->telefax2)
						{
							($this->Template->telefax) ? ($this->Template->telefax .=  ", ".$objAdresse->telefax2) : ($this->Template->telefax .=  $objAdresse->telefax2);
						}
					}
					// Email zusammenbauen
					if($objAdresse->email_view)
					{
						if($objAdresse->email1) $this->Template->email .= $this->replaceInsertTags("{{email::".$objAdresse->email1."}}");
						if($objAdresse->email2) ($this->Template->email) ? ($this->Template->email .= ", ".$this->replaceInsertTags("{{email::".$objAdresse->email2."}}")) : ($this->Template->email .= $this->replaceInsertTags("{{email::".$objAdresse->email2."}}"));
						if($objAdresse->email3) ($this->Template->email) ? ($this->Template->email .= ", ".$this->replaceInsertTags("{{email::".$objAdresse->email3."}}")) : ($this->Template->email .= $this->replaceInsertTags("{{email::".$objAdresse->email3."}}"));
						if($objAdresse->email4) ($this->Template->email) ? ($this->Template->email .= ", ".$this->replaceInsertTags("{{email::".$objAdresse->email4."}}")) : ($this->Template->email .= $this->replaceInsertTags("{{email::".$objAdresse->email4."}}"));
						if($objAdresse->email5) ($this->Template->email) ? ($this->Template->email .= ", ".$this->replaceInsertTags("{{email::".$objAdresse->email5."}}")) : ($this->Template->email .= $this->replaceInsertTags("{{email::".$objAdresse->email5."}}"));
						if($objAdresse->email6) ($this->Template->email) ? ($this->Template->email .= ", ".$this->replaceInsertTags("{{email::".$objAdresse->email6."}}")) : ($this->Template->email .= $this->replaceInsertTags("{{email::".$objAdresse->email6."}}"));
					}
					// Restliche Daten in das Template schreiben
					$this->Template->id       = $objAdresse->id      ;
					$this->Template->nachname = $objAdresse->nachname;
					$this->Template->vorname  = $objAdresse->vorname ;
					$this->Template->titel    = $objAdresse->titel   ;
					$this->Template->firma    = $objAdresse->firma   ;
					$this->Template->strasse  = $objAdresse->strasse ;
					$this->Template->plz      = $objAdresse->plz     ;
					$this->Template->ort      = $objAdresse->ort     ;
					$this->Template->telefon1 = $objAdresse->telefon1;
					$this->Template->telefon2 = $objAdresse->telefon2;
					$this->Template->telefon3 = $objAdresse->telefon3;
					$this->Template->telefon4 = $objAdresse->telefon4;
					$this->Template->telefax1 = $objAdresse->telefax1;
					$this->Template->telefax2 = $objAdresse->telefax2;
					$this->Template->email1   = $objAdresse->email1  ;
					$this->Template->email2   = $objAdresse->email2  ;
					$this->Template->email3   = $objAdresse->email3  ;
					$this->Template->email4   = $objAdresse->email4  ;
					$this->Template->email5   = $objAdresse->email5  ;
					$this->Template->email6   = $objAdresse->email6  ;
					$this->Template->homepage = $objAdresse->homepage;
					$this->Template->facebook = $objAdresse->facebook;
					$this->Template->twitter  = $objAdresse->twitter ;
					$this->Template->google   = $objAdresse->google  ;
					$this->Template->icq      = $objAdresse->icq     ;
					$this->Template->yahoo    = $objAdresse->yahoo   ;
					$this->Template->aim      = $objAdresse->aim     ;
					$this->Template->msn      = $objAdresse->msn     ;
					$this->Template->irc      = $objAdresse->irc     ;
					$this->Template->bild     = $objAdresse->bild    ;
					$this->Template->text     = $objAdresse->text    ;
					$this->Template->info     = $objAdresse->info    ;
					$this->Template->aktiv    = $objAdresse->aktiv   ;
				}
			}
			return $this->Template->parse();
		}
		// nicht unser Insert-Tag
		return false;
	}

	protected function Handy($nummer)
	{

		$vorwahl = substr($nummer,0,4);
		switch($vorwahl)
		{
			case "0151":
			case "0163":
			case "0170":
			case "0171":
			case "0172":
			case "0173":
			case "0174":
			case "0175":
			case "0176":
			case "0177":
			return TRUE;
			default: return FALSE;
		}
	}

}
