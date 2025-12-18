<?php

namespace Schachbulle\ContaoAdressenBundle\Classes;

/*
 */

class Wertungsreferenten extends \Contao\Module
{

	var $strTemplate = 'adresse_referenten';
	var $subTemplate = 'adresse_default';
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \Contao\BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ADRESSEN WERTUNGSREFERENTEN ###';
			//$objTemplate->title = $this->name;
			//$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		
		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$this->import('Database');

		$objAdressen = $this->Database->prepare('SELECT * FROM tl_adressen WHERE aktiv = ?')
		                              ->execute(1, '');

		$referenten = array();
		$content = '';
		
		$bezirksname = $GLOBALS['TL_LANG']['tl_adressen']['verbaende']; // Namen der Bezirke/Verbände
		$bezirksadresse = array(); // zugehörige Adressen
		
		if($objAdressen->numRows > 1)
		{
			while($objAdressen->next()) 
			{
				// Wertungsbezirke extrahieren
				if($objAdressen->wertungsreferent)
				{
					$bezirke = unserialize($objAdressen->wertungsreferent);
				}
				else
				{
					$bezirke = array();
				}
				
				if($bezirke)
				{
					foreach($bezirke as $bezirk)
					{
						// Adresse dem Bezirk zuweisen
						$this->Adresstemplate = new \Contao\FrontendTemplate($this->subTemplate);
						$temp = '<div class="ce_adressen">';
						$temp .= '<h3>'.$bezirksname[$bezirk].'</h3>';
						$temp .= $this->FormatiereAdresse($objAdressen, $this->Adresstemplate);
						$temp .= '</div>';
						$bezirksadresse[$bezirk] = $temp;
					}
				}
			}

		}

