<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Tests\Dca;

use Contao\StringUtil;
use Contao\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die Erweiterung der Contao-Einstellungen.
 *
 * Die DCA-Datei wird wie von Contao selbst eingebunden und anschließend auf
 * ihre Struktur und das Verhalten ihrer Rückrufe untersucht.
 */
class TlSettingsTest extends TestCase
{
	/**
	 * @var array<string, mixed>
	 */
	private static $arrDca = array();

	/**
	 * Lädt die DCA einmal für alle Prüfungen dieser Klasse.
	 *
	 * Vorher wird das Grundgerüst angelegt, das sonst der Contao-Kern
	 * mitbringt: Ohne die vorhandene Standardpalette liefe die Erweiterung der
	 * Palette in der DCA-Datei ins Leere.
	 */
	public static function setUpBeforeClass(): void
	{
		$GLOBALS['TL_DCA']['tl_settings'] = array(
			'palettes' => array('default' => '{global_legend},dateFormat'),
			'fields' => array(),
		);

		include \dirname(__DIR__, 2).'/src/Resources/contao/dca/tl_settings.php';

		self::$arrDca = $GLOBALS['TL_DCA'];
	}

	/**
	 * Die Felder des Bundles hängen an der Standardpalette.
	 */
	public function testPaletteWirdErweitert(): void
	{
		$strPalette = self::$arrDca['tl_settings']['palettes']['default'];

		foreach (array('adressen_defaultImage', 'adressen_ImageSize') as $strField)
		{
			$this->assertArrayHasKey($strField, self::$arrDca['tl_settings']['fields'], $strField);
			$this->assertStringContainsString($strField, $strPalette, $strField);
		}
	}

	/**
	 * Das Standardbild legt seine Datei-Kennung in der lesbaren Schreibweise ab.
	 *
	 * Der Dateibaum liefert die Kennung als 16 Byte langen Binärwert. Die
	 * Einstellungen landen aber in system/config/localconfig.php, also in einer
	 * PHP-Datei mit einfach gequoteten Zeichenketten, in der Nullbytes und
	 * Backslashes verloren gehen — der Wert käme beschädigt zurück und
	 * FilesModel::findByUuid() fände die Datei nicht mehr. Der save_callback
	 * muss deshalb umwandeln, ohne dabei einen bereits lesbaren oder einen
	 * leeren Wert anzutasten.
	 */
	public function testStandardbildWirdLesbarGespeichert(): void
	{
		// Enthält bewusst 0x00 und 0x5c, also genau die kritischen Bytes
		$strUuid = '5c00335c-8eb1-11f1-af96-005c97f36200';
		$binUuid = StringUtil::uuidToBin($strUuid);

		$arrCallbacks = self::$arrDca['tl_settings']['fields']['adressen_defaultImage']['save_callback'] ?? array();

		$this->assertNotEmpty($arrCallbacks);

		// Die Rückrufe nacheinander anwenden, wie DC_File es tut
		$fnAnwenden = static function ($varValue) use ($arrCallbacks) {
			foreach ($arrCallbacks as $callback)
			{
				$varValue = $callback($varValue);
			}

			return $varValue;
		};

		$varGespeichert = $fnAnwenden($binUuid);

		$this->assertSame($strUuid, $varGespeichert);
		$this->assertTrue(Validator::isStringUuid($varGespeichert));
		$this->assertSame($binUuid, StringUtil::uuidToBin($varGespeichert));

		// Ein zweiter Durchlauf darf nichts mehr verändern
		$this->assertSame($strUuid, $fnAnwenden($strUuid));

		// Kein Bild ausgewählt
		$this->assertSame('', $fnAnwenden(''));
	}
}
