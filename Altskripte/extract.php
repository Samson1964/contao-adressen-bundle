<?php 

// Melde alle Fehler außer E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);

/** 
 * Initialize the system 
 */ 
define('TL_MODE', 'FE'); 
define('TL_FILES_URL', ''); 
define('TL_SCRIPT', 'php/adressen/extract.php'); 
require($_SERVER['DOCUMENT_ROOT'].'../../system/initialize.php'); 

/** 
 * extends \System ?
 */ 
class ExtractJob extends \PageRegular 
{ 

	var $aktzeit;
	
	/** 
	 * Initialize the object (do not remove) 
	 */ 
	public function __construct() 
	{ 
		$this->aktzeit = time();
		$GLOBALS['TL_CONFIG']['displayErrors'] = true;
		parent::__construct(); 
	} 
	
	/** 
	 * Ausführen 
	 */ 
	public function run() 
	{ 

		// In Inhaltselementen vom Typ "text" im Feld "text" nach adresse-Tags suchen, 
		// dabei nur veröffentlichte Elemente berücksichtigen 
		$objContent = \Database::getInstance()->prepare("SELECT * FROM tl_content WHERE text LIKE '%{{adresse::%' AND type = 'text' AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND invisible = ''") 
				  							  ->execute($this->aktzeit, $this->aktzeit); 
		$inserttags = 0; // Tags-Zähler initialisieren

		while ($objContent->next()) 
		{ 
			// Werte extrahieren
			preg_match_all("/{{adresse::(.*)}}/",$objContent->text,$matches);
			//echo '<pre>';
			//print_r($matches);
			//echo '</pre>';
			// Trefferliste auswerten
			foreach($matches[1] as $match)
			{
				// Nur ID der Adresse benutzen
				$value = explode("::",$match);
				$adrnr = $value[0];
				// Informationen zum Artikel des Inhaltselements suchen
				$objArtikel = \Database::getInstance()->prepare("SELECT * FROM " . $objContent->ptable . " WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = 1") 
				  							  		  ->execute($objContent->pid, $this->aktzeit, $this->aktzeit); 
				// Informationen zur Seite des Artikels suchen
				$objSeite = \Database::getInstance()->prepare("SELECT * FROM tl_page WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = 1") 
				  							  		->execute($objArtikel->pid, $this->aktzeit, $this->aktzeit);
				if($objArtikel->id && $objSeite->id)
				{ 
					$objPage = \Controller::getPageDetails($objSeite->id);
					$adresse[$value[0]][] = array
					(
						'page_alias'      => $objSeite->alias, 
						'page_id'         => $objSeite->id, 
						'article_alias'   => $objArtikel->alias, 
						'article_id'      => $objArtikel->id, 
						'content_id'      => $objContent->id, 
						'type'            => 'tag', 
						'domain'          => $objPage->domain,
						'foto_aktiv'      => false,
						'foto_alternativ' => false
					);
					$inserttags++;
				}
			}
		} 

		// Inhaltselemente vom Typ "adressen" suchen, 
		// dabei nur veröffentlichte Elemente berücksichtigen 
		$objAdressen = \Database::getInstance()->prepare("SELECT * FROM tl_content WHERE type = 'adressen' AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND invisible = ''") 
				  							  ->execute($this->aktzeit, $this->aktzeit); 
		$insertelements = 0; // Objekte-Zähler initialisieren

		while ($objAdressen->next())
		{
			if ($objAdressen->ptable == 'tl_article' || $objAdressen->ptable == 'tl_news')
			{
				// Informationen zum Artikel des Inhaltselements suchen
				$objArtikel = \Database::getInstance()->prepare("SELECT * FROM " . $objAdressen->ptable . " WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = 1") 
				                                      ->execute($objAdressen->pid, $this->aktzeit, $this->aktzeit); 
				// Informationen zur Seite des Artikels suchen
				$objSeite = \Database::getInstance()->prepare("SELECT * FROM tl_page WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = 1") 
				                                    ->execute($objArtikel->pid, $this->aktzeit, $this->aktzeit); 
				if($objArtikel->id && $objSeite->id)
				{ 
					// Separates Foto bei aktuellem Element?
					$altfoto = false; 
					if($objAdressen->adresse_viewfoto && $objAdressen->addImage)
					{
						//$objModel = \FilesModel::findByPk($objAdressen->singleSRC);
						$objModel = \FilesModel::findByUuid($objAdressen->singleSRC);
						if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
						{
							$altfoto = $objModel->path;
						}
					}
					// Domain der Seite ermitteln und adresse-Array schreiben
					$objPage = \Controller::getPageDetails($objSeite->id);
					$adresse[$objAdressen->adresse_id][] = array
					(
						'page_alias'      => $objSeite->alias, 
						'page_id'         => $objSeite->id, 
						'article_alias'   => $objArtikel->alias, 
						'article_id'      => $objArtikel->id, 
						'content_id'      => $objAdressen->id, 
						'type'            => 'element', 
						'domain'          => $objPage->domain,
						'foto_aktiv'      => $objAdressen->adresse_viewfoto,
						'foto_alternativ' => $altfoto
					);
					$insertelements++;
				}
			}
		} 

		// Spalte links in tl_adressen leeren
		\Database::getInstance()->prepare('UPDATE tl_adressen SET links = NULL') 
								->execute(); 
		
		// Treffer in Spalte links von tl_adressen eintragen
		foreach($adresse as $key => $value)
		{
			$ausgabe = "";
			foreach($value as $satz)
			{
				$ausgabe .= 'http://' . $satz['domain'] . '/' . $satz['page_alias'].".html\n";
			}
			// tl_adressen aktualisieren
			\Database::getInstance()->prepare('UPDATE tl_adressen SET links = ? WHERE id = ?') 
									->execute($ausgabe, $key); 
			
		}
		echo count($adresse)." Adressen sind online<br>\n";
		echo "$inserttags Inserttags adresse gefunden<br>\n";
		echo "$insertelements Inhaltselemente Adresse gefunden<br>\n";
		echo "Fertig";
		//echo '<pre>';
		//print_r($adresse);
		//echo '</pre>';
	} 
} 

/** 
 * Instantiate controller 
 */ 
$objExtract = new ExtractJob(); 
$objExtract->run();  

?> 
