<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Schachbulle\ContaoAdressenBundle\ContaoAdressenBundle;

/*
 * Paletten
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_selectmails';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_addImage';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'adresse_altformat';

// Das Feld "guest" gibt es nur bis Contao 4.13, "space" existiert seit Contao 4 gar nicht mehr
$strExpert = ContaoAdressenBundle::isContao5() ? 'cssID' : 'guest,cssID';

// Für ein abweichendes Template wird das Contao-Standardfeld "customTpl" genutzt
$GLOBALS['TL_DCA']['tl_content']['palettes']['adressen'] = '{type_legend},type,headline;{adresse_legend},adresse_id,adresse_funktion,adresse_zusatz,adresse_selectmails;{adressefoto_legend},adresse_addImage,adresse_altformat;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},'.$strExpert.';{invisible_legend:hide},invisible,start,stop';

unset($strExpert);

// Unterpaletten
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_selectmails'] = 'adresse_mails';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_addImage'] = 'adresse_bildvorschau,singleSRC';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['adresse_altformat'] = 'size';

/*
 * Felder
 */

// Adressenliste anzeigen
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_id'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_id'],
	'exclude'              => true,
	'options_callback'     => array('tl_content_adresse', 'getAdressenListe'),
	'inputType'            => 'select',
	'eval'                 => array
	(
		'mandatory'        => false,
		'multiple'         => false,
		'chosen'           => true,
		'submitOnChange'   => true,
		'includeBlankOption' => true,
		'tl_class'         => 'wizard'
	),
	'wizard'               => array
	(
		array('tl_content_adresse', 'editAdresse')
	),
	'sql'                  => "int(10) unsigned NOT NULL default '0'"
);

// Funktion (wird vor dem Namen angezeigt)
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_funktion'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_funktion'],
	'exclude'              => true,
	'search'               => true,
	'inputType'            => 'text',
	'eval'                 => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
	'sql'                  => "varchar(255) NOT NULL default ''"
);

// Zusatztext (wird zwischen der Funktion und dem Namen angezeigt)
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_zusatz'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_zusatz'],
	'exclude'              => true,
	'search'               => true,
	'inputType'            => 'text',
	'eval'                 => array('maxlength'=>255, 'tl_class'=>'w50', 'allowHtml'=>true),
	'sql'                  => "varchar(255) NOT NULL default ''"
);

// Zeigt das Standardfoto aus tl_adressen an
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_bildvorschau'] = array
(
	'exclude'              => true,
	'input_field_callback' => array('tl_content_adresse', 'getThumbnail'),
);

// Fotoanzeige aktivieren
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_addImage'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_addImage'],
	'exclude'              => true,
	'filter'               => true,
	'default'              => true,
	'inputType'            => 'checkbox',
	'eval'                 => array
	(
		'submitOnChange'   => true,
		'tl_class'         => 'w50'
	),
	'sql'                  => "char(1) NOT NULL default '1'"
);

// Abweichendes Bildformat aktivieren
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_altformat'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_altformat'],
	'exclude'              => true,
	'filter'               => true,
	'default'              => false,
	'inputType'            => 'checkbox',
	'eval'                 => array
	(
		'submitOnChange'   => true,
		'tl_class'         => 'w50'
	),
	'sql'                  => "char(1) NOT NULL default ''"
);

// Nur ausgewählte E-Mail-Adressen anzeigen
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_selectmails'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_selectmails'],
	'exclude'              => true,
	'filter'               => true,
	'inputType'            => 'checkbox',
	'eval'                 => array
	(
		'submitOnChange'   => true,
		'tl_class'         => 'clr w50'
	),
	'sql'                  => "char(1) NOT NULL default ''"
);

// Anzuzeigende E-Mail-Adressen auswählen
$GLOBALS['TL_DCA']['tl_content']['fields']['adresse_mails'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['adresse_mails'],
	'exclude'              => true,
	'inputType'            => 'checkboxWizard',
	'options_callback'     => array('tl_content_adresse', 'getMails'),
	'eval'                 => array
	(
		'tl_class'         => 'w50',
		'multiple'         => true
	),
	'sql'                  => "blob NULL"
);

// Feld singleSRC bei Adressen-Elementen anpassen
$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['load_callback'][] = array('tl_content_adresse', 'setSingleSrcFlags');

/**
 * Callback-Klasse für die Adressen-Felder in tl_content
 */
class tl_content_adresse extends Backend
{
	/**
	 * Passt die Eigenschaften des Feldes "singleSRC" für Adressen-Elemente an.
	 *
	 * @param mixed $varValue
	 *
	 * @return mixed
	 */
	public function setSingleSrcFlags($varValue, DataContainer $dc)
	{
		if (($dc->activeRecord->type ?? null) === 'adressen')
		{
			$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['eval']['mandatory'] = false;
			$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['eval']['tl_class'] = 'w50';
			$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['eval']['extensions'] = Config::get('validImageTypes');
			$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['label'] = &$GLOBALS['TL_LANG']['tl_content']['adresse_singleSRC'];
		}

		return $varValue;
	}

