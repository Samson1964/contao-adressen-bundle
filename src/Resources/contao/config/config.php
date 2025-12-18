<?php

$GLOBALS['BE_MOD']['content']['adressen'] = array(
	'tables'      => array('tl_adressen', 'tl_adressen_categories'),
	'icon'        => 'bundles/contaoadressen/images/icon.svg',
	'import'      => array('Schachbulle\ContaoAdressenBundle\Classes\Adressen_Backend','importAdressen'),
	'export'      => array('Schachbulle\ContaoAdressenBundle\Classes\Adressen_Backend','exportAdressen'),
);

/**
 * Frontend-Module
 */
$GLOBALS['FE_MOD']['adressen'] = array
(
	'adressen_wertungsreferenten' => 'Schachbulle\ContaoAdressenBundle\Modules\Wertungsreferenten',
	'adressen_suche'              => 'Schachbulle\ContaoAdressenBundle\Classes\Suche',
);  


/**
 * Inserttag für Adressersetzung in den Hooks anmelden
 */

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Schachbulle\ContaoAdressenBundle\Classes\Adressen_Frontend','adresse_ersetzen');

/**
 * Optionen für Bildbeschneidung
 */

$GLOBALS['TL_ADRESSEN'] = array(0,10,20,30,40,50,60,70,80,90,100);

/**
 * Inhaltselemente
 */
 
$GLOBALS['TL_CTE']['includes']['adressen'] = 'Schachbulle\ContaoAdressenBundle\ContentElements\Adresse';
