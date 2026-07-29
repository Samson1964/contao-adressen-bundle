<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Tests\Classes;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoAdressenBundle\Classes\Adressdaten;
use Schachbulle\ContaoAdressenBundle\Tests\Stub\AdressStub;

/**
 * Prüft die Aufbereitung eines Datensatzes aus tl_adressen.
 */
class AdressdatenTest extends TestCase
{
	public function testNameSetztTitelVornameUndNachnameZusammen(): void
	{
		$objAdresse = new AdressStub(array('titel' => 'Dr.', 'vorname' => 'Erika', 'nachname' => 'Mustermann'));

		$this->assertSame('Dr. Erika Mustermann', Adressdaten::name($objAdresse));
	}

	public function testNameLaesstLeereBestandteileWeg(): void
	{
		$objAdresse = new AdressStub(array('nachname' => 'Mustermann'));

		$this->assertSame('Mustermann', Adressdaten::name($objAdresse));
	}

	public function testVisitenkarteErsetztZeilenumbruecheUndAnfuehrungszeichen(): void
	{
		$objAdresse = new AdressStub(array('text' => "Zeile 1\r\nZeile 2\nSagt \"Hallo\""));

		$this->assertSame('Zeile 1<br />Zeile 2<br />Sagt &quot;Hallo&quot;', Adressdaten::visitenkarte($objAdresse));
	}

	public function testVisitenkarteIstLeerOhneText(): void
	{
		$this->assertSame('', Adressdaten::visitenkarte(new AdressStub()));
	}

	public function testAnschriftMitStrasse(): void
	{
		$objAdresse = new AdressStub(array
		(
			'ort_view'     => '1',
			'strasse_view' => '1',
			'strasse'      => 'Musterweg 1',
			'plz'          => '12345',
			'ort'          => 'Musterstadt',
		));

		$this->assertSame('Musterweg 1, 12345 Musterstadt', Adressdaten::anschrift($objAdresse));
	}

	public function testAnschriftOhneStrasseWennStrasseNichtOeffentlich(): void
	{
		$objAdresse = new AdressStub(array
		(
			'ort_view'     => '1',
			'strasse_view' => '',
			'strasse'      => 'Musterweg 1',
			'plz'          => '12345',
			'ort'          => 'Musterstadt',
		));

		$this->assertSame('12345 Musterstadt', Adressdaten::anschrift($objAdresse));
	}

	public function testAnschriftIstLeerWennOrtNichtOeffentlich(): void
	{
		$objAdresse = new AdressStub(array
		(
			'ort_view'     => '',
			'strasse_view' => '1',
			'strasse'      => 'Musterweg 1',
			'plz'          => '12345',
			'ort'          => 'Musterstadt',
		));

		$this->assertSame('', Adressdaten::anschrift($objAdresse));
	}

	public function testAnschriftNurMitStrasseHatKeinenSchliessendenTrenner(): void
	{
		$objAdresse = new AdressStub(array
		(
			'ort_view'     => '1',
			'strasse_view' => '1',
			'strasse'      => 'Musterweg 1',
		));

		$this->assertSame('Musterweg 1', Adressdaten::anschrift($objAdresse));
	}

	public function testTelefonnummernWerdenNachFestnetzUndMobilfunkGetrennt(): void
	{
		$objAdresse = new AdressStub(array
		(
			'telefon_view' => '1',
			'telefon1'     => '030 1234567',
			'telefon2'     => '0171 1234567',
			'telefon3'     => '',
			'telefon4'     => '0221 7654321',
		));

		$arrNummern = Adressdaten::telefonnummern($objAdresse);

		$this->assertSame(array('030 1234567', '0171 1234567', '0221 7654321'), $arrNummern['alle']);
		$this->assertSame(array('030 1234567', '0221 7654321'), $arrNummern['fest']);
		$this->assertSame(array('0171 1234567'), $arrNummern['mobil']);
	}

	public function testTelefonnummernSindLeerWennNichtOeffentlich(): void
	{
		$objAdresse = new AdressStub(array('telefon_view' => '', 'telefon1' => '030 1234567'));

		$arrNummern = Adressdaten::telefonnummern($objAdresse);

		$this->assertSame(array(), $arrNummern['alle']);
		$this->assertSame(array(), $arrNummern['fest']);
		$this->assertSame(array(), $arrNummern['mobil']);
	}

	public function testTelefaxnummern(): void
	{
		$objAdresse = new AdressStub(array('telefax_view' => '1', 'telefax1' => '030 111', 'telefax2' => ''));

		$this->assertSame(array('030 111'), Adressdaten::telefaxnummern($objAdresse));
	}

	public function testEmailadressen(): void
	{
		$objAdresse = new AdressStub(array
		(
			'email_view' => '1',
			'email1'     => 'a@example.com',
			'email3'     => 'c@example.com',
		));

		$this->assertSame(array('a@example.com', 'c@example.com'), Adressdaten::emailadressen($objAdresse));
	}

	public function testEmailadressenMitWeisserListe(): void
	{
		$objAdresse = new AdressStub(array
		(
			'email_view' => '1',
			'email1'     => 'a@example.com',
			'email2'     => 'b@example.com',
		));

		$this->assertSame(array('b@example.com'), Adressdaten::emailadressen($objAdresse, array('b@example.com')));
	}

	public function testEmailadressenMitLeererWeisserListe(): void
	{
		$objAdresse = new AdressStub(array('email_view' => '1', 'email1' => 'a@example.com'));

		$this->assertSame(array(), Adressdaten::emailadressen($objAdresse, array()));
	}

	public function testEmailadressenSindLeerWennNichtOeffentlich(): void
	{
		$objAdresse = new AdressStub(array('email_view' => '', 'email1' => 'a@example.com'));

		$this->assertSame(array(), Adressdaten::emailadressen($objAdresse));
	}

	/**
	 * @dataProvider mobilfunkProvider
	 */
	public function testIstMobilfunk(string $strNummer, bool $blnErwartet): void
	{
		$this->assertSame($blnErwartet, Adressdaten::istMobilfunk($strNummer));
	}

	/**
	 * @return list<array{0: string, 1: bool}>
	 */
	public static function mobilfunkProvider(): array
	{
		return array
		(
			array('0171 1234567', true),
			array('0151/1234567', true),
			array('+49 176 1234567', true),
			array('0049 179 1234567', true),
			array('030 1234567', false),
			array('0221-7654321', false),
			array('+49 30 1234567', false),
			array('', false),
			array('0180 1234567', false),
		);
	}

	/**
	 * @dataProvider telefonlinkProvider
	 */
	public function testTelefonlink(string $strNummer, string $strErwartet): void
	{
		$this->assertSame($strErwartet, Adressdaten::telefonlink($strNummer));
	}

	/**
	 * @return list<array{0: string, 1: string}>
	 */
	public static function telefonlinkProvider(): array
	{
		return array
		(
			array('+43 (0)699 11112222', '+4369911112222'),
			array('0030 210 1234', '+302101234'),
			array('030 1234567', '+49301234567'),
			array('0171/123 45 67', '+491711234567'),
			array('49 30 1234567', '+49301234567'),
			array('', ''),
			array('  ', ''),
			array('kein Anschluss', ''),
		);
	}
}
