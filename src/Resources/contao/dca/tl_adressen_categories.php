<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DC_Table;
use Schachbulle\ContaoAdressenBundle\ContaoAdressenBundle;

/**
 * Tabelle tl_adressen_categories
 *
 * Kategorien, die einer Adresse im Feld tl_adressen.funktionen zugewiesen
 * werden können.
 */
$GLOBALS['TL_DCA']['tl_adressen_categories'] = array
(

	// Konfiguration
	'config' => array
	(
		// Contao 5 erwartet den vollqualifizierten Klassennamen, Contao 4.13 den Kurznamen
		'dataContainer'               => ContaoAdressenBundle::isContao5() ? DC_Table::class : 'Table',
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                  => 'primary',
			)
		)
	),

	// Datensätze auflisten
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2, // DataContainer::MODE_SORTABLE
			'fields'                  => array('category'),
			'flag'                    => 1, // DataContainer::SORT_INITIAL_LETTER_ASC
			'defaultSearchField'      => 'category',
			'panelLayout'             => 'filter,sort;search,limit',
		),
		'label' => array
		(
			'fields'                  => array('category'),
			'format'                  => '%s',
		),
		// global_operations und operations werden weiter unten versionsabhängig gesetzt
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{name_legend},category;{active_legend},active'
	),

	// Felder
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'category' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen_categories']['category'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'long'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'active' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen_categories']['active'],
			'toggle'                  => true,
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 1, // DataContainer::SORT_INITIAL_LETTER_ASC
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true, 'tl_class' => 'w50'),
			'sql'                     => array('type' => 'boolean', 'default' => true)
		),
	)
);

/*
 * Operationen versionsabhängig setzen: Contao 5 kennt die Kurzschreibweise,
 * Contao 4.13 benötigt vollständige Arrays.
 */
if (ContaoAdressenBundle::isContao5())
{
	$GLOBALS['TL_DCA']['tl_adressen_categories']['list']['global_operations'] = array
	(
		'adressen' => array
		(
			'href'                => 'table=tl_adressen',
			'primary'             => true,
			'icon'                => 'bundles/contaoadressen/images/icon.svg',
		),
		'all'
	);

	$GLOBALS['TL_DCA']['tl_adressen_categories']['list']['operations'] = array
	(
		'edit',
		'delete',
		'toggle',
		'show'
	);
}
else
{
	$GLOBALS['TL_DCA']['tl_adressen_categories']['list']['global_operations'] = array
	(
		'adressen' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['adressen'],
			'href'                => 'table=tl_adressen',
			'icon'                => 'bundles/contaoadressen/images/icon.svg',
			'attributes'          => 'onclick="Backend.getScrollOffset()"'
		),
		'all' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
			'href'                => 'act=select',
			'class'               => 'header_edit_all',
			'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
		)
	);

	$GLOBALS['TL_DCA']['tl_adressen_categories']['list']['operations'] = array
	(
		'edit' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['edit'],
			'href'                => 'act=edit',
			'icon'                => 'edit.svg'
		),
		'delete' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['delete'],
			'href'                => 'act=delete',
			'icon'                => 'delete.svg',
			'attributes'          => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"'
		),
		'toggle' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['toggle'],
			'href'                => 'act=toggle&amp;field=active',
			'icon'                => 'visible.svg'
		),
		'show' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['show'],
			'href'                => 'act=show',
			'icon'                => 'show.svg'
		)
	);
}
