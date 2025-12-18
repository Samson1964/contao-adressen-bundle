<?php

/**
 * Tabelle tl_adressen
 */
$GLOBALS['TL_DCA']['tl_adressen'] = array
(

	// Konfiguration
	'config' => array
	(
		'dataContainer'               => \Contao\DC_Table::class,
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
			'mode'                    => 2,
			'fields'                  => array('nachname','vorname'),
			'flag'                    => 1,
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
		'global_operations' => array
		(
			'categories' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['categories'],
				'href'                => 'table=tl_adressen_categories',
				'primary'             => true,
				'icon'                => 'bundles/contaoadressen/images/categories.svg',
				'attributes'          => 'width="16" height="16"'
			),
			'import' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['import'],
				'icon'                => 'bundles/contaoadressen/images/importCSV.svg',
				'primary'             => false,
				'href'                => 'key=import',
				'class'               => 'header_csv_import',
				'attributes'          => 'width="16" height="16"'
			),
			'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['export'],
				'icon'                => 'bundles/contaoadressen/images/exportCSV.svg',
				'primary'             => false,
				'href'                => 'key=export',
				'class'               => 'header_csv_export',
				'attributes'          => 'width="16" height="16"'
			),
			'!all'
		),
		'operations' => array
		(
			'!edit',
			'!copy',
			'!delete',
			'toggle' => array
			(
				'href'                => 'act=toggle&amp;field=aktiv',
				'icon'                => 'visible.svg',
				'primary'             => true,
				'showInHeader'        => true
			),
			'!show'
		)
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{person_legende},nachname,vorname,titel,firma,club;{adresse_legende:hide},plz,ort,ort_view,strasse,strasse_view;{telefon_legende:hide},telefon1,telefon2,telefon3,telefon4,telefon_view;{telefax_legende:hide},telefax1,telefax2,telefax_view;{email_legende:hide},email1,email2,email3,email4,email5,email6,email_view;{bank_legend},inhaber,iban,bic;{funktionen_legende:hide},wertungsreferent,funktionen;{web_legende:hide},homepage,facebook,twitter,instagram,skype,whatsapp,threema,telegram,irc;{image_legend:hide},singleSRC;{text_legende:hide},text;{info_legende:hide},info,links,source;{aktiv_legende},aktiv;{publish_legend},published'
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
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			),
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
			'options_callback'        => array('Schachbulle\ContaoAdressenBundle\Classes\Funktionen', 'getFunktionen'),
			'eval'                    => array('tl_class'=>'w50', 'multiple'=>true),
			'sql'                     => "text NULL"
		),
		'telefon_view' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefon_view'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			),
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
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			),
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
			'eval'                    => array
			(
				'tl_class'            => 'w50'
			),
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
			'eval'                    => array('mandatory'=>false, 'maxlength'=>22, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(22) NOT NULL default ''"
		),
		'bic' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['bic'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>11, 'tl_class'=>'w50'),
			'sql'                     => "varchar(11) NOT NULL default ''"
		),
		'homepage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['homepage'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'default'                 => 'http://',
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
		'addImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['addImage'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'submitOnChange'      => true,
				'tl_class'            => 'w50'
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'filesOnly'           => true,
				'extensions'          => \Contao\Config::get('validImageTypes'),
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
			'flag'                    => \Contao\DataContainer::SORT_INITIAL_LETTER_ASC,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => array('type' => 'boolean', 'default' => true)
		),
		'prozentx' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['prozentx'],
			'exclude'                 => true,
			'default'                 => 50,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_ADRESSEN'],
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
		),
		'prozenty' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['prozenty'],
			'exclude'                 => true,
			'default'                 => 50,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_ADRESSEN'],
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
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
		// Feld, das alle Strings enthält, die durchsucht werden können
		'searchstring' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['searchstring'],
			'inputType'               => 'textarea',
			'sql'                     => "text NULL"
		),
	)
);

/**
 * Class tl_member_aktivicon
 */
