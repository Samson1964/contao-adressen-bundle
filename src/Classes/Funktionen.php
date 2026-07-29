<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

use Contao\Database;
use Contao\StringUtil;

/**
 * Allgemeine Hilfsfunktionen der Adressen-Verwaltung.
 */
class Funktionen
{
	/**
	 * Führt Contaos generateAlias() aus und ersetzt anschließend evtl.
	 * verbliebene Umlaute durch ihre ausgeschriebene Form.
	 *
	 * Wichtig: Sowohl der in tl_adressen.searchstring abgelegte Suchstring als
	 * auch der Suchbegriff im Frontend-Suchmodul müssen über diese Methode
	 * normalisiert werden, sonst passen die Werte nicht zusammen.
	 */
	public static function generateAlias(string $strString): string
	{
		if ($strString === '')
		{
			return '';
		}

		$arrSearch  = array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß');
		$arrReplace = array('ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss');

		return str_replace($arrSearch, $arrReplace, StringUtil::generateAlias($strString));
	}

	/**
	 * Liefert alle Kategorien aus tl_adressen_categories als Options-Array.
	 *
	 * Wird als options_callback des Feldes tl_adressen.funktionen genutzt.
	 * Contao übergibt dem Callback den DataContainer – deshalb hat die Methode
	 * bewusst keine Parameter (überzählige Argumente sind in PHP erlaubt).
	 *
	 * @return array<int, string>
	 */
	public static function getFunktionen(): array
	{
		return self::ladeKategorien(false);
	}

	/**
	 * Liefert nur die aktivierten Kategorien (für das Frontend-Suchmodul).
	 *
	 * @return array<int, string>
	 */
	public static function getAktiveFunktionen(): array
	{
		return self::ladeKategorien(true);
	}

	/**
	 * Liest die Kategorien aus der Datenbank.
	 *
	 * @return array<int, string>
	 */
	private static function ladeKategorien(bool $blnNurAktive): array
	{
		$strSql = 'SELECT id, category FROM tl_adressen_categories '
			.($blnNurAktive ? "WHERE active = '1' " : '')
			.'ORDER BY category';

		$objResult = Database::getInstance()->prepare($strSql)->execute();

		$arrCategories = array();

		while ($objResult->next())
		{
			$arrCategories[(int) $objResult->id] = (string) $objResult->category;
		}

		return $arrCategories;
	}
}
