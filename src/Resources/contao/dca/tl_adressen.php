<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\Config;
use Contao\DataContainer;
use Contao\Database;
use Contao\DC_Table;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoAdressenBundle\Classes\Funktionen;
use Schachbulle\ContaoAdressenBundle\ContaoAdressenBundle;

/**
 * Tabelle tl_adressen
 */
$GLOBALS['TL_DCA']['tl_adressen'] = array
(

	// Konfiguration
	'config' => array
	(
		// Contao 5 erwartet den vollqualifizierten Klassennamen, Contao 4.13 den Kurznamen
		'dataContainer'               => ContaoAdressenBundle::isContao5() ? DC_Table::class : 'Table',
		'enableVersioning'            => true,
		'markAsCopy'                  => 'nachname',
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		),
		'onload_callback'             => array
		(
			array('tl_adressen', 'applyAdressenFilter'),
		),
		'onsubmit_callback' => array
		(
			array('tl_adressen', 'generateSearchstring')
		),
	),

	// Datensätze auflisten
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2, // DataContainer::MODE_SORTABLE
			'fields'                  => array('nachname','vorname'),
			'flag'                    => 1, // DataContainer::SORT_INITIAL_LETTER_ASC
			'defaultSearchField'      => 'nachname',
			'panelLayout'             => 'adr_filter;filter;sort,search,limit',
			'panel_callback'          => array('adr_filter' => array('tl_adressen', 'generateAdressenFilter')),
		),
		'label' => array
		(
			// Das Feld aktiv wird vom label_callback überschrieben
			'fields'                  => array('aktiv','id','nachname','vorname','firma','plz','ort'),
			'showColumns'             => true,
			'format'                  => '%s',
			'label_callback'          => array('tl_adressen','addIcon')
		),
		// global_operations und operations werden weiter unten versionsabhängig gesetzt
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{person_legende},nachname,vorname,titel,firma,club;{adresse_legende:hide},plz,ort,ort_view,strasse,strasse_view;{telefon_legende:hide},telefon1,telefon2,telefon3,telefon4,telefon_view;{telefax_legende:hide},telefax1,telefax2,telefax_view;{email_legende:hide},email1,email2,email3,email4,email5,email6,email_view;{bank_legend},inhaber,iban,bic;{funktionen_legende:hide},wertungsreferent,funktionen;{web_legende:hide},homepage,facebook,twitter,instagram,skype,whatsapp,threema,telegram,irc;{image_legend:hide},singleSRC;{text_legende:hide},text;{info_legende:hide},info,links,source;{aktiv_legende},aktiv'
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['tstamp'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'nachname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['nachname'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'vorname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['vorname'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'titel' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['titel'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'firma' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['firma'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'club' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['club'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'ort_view' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['ort_view'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'plz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['plz'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'ort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['ort'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'strasse_view' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['strasse_view'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 clr'),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'strasse' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['strasse'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'wertungsreferent' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['wertungsreferent'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkboxWizard',
			'options'                 => &$GLOBALS['TL_LANG']['tl_adressen']['verbaende'],
			'eval'                    => array('tl_class'=>'w50 clr', 'multiple'=>true),
			'sql'                     => "blob NULL"
		),
		'funktionen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['funktionen'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => array(Funktionen::class, 'getFunktionen'),
			'eval'                    => array('tl_class'=>'w50', 'multiple'=>true),
			'sql'                     => "text NULL"
		),
		'telefon_view' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefon_view'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'telefon1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefon1'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefon2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefon2'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefon3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefon3'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefon4' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefon4'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefax_view' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefax_view'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'telefax1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefax1'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'telefax2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefax2'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'email_view' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email_view'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
		'email1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email1'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'email2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email2'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'email3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email3'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'email4' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email4'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'email5' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email5'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'email6' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['email6'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'inhaber' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['inhaber'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'iban' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['iban'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>34, 'rgxp'=>'alnum', 'nospace'=>true, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(34) NOT NULL default ''"
		),
		'bic' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['bic'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>11, 'rgxp'=>'alnum', 'nospace'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		'homepage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['homepage'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'default'                 => 'https://',
			'save_callback'           => array
			(
				array('tl_adressen', 'saveHomepage')
			),
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'tl_class'=>'long clr'),
			'sql'                     => "text NULL"
		),
		'facebook' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['facebook'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'twitter' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['twitter'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'instagram' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['instagram'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'skype' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['skype'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'whatsapp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['whatsapp'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'threema' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['threema'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'telegram' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telegram'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'irc' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['irc'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'filesOnly'           => true,
				'extensions'          => Config::get('validImageTypes'),
				'fieldType'           => 'radio',
				'mandatory'           => false
			),
			'sql'                     => "binary(16) NULL"
		),
		'text' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['text'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'tl_class'=>'long'),
			'sql'                     => "text NULL"
		),
		'info' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['info'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'tl_class'=>'long'),
			'sql'                     => "text NULL"
		),
		'links' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['links'],
			'inputType'               => 'textarea',
			'exclude'                 => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'tl_class'=>'long', 'readonly'=>true),
			'sql'                     => "text NULL"
		),
		'aktiv' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['aktiv'],
			'toggle'                  => true,
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 1, // DataContainer::SORT_INITIAL_LETTER_ASC
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => array('type' => 'boolean', 'default' => true)
		),
		'source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['source'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		// Feld, das alle Strings enthält, die durchsucht werden können.
		// Wird ausschließlich von tl_adressen::generateSearchstring befüllt und
		// taucht deshalb in keiner Palette auf.
		'searchstring' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['searchstring'],
			'sql'                     => "text NULL"
		),
	)
);

/*
 * Operationen versionsabhängig setzen: Contao 5 kennt die Kurzschreibweise
 * ("edit", "!all"), Contao 4.13 benötigt vollständige Arrays.
 */
if (ContaoAdressenBundle::isContao5())
{
	$GLOBALS['TL_DCA']['tl_adressen']['list']['global_operations'] = array
	(
		'categories' => array
		(
			'href'                => 'table=tl_adressen_categories',
			'primary'             => true,
			'icon'                => 'bundles/contaoadressen/images/categories.svg',
		),
		'import' => array
		(
			'href'                => 'key=import',
			'icon'                => 'bundles/contaoadressen/images/importCSV.svg',
			'class'               => 'header_csv_import',
		),
		'export' => array
		(
			'href'                => 'key=export',
			'icon'                => 'bundles/contaoadressen/images/exportCSV.svg',
			'class'               => 'header_csv_export',
		),
		'all'
	);

	$GLOBALS['TL_DCA']['tl_adressen']['list']['operations'] = array
	(
		'edit',
		'copy',
		'delete',
		'toggle',
		'show'
	);
}
else
{
	$GLOBALS['TL_DCA']['tl_adressen']['list']['global_operations'] = array
	(
		'categories' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['categories'],
			'href'                => 'table=tl_adressen_categories',
			'icon'                => 'bundles/contaoadressen/images/categories.svg',
			'attributes'          => 'onclick="Backend.getScrollOffset()"'
		),
		'import' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['import'],
			'href'                => 'key=import',
			'icon'                => 'bundles/contaoadressen/images/importCSV.svg',
			'class'               => 'header_csv_import',
			'attributes'          => 'onclick="Backend.getScrollOffset()"'
		),
		'export' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['export'],
			'href'                => 'key=export',
			'icon'                => 'bundles/contaoadressen/images/exportCSV.svg',
			'class'               => 'header_csv_export',
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

	$GLOBALS['TL_DCA']['tl_adressen']['list']['operations'] = array
	(
		'edit' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['edit'],
			'href'                => 'act=edit',
			'icon'                => 'edit.svg'
		),
		'copy' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['copy'],
			'href'                => 'act=copy',
			'icon'                => 'copy.svg'
		),
		'delete' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['delete'],
			'href'                => 'act=delete',
			'icon'                => 'delete.svg',
			'attributes'          => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"'
		),
		'toggle' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['toggle'],
			'href'                => 'act=toggle&amp;field=aktiv',
			'icon'                => 'visible.svg'
		),
		'show' => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['show'],
			'href'                => 'act=show',
			'icon'                => 'show.svg'
		)
	);
}

