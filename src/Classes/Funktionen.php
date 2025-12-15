<?php

namespace Schachbulle\ContaoAdressenBundle\Classes;

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Check Helper 
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2015
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */


/**
 * Class BannerCheckHelper
 *
 * @copyright  Glen Langer 2015
 * @author     Glen Langer
 * @package    Banner
 */

class Funktionen extends \Contao\Frontend
{
	/**
	 * Current object instance
	 * @var object
	 */
	protected static $instance = null;

	var $user;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Benutzerdaten laden
		if(FE_USER_LOGGED_IN)
		{
			// Frontenduser eingeloggt
			$this->user = \Contao\FrontendUser::getInstance();
		}
		parent::__construct();
	}


	/**
	 * Return the current object instance (Singleton)
	 * @return BannerCheckHelper
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new \Schachbulle\ContaoAdressenBundle\Classes\Funktionen();
		}
	
		return self::$instance;
	}


	/**
	 * Führt Contao's generateAlias aus und modifiziert ggfs. das Ergebnis
	 * @param string	String, der geglättet werden soll
	 * @return			fertiger String
	 */
	public static function generateAlias($string)
	{
		$string = \Contao\StringUtil::generateAlias($string);
		$search  = array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß');
		$replace = array('ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss');
		return str_replace($search, $replace, $string);
	}


	/**
	 * Gibt ein Array mit den Funktionen zurück
	 * @param id	ID in DeWIS
	 * @return		ID des Contao-Mitgliedes
	 */
	public static function getFunktionen($inaktiv = true)
	{
		// Kategorien laden (neue Variante)
		$arrCats = array();
		$sql = $inaktiv ? '' : 'WHERE active = 1 ';
		
		$objResult = \Contao\Database::getInstance()->prepare('SELECT * FROM tl_adressen_categories '.$sql.'ORDER BY category')->execute();

		while($objResult->next())
		{
			$arrCats[$objResult->id] = $objResult->category;
		}
		return $arrCats;

		// Referate zuordnen (alte Variante)
		$array = array
		(
			'prae'      => 'DSB-Präsidium',
			'gs'        => 'DSB-Geschäftsstelle',
			'ha'        => 'DSB-Hauptausschuss',
			'kaus'      => 'DSB-Kommission Ausbildung',
			'klsp'      => 'DSB-Kommission Leistungssport',
			'kfrau'     => 'DSB-Kommission Frauenschach',
			'ksen'      => 'DSB-Kommission Seniorenschach',
			'kdwz'      => 'DSB-Wertungskommission',
			'ksr'       => 'DSB-Schiedsrichterkommission',
			'bsger'     => 'Bundesschiedsgericht',
			'kspk'      => 'Bundesspielkommission',
			'btger'     => 'Bundesturniergericht',
			'lvprae'    => 'Präsident Landesverband',
			'ehren'     => 'Ehrenpräsident/-mitglied',
			'rech'      => 'Rechnungsprüfer',
			'atrain'    => 'A-Trainerausbildung 2017',
			'kon17'     => 'Bundeskongress 2017',
			'kaderm'    => 'Kader männlich',
			'kaderw'    => 'Kader weiblich',
			'kaderabm'  => 'Kader A und B männlich',
			'kaderabw'  => 'Kader A und B weiblich',
			'kaderacm'  => 'Kader A bis C männlich',
			'kaderacw'  => 'Kader A bis C weiblich',
			'kaderalle' => 'Kader A, B, C und D/C männlich und weiblich'
		);
		return $array;

	}

}
