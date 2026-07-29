<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Tests\Classes;

use Contao\StringUtil;
use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoAdressenBundle\Classes\Adressdaten;
use Schachbulle\ContaoAdressenBundle\Tests\Stub\AdressStub;

/**
 * Prüft die Ausgabe der Anschrift als Google-Maps-Link.
 *
 * Der Test benötigt die Contao-Klasse StringUtil und wird übersprungen, wenn
 * keine Contao-Installation im Autoloader verfügbar ist.
 */
class AdressdatenKarteTest extends TestCase
{
	protected function setUp(): void
	{
		if (!class_exists(StringUtil::class))
		{
			$this->markTestSkipped('Contao ist im Autoloader nicht verfügbar.');
		}
	}

	public function testAnschriftMitKarteErzeugtEinenLink(): void
	{
		$objAdresse = new AdressStub(array
		(
			'ort_view'     => '1',
			'strasse_view' => '1',
			'strasse'      => 'Musterweg 1',
			'plz'          => '12345',
			'ort'          => 'Musterstadt',
		));

		$strHtml = Adressdaten::anschriftMitKarte($objAdresse);

		$this->assertStringContainsString('<a class="google"', $strHtml);
		$this->assertStringContainsString('Musterweg%201%2C%2012345%20Musterstadt', $strHtml);
		$this->assertStringContainsString('>Musterweg 1, 12345 Musterstadt</a>', $strHtml);
		$this->assertStringContainsString('rel="noopener"', $strHtml);
	}

	public function testAnschriftMitKarteMaskiertSonderzeichen(): void
	{
		$objAdresse = new AdressStub(array
		(
			'ort_view' => '1',
			'ort'      => 'Musterstadt "Nord" <b>',
		));

		$strHtml = Adressdaten::anschriftMitKarte($objAdresse);

		$this->assertStringNotContainsString('<b>', $strHtml);
		$this->assertStringContainsString('&lt;b&gt;', $strHtml);
	}

	public function testAnschriftMitKarteIstLeerOhneAnschrift(): void
	{
		$this->assertSame('', Adressdaten::anschriftMitKarte(new AdressStub()));
	}
}
