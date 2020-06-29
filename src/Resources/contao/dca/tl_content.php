<?php

/**
 * Paletten
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_selectmails';

$GLOBALS['TL_DCA']['tl_content']['palettes']['adressen'] = '{type_legend},type,headline;{adresse_legend},adresse_id,adresse_funktion,adresse_zusatz,adresse_tpl,adresse_bildvorschau,adresse_viewfoto,adresse_selectmails;{adressbild_legend:hide},addImage;{protected_legend:hide},protected;{expert_legend:hide},guest,cssID,space;{invisible_legend:hide},invisible,start,stop';

// Subpalettes
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_selectmails'] = 'adresse_mails';
	
/**
 * Felder
 */

// Adressenliste anzeigen
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_id'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_id'],
	'exclude'              => true,
	'options_callback'     => array('tl_content_adresse', 'getAdressenListe'),
	'inputType'            => 'select',
	'eval'                 => array
	(
		'mandatory'=>false, 
		'multiple'=>false, 
		'chosen'=>true,
		'submitOnChange'=>true
	),
	'wizard'               => array
	(
		array('tl_content_adresse', 'editAdresse')
	),
	'sql'                  => "int(10) unsigned NOT NULL default '0'" 
);

// Funktion (wird vor dem Namen angezeigt)
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_funktion'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_funktion'],
	'exclude'              => true,
	'search'               => true,
	'inputType'            => 'text',
	'eval'                 => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
	'sql'                  => "varchar(255) NOT NULL default ''"
);

// Zusatztext (wird zwischen der Funktion und dem Namen angezeigt)
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_zusatz'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_zusatz'],
	'exclude'              => true,
	'search'               => true,
	'inputType'            => 'text',
	'eval'                 => array('maxlength'=>255, 'tl_class'=>'w50', 'allowHtml'=>true),
	'sql'                  => "varchar(255) NOT NULL default ''"
);

// Template zuweisen
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_tpl'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_tpl'],
	'exclude'              => true,
	'inputType'            => 'select',
	'options_callback'     => array('tl_content_adresse', 'getAdressenTemplates'),
	'eval'                 => array('tl_class'=>'w50 clr'),
	'sql'                  => "varchar(32) NOT NULL default ''"
); 

// Zeigt das Standardfoto aus tl_adressen an
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_bildvorschau'] = array
(
	'exclude'              => true,
	'input_field_callback' => array('tl_content_adresse', 'getThumbnail'),
); 

// Foto anzeigen ja/nein?
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_viewfoto'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_viewfoto'],
	'inputType'            => 'checkbox',
	'default'              => true,
	'eval'                 => array('tl_class' => 'w50', 'isBoolean' => true),
	'sql'                  => "char(1) NOT NULL default ''",
);

// Nur bestimmte Adressen aktivieren einschalten
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_selectmails'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_selectmails'],
	'exclude'              => true,
	'filter'               => true,
	'inputType'            => 'checkbox',
	'eval'                 => array
	(
		'submitOnChange'   => true,
		'tl_class'         => 'clr w50'
	),
	'sql'                  => "char(1) NOT NULL default ''"
);

// Anzuzeigende Adressen auswählen
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_mails'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_mails'],
	'exclude'              => true,
	'inputType'            => 'checkboxWizard',
	'options_callback'     => array('tl_content_adresse', 'getMails'),
	'eval'                 => array
	(
		'tl_class'         => 'w50', 
		'multiple'         => true
	),
	'sql'                  => "varchar(64) NOT NULL default ''"
);

/*****************************************
 * Klasse tl_content_adresse
 *****************************************/
 
class tl_content_adresse extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Funktion editAdresse
	 * @param \DataContainer
	 * @return string
	 */
	public function editAdresse(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=adressen&amp;table=tl_adressen&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':765,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
	} 
	
	public function getAdressenTemplates()
	{
		return $this->getTemplateGroup('adresse_');
	} 

	public function getAdressenListe(DataContainer $dc)
	{
		$array = array();
		$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen ORDER BY alias ASC")->execute();
		while($objAdresse->next())
		{
			($objAdresse->aktiv) ? $aktivstatus = '' : $aktivstatus = $GLOBALS['TL_LANG']['tl_content']['adresse_nichtaktiv'];
			($objAdresse->vorname) ? $array[$objAdresse->id] = $objAdresse->nachname.','.$objAdresse->vorname.$aktivstatus : $array[$objAdresse->id] = $objAdresse->nachname.$aktivstatus;
		}
		return $array;

	}

	public function getMails(DataContainer $dc)
	{
		//print_r($dc);
		$array = array();
		$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen WHERE id = ?")
		                             ->execute($dc->activeRecord->adresse_id);

		$objAdresse->email1 ? $array[1] = $objAdresse->email1 : '';
		$objAdresse->email2 ? $array[2] = $objAdresse->email2 : '';
		$objAdresse->email3 ? $array[3] = $objAdresse->email3 : '';
		$objAdresse->email4 ? $array[4] = $objAdresse->email4 : '';
		$objAdresse->email5 ? $array[5] = $objAdresse->email5 : '';
		$objAdresse->email6 ? $array[6] = $objAdresse->email6 : '';

		return $array;

	}

	public function getThumbnail(DataContainer $dc)
	{
		$keinbild = '
<div class="w50 clr">
  <h3><label>'.$GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau_fehlt'][0].'</label></h3>
  <p class="tl_help tl_tip" title="">'.$GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau_fehlt'][1].'</p>
</div>'; 
		
		//echo "<pre>";
		//print_r($dc->activeRecord);
		//echo "</pre>";

		if($dc->activeRecord->adresse_id)
		{
			$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen WHERE id=?")->execute($dc->activeRecord->adresse_id);
			if($objAdresse->addImage)
			{
				$strBild = '';
				(version_compare(VERSION, '3.2', '>=')) ? $objBild = \FilesModel::findByUuid($objAdresse->singleSRC) : $objBild = \FilesModel::findByPk($objAdresse->singleSRC);
				
				// Add cover image
				if($objBild !== null)
				{
					$size = unserialize($objAdresse->size);
					if(!$size[0] && !$size[1])
					{
						// Breite/Höhe nicht definiert, deshalb festlegen auf 100
						$size[0] = 100;
						$size[1] = 100;
					}
					$strBild = \Image::getHtml(\Image::get($objBild->path, $size[0], $size[1], $size[2]));
				}
				else $strBild = $GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau_leer'];
				return '
<div class="w50 clr">
  <h3><label>'.$GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau'][0].'</label></h3>
  '.$strBild.'
  <p class="tl_help tl_tip" title="">'.$GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau'][1].'</p>
</div>';
			} 
			else return $keinbild;
		}
		else return $keinbild;
	}

}
