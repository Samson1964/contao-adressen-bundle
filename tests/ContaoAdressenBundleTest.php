<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Tests;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoAdressenBundle\ContaoAdressenBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Prüft die Bundle-Klasse und die Versionserkennung.
 */
class ContaoAdressenBundleTest extends TestCase
{
	public function testIstEinSymfonyBundle(): void
	{
		if (!class_exists(Bundle::class))
		{
			$this->markTestSkipped('Symfony HttpKernel ist im Autoloader nicht verfügbar.');
		}

		$this->assertInstanceOf(Bundle::class, new ContaoAdressenBundle());
	}

	public function testIsContao5LiefertEinenBooleanUndWirftKeineAusnahme(): void
	{
		// Ohne installiertes contao/core-bundle muss die Erkennung false
		// liefern statt einen Fehler auszulösen.
		$this->assertIsBool(ContaoAdressenBundle::isContao5());
	}
}
