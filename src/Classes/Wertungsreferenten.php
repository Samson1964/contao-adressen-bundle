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
		
		$bezirksname = $this->getReferenten(); // Namen der Bezirke/Verbände
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
	
	public function getReferenten()
	{
		// Referate zuordnen
		$array = array
		(
			'00000' => '00000 Deutscher Schachbund',
			'10000' => '10000 Badischer Schachverband',
			'10100' => '10100 Mannheim',
			'10200' => '10200 Heidelberg',
			'10300' => '10300 Odenwald',
			'10400' => '10400 Karlsruhe',
			'10500' => '10500 Pforzheim',
			'10600' => '10600 Mittelbaden',
			'10700' => '10700 Ortenau',
			'10800' => '10800 Freiburg',
			'10900' => '10900 Hochrhein',
			'10A00' => '10A00 Schwarzwald',
			'10B00' => '10B00 Bodensee',
			'20000' => '20000 Bayerischer Schachbund e.V.',
			'21000' => '21000 Mittelfranken',
			'21100' => '21100 Mittelfranken-Mitte',
			'21200' => '21200 Mittelfranken-Nord',
			'21300' => '21300 Mittelfranken-Ost',
			'21400' => '21400 Mittelfranken-Süd',
			'21500' => '21500 Mittelfranken-West',
			'22000' => '22000 München',
			'23000' => '23000 Niederbayern',
			'24000' => '24000 BV Oberbayern e.V.',
			'24100' => '24100 Schachkreis IN-FS',
			'24200' => '24200 Schachkreis Inn-Chiemgau',
			'24400' => '24400 Schachkreis Zugspitze',
			'25000' => '25000 BV Oberfranken',
			'25100' => '25100 Kreisverband Bamberg',
			'25200' => '25200 Kreisverband Bayreuth',
			'25300' => '25300 Kreisverband Hof',
			'25400' => '25400 Kreisverband Coburg/Neustadt',
			'25500' => '25500 Kreisverband Marktredwitz',
			'25600' => '25600 Kreisverband Lichtenfels/Kronach',
			'26000' => '26000 Schachverband Oberpfalz e.V.',
			'27000' => '27000 Schwaben',
			'27100' => '27100 Augsburg',
			'27200' => '27200 Mittelschwaben',
			'27300' => '27300 Nordschwaben',
			'27400' => '27400 Südschwaben',
			'28000' => '28000 Unterfranken e.V',
			'28100' => '28100 Spessart/Untermain',
			'28200' => '28200 Mainspessart',
			'28300' => '28300 Haßberge-Rhön',
			'28400' => '28400 Maindreieck',
			'30000' => '30000 Berliner Schachverband',
			'40000' => '40000 Hamburger Schachverband',
			'50000' => '50000 Hessischer Schachverband',
			'51000' => '51000 Kassel-Nordhessen',
			'52000' => '52000 Osthessen',
			'53000' => '53000 Lahn-Eder',
			'54000' => '54000 Main-Vogelsberg',
			'55000' => '55000 Frankfurt',
			'56000' => '56000 Starkenburg',
			'57000' => '57000 Main-Taunus',
			'58000' => '58000 Rhein-Taunus',
			'59000' => '59000 Lahn',
			'5A000' => '5A000 Bergstraße',
			'60000' => '60000 Schachbund Nordrhein-Westfalen e.V.',
			'61000' => '61000 SV Ruhrgebiet e.V.',
			'61100' => '61100 Schachbezirk Bochum',
			'61200' => '61200 Schachgemeinschaft Dortmund',
			'61300' => '61300 Schachbezirk Essen',
			'61400' => '61400 Schachbezirk Emscher-Lippe',
			'61500' => '61500 Schachbezirk Hamm',
			'61600' => '61600 Mülheim an der Ruhr 1922 e.V.',
			'61700' => '61700 Schachbezirk Herne - Vest',
			'62000' => '62000 Niederrheinischer Schachverband 1901 e.V.',
			'62100' => '62100 Schachbezirk Bergisch-Land',
			'62200' => '62200 Schachbezirk Düsseldorf',
			'62300' => '62300 Schachbezirk Duisburg',
			'62400' => '62400 Linker Niederrhein',
			'62500' => '62500 Schachbezirk Kreis Wesel e.V.',
			'63000' => '63000 Schachverband Südwestfalen',
			'63200' => '63200 Schachbezirk Iserlohn',
			'63300' => '63300 Schachbezirk Oberberg',
			'63400' => '63400 Schachbezirk Hochsauerland',
			'63500' => '63500 Schachbezirk Sauerland',
			'63600' => '63600 Schachbezirk Siegerland',
			'64000' => '64000 Schachverband Ostwestfalen-Lippe',
			'64100' => '64100 Schachbezirk Bielefeld',
			'64200' => '64200 Schachbezirk Hellweg',
			'64300' => '64300 Schachbezirk Lippe',
			'64400' => '64400 Schachbezirk Porta',
			'64500' => '64500 Schachbezirk Teutoburger Wald-West',
			'65000' => '65000 Schachverband Münsterland',
			'65100' => '65100 Schachbezirk Steinfurt',
			'65200' => '65200 Schachbezirk Borken',
			'65300' => '65300 Schachbezirk Münster',
			'66000' => '66000 Schachverband Mittelrhein e.V.',
			'66100' => '66100 Aachener Schachverband 1928 e.V.',
			'66200' => '66200 Bonn/Rhein-Sieg e.V.',
			'66300' => '66300 Kölner Schachverband von 1920 e.V.',
			'66400' => '66400 Schachbezirk Rur-Erft',
			'66500' => '66500 Schachbezirk Rhein-Wupper',
			'70000' => '70000 Niedersächsischer Schachverband e. V.',
			'70100' => '70100 Bezirk 1 Hannover',
			'70200' => '70200 Bezirk 2 Braunschweig',
			'70300' => '70300 Bezirk 3 Südniedersachsen',
			'70400' => '70400 Bezirk 4 Lüneburg',
			'70500' => '70500 Bezirk 5 Oldenburg-Ostfriesland',
			'70600' => '70600 Bezirk 6 Osnabrück-Emsland',
			'80000' => '80000 SB Rheinland-Pfalz e.V.',
			'81000' => '81000 Schachverband Rheinland e.V.',
			'81100' => '81100 Bezirk I Rhein-Ahr-Mosel',
			'81200' => '81200 Bezirk II Rhein-Nahe',
			'81300' => '81300 Bezirk III Rhein-Westerwald',
			'81500' => '81500 Bezirk IV Trier',
			'82000' => '82000 SB Rheinhessen e.V.',
			'83000' => '83000 Pfälzischer Schachbund e.V.',
			'83100' => '83100 Bezirk I Kaiserslautern',
			'83200' => '83200 Bezirk II Ludwigshafen',
			'83300' => '83300 Bezirk III Neustadt',
			'83400' => '83400 Bezirk IV Landau',
			'83500' => '83500 Bezirk V Pirmasens',
			'83600' => '83600 Bezirk VI Ramstein',
			'90000' => '90000 Saarländischer Schachverband',
			'A0000' => 'A0000 SVB Schleswig-Holstein',
			'A0100' => 'A0100 Bezirk I Nord',
			'A0200' => 'A0200 Bezirk II West',
			'A0600' => 'A0600 Bezirk VI Kiel',
			'A0800' => 'A0800 Bezirk Ost',
			'B0000' => 'B0000 Landesschachbund Bremen',
			'C0000' => 'C0000 Schachverband Württemberg e.V.',
			'C0100' => 'C0100 Bezirk Oberschwaben',
			'C0200' => 'C0200 Bezirk Alb/Schwarzwald',
			'C0300' => 'C0300 Bezirk Neckar-Fils',
			'C0400' => 'C0400 Bezirk Ostalb',
			'C0500' => 'C0500 Bezirk Stuttgart',
			'C0600' => 'C0600 Bezirk Unterland-Hohenlohe',
			'C11' => 'C11 Kreis Oberschwaben Nord',
			'C12' => 'C12 Kreis Oberschwaben Süd',
			'C21' => 'C21 Kreis Zollern Alb',
			'C22' => 'C22 Kreis Donau Neckar',
			'C23' => 'C23 Kreis Schwarzwald',
			'C31' => 'C31 Kreis Esslingen/Nürtingen',
			'C32' => 'C32 Kreis Reutlingen/Tübingen',
			'C33' => 'C33 Kreis Filstal',
			'C41' => 'C41 Kreis Aalen',
			'C42' => 'C42 Kreis Heidenheim',
			'C43' => 'C43 Kreis Schwäbisch Gmünd',
			'C51' => 'C51 Kreis Stuttgart Ost',
			'C52' => 'C52 Kreis Stuttgart Mitte',
			'C53' => 'C53 Kreis Stuttgart West',
			'C61' => 'C61 Kreis Heilbronn/Hohenlohe',
			'C62' => 'C62 Kreis Ludwigsburg',
			'D0000' => 'D0000 Schachbund Brandenburg',
			'D1000' => 'D1000 Cottbus',
			'D2000' => 'D2000 Frankfurt/O.',
			'D3000' => 'D3000 Potsdam',
			'E0000' => 'E0000 LSV Mecklenburg-Vorpommern',
			'E0100' => 'E0100 Spielbezirk West',
			'E0200' => 'E0200 Spielbezirk Mitte',
			'E0300' => 'E0300 Spielbezirk Ost',
			'F0000' => 'F0000 Schachverband Sachsen e.V.',
			'F1000' => 'F1000 Leipzig',
			'F1100' => 'F1100 Landkreis Delitzsch',
			'F1200' => 'F1200 Landkreis Döbeln',
			'F1300' => 'F1300 Landkreis Torgau-Oschatz',
			'F1500' => 'F1500 Stadt Leipzig',
			'F1800' => 'F1800 Kreis Leipziger Land',
			'F1900' => 'F1900 Muldentalkreis',
			'F2000' => 'F2000 Dresden',
			'F2100' => 'F2100 Landkreis Riesa-Großenhain',
			'F2200' => 'F2200 Landkreis Sächsische Schweiz',
			'F2300' => 'F2300 Landkreis Kamenz',
			'F2400' => 'F2400 Stadt Hoyerswerda',
			'F2500' => 'F2500 Weiseritzkreis',
			'F2600' => 'F2600 Landkreis Meißen',
			'F2700' => 'F2700 Stadt Görlitz',
			'F2800' => 'F2800 Stadt Dresden',
			'F2900' => 'F2900 Landkreis Löbau-Zittau',
			'F2A00' => 'F2A00 Landkreis Bautzen',
			'F2B00' => 'F2B00 Niederschlesischer Oberlausitzkreis',
			'F3000' => 'F3000 Chemnitz',
			'F3100' => 'F3100 Landkreis Stollberg',
			'F3200' => 'F3200 Landkreis Mittweida',
			'F3300' => 'F3300 Landkreis Freiberg',
			'F3400' => 'F3400 Landkreis Chemnitzer Land',
			'F3500' => 'F3500 Landkreis Annaberg',
			'F3600' => 'F3600 Stadt Chemnitz',
			'F3700' => 'F3700 Vogtlandkreis',
			'F3800' => 'F3800 Stadt Zwickau',
			'F3900' => 'F3900 Mittlerer Erzgebirgskreis',
			'F3A00' => 'F3A00 Landkreis Zwicker Land',
			'F3B00' => 'F3B00 Stadt Plauen',
			'F3C00' => 'F3C00 Landkreis Aue-Schwarzenberg',
			'G0000' => 'G0000 LSV Sachsen-Anhalt',
			'G0100' => 'G0100 Schachbezirk Dessau',
			'G0200' => 'G0200 Schachbezirk Halle',
			'G0300' => 'G0300 Schachbezirk Magdeburg',
			'H0000' => 'H0000 Thüringer Schachbund',
			'H1000' => 'H1000 Schachbezirk Nord',
			'H1100' => 'H1100 Schachkreis Kyffhäuser',
			'H1200' => 'H1200 Schachkreis Eichsfeld',
			'H1300' => 'H1300 Schachkreis Nordhausen',
			'H1400' => 'H1400 Schachkreis Unstrut-Hainich',
			'H1500' => 'H1500 Schachkreis Gotha',
			'H2000' => 'H2000 Schachbezirk Mitte',
			'H2100' => 'H2100 Schachkreis Erfurt',
			'H2200' => 'H2200 Schachkreis Weimarer Land',
			'H2300' => 'H2300 Schachkreis Sömmerda',
			'H2400' => 'H2400 Schachkreis Ilm-Kreis',
			'H2500' => 'H2500 Schachkreis Weimar',
			'H3000' => 'H3000 Schachbezirk Ost',
			'H3100' => 'H3100 Schachkreis Gera',
			'H3200' => 'H3200 Schachkreis Jena/Holzlandkreis',
			'H3300' => 'H3300 Schachkreis Greiz',
			'H3400' => 'H3400 Schachkreis Altenburger Land',
			'H3500' => 'H3500 Schachkreis Saalfeld-Rudolstadt/SOK',
			'H4000' => 'H4000 Schachbezirk Süd',
			'H4100' => 'H4100 Schachkreis Schmalkalden-Meiningen/Suhl',
			'H4300' => 'H4300 Schachkreis Wartburgkreis/Eisenach',
			'K0000' => 'K0000 Ausländer',
			'L0000' => 'L0000 Deutscher Blinden- und Sehbehinderten-Schachbund',
			'M0000' => 'M0000 Schwalbe'
		);
		return $array;
	}

}