/**
 * Callback-Klasse für tl_adressen
 */
class tl_adressen extends Backend
{
	/**
	 * Sammelt beim Doppel-Filter alle E-Mail-Adressen mit den zugehörigen
	 * Datensatz-IDs: [E-Mail => [ID, ID, ...]]
	 *
	 * @var array<string, list<int>>
	 */
	private array $adressensuche = array();

	/**
	 * Ersetzt die Spalte "aktiv" in der Listenansicht durch ein Statussymbol.
	 *
	 * @param array<string, mixed> $row
	 * @param string               $label
	 * @param array<int, string>   $args
	 *
	 * @return array<int, string>
	 */
	public function addIcon($row, $label, DataContainer $dc, $args)
	{
		// Anzahl der Einbindungen feststellen (jede URL steht in einer eigenen Zeile)
		$arrLinks = array_filter(array_map('trim', explode("\n", (string) ($row['links'] ?? ''))));
		$seiten   = \count($arrLinks);
		$wort     = ($seiten == 1) ? 'Seite' : 'Seiten';
		$aktiv    = (bool) ($row['aktiv'] ?? false);

		if ($aktiv && $seiten)
		{
			// Adresse aktiv, eine oder mehrere Einbindungen
			$icon  = 'bundles/contaoadressen/images/gruen_rahmen.svg';
			$title = 'Adresse eingebunden auf '.$seiten.' '.$wort;
		}
		elseif ($aktiv)
		{
			// Adresse aktiv, keine Einbindungen
			$icon  = 'bundles/contaoadressen/images/gelb_rahmen.svg';
			$title = 'Adresse aktiv, aber nicht eingebunden';
		}
		elseif ($seiten)
		{
			// Adresse nicht aktiv, aber eingebunden – das sollte nicht vorkommen
			$icon  = 'bundles/contaoadressen/images/rot_rahmen.svg';
			$title = 'Adresse nicht aktiv, aber auf '.$seiten.' '.$wort.' eingebunden';
		}
		else
		{
			// Adresse nicht aktiv und nicht eingebunden
			$icon  = 'bundles/contaoadressen/images/grau.svg';
			$title = 'Adresse deaktiviert';
		}

		// Spalte 0 (aktiv) in der Ausgabe überschreiben.
		// Die Größe steckt in den SVG-Dateien selbst (16x16); ein zusätzliches
		// width/height-Attribut würde nur doppelte Attribute erzeugen und wirkte
		// in Contao 4.13 ohnehin nicht, weil dort die Dateigröße Vorrang hat.
		$args[0] = '<span title="'.StringUtil::specialchars($title).'">'.Image::getHtml($icon, $title).'</span>';

		return $args;
	}

