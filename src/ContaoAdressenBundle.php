<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle;

use Composer\InstalledVersions;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Haupt-Bundle-Klasse der Adressen-Verwaltung.
 */
class ContaoAdressenBundle extends Bundle
{
	/**
	 * Prüft anhand der installierten Version, ob Contao 5 (oder neuer) läuft.
	 *
	 * Wird von den DCA-Dateien genutzt, um Treiberklasse und Operationsleiste
	 * versionsgerecht zu setzen: Contao 5 erwartet in "config.dataContainer" den
	 * vollqualifizierten Klassennamen und kennt die Kurzschreibweise der
	 * Operationen, Contao 4.13 erwartet den Kurznamen "Table" und vollständige
	 * Operations-Arrays.
	 */
	public static function isContao5(): bool
	{
		if (!class_exists(InstalledVersions::class))
		{
			return false;
		}

		return version_compare(
			(string) InstalledVersions::getVersion('contao/core-bundle'),
			'5.0.0',
			'>='
		);
	}
}