class tl_adressen extends \Contao\Backend
{
	var $adressensuche = array();

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @param DataContainer
     * @param array
     * @return string
     */
	public function addIcon($row, $label, \Contao\DataContainer $dc, $args)
	{
		// Anzahl Einbindungen feststellen und Singular/Plural zuweisen
		$seiten = count(explode("\n",$row['links']))-1;
		($seiten == 1) ? ($wort = 'Seite') : ($wort = 'Seiten');

		if(!$row['aktiv'])
		{
			// Adresse deaktiviert
			$icon = 'bundles/contaoadressen/images/grau.svg';
			$title = 'Adresse deaktiviert';
		}
		elseif($row['aktiv'] && $row['links'])
		{
			// Adresse aktiv, ein oder mehrere Einbindungen
			$icon = 'bundles/contaoadressen/images/gruen_rahmen.svg';
			$title = 'Adresse eingebunden auf '.$seiten.' '.$wort;
		}
		elseif($row['aktiv'])
		{
			// Adresse aktiv, keine Einbindungen
			$icon = 'bundles/contaoadressen/images/gelb_rahmen.svg';
			$title = 'Adresse aktiv, aber nicht eingebunden';
		}
		elseif($row['links'])
		{
			// Adresse nicht aktiv, ein oder mehrere Einbindungen
			$icon = 'bundles/contaoadressen/images/gelb_rahmen.svg';
			$title = 'Adresse nicht aktiv, aber auf '.$seiten.' '.$wort.' eingebunden';
		}
		else
		{
			// Adresse nicht aktiv, keine Einbindungen
			$icon = 'bundles/contaoadressen/images/rot_rahmen.svg';
			$title = 'Adresse nicht aktiv und nicht eingebunden';
		}

		// Spalte 0 (aktiv) in Ausgabe überschreiben
		$args[0] = '<span><a href="" title="'.$title.'">'.\Contao\Image::getHtml($icon, '', 'width="16" height="16"').'</a></span>';

		// Modifizierte Zeile zurückgeben
		return $args;

	}


	public function saveHomepage($varValue, \Contao\DataContainer $dc)
	{
		// Ersetzt http:// wenn nichts dahinter steht
		if($varValue == 'http://') $varValue = '';
		return $varValue;
	}

