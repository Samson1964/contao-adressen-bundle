<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Classes;

use Contao\StringUtil;

/**
 * Bereitet einen Datensatz aus tl_adressen für die Ausgabe auf.
 *
 * Die Methoden werden vom Inhaltselement, vom Insert-Tag und vom
 * Wertungsreferenten-Modul gemeinsam genutzt. Sie berücksichtigen jeweils die
 * Schalter *_view, mit denen im Backend festgelegt wird, welche Angaben
 * überhaupt öffentlich sind.
 *
 * Als $objAdresse wird ein Contao\Database\Result oder ein Model erwartet –
 * beide liefern die Spalten über __get().
 */
final class Adressdaten
{
	/**
	 * Deutsche Mobilfunk-Vorwahlen
	 */
	private const MOBILFUNK_VORWAHLEN = array
	(
		'0150', '0151', '0152', '0155', '0156', '0157', '0159',
		'0160', '0161', '0162', '0163',
		'0170', '0171', '0172', '0173', '0174', '0175', '0176', '0177', '0178', '0179'
	);

	private function __construct()
	{
	}

	/**
	 * Baut den vollständigen Namen aus Titel, Vorname und Nachname zusammen.
	 */
	public static function name(object $objAdresse): string
	{
		$arrTeile = array
		(
			trim((string) $objAdresse->titel),
			trim((string) $objAdresse->vorname),
			trim((string) $objAdresse->nachname),
		);

		return implode(' ', array_filter($arrTeile));
	}

	/**
	 * Bereitet den Visitenkarten-Text für die Anzeige im Tooltip auf
	 * (Zeilenumbrüche in <br>, Anführungszeichen maskieren).
	 */
	public static function visitenkarte(object $objAdresse): string
	{
		$strText = (string) $objAdresse->text;

		if ($strText === '')
		{
			return '';
		}

		$strText = str_replace(array("\r\n", "\n"), '<br />', $strText);

		return str_replace('"', '&quot;', $strText);
	}

	/**
	 * Liefert die Anschrift als reinen Text, sofern sie veröffentlicht werden darf.
	 *
	 * Die Straße wird nur ausgegeben, wenn zusätzlich auch PLZ und Ort
	 * öffentlich sind.
	 */
	public static function anschrift(object $objAdresse): string
	{
		if (!$objAdresse->ort_view)
		{
			return '';
		}

		$strAnschrift = trim((string) $objAdresse->plz.' '.(string) $objAdresse->ort);

		if ($objAdresse->strasse_view && (string) $objAdresse->strasse !== '')
		{
			$strAnschrift = trim((string) $objAdresse->strasse.', '.$strAnschrift, ', ');
		}

		return trim($strAnschrift);
	}

	/**
	 * Liefert die Anschrift als Link auf Google Maps.
	 */
	public static function anschriftMitKarte(object $objAdresse): string
	{
		$strAnschrift = self::anschrift($objAdresse);

		if ($strAnschrift === '')
		{
			return '';
		}

		$strUrl = 'https://maps.google.de/maps?hl=de&t=h&iwloc=addr&q='.rawurlencode($strAnschrift);

		return '<a class="google" title="Adresse in Googlemap suchen" href="'.StringUtil::specialchars($strUrl).'" target="_blank" rel="noopener">'.StringUtil::specialchars($strAnschrift).'</a>';
	}

	/**
	 * Liefert die Telefonnummern, getrennt nach Festnetz und Mobilfunk.
	 *
	 * @return array{alle: list<string>, fest: list<string>, mobil: list<string>}
	 */
	public static function telefonnummern(object $objAdresse): array
	{
		$arrNummern = array('alle' => array(), 'fest' => array(), 'mobil' => array());

		if (!$objAdresse->telefon_view)
		{
			return $arrNummern;
		}

		for ($i = 1; $i <= 4; $i++)
		{
			$strNummer = trim((string) $objAdresse->{'telefon'.$i});

			if ($strNummer === '')
			{
				continue;
			}

			$arrNummern['alle'][] = $strNummer;
			$arrNummern[self::istMobilfunk($strNummer) ? 'mobil' : 'fest'][] = $strNummer;
		}

		return $arrNummern;
	}

	/**
	 * Liefert die Telefaxnummern.
	 *
	 * @return list<string>
	 */
	public static function telefaxnummern(object $objAdresse): array
	{
		$arrNummern = array();

		if (!$objAdresse->telefax_view)
		{
			return $arrNummern;
		}

		for ($i = 1; $i <= 2; $i++)
		{
			$strNummer = trim((string) $objAdresse->{'telefax'.$i});

			if ($strNummer !== '')
			{
				$arrNummern[] = $strNummer;
			}
		}

		return $arrNummern;
	}

	/**
	 * Liefert die E-Mail-Adressen.
	 *
	 * @param list<string>|null $arrErlaubt Wenn gesetzt, werden nur die in
	 *                                      dieser Liste enthaltenen Adressen
	 *                                      zurückgegeben.
	 *
	 * @return list<string>
	 */
	public static function emailadressen(object $objAdresse, ?array $arrErlaubt = null): array
	{
		$arrMails = array();

		if (!$objAdresse->email_view)
		{
			return $arrMails;
		}

		for ($i = 1; $i <= 6; $i++)
		{
			$strMail = trim((string) $objAdresse->{'email'.$i});

			if ($strMail === '')
			{
				continue;
			}

			if ($arrErlaubt !== null && !\in_array($strMail, $arrErlaubt, true))
			{
				continue;
			}

			$arrMails[] = $strMail;
		}

		return $arrMails;
	}

	/**
	 * Prüft, ob eine Telefonnummer zu einem deutschen Mobilfunknetz gehört.
	 */
	public static function istMobilfunk(string $strNummer): bool
	{
		$strNummer = preg_replace('/[^0-9+]/', '', $strNummer) ?? '';

		// Internationale Schreibweise auf die nationale zurückführen
		if (strncmp($strNummer, '+49', 3) === 0)
		{
			$strNummer = '0'.substr($strNummer, 3);
		}
		elseif (strncmp($strNummer, '0049', 4) === 0)
		{
			$strNummer = '0'.substr($strNummer, 4);
		}

		return \in_array(substr($strNummer, 0, 4), self::MOBILFUNK_VORWAHLEN, true);
	}

	/**
	 * Konvertiert eine Telefonnummer in eine wählbare Form für tel:-Links.
	 *
	 * Aus z.B. "+43 (0)699 11112222" wird "+4369911112222".
	 */
	public static function telefonlink(string $strNummer): string
	{
		$strNummer = trim($strNummer);

		if ($strNummer === '')
		{
			return '';
		}

		// Zeichen in ihre ursprüngliche Form umwandeln und Sonderzeichen entfernen
		$strNummer = html_entity_decode($strNummer, ENT_QUOTES, 'UTF-8');
		$strNummer = str_replace('(0)', '', $strNummer);
		$strNummer = preg_replace('/[^0-9]/', '', $strNummer) ?? '';

		if ($strNummer === '')
		{
			return '';
		}

		if (strncmp($strNummer, '00', 2) === 0)
		{
			// Führende 00 durch + ersetzen
			return '+'.substr($strNummer, 2);
		}

		if (strncmp($strNummer, '0', 1) === 0)
		{
			// Nationale Nummer: führende 0 durch die deutsche Ländervorwahl ersetzen
			return '+49'.substr($strNummer, 1);
		}

		// Keine 0 am Anfang, dann ist es bereits eine Ländervorwahl
		return '+'.$strNummer;
	}
}
