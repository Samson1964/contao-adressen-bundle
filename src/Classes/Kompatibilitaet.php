<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

use Contao\Controller;
use Contao\System;

/**
 * Kapselt die API-Unterschiede zwischen Contao 4.13 und Contao 5.
 *
 * Die Klasse wird bewusst nicht instanziert, sie enthält ausschließlich
 * statische Hilfsmethoden.
 */
final class Kompatibilitaet
{
	private function __construct()
	{
	}

	/**
	 * Ersetzt die Insert-Tags in einem String.
	 *
	 * Contao 5 stellt dafür den Service "contao.insert_tag.parser" bereit,
	 * in Contao 4.13 gibt es nur Controller::replaceInsertTags().
	 *
	 * @param bool $blnCache false erzwingt eine nicht zwischengespeicherte
	 *                       (inline) Ersetzung – wichtig für E-Mail-Adressen,
	 *                       die nicht im Seitencache landen dürfen.
	 */
	public static function insertTagsErsetzen(string $strBuffer, bool $blnCache = false): string
	{
		if ($strBuffer === '')
		{
			return '';
		}

		$container = System::getContainer();

		if ($container !== null && $container->has('contao.insert_tag.parser'))
		{
			$parser = $container->get('contao.insert_tag.parser');

			return $blnCache ? $parser->replace($strBuffer) : $parser->replaceInline($strBuffer);
		}

		// Contao 4.13
		return (string) Controller::replaceInsertTags($strBuffer, $blnCache);
	}
}
