<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\BackendUser;
use Contao\System;

/*
 * Palette erweitern
 *
 * Hinweis: tl_settings wird über DC_File in die localconfig geschrieben, die
 * Felder brauchen deshalb keine "sql"-Definition.
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{adressen_legend:hide},adressen_defaultImage,adressen_ImageSize';

/*
 * Felder
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
	'options_callback'        => static function ()
	{
		return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
	}
);
