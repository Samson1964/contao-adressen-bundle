<?php

/**
 * Paletten
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_selectmails';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_alttemplate';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_addImage';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_altformat';


$GLOBALS['TL_DCA']['tl_content']['palettes']['adressen'] = '{type_legend},type,headline;{adresse_legend},adresse_id,adresse_funktion,adresse_zusatz,adresse_selectmails;{adressefoto_legend},adresse_addImage,adresse_altformat;{template_legend:hide},customTpl;{adresstemplate_legend:hide},adresse_alttemplate;{protected_legend:hide},protected;{expert_legend:hide},guest,cssID,space;{invisible_legend:hide},invisible,start,stop';

// Subpalettes
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_selectmails'] = 'adresse_mails';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_alttemplate'] = 'adresse_tpl';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_addImage'] = 'adresse_bildvorschau,singleSRC';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_altformat'] = 'size';
	
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
		'mandatory'        => false, 
		'multiple'         => false, 
		'chosen'           => true,
		'submitOnChange'   => true,
		'tl_class'         => 'wizard'
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

// Zeigt das Standardfoto aus tl_adressen an
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_bildvorschau'] = array
(
	'exclude'              => true,
	'input_field_callback' => array('tl_content_adresse', 'getThumbnail'),
); 

// Alternatives Foto aktivieren
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_addImage'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_addImage'],
	'exclude'              => true,
	'filter'               => true,
	'default'              => true,
	'inputType'            => 'checkbox',
	'eval'                 => array
	(
		'submitOnChange'   => true,
		'tl_class'         => 'w50'
	),
	'sql'                  => "char(1) NOT NULL default '1'"
);

// Alternatives Foto aktivieren
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_altformat'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_altformat'],
	'exclude'              => true,
	'filter'               => true,
	'default'              => false,
	'inputType'            => 'checkbox',
	'eval'                 => array
	(
		'submitOnChange'   => true,
		'tl_class'         => 'w50'
	),
	'sql'                  => "char(1) NOT NULL default ''"
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
	'sql'                  => "blob NULL"
);

// Feld singleSRC dynamisch ändern bei Adressen
$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['load_callback'][] =  array('tl_content_adresse', 'setSingleSrcFlags');

/*****************************************
 * Klasse tl_content_adresse
 *****************************************/
 
class tl_content_adresse extends \Contao\Backend
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
	 * Dynamically add flags to the "singleSRC" field
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return mixed
	 */
	public function setSingleSrcFlags($varValue, \Contao\DataContainer $dc)
	{
		if($dc->activeRecord)
		{
			// Content-Element temporär ändern
			if($dc->activeRecord->type == 'adressen')
			{
				$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['eval']['mandatory'] = false;
				$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['eval']['tl_class'] = 'w50';
				$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['eval']['extensions'] = \Contao\Config::get('validImageTypes');
				$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['label'] = &$GLOBALS['TL_LANG']['tl_content']['adresse_singleSRC'];
			}
		}

		return $varValue;
	}

	/**
	 * Funktion editAdresse
	 * @param \DataContainer
	 * @return string
	 */
	public function editAdresse(\Contao\DataContainer $dc)
	{

		if($dc->value < 1)
		{
			return '';
		}

		$title = sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'], $dc->value);

		return ' <a href="contao/main.php?do=adressen&amp;table=tl_adressen&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;&amp;rt=' . REQUEST_TOKEN . '" title="' . StringUtil::specialchars($title) . '" onclick="Backend.openModalIframe({\'title\':\'' . StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.svg', $title) . '</a>';
		
	} 
	
	public function getAdressenListe(\Contao\DataContainer $dc)
	{
		$array = array();
		$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen ORDER BY nachname ASC, vorname ASC")->execute();
		while($objAdresse->next())
		{
			// Aktivstatus der Adresse ermitteln
			$aktivstatus = $objAdresse->aktiv ? '' : $GLOBALS['TL_LANG']['tl_content']['adresse_nichtaktiv'];
			// Adresse zuordnen
			$temp = $objAdresse->nachname ? $objAdresse->nachname.','.$objAdresse->vorname.$aktivstatus : '(Firma) '.$objAdresse->firma.$aktivstatus;
			$array[$objAdresse->id] = $temp.($objAdresse->ort ? ' ('.$objAdresse->ort.')' : '');

		}
		return $array;

	}

	public function getMails(\Contao\DataContainer $dc)
	{
		//print_r($dc);
		$array = array();
		$objAdresse = \Contao\Database::getInstance()->prepare("SELECT * FROM tl_adressen WHERE id = ?")
		                                      ->execute($dc->activeRecord->adresse_id);

		if($objAdresse->email1) $array[] = $objAdresse->email1;
		if($objAdresse->email2) $array[] = $objAdresse->email2;
		if($objAdresse->email3) $array[] = $objAdresse->email3;
		if($objAdresse->email4) $array[] = $objAdresse->email4;
		if($objAdresse->email5) $array[] = $objAdresse->email5;
		if($objAdresse->email6) $array[] = $objAdresse->email6;

		//$emails = unserialize($objAdresse->emails);
		//if(is_array($emails) && count($emails) > 0)
		//{
		//	foreach($emails as $item)
		//	{
		//		$array[] = $item['mail'];
		//	}
		//}

		return $array;

	}

	public function getThumbnail(\Contao\DataContainer $dc)
	{

		if($dc->activeRecord->adresse_id)
		{
			$objAdresse = $this->Database->prepare("SELECT * FROM tl_adressen WHERE id=?")->execute($dc->activeRecord->adresse_id);

			// Bild extrahieren
			if($objAdresse->singleSRC)
			{
				$objFile = \Contao\FilesModel::findByPk($objAdresse->singleSRC);
			}
			else
			{
				$objFile = \Contao\FilesModel::findByUuid($GLOBALS['TL_CONFIG']['adressen_defaultImage']);
			}

			$objBild = new \stdClass();
			$strBild = '';
			if(isset($objFile->path))
			{
				\Contao\Controller::addImageToTemplate($objBild, array('singleSRC' => $objFile->path, 'size' => unserialize($GLOBALS['TL_CONFIG']['adressen_ImageSize'])), \Config::get('maxImageWidth'), null, $objFile);
				$strBild = '<img src="'.$objBild->src.'" alt="'.$objBild->alt.'" title="'.$objBild->imageTitle.'" '.$objBild->imgSize.'>';
			}

			return '
<div class="w50 clr widget">
  <h3><label>'.$GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau'][0].'</label></h3>
  '.$strBild.'
  <p class="tl_help tl_tip" title="">'.$GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau'][1].'</p>
</div>';

		}
		else return '';
	}

}