	/**
	 * Entfernt einen leeren Protokoll-Platzhalter aus dem Homepage-Feld.
	 *
	 * @param mixed $varValue
	 *
	 * @return mixed
	 */
	public function saveHomepage($varValue, DataContainer $dc)
	{
		$strValue = trim((string) $varValue);

		if ($strValue === 'http://' || $strValue === 'https://')
		{
			return '';
		}

		return $strValue;
	}

	/**
	 * Baut aus allen durchsuchbaren Feldern einen normalisierten Suchstring und
	 * legt ihn in tl_adressen.searchstring ab (wird vom Frontend-Suchmodul genutzt).
	 */
	public function generateSearchstring(DataContainer $dc): void
	{
		if (!$dc->id)
		{
			return;
		}

		$objAdresse = Database::getInstance()
			->prepare('SELECT * FROM tl_adressen WHERE id = ?')
			->execute($dc->id);

		if (!$objAdresse->numRows)
		{
			return;
		}

		$arrFelder = array
		(
			'nachname', 'vorname', 'firma', 'plz', 'ort', 'strasse',
			'telefon1', 'telefon2', 'telefon3', 'telefon4',
			'telefax1', 'telefax2',
			'email1', 'email2', 'email3', 'email4', 'email5', 'email6',
			'text', 'info'
		);

		$arrWerte = array();

		foreach ($arrFelder as $strFeld)
		{
			$arrWerte[] = (string) $objAdresse->$strFeld;
		}

		$strSuchstring = Funktionen::generateAlias(implode('-', $arrWerte));

		Database::getInstance()
			->prepare('UPDATE tl_adressen SET searchstring = ? WHERE id = ?')
			->execute($strSuchstring, $dc->id);
	}

