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
		'dataContainer'               => \Contao\DC_Table::class,
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
			'defaultSearchField'      => 'category',
			'panelLayout'             => 'filter,sort;search,limit',
		),
		'label' => array
		(
			'fields'                  => array('category'),
			'format'                  => '%s',
			//'label_callback'          => array('tl_adressen_categories', 'addPublishedType'),
		),
		'global_operations' => array
		(
			'adressen' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_adressen_categories']['adressen'],
				'href'                => 'table=tl_adressen',
				'primary'             => true,
				'icon'                => 'bundles/contaoadressen/images/icon.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'!all'
		),
		'operations' => array
		(
			'!edit',
			'toggle' => array
			(
				'href'                => 'act=toggle&amp;field=active',
				'icon'                => 'visible.svg',
				'primary'             => true,
			),
			'!show'
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
			'toggle'                  => true,
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => \Contao\DataContainer::SORT_INITIAL_LETTER_ASC,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true, 'tl_class' => 'w50'),
			'sql'                     => array('type' => 'boolean', 'default' => true)
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

	public function addPublishedType($row, $label, \Contao\DataContainer $dc, $args)
	{
		$css = $row['active'] ? 'published' : 'unpublished';

		$args[0] = '<span class="'.$css.'">'.$args[0].'</span>';
		//$args[1] = $args[1] . '<a href="' . $row['fileLink'] . '"><img src="path/to/icon.png"></a>'; // $args holds an array to your fields and their values. In my case $args[1] refers to the 'fileName' column
		return $args;
	}
}
