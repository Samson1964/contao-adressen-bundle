<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Bootstrap für die Unit-Tests.
 *
 * Wurde im Bundle "composer install" ausgeführt, wird der erzeugte Autoloader
 * genutzt. Andernfalls (z.B. beim Aufruf über ein eigenständig installiertes
 * PHPUnit) wird ein minimaler PSR-4-Autoloader registriert und optional der
 * Autoloader einer Contao-Referenzinstallation eingebunden, damit die
 * Contao-Klassen zur Verfügung stehen.
 */

$strRoot = \dirname(__DIR__);

if (is_file($strRoot.'/vendor/autoload.php'))
{
	require $strRoot.'/vendor/autoload.php';
}
else
{
	// Contao-Referenzinstallation für die Contao-Klassen (optional)
	foreach (array(getenv('CONTAO_TEST_DIR'), $strRoot.'/../../contao-test') as $strContao)
	{
		if ($strContao && is_file($strContao.'/vendor/autoload.php'))
		{
			require $strContao.'/vendor/autoload.php';
			break;
		}
	}

	// Muss nach Composer registriert werden – und zwar vorrangig (prepend),
	// weil Composer seinen Autoloader ebenfalls vorne einhängt und dort evtl.
	// eine ältere Kopie dieses Bundles liegt.
	spl_autoload_register(static function (string $strClass) use ($strRoot): void {
		$arrPrefixe = array
		(
			'Schachbulle\\ContaoAdressenBundle\\Tests\\' => $strRoot.'/tests/',
			'Schachbulle\\ContaoAdressenBundle\\'        => $strRoot.'/src/',
		);

		foreach ($arrPrefixe as $strPrefix => $strDir)
		{
			if (strncmp($strClass, $strPrefix, \strlen($strPrefix)) !== 0)
			{
				continue;
			}

			$strFile = $strDir.str_replace('\\', '/', substr($strClass, \strlen($strPrefix))).'.php';

			if (is_file($strFile))
			{
				require $strFile;
			}

			return;
		}
	}, true, true);
}