	/**
	 * Erzeugt das zusätzliche Filterpanel über der Adressliste.
	 */
	public function generateAdressenFilter(): string
	{
		if (Input::get('id') > 0)
		{
			return '';
		}

		$session = System::getContainer()->get('request_stack')->getSession()->all();

		$arrFilters = array
		(
			'adr_filter' => array
			(
				'name'    => 'adr_filter',
				'label'   => $GLOBALS['TL_LANG']['tl_adressen']['filter_extended'] ?? 'Erweiterter Filter',
				'options' => array
				(
					'doubled' => $GLOBALS['TL_LANG']['tl_adressen']['filter_emaildoubles'] ?? 'Doppelte E-Mail-Adressen',
				)
			),
		);

		$strBuffer = '
<div class="tl_advanced_filter adr_filter tl_subpanel">
<strong>'.($GLOBALS['TL_LANG']['tl_adressen']['filter'] ?? 'Filter').'</strong>'."\n";

		foreach ($arrFilters as $arrFilter)
		{
			$strAktiv   = $session['filter']['tl_adressen'][$arrFilter['name']] ?? null;
			$strOptions = '
<option value="'.$arrFilter['name'].'">'.$arrFilter['label'].'</option>
<option value="'.$arrFilter['name'].'">---</option>'."\n";

			foreach ($arrFilter['options'] as $k => $v)
			{
				$strOptions .= '<option value="'.$k.'"'.((string) $strAktiv === (string) $k ? ' selected' : '').'>'.$v.'</option>'."\n";
			}

			$strBuffer .= '<select name="'.$arrFilter['name'].'" id="'.$arrFilter['name'].'" class="tl_select'.($strAktiv ? ' active' : '').'">
'.$strOptions.'
</select>'."\n";
		}

		return $strBuffer.'</div>';
	}

	/**
	 * Wertet das zusätzliche Filterpanel aus und schränkt die Listenansicht ein.
	 */
	public function applyAdressenFilter(): void
	{
		$objSession = System::getContainer()->get('request_stack')->getSession();
		$session    = $objSession->all();

		// Filterwerte aus dem Request in der Session ablegen
		$blnGeaendert = false;

		foreach (array_keys($_POST) as $k)
		{
			if (!\is_string($k) || strncmp($k, 'adr_', 4) !== 0)
			{
				continue;
			}

			$varValue = Input::post($k);

			if ($k === $varValue)
			{
				// Filter zurücksetzen (der gewählte Wert entspricht dem Namen des Filters)
				unset($session['filter']['tl_adressen'][$k]);
			}
			else
			{
				$session['filter']['tl_adressen'][$k] = $varValue;
			}

			$blnGeaendert = true;
		}

		if ($blnGeaendert)
		{
			$objSession->replace($session);
		}

		if (Input::get('id') > 0 || empty($session['filter']['tl_adressen']))
		{
			return;
		}

		$arrAdressen = null;

		foreach ($session['filter']['tl_adressen'] as $k => $v)
		{
			if (strncmp((string) $k, 'adr_', 4) !== 0)
			{
				continue;
			}

			// Adressen mit doppelt vergebenen E-Mail-Adressen anzeigen
			if ($k === 'adr_filter' && $v === 'doubled')
			{
				$arrAdressen = $this->findeDoppelteAdressen();
			}
		}

		if (\is_array($arrAdressen) && empty($arrAdressen))
		{
			// Kein Treffer: eine nicht existierende ID erzwingt eine leere Liste
			$arrAdressen = array(0);
		}

		if ($arrAdressen !== null)
		{
			$GLOBALS['TL_DCA']['tl_adressen']['list']['sorting']['root'] = $arrAdressen;
		}
	}

	/**
	 * Liefert die IDs aller Adressen, die sich mindestens eine E-Mail-Adresse
	 * mit einem anderen Datensatz teilen.
	 *
	 * @return list<int>
	 */
	private function findeDoppelteAdressen(): array
	{
		$this->adressensuche = array();

		$objAdressen = Database::getInstance()
			->prepare('SELECT id, email1, email2, email3, email4, email5, email6 FROM tl_adressen')
			->execute();

		while ($objAdressen->next())
		{
			for ($i = 1; $i <= 6; $i++)
			{
				$this->addAdresse((string) $objAdressen->{'email'.$i}, (int) $objAdressen->id);
			}
		}

		$arrIds = array();

		foreach ($this->adressensuche as $arrTreffer)
		{
			if (\count($arrTreffer) > 1)
			{
				$arrIds = array_merge($arrIds, $arrTreffer);
			}
		}

		return array_values(array_unique($arrIds));
	}

	/**
	 * Merkt sich eine E-Mail-Adresse mit der zugehörigen Datensatz-ID.
	 */
	private function addAdresse(string $strEmail, int $intId): void
	{
		$strEmail = strtolower(trim($strEmail));

		if ($strEmail === '')
		{
			return;
		}

		$this->adressensuche[$strEmail][] = $intId;
	}
}
