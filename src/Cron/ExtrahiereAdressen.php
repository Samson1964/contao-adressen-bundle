<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\PageModel;
use Psr\Log\LoggerInterface;

/**
 * Cronjob "Adressen extrahieren"
 *
 * Ermittelt, auf welchen veröffentlichten Seiten jede Adresse eingebunden ist
 * (Insert-Tags {{adresse::ID}} in Text-Inhaltselementen sowie Inhaltselemente
 * vom Typ "adressen") und schreibt die gefundenen URLs in die Spalte
 * tl_adressen.links. Diese Spalte steuert das Statussymbol in der Adressliste
 * und die Seiten-Auflistung im Kontroll-Cronjob.
 *
 * Das Ausführungsintervall wird in Resources/config/services.yaml über den
 * Tag "contao.cronjob" (interval) festgelegt.
 */
class ExtrahiereAdressen
{
	/**
	 * Elterntabellen, für die eine Seiten-URL ermittelt werden kann
	 */
	private const PTABLES = array('tl_article', 'tl_news');

	/**
	 * Zwischenspeicher für bereits aufgelöste Seiten-URLs: [ptable.pid => URL]
	 *
	 * @var array<string, string|null>
	 */
	private array $arrUrlCache = array();

	public function __construct(
		private readonly ContaoFramework $framework,
		private readonly LoggerInterface|null $logger = null,
	)
	{
	}

	public function __invoke(): void
	{
		// Contao-Framework initialisieren, damit die Legacy-Klassen (Database,
		// PageModel) im CLI-/Cron-Kontext verfügbar sind.
		$this->framework->initialize();

		$this->arrUrlCache = array();

		$objDatabase = Database::getInstance();
		$intNow      = time();

		$arrAdresse  = array(); // [Adress-ID => [URL, URL, ...]]
		$intTags     = 0;       // Anzahl gefundener {{adresse::}}-Insert-Tags
		$intElemente = 0;       // Anzahl gefundener Inhaltselemente vom Typ "adressen"

		// 1) Text-Inhaltselemente mit {{adresse::...}}-Tags (nur veröffentlichte)
		$objContent = $objDatabase
			->prepare("SELECT ptable, pid, text FROM tl_content WHERE text LIKE '%{{adresse::%' AND type = 'text' AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND invisible = ''")
			->execute($intNow, $intNow);

		while ($objContent->next())
		{
			preg_match_all('/\{\{adresse::([^}]+)\}\}/', (string) $objContent->text, $arrMatches);

			if (!$arrMatches[1])
			{
				continue;
			}

			$strUrl = $this->ermittleSeitenUrl($objDatabase, (string) $objContent->ptable, (int) $objContent->pid, $intNow);

			if ($strUrl === null)
			{
				continue;
			}

			foreach ($arrMatches[1] as $strMatch)
			{
				// Nur die ID der Adresse verwenden (vor einem evtl. ::Funktion-Zusatz)
				$arrValue = explode('::', $strMatch);
				$intAdr   = (int) $arrValue[0];

				if ($intAdr < 1)
				{
					continue;
				}

				$arrAdresse[$intAdr][] = $strUrl;
				$intTags++;
			}
		}

		// 2) Inhaltselemente vom Typ "adressen" (nur veröffentlichte)
		$objAdressen = $objDatabase
			->prepare("SELECT ptable, pid, adresse_id FROM tl_content WHERE type = 'adressen' AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND invisible = ''")
			->execute($intNow, $intNow);

		while ($objAdressen->next())
		{
			$intAdr = (int) $objAdressen->adresse_id;

			if ($intAdr < 1)
			{
				continue;
			}

			$strUrl = $this->ermittleSeitenUrl($objDatabase, (string) $objAdressen->ptable, (int) $objAdressen->pid, $intNow);

			if ($strUrl === null)
			{
				continue;
			}

			$arrAdresse[$intAdr][] = $strUrl;
			$intElemente++;
		}

		// Spalte links zurücksetzen und mit den gefundenen URLs neu befüllen.
		// Hinweis: jede URL steht in einer eigenen Zeile; tl_adressen::addIcon zählt sie.
		$objDatabase->prepare('UPDATE tl_adressen SET links = NULL')->execute();

		$objStatement = $objDatabase->prepare('UPDATE tl_adressen SET links = ? WHERE id = ?');

		foreach ($arrAdresse as $intId => $arrUrls)
		{
			// Mehrfachnennungen derselben Seite zusammenfassen
			$arrUrls = array_values(array_unique($arrUrls));

			$objStatement->execute(implode("\n", $arrUrls)."\n", $intId);
		}

		$this->logger?->info(sprintf(
			'[Adressen-Verwaltung] %d Adressen online (%d Insert-Tags, %d Inhaltselemente)',
			\count($arrAdresse),
			$intTags,
			$intElemente
		));
	}