		// Daten übertragen
		$content = '';
		foreach($bezirksname as $key => $value)
		{
			$content .= $bezirksadresse[$key];
		}	
		$this->Template->daten = $content;

	}

	function FormatiereAdresse($data, $template)
	{
		
		$content = '';
		
		// Name zusammenbauen
		$template->name = $data->nachname;
		if($data->vorname) $template->name = $data->vorname." ".$template->name;
		if($data->titel) $template->name = $data->titel." ".$template->name;

		// Visitenkarte zusammenbauen
		if($objAdresse->text)
		{
			$template->visitenkarte = str_replace("\r\n","<br />",$data->text);
			$template->visitenkarte = str_replace("\n","<br />",$template->visitenkarte);
			$template->visitenkarte = str_replace('"',"&quot;",$template->visitenkarte);
		}

		// Adresse zusammenbauen
		if($data->ort_view && $data->ort) $template->adresse = $data->ort;
		if($data->ort_view && $data->plz) $template->adresse = $data->plz." ".$template->adresse;
		if($data->strasse_view && $data->ort_view && $data->strasse) $template->adresse = $data->strasse.", ".$template->adresse;

		// Telefon-Arrays erstellen
		if($data->telefon_view)
		{
			$telefon = array();
			$telefon_fest = array();
			$telefon_mobil = array();
			if($data->telefon1) 
			{
				$telefon[] = $data->telefon1;
				(\Schachbulle\ContaoAdressenBundle\ContentElements\Adresse::Mobilfunk($data->telefon1)) ? $telefon_mobil[] = $data->telefon1 : $telefon_fest[] = $data->telefon1;
			}
			if($data->telefon2) 
			{
				$telefon[] = $data->telefon2;
				(\Schachbulle\ContaoAdressenBundle\ContentElements\Adresse::Mobilfunk($data->telefon2)) ? $telefon_mobil[] = $data->telefon2 : $telefon_fest[] = $data->telefon2;
			}
			if($data->telefon3) 
			{
				$telefon[] = $data->telefon3;
				(\Schachbulle\ContaoAdressenBundle\ContentElements\Adresse::Mobilfunk($data->telefon3)) ? $telefon_mobil[] = $data->telefon3 : $telefon_fest[] = $data->telefon3;
			}
			if($data->telefon4) 
			{
				$telefon[] = $data->telefon4;
				(\Schachbulle\ContaoAdressenBundle\ContentElements\Adresse::Mobilfunk($data->telefon4)) ? $telefon_mobil[] = $data->telefon4 : $telefon_fest[] = $data->telefon4;
			}
		}

		// Telefax-Array erstellen
		if($data->telefax_view)
		{
			$telefax = array();
			if($data->telefax1) $telefax[] = $data->telefax1;
			if($data->telefax2) $telefax[] = $data->telefax2;
		}

		// Email-Array erstellen
		if($data->email_view)
		{
			$email = array();
			if($data->email1) $email[] = $data->email1;
			if($data->email2) $email[] = $data->email2;
			if($data->email3) $email[] = $data->email3;
			if($data->email4) $email[] = $data->email4;
			if($data->email5) $email[] = $data->email5;
			if($data->email6) $email[] = $data->email6;
		}

		// Bild-Elemente erstellen
		if($this->adresse_viewfoto)
		{
			// Fotoausgabe erwünscht, jetzt die Quelle bestimmen
			if($this->addImage && $this->singleSRC != '')
			{
				// Bild aus Inhaltselement verwenden!
				if($this->singleSRC)
				{
					(version_compare(VERSION, '3.2', '>=')) ? $objModel = \Contao\FilesModel::findByUuid($this->singleSRC) : $objModel = \FilesModel::findByPk($this->singleSRC);
					if($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
					{
						$bildurl = $objModel->path;
						$bildarray['singleSRC'] = $bildurl;
						$bildarray['alt'] = $this->alt;
						$bildarray['size'] = $this->size;
						$bildarray['imagemargin'] = $this->imagemargin;
						$bildarray['imageUrl'] = $this->imageUrl;
						$bildarray['fullsize'] = $this->fullsize;
						$bildarray['caption'] = $this->caption;
						$bildarray['floating'] = $this->floating;
						// Templatewerte des Bildes von Contao zusammenbauen lassen
						$this->addImageToTemplate($template, $bildarray); 
					}
				}
			}
			elseif($data->addImage && $data->singleSRC != '')
			{
				// Bild aus tl_adressen verwenden!
				if($data->singleSRC)
				{
					(version_compare(VERSION, '3.2', '>=')) ? $objModel = \Contao\FilesModel::findByUuid($data->singleSRC) : $objModel = \FilesModel::findByPk($data->singleSRC);
					if($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
					{
						$bildurl = $objModel->path;
						$data->singleSRC = $bildurl;
						// Templatewerte des Bildes von Contao zusammenbauen lassen
						$this->addImageToTemplate($template, $data->row()); 
					}
				}
			}
		}

		// Daten aus tl_adressen in das Template schreiben
		$template->id            = $data->id;
		$template->nachname      = $data->nachname;
		$template->vorname       = $data->vorname ;
		$template->titel         = $data->titel   ;
		$template->firma         = $data->firma   ;
		$template->strasse       = $data->strasse ;
		$template->plz           = $data->plz     ;
		$template->ort           = $data->ort     ;
		$template->telefon       = $telefon;
		$template->telefon_fest  = $telefon_fest;
		$template->telefon_mobil = $telefon_mobil;
		$template->telefax       = $telefax;
		$template->email         = $email;
		$template->homepage      = $data->homepage;
		$template->facebook      = $data->facebook;
		$template->twitter       = $data->twitter ;
		$template->google        = $data->google  ;
		$template->icq           = $data->icq     ;
		$template->yahoo         = $data->yahoo   ;
		$template->aim           = $data->aim     ;
		$template->msn           = $data->msn     ;
		$template->irc           = $data->irc     ;
		$template->viewfoto      = true;
		$template->text          = $data->text    ;
		$template->info          = $data->info    ;
		$template->aktiv         = $data->aktiv   ;

		// Standardbildausrichtung setzen
		if(!$template->floatClass) $template->floatClass = 'float_left'; 
		
		return $template->parse();
		
	}

}