	/**
	 * Erzeugt den Bearbeiten-Assistenten neben der Adressauswahl.
	 */
	public function editAdresse(DataContainer $dc): string
	{
		$intId = (int) $dc->value;

		if ($intId < 1)
		{
			return '';
		}

		$title = sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'] ?? 'Adresse ID %s bearbeiten', $intId);

		// Backend-URL und Request-Token über die Services erzeugen
		// (contao/main.php und die Konstante REQUEST_TOKEN existieren in Contao 5 nicht mehr)
		$strToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
		$strUrl = System::getContainer()->get('router')->generate('contao_backend', array
		(
			'do'    => 'adressen',
			'table' => 'tl_adressen',
			'act'   => 'edit',
			'id'    => $intId,
			'popup' => 1,
			'rt'    => $strToken,
		));

		return ' <a href="'.StringUtil::specialchars($strUrl).'" title="'.StringUtil::specialchars($title).'" onclick="Backend.openModalIframe({\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'", $title)).'\',\'url\':this.href});return false">'.Image::getHtml('alias.svg', $title).'</a>';
	}

	/**
	 * Liefert alle Adressen für die Auswahlliste.
	 *
	 * @return array<int, string>
	 */
	public function getAdressenListe(DataContainer $dc): array
	{
		$arrOptions = array();

		$objAdresse = Database::getInstance()
			->prepare('SELECT id, nachname, vorname, firma, ort, aktiv FROM tl_adressen ORDER BY nachname ASC, vorname ASC')
			->execute();

		while ($objAdresse->next())
		{
			$strStatus = $objAdresse->aktiv ? '' : ($GLOBALS['TL_LANG']['tl_content']['adresse_nichtaktiv'] ?? ' (nicht aktiv)');

			$strLabel = $objAdresse->nachname
				? $objAdresse->nachname.', '.$objAdresse->vorname.$strStatus
				: '(Firma) '.$objAdresse->firma.$strStatus;

			$arrOptions[(int) $objAdresse->id] = $strLabel.($objAdresse->ort ? ' ('.$objAdresse->ort.')' : '');
		}

		return $arrOptions;
	}

	/**
	 * Liefert alle E-Mail-Adressen der gewählten Adresse.
	 *
	 * @return list<string>
	 */
	public function getMails(DataContainer $dc): array
	{
		$intAdresse = (int) ($dc->activeRecord->adresse_id ?? 0);

		if ($intAdresse < 1)
		{
			return array();
		}

		$objAdresse = Database::getInstance()
			->prepare('SELECT email1, email2, email3, email4, email5, email6 FROM tl_adressen WHERE id = ?')
			->execute($intAdresse);

		if (!$objAdresse->numRows)
		{
			return array();
		}

		$arrMails = array();

		for ($i = 1; $i <= 6; $i++)
		{
			$strMail = (string) $objAdresse->{'email'.$i};

			if ($strMail !== '')
			{
				$arrMails[] = $strMail;
			}
		}

		return $arrMails;
	}

	/**
	 * Zeigt das in tl_adressen hinterlegte Standardfoto als Vorschau an.
	 */
	public function getThumbnail(DataContainer $dc): string
	{
		$intAdresse = (int) ($dc->activeRecord->adresse_id ?? 0);

		if ($intAdresse < 1)
		{
			return '';
		}

		$objAdresse = Database::getInstance()
			->prepare('SELECT singleSRC FROM tl_adressen WHERE id = ?')
			->execute($intAdresse);

		// Bild ermitteln (singleSRC ist eine binäre UUID)
		$varUuid = ($objAdresse->numRows && $objAdresse->singleSRC)
			? $objAdresse->singleSRC
			: Config::get('adressen_defaultImage');

		$strBild = '';
		$objFile = $varUuid ? FilesModel::findByUuid($varUuid) : null;

		if ($objFile !== null && $objFile->path)
		{
			// Vorschaubild über den Image-Studio-Service erzeugen
			// (Controller::addImageToTemplate() existiert in Contao 5 nicht mehr)
			$figure = System::getContainer()->get('contao.image.studio')
				->createFigureBuilder()
				->fromPath($objFile->path)
				->setSize(StringUtil::deserialize(Config::get('adressen_ImageSize')))
				->buildIfResourceExists();

			if ($figure !== null)
			{
				$objBild = new \stdClass();
				$figure->applyLegacyTemplateData($objBild);

				$strBild = '<img src="'.StringUtil::specialchars((string) ($objBild->src ?? '')).'" alt="'.StringUtil::specialchars((string) ($objBild->alt ?? '')).'" title="'.StringUtil::specialchars((string) ($objBild->imageTitle ?? '')).'"'.($objBild->imgSize ?? '').'>';
			}
		}

		return '
<div class="w50 clr widget">
  <h3><label>'.($GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau'][0] ?? '').'</label></h3>
  '.$strBild.'
  <p class="tl_help tl_tip" title="">'.($GLOBALS['TL_LANG']['tl_content']['adresse_bildvorschau'][1] ?? '').'</p>
</div>';
	}
}