	/**
	 * Ermittelt die absolute URL der veröffentlichten Seite, zu der ein Artikel
	 * (tl_article) bzw. ein News-Eintrag (tl_news) gehört.
	 *
	 * Gibt null zurück, wenn Artikel oder Seite nicht veröffentlicht sind bzw.
	 * keine Seite gefunden wird.
	 */
	private function ermittleSeitenUrl(Database $objDatabase, string $strPtable, int $intPid, int $intNow): ?string
	{
		// Der Tabellenname muss aus der Whitelist stammen und die Tabelle muss
		// existieren (tl_news gibt es nur mit installiertem News-Bundle)
		if ($intPid < 1 || !\in_array($strPtable, self::PTABLES, true) || !$objDatabase->tableExists($strPtable))
		{
			return null;
		}

		$strCacheKey = $strPtable.'.'.$intPid;

		if (\array_key_exists($strCacheKey, $this->arrUrlCache))
		{
			return $this->arrUrlCache[$strCacheKey];
		}

		return $this->arrUrlCache[$strCacheKey] = $this->ladeSeitenUrl($objDatabase, $strPtable, $intPid, $intNow);
	}

	/**
	 * Löst die Seiten-URL tatsächlich auf (ohne Zwischenspeicher).
	 */
	private function ladeSeitenUrl(Database $objDatabase, string $strPtable, int $intPid, int $intNow): ?string
	{
		// Artikel/News des Inhaltselements (nur veröffentlichte).
		// Der Tabellenname stammt aus der Whitelist PTABLES.
		$objArtikel = $objDatabase
			->prepare('SELECT * FROM '.$strPtable." WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = '1'")
			->execute($intPid, $intNow, $intNow);

		if (!$objArtikel->numRows)
		{
			return null;
		}

		if ($strPtable === 'tl_news')
		{
			// Bei News verweist pid auf das Archiv, die Zielseite steht im Archiv
			$intSeite = 0;

			if ($objDatabase->tableExists('tl_news_archive'))
			{
				$objArchiv = $objDatabase
					->prepare('SELECT jumpTo FROM tl_news_archive WHERE id = ?')
					->execute($objArtikel->pid);

				$intSeite = $objArchiv->numRows ? (int) $objArchiv->jumpTo : 0;
			}
		}
		else
		{
			$intSeite = (int) $objArtikel->pid;
		}

		if ($intSeite < 1)
		{
			return null;
		}

		// Seite muss veröffentlicht sein
		$objSeite = $objDatabase
			->prepare("SELECT id FROM tl_page WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = '1'")
			->execute($intSeite, $intNow, $intNow);

		if (!$objSeite->numRows)
		{
			return null;
		}

		$objPage = PageModel::findWithDetails($intSeite);

		if ($objPage === null)
		{
			return null;
		}

		try
		{
			return $objPage->getAbsoluteUrl();
		}
		catch (\Throwable $e)
		{
			// Fallback, falls im CLI-Kontext keine absolute URL erzeugt werden kann
			$this->logger?->debug(sprintf('[Adressen-Verwaltung] URL für Seite %d konnte nicht erzeugt werden: %s', $intSeite, $e->getMessage()));

			return $objPage->domain ? 'https://'.$objPage->domain.'/'.$objPage->alias : null;
		}
	}
}
