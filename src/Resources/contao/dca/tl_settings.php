<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   fen
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2013
 */

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{adressen_legend:hide},adressen_defaultImage,adressen_ImageSize';

/**
 * fields
 */

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_defaultImage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_defaultImage'],
	'inputType'               => 'fileTree',
	'eval'                    => array
	(
		'filesOnly'           => true,
		'fieldType'           => 'radio',
		'tl_class'            => 'w50 clr'
	)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_ImageSize'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_ImageSize'],
	'exclude'                 => true,
	'inputType'               => 'imageSize',
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
	'options_callback' => static function ()
	{
		return System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance());
	},
	'sql'                     => "varchar(255) NOT NULL default ''"
);