	/**
	 * Generiert automatisch ein Alias aus Vorname und Nachname
	 * @param mixed
	 * @param \DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateSearchstring(\Contao\DataContainer $dc)
	{
		$temp = $dc->activeRecord->nachname;
		$temp .= '-'.$dc->activeRecord->vorname;
		$temp .= '-'.$dc->activeRecord->firma;
		$temp .= '-'.$dc->activeRecord->plz;
		$temp .= '-'.$dc->activeRecord->ort;
		$temp .= '-'.$dc->activeRecord->strasse;
		$temp .= '-'.$dc->activeRecord->telefon1;
		$temp .= '-'.$dc->activeRecord->telefon2;
		$temp .= '-'.$dc->activeRecord->telefon3;
		$temp .= '-'.$dc->activeRecord->telefon4;
		$temp .= '-'.$dc->activeRecord->telefax1;
		$temp .= '-'.$dc->activeRecord->telefax2;
		$temp .= '-'.$dc->activeRecord->email1;
		$temp .= '-'.$dc->activeRecord->email2;
		$temp .= '-'.$dc->activeRecord->email3;
		$temp .= '-'.$dc->activeRecord->email4;
		$temp .= '-'.$dc->activeRecord->email5;
		$temp .= '-'.$dc->activeRecord->email6;
		$temp .= '-'.$dc->activeRecord->text;
		$temp .= '-'.$dc->activeRecord->info;

		$temp = \Schachbulle\ContaoAdressenBundle\Classes\Funktionen::generateAlias($temp);
		\Contao\Database::getInstance()->prepare("UPDATE tl_adressen SET searchstring = ? WHERE id = ?")
		                               ->execute($temp, $dc->id);
	}

	/**
	 * Return the link picker wizard
	 * @param \DataContainer
	 * @return string
	 */
	public function pagePicker(\Contao\DataContainer $dc)
	{
		return ' <a href="contao/page.php?do='.Input::get('do').'&amp;table='.$dc->table.'&amp;field='.$dc->field.'&amp;value='.str_replace(array('{{link_url::', '}}'), '', $dc->value).'" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\''.specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])).'\',\'url\':this.href,\'id\':\''.$dc->field.'\',\'tag\':\'ctrl_'.$dc->field . ((Input::get('act') == 'editAll') ? '_' . $dc->id : '').'\',\'self\':this});return false">' . Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
	}

	public function addAdresse($email, $id)
	{
		if($email)
		{
			$this->adressensuche[$email][] = $id;
		}
	}

	public function getAdressen()
	{
		$idArray = array();
		//echo '<pre>';
		//print_r($this->adressensuche);
		//echo '</pre>';
		$zaehler = 0;
		foreach($this->adressensuche as $email => $arr)
		{
			if(count($arr) > 1)
			{
				$zaehler++;
				foreach($arr as $id)
				{
					$idArray[] = $id;
				}
			}
		}
		//echo "Zähler: $zaehler";
		//print_r($idArray);
		return array_unique($idArray);
	}

	/**
	 * Generate advanced filter panel and return them as HTML
	 * @return string
	 */
	public static function generateAdressenFilter()
	{
		if(\Contao\Input::get('id') > 0) 
		{
			return '';
		}
		
		$objSession = \Contao\System::getContainer()->get('request_stack')->getSession();
		$session = $objSession->all();

		// Filter
		$arrFilters = array
		(
			'adr_filter' => array
			(
				'name'    => 'adr_filter',
				'label'   => $GLOBALS['TL_LANG']['tl_adressen']['filter_extended'],
				'options' => array
				(
					'doubled'  => $GLOBALS['TL_LANG']['tl_adressen']['filter_emaildoubles'],
				)
			),
		);
		
		$strBuffer = '
<div class="tl_advanced_filter adr_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['tl_adressen']['filter'] . '</strong>' . "\n";

		// Generiere Filter
		foreach($arrFilters as $arrFilter) 
		{
			$strOptions = '
<option value="' . $arrFilter['name'] . '">' . $arrFilter['label'] . '</option>
<option value="' . $arrFilter['name'] . '">---</option>' . "\n";

			// Generiere Optionen
			foreach($arrFilter['options'] as $k => $v) 
			{
				$strOptions .= '<option value="' . $k . '"' . ((($session['filter']['tl_adressen'][$arrFilter['name']] ?? null) === (string) $k) ? ' selected' : '') . '>' . $v . '</option>' . "\n";
			}

			$strBuffer .= '<select name="' . $arrFilter['name'] . '" id="' . $arrFilter['name'] . '" class="tl_select' . (isset($session['filter']['tl_iso_product'][$arrFilter['name']]) ? ' active' : '') . '">
' . $strOptions . '
</select>' . "\n";
		}
		
		return $strBuffer . '</div>';
	}

	/**
	 * Apply advanced filters to product list view
	 * @return void
	 */
	public function applyAdressenFilter()
	{
		$objSession = \Contao\System::getContainer()->get('request_stack')->getSession();
		$session = $objSession->all();
		
		// Store filter values in the session
		foreach ($_POST as $k => $v) 
		{
			if (substr($k, 0, 4) != 'adr_') 
			{
				continue;
			}
			
			// Reset the filter
			if ($k == \Contao\Input::post($k)) 
			{
				unset($session['filter']['tl_adressen'][$k]);
			} // Apply the filter
			else 
			{
				$session['filter']['tl_adressen'][$k] = \Contao\Input::post($k);
			}
		}
		
		$objSession->replace($session);
		
		if (\Contao\Input::get('id') > 0 || !isset($session['filter']['tl_adressen'])) 
		{
			return;
		}
		
		$arrAdressen = null;
		
		// Filter the products
		foreach ($session['filter']['tl_adressen'] as $k => $v) 
		{
			if (substr($k, 0, 4) != 'adr_') 
			{
				continue;
			}
			
			switch ($k) 
			{
				case 'adr_filter': // Adressen mit doppelten E-Mail-Adressen anzeigen
					switch ($v) 
					{
						case 'doubled': // Adressen mit doppelten E-Mail-Adressen anzeigen
				
						$objAdressen = \Contao\Database::getInstance()->prepare("SELECT * FROM tl_adressen")
						                                              ->execute();
						if($objAdressen->numRows)
						{
							// Alle E-Mail-Adressen in Array mit Verweis auf Datensatz-ID speichern
							while($objAdressen->next())
							{
								self::addAdresse($objAdressen->email1, $objAdressen->id);
								self::addAdresse($objAdressen->email2, $objAdressen->id);
								self::addAdresse($objAdressen->email3, $objAdressen->id);
								self::addAdresse($objAdressen->email4, $objAdressen->id);
								self::addAdresse($objAdressen->email5, $objAdressen->id);
								self::addAdresse($objAdressen->email6, $objAdressen->id);
							}
						}
						$arrAdressen = self::getAdressen();
						//print_r($arrAdressen);
						break;
			
					default:  
				}
			}
		}
		
		if (\is_array($arrAdressen) && empty($arrAdressen)) 
		{
			$arrAdressen = array(0);
		}
		
		$GLOBALS['TL_DCA']['tl_adressen']['list']['sorting']['root'] = $arrAdressen;
	}

}
