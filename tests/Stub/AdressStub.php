<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Tests\Stub;

/**
 * Minimaler Ersatz für Contao\Database\Result in den Unit-Tests.
 *
 * Liefert – wie das Original – für nicht gesetzte Spalten null.
 */
class AdressStub
{
	/**
	 * @param array<string, mixed> $arrDaten
	 */
	public function __construct(private readonly array $arrDaten = array())
	{
	}

	public function __get(string $strKey): mixed
	{
		return $this->arrDaten[$strKey] ?? null;
	}

	public function __isset(string $strKey): bool
	{
		return isset($this->arrDaten[$strKey]);
	}
}
