<?php

/**
 * Tabelle tl_adressen
 */
$GLOBALS['TL_DCA']['tl_adressen'] = array
(

	// Konfiguration
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'alias' => 'index'
			)
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
			'panelLayout'             => 'filter;sort,search,limit',
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
				'icon'                => 'bundles/contaoadressen/images/categories.png',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'import' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['import'],
				'icon'                => 'bundles/contaoadressen/images/importCSV.gif',
				'href'                => 'key=import',
				'class'               => 'header_csv_import',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['export'],
				'icon'                => 'bundles/contaoadressen/images/exportCSV.gif',
				'href'                => 'key=export',
				'class'               => 'header_csv_export',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif',
				'attributes'          => 'style="margin-right:3px"'
			),
		)
	),

	// Paletten
	'palettes' => array
	(
		'__selector__'                => array('addImage'),
		'default'                     => '{person_legende},nachname,vorname,titel,firma,club;{adresse_legende:hide},plz,ort,ort_view,strasse,strasse_view;{adressen_legend:hide},adressen;{telefon_legende:hide},telefon1,telefon2,telefon3,telefon4,telefon_view;{telefone_legend:hide},telefone;{telefax_legende:hide},telefax1,telefax2,telefax_view;{email_legende:hide},email1,email2,email3,email4,email5,email6,email_view;{emails_legend:hide},emails;{bank_legend},inhaber,iban,bic;{funktionen_legende:hide},wertungsreferent,funktionen;{web_legende:hide},homepage,facebook,twitter,google,icq,yahoo,aim,msn,irc;{image_legend:hide},addImage;{text_legende:hide},text;{info_legende:hide},info,links,source;{aktiv_legende},aktiv;{alias_legende},alias'
	),

	// Unterpaletten
	'subpalettes' => array
	(
		'addImage'                    => 'singleSRC,alt,size,imagemargin,imageUrl,fullsize,caption,floating'
	),

	// Felder
	'fields' => array
	(
		'id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['id'],
			'sorting'                 => true,
			'search'                  => true,
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
		'adressen' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['adressen'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'public_plzort' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['adressen_public_plzort'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'style'             => 'width: 20px',
							'valign'            => 'middle'
						)
					),
					'plz' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['adressen_plz'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width: 100px',
						),
					),
					'ort' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['adressen_ort'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width: 170px',
						)
					),
					'public_str' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['adressen_public_str'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'style'             => 'width: 20px',
							'valign'            => 'middle'
						)
					),
					'strasse' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['adressen_strasse'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width: 200px',
						)
					),
					'googlemap' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['adressen_googlemap'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'style'             => 'width: 20px',
							'valign'            => 'middle'
						)
					),
				)
			),
			'sql'                   => "blob NULL"
		),
		'wertungsreferent' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['wertungsreferent'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => array('tl_adressen', 'getReferenten'),
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
		'telefone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['telefone'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'public_num' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['telefone_public_num'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'style'             => 'width: 20px',
							'valign'            => 'middle'
						)
					),
					'nummer' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['telefone_nummer'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width: 200px',
						)
					),
				)
			),
			'sql'                   => "blob NULL"
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
		'emails' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['emails'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'buttonPos'           => 'top',
				'columnFields'        => array
				(
					'public_mail' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['emails_public_mail'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'style'             => 'width: 20px',
							'valign'            => 'middle'
						)
					),
					'mail' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_adressen']['emails_mail'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style'             => 'width: 300px',
							'rgxp'              => 'email'
						)
					),
				)
			),
			'sql'                   => "blob NULL"
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
		'google' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['google'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'icq' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['icq'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'yahoo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['yahoo'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'aim' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['aim'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => false,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'msn' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['msn'],
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
			'eval'                    => array('filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'fieldType'=>'radio', 'mandatory'=>true),
			'save_callback'           => array
			(
				array('tl_adressen', 'storeFileMetaInformation')
			), 
			'sql'                     => "binary(16) NULL"
		), 
		'alt' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['alt'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'long'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'size' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => System::getImageSizes(), 
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'imagemargin' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['imagemargin'],
			'exclude'                 => true,
			'inputType'               => 'trbl',
			'options'                 => array('px', '%', 'em', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),
		'imageUrl' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['imageUrl'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50 wizard'),
			'wizard' => array
			(
				array('tl_adressen', 'pagePicker')
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'fullsize' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['fullsize'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'caption' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['caption'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'allowHtml'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'floating' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['floating'],
			'exclude'                 => true,
			'inputType'               => 'radioTable',
			'options'                 => array('above', 'left', 'right', 'below'),
			'eval'                    => array('cols'=>4, 'tl_class'=>'w50'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'sql'                     => "varchar(12) NOT NULL default ''"
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
			'exclude'                 => true,
			'filter'                  => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['alias'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'save_callback' => array
			(
				array('tl_adressen', 'generateAlias')
			),
			'sql'                     => "varbinary(128) NOT NULL default ''"
		), 
		// Alte Felder, die mit runonce umkopiert werden
		//'addBild' => array
		//(
		//	'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['addBild'],
		//	'exclude'                 => true,
		//	'inputType'               => 'checkbox',
		//	'eval'                    => array('submitOnChange'=>true),
		//	'sql'                     => "char(1) NOT NULL default ''"
		//),
		//'bild' => array
		//(
		//	'label'                   => &$GLOBALS['TL_LANG']['tl_adressen']['bild'],
		//	'inputType'               => 'fileTree',
		//	'exclude'                 => true,
		//	'flag'                    => 1,
		//	'eval'                    => array('mandatory'=>false, 'files'=>true, 'files_only'=>true, 'fieldType'=>'radio', 'extensions'=>'jpg,gif,png', 'tl_class'=>'w50 clr'),
		//	'sql'                     => "varchar(255) NOT NULL default ''"
		//),
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

// Bildfeld anpassen, wenn Contao >= 3.2 aktiv ist
if(version_compare(VERSION, '3.2', '>='))
{
	$GLOBALS['TL_DCA']['tl_adressen']['fields']['singleSRC']['sql'] = "binary(16) NULL";
}

/**
 * Class tl_member_aktivicon
 */
class tl_adressen extends Backend
{
    /**
     * Add an image to each record
     * @param array
     * @param string
     * @param DataContainer
     * @param array
     * @return string
     */
	public function addIcon($row, $label, DataContainer $dc, $args)
	{
		// Anzahl Einbindungen feststellen und Singular/Plural zuweisen
		$seiten = count(explode("\n",$row['links']))-1;
		($seiten == 1) ? ($wort = 'Seite') : ($wort = 'Seiten');

		if($row['aktiv'] && $row['links'])
		{
			// Adresse aktiv, ein oder mehrere Einbindungen
			$icon = 'bundles/contaoadressen/images/gruen.png';
			$title = 'Adresse eingebunden auf '.$seiten.' '.$wort;
		}
		elseif($row['aktiv'])
		{
			// Adresse aktiv, keine Einbindungen
			$icon = 'bundles/contaoadressen/images/gelb.png';
			$title = 'Adresse aktiv, aber nicht eingebunden';
		}
		elseif($row['links'])
		{
			// Adresse nicht aktiv, ein oder mehrere Einbindungen
			$icon = 'bundles/contaoadressen/images/gelb.png';
			$title = 'Adresse nicht aktiv, aber auf '.$seiten.' '.$wort.' eingebunden';
		}
		else
		{
			// Adresse nicht aktiv, keine Einbindungen
			$icon = 'bundles/contaoadressen/images/rot.png';
			$title = 'Adresse nicht aktiv und nicht eingebunden';
		}

		// Spalte 0 (aktiv) in Ausgabe überschreiben
		$args[0] = '<span href="" title="'.$title.'">'.Image::getHtml($icon,'').'</span>';

		// Modifizierte Zeile zurückgeben
		return $args;

	}

	/**
	 * Generiert automatisch ein Alias aus Vorname und Nachname
	 * @param mixed
	 * @param \DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
			$varValue = standardize(\StringUtil::restoreBasicEntities($dc->activeRecord->nachname.'-'.$dc->activeRecord->vorname));
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_adressen WHERE alias=?")
								   ->execute($varValue);

		// Check whether the news alias exists
		if ($objAlias->numRows > 1 && !$autoAlias)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
		}

		// Add ID to alias
		if ($objAlias->numRows && $autoAlias)
		{
			$varValue .= '-' . $dc->id;
		}

		return $varValue;
	} 

	/**
	 * Generiert automatisch ein Alias aus Vorname und Nachname
	 * @param mixed
	 * @param \DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateSearchstring(DataContainer $dc)
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

		$temp = \Schachbulle\ContaoAdressenBundle\Classes\Funktionen::getFunktionen($temp);
		\Database::getInstance()->prepare("UPDATE tl_adressen SET searchstring = ? WHERE id = ?")
		                        ->execute($temp, $dc->id);
	}

	/**
	 * Return the link picker wizard
	 * @param \DataContainer
	 * @return string
	 */
	public function pagePicker(DataContainer $dc)
	{
		return ' <a href="contao/page.php?do='.Input::get('do').'&amp;table='.$dc->table.'&amp;field='.$dc->field.'&amp;value='.str_replace(array('{{link_url::', '}}'), '', $dc->value).'" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\''.specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])).'\',\'url\':this.href,\'id\':\''.$dc->field.'\',\'tag\':\'ctrl_'.$dc->field . ((Input::get('act') == 'editAll') ? '_' . $dc->id : '').'\',\'self\':this});return false">' . Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
	}

	/**
	 * Trägt die Metdaten in "alt" und "caption" ein, wenn das Bild gewechselt wird und die Metafelder leer sind
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return mixed
	 */
	public function storeFileMetaInformation($varValue, DataContainer $dc)
	{
		if ($dc->activeRecord->singleSRC != $varValue)
		{
			$objFile = \FilesModel::findByUuid($varValue);
			if ($objFile === null)
			{
				return $varValue;
			}

			$arrMeta = deserialize($objFile->meta);
			if (empty($arrMeta))
			{
				return $varValue;
			}

			$strLanguage = 'de';
			if (isset($arrMeta[$strLanguage]))
			{
				if (\Input::post('alt') == '' && !empty($arrMeta[$strLanguage]['title']))
				{
					\Input::setPost('alt', $arrMeta[$strLanguage]['title']);
				}

				if (\Input::post('caption') == '' && !empty($arrMeta[$strLanguage]['caption']))
				{
					\Input::setPost('caption', $arrMeta[$strLanguage]['caption']);
				}
			}

		}

		return $varValue;
	} 

	public function getReferenten(DataContainer $dc)
	{
		// Referate zuordnen
		$array = array
		(
			'00000' => '00000 Deutscher Schachbund',
			'10000' => '10000 Badischer Schachverband',
			'10100' => '10100 Mannheim',
			'10200' => '10200 Heidelberg',
			'10300' => '10300 Odenwald',
			'10400' => '10400 Karlsruhe',
			'10500' => '10500 Pforzheim',
			'10600' => '10600 Mittelbaden',
			'10700' => '10700 Ortenau',
			'10800' => '10800 Freiburg',
			'10900' => '10900 Hochrhein',
			'10A00' => '10A00 Schwarzwald',
			'10B00' => '10B00 Bodensee',
			'20000' => '20000 Bayerischer Schachbund e.V.',
			'21000' => '21000 Mittelfranken',
			'21100' => '21100 Mittelfranken-Mitte',
			'21200' => '21200 Mittelfranken-Nord',
			'21300' => '21300 Mittelfranken-Ost',
			'21400' => '21400 Mittelfranken-Süd',
			'21500' => '21500 Mittelfranken-West',
			'22000' => '22000 München',
			'23000' => '23000 Niederbayern',
			'24000' => '24000 BV Oberbayern e.V.',
			'24100' => '24100 Schachkreis IN-FS',
			'24200' => '24200 Schachkreis Inn-Chiemgau',
			'24400' => '24400 Schachkreis Zugspitze',
			'25000' => '25000 BV Oberfranken',
			'25100' => '25100 Kreisverband Bamberg',
			'25200' => '25200 Kreisverband Bayreuth',
			'25300' => '25300 Kreisverband Hof',
			'25400' => '25400 Kreisverband Coburg/Neustadt',
			'25500' => '25500 Kreisverband Marktredwitz',
			'25600' => '25600 Kreisverband Lichtenfels/Kronach',
			'26000' => '26000 Schachverband Oberpfalz e.V.',
			'27000' => '27000 Schwaben',
			'27100' => '27100 Augsburg',
			'27200' => '27200 Mittelschwaben',
			'27300' => '27300 Nordschwaben',
			'27400' => '27400 Südschwaben',
			'28000' => '28000 Unterfranken e.V',
			'28100' => '28100 Spessart/Untermain',
			'28200' => '28200 Mainspessart',
			'28300' => '28300 Haßberge-Rhön',
			'28400' => '28400 Maindreieck',
			'30000' => '30000 Berliner Schachverband',
			'40000' => '40000 Hamburger Schachverband',
			'50000' => '50000 Hessischer Schachverband',
			'51000' => '51000 Kassel-Nordhessen',
			'52000' => '52000 Osthessen',
			'53000' => '53000 Lahn-Eder',
			'54000' => '54000 Main-Vogelsberg',
			'55000' => '55000 Frankfurt',
			'56000' => '56000 Starkenburg',
			'57000' => '57000 Main-Taunus',
			'58000' => '58000 Rhein-Taunus',
			'59000' => '59000 Lahn',
			'5A000' => '5A000 Bergstraße',
			'60000' => '60000 Schachbund Nordrhein-Westfalen e.V.',
			'61000' => '61000 SV Ruhrgebiet e.V.',
			'61100' => '61100 Schachbezirk Bochum',
			'61200' => '61200 Schachgemeinschaft Dortmund',
			'61300' => '61300 Schachbezirk Essen',
			'61400' => '61400 Schachbezirk Emscher-Lippe',
			'61500' => '61500 Schachbezirk Hamm',
			'61600' => '61600 Mülheim an der Ruhr 1922 e.V.',
			'61700' => '61700 Schachbezirk Herne - Vest',
			'62000' => '62000 Niederrheinischer Schachverband 1901 e.V.',
			'62100' => '62100 Schachbezirk Bergisch-Land',
			'62200' => '62200 Schachbezirk Düsseldorf',
			'62300' => '62300 Schachbezirk Duisburg',
			'62400' => '62400 Linker Niederrhein',
			'62500' => '62500 Schachbezirk Kreis Wesel e.V.',
			'63000' => '63000 Schachverband Südwestfalen',
			'63200' => '63200 Schachbezirk Iserlohn',
			'63300' => '63300 Schachbezirk Oberberg',
			'63400' => '63400 Schachbezirk Hochsauerland',
			'63500' => '63500 Schachbezirk Sauerland',
			'63600' => '63600 Schachbezirk Siegerland',
			'64000' => '64000 Schachverband Ostwestfalen-Lippe',
			'64100' => '64100 Schachbezirk Bielefeld',
			'64200' => '64200 Schachbezirk Hellweg',
			'64300' => '64300 Schachbezirk Lippe',
			'64400' => '64400 Schachbezirk Porta',
			'64500' => '64500 Schachbezirk Teutoburger Wald-West',
			'65000' => '65000 Schachverband Münsterland',
			'65100' => '65100 Schachbezirk Steinfurt',
			'65200' => '65200 Schachbezirk Borken',
			'65300' => '65300 Schachbezirk Münster',
			'66000' => '66000 Schachverband Mittelrhein e.V.',
			'66100' => '66100 Aachener Schachverband 1928 e.V.',
			'66200' => '66200 Bonn/Rhein-Sieg e.V.',
			'66300' => '66300 Kölner Schachverband von 1920 e.V.',
			'66400' => '66400 Schachbezirk Rur-Erft',
			'66500' => '66500 Schachbezirk Rhein-Wupper',
			'70000' => '70000 Niedersächsischer Schachverband e. V.',
			'70100' => '70100 Bezirk 1 Hannover',
			'70200' => '70200 Bezirk 2 Braunschweig',
			'70300' => '70300 Bezirk 3 Südniedersachsen',
			'70400' => '70400 Bezirk 4 Lüneburg',
			'70500' => '70500 Bezirk 5 Oldenburg-Ostfriesland',
			'70600' => '70600 Bezirk 6 Osnabrück-Emsland',
			'80000' => '80000 SB Rheinland-Pfalz e.V.',
			'81000' => '81000 Schachverband Rheinland e.V.',
			'81100' => '81100 Bezirk I Rhein-Ahr-Mosel',
			'81200' => '81200 Bezirk II Rhein-Nahe',
			'81300' => '81300 Bezirk III Rhein-Westerwald',
			'81500' => '81500 Bezirk IV Trier',
			'82000' => '82000 SB Rheinhessen e.V.',
			'83000' => '83000 Pfälzischer Schachbund e.V.',
			'83100' => '83100 Bezirk I Kaiserslautern',
			'83200' => '83200 Bezirk II Ludwigshafen',
			'83300' => '83300 Bezirk III Neustadt',
			'83400' => '83400 Bezirk IV Landau',
			'83500' => '83500 Bezirk V Pirmasens',
			'83600' => '83600 Bezirk VI Ramstein',
			'90000' => '90000 Saarländischer Schachverband',
			'A0000' => 'A0000 SVB Schleswig-Holstein',
			'A0100' => 'A0100 Bezirk I Nord',
			'A0200' => 'A0200 Bezirk II West',
			'A0600' => 'A0600 Bezirk VI Kiel',
			'A0800' => 'A0800 Bezirk Ost',
			'B0000' => 'B0000 Landesschachbund Bremen',
			'C0000' => 'C0000 Schachverband Württemberg e.V.',
			'C0100' => 'C0100 Bezirk Oberschwaben',
			'C0200' => 'C0200 Bezirk Alb/Schwarzwald',
			'C0300' => 'C0300 Bezirk Neckar-Fils',
			'C0400' => 'C0400 Bezirk Ostalb',
			'C0500' => 'C0500 Bezirk Stuttgart',
			'C0600' => 'C0600 Bezirk Unterland-Hohenlohe',
			'C11' => 'C11 Kreis Oberschwaben Nord',
			'C12' => 'C12 Kreis Oberschwaben Süd',
			'C21' => 'C21 Kreis Zollern Alb',
			'C22' => 'C22 Kreis Donau Neckar',
			'C23' => 'C23 Kreis Schwarzwald',
			'C31' => 'C31 Kreis Esslingen/Nürtingen',
			'C32' => 'C32 Kreis Reutlingen/Tübingen',
			'C33' => 'C33 Kreis Filstal',
			'C41' => 'C41 Kreis Aalen',
			'C42' => 'C42 Kreis Heidenheim',
			'C43' => 'C43 Kreis Schwäbisch Gmünd',
			'C51' => 'C51 Kreis Stuttgart Ost',
			'C52' => 'C52 Kreis Stuttgart Mitte',
			'C53' => 'C53 Kreis Stuttgart West',
			'C61' => 'C61 Kreis Heilbronn/Hohenlohe',
			'C62' => 'C62 Kreis Ludwigsburg',
			'D0000' => 'D0000 Schachbund Brandenburg',
			'D1000' => 'D1000 Cottbus',
			'D2000' => 'D2000 Frankfurt/O.',
			'D3000' => 'D3000 Potsdam',
			'E0000' => 'E0000 LSV Mecklenburg-Vorpommern',
			'E0100' => 'E0100 Spielbezirk West',
			'E0200' => 'E0200 Spielbezirk Mitte',
			'E0300' => 'E0300 Spielbezirk Ost',
			'F0000' => 'F0000 Schachverband Sachsen e.V.',
			'F1000' => 'F1000 Leipzig',
			'F1100' => 'F1100 Landkreis Delitzsch',
			'F1200' => 'F1200 Landkreis Döbeln',
			'F1300' => 'F1300 Landkreis Torgau-Oschatz',
			'F1500' => 'F1500 Stadt Leipzig',
			'F1800' => 'F1800 Kreis Leipziger Land',
			'F1900' => 'F1900 Muldentalkreis',
			'F2000' => 'F2000 Dresden',
			'F2100' => 'F2100 Landkreis Riesa-Großenhain',
			'F2200' => 'F2200 Landkreis Sächsische Schweiz',
			'F2300' => 'F2300 Landkreis Kamenz',
			'F2400' => 'F2400 Stadt Hoyerswerda',
			'F2500' => 'F2500 Weiseritzkreis',
			'F2600' => 'F2600 Landkreis Meißen',
			'F2700' => 'F2700 Stadt Görlitz',
			'F2800' => 'F2800 Stadt Dresden',
			'F2900' => 'F2900 Landkreis Löbau-Zittau',
			'F2A00' => 'F2A00 Landkreis Bautzen',
			'F2B00' => 'F2B00 Niederschlesischer Oberlausitzkreis',
			'F3000' => 'F3000 Chemnitz',
			'F3100' => 'F3100 Landkreis Stollberg',
			'F3200' => 'F3200 Landkreis Mittweida',
			'F3300' => 'F3300 Landkreis Freiberg',
			'F3400' => 'F3400 Landkreis Chemnitzer Land',
			'F3500' => 'F3500 Landkreis Annaberg',
			'F3600' => 'F3600 Stadt Chemnitz',
			'F3700' => 'F3700 Vogtlandkreis',
			'F3800' => 'F3800 Stadt Zwickau',
			'F3900' => 'F3900 Mittlerer Erzgebirgskreis',
			'F3A00' => 'F3A00 Landkreis Zwicker Land',
			'F3B00' => 'F3B00 Stadt Plauen',
			'F3C00' => 'F3C00 Landkreis Aue-Schwarzenberg',
			'G0000' => 'G0000 LSV Sachsen-Anhalt',
			'G0100' => 'G0100 Schachbezirk Dessau',
			'G0200' => 'G0200 Schachbezirk Halle',
			'G0300' => 'G0300 Schachbezirk Magdeburg',
			'H0000' => 'H0000 Thüringer Schachbund',
			'H1000' => 'H1000 Schachbezirk Nord',
			'H1100' => 'H1100 Schachkreis Kyffhäuser',
			'H1200' => 'H1200 Schachkreis Eichsfeld',
			'H1300' => 'H1300 Schachkreis Nordhausen',
			'H1400' => 'H1400 Schachkreis Unstrut-Hainich',
			'H1500' => 'H1500 Schachkreis Gotha',
			'H2000' => 'H2000 Schachbezirk Mitte',
			'H2100' => 'H2100 Schachkreis Erfurt',
			'H2200' => 'H2200 Schachkreis Weimarer Land',
			'H2300' => 'H2300 Schachkreis Sömmerda',
			'H2400' => 'H2400 Schachkreis Ilm-Kreis',
			'H2500' => 'H2500 Schachkreis Weimar',
			'H3000' => 'H3000 Schachbezirk Ost',
			'H3100' => 'H3100 Schachkreis Gera',
			'H3200' => 'H3200 Schachkreis Jena/Holzlandkreis',
			'H3300' => 'H3300 Schachkreis Greiz',
			'H3400' => 'H3400 Schachkreis Altenburger Land',
			'H3500' => 'H3500 Schachkreis Saalfeld-Rudolstadt/SOK',
			'H4000' => 'H4000 Schachbezirk Süd',
			'H4100' => 'H4100 Schachkreis Schmalkalden-Meiningen/Suhl',
			'H4300' => 'H4300 Schachkreis Wartburgkreis/Eisenach',
			'K0000' => 'K0000 Ausländer',
			'L0000' => 'L0000 Deutscher Blinden- und Sehbehinderten-Schachbund',
			'M0000' => 'M0000 Schwalbe'
		);
		return $array;

	}

}
