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
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{adressen_legend:hide},adressen_defaultImage,adressen_ImageSize'
	.';{adressen_cron_legend:hide},adressen_cron_absender,adressen_cron_absendername,adressen_cron_replyto,adressen_cron_betreff,adressen_cron_fotourl,adressen_cron_live,adressen_cron_testempfaenger';

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

/*
 * Einstellungen des Kontroll-Cronjobs (Cron\KontrolliereAdressen)
 *
 * Der Cronjob verschickt vierteljährlich an alle aktiven, eingebundenen
 * Adressen eine E-Mail mit den gespeicherten Daten. Ohne gesetzte
 * Absenderadresse verschickt er nichts.
 */

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_absender'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_absender'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp'=>'email', 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_absendername'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_absendername'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_replyto'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_replyto'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50 clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_betreff'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_betreff'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_fotourl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_fotourl'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp'=>'url', 'maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long clr')
);

// Sicherheitsschalter: ohne Haken bleibt der Cronjob im Testmodus
$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_live'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_live'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 clr m12')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['adressen_cron_testempfaenger'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_testempfaenger'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50')
);
