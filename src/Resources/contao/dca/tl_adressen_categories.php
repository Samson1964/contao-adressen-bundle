<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package News
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_adressen_categories
 */
$GLOBALS['TL_DCA']['tl_adressen_categories'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'switchToEdit'                => true, 
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                 => 'primary',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('category'),
			'flag'                    => 1,
			'panelLayout'             => 'myfilter;filter,sort;search,limit',
		),
		'label' => array
		(
			'fields'                  => array('category'),
			'format'                  => '%s',
		),
		'global_operations' => array
		(
			'adressen' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['adressen'],
				'href'                => 'table=tl_adressen',
				'icon'                => 'bundles/contaoadressen/images/icon.png',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			/*'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
			),*/
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['toggle'],
				'attributes'           => 'onclick="Backend.getScrollOffset()"',
				'haste_ajax_operation' => array
				(
					'field'            => 'active',
					'options'          => array
					(
						array('value' => '', 'icon' => 'invisible.svg'),
						array('value' => '1', 'icon' => 'visible.svg'),
					),
				),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},category;{active_legend},active'
	),

	// Fields
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
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'long'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'active' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_adressen_categories']['active'],
			'inputType'               => 'checkbox',
			'exclude'                 => true,
			'default'                 => 1,
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default '1'"
		),
	)
);


/**
 * Class tl_adressen_categories
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    News
 */
class tl_adressen_categories extends \Contao\Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

}
