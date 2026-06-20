<?php

namespace Schachbulle\ContaoAdressenBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Psr\Log\LoggerInterface;

/**
 * Cronjob "Adressen extrahieren"
 *
 * Ermittelt, auf welchen veröffentlichten Seiten jede Adresse eingebunden ist
 * (Insert-Tags {{adresse::ID}} in Text-Inhaltselementen sowie Inhaltselemente
 * vom Typ "adressen") und schreibt die gefundenen URLs in die Spalte
 * tl_adressen.links. Diese Spalte steuert u. a. das Status-Icon in der
 * Adressliste und die Seiten-Auflistung im Kontroll-Cronjob.
 *
 * Ersetzt das frühere Standalone-Skript src/Resources/public/extract.php.
 * Das Ausführungsintervall wird in Resources/config/services.yml über den
 * Tag "contao.cronjob" (interval) festgelegt.
 */
class ExtrahiereAdressen
{
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

		$db  = \Contao\Database::getInstance();
		$now = time();

		$arrAdresse  = array(); // [adresseId => [URL, URL, ...]]
		$intTags     = 0;       // Anzahl gefundener {{adresse::}}-Insert-Tags
		$intElemente = 0;       // Anzahl gefundener Inhaltselemente vom Typ "adressen"

		// 1) Text-Inhaltselemente mit {{adresse::...}}-Tags (nur veröffentlichte)
		$objContent = $db->prepare("SELECT * FROM tl_content WHERE text LIKE '%{{adresse::%' AND type = 'text' AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND invisible = ''")
		                 ->execute($now, $now);

		while ($objContent->next())
		{
			if (!$objContent->ptable)
			{
				continue;
			}

			preg_match_all('/\{\{adresse::([^}]+)\}\}/', (string) $objContent->text, $arrMatches);

			foreach ($arrMatches[1] as $strMatch)
			{
				// Nur die ID der Adresse verwenden (vor einem evtl. ::Funktion-Zusatz)
				$arrValue = explode('::', $strMatch);
				$intAdr   = (int) $arrValue[0];

				$strUrl = $this->ermittleSeitenUrl($db, $objContent->ptable, $objContent->pid, $now);

				if ($strUrl !== null)
				{
					$arrAdresse[$intAdr][] = $strUrl;
					$intTags++;
				}
			}
		}

		// 2) Inhaltselemente vom Typ "adressen" (nur veröffentlichte)
		$objAdressen = $db->prepare("SELECT * FROM tl_content WHERE type = 'adressen' AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND invisible = ''")
		                  ->execute($now, $now);

		while ($objAdressen->next())
		{
			if ($objAdressen->ptable != 'tl_article' && $objAdressen->ptable != 'tl_news')
			{
				continue;
			}

			$strUrl = $this->ermittleSeitenUrl($db, $objAdressen->ptable, $objAdressen->pid, $now);

			if ($strUrl !== null)
			{
				$arrAdresse[(int) $objAdressen->adresse_id][] = $strUrl;
				$intElemente++;
			}
		}

		// Spalte links zurücksetzen und mit den gefundenen URLs neu befüllen.
		// Hinweis: jede URL endet mit "\n"; tl_adressen::addIcon zählt die Zeilen.
		$db->prepare('UPDATE tl_adressen SET links = NULL')->execute();

		foreach ($arrAdresse as $intId => $arrUrls)
		{
			$strLinks = '';

			foreach ($arrUrls as $strUrl)
			{
				$strLinks .= $strUrl . "\n";
			}

			$db->prepare('UPDATE tl_adressen SET links = ? WHERE id = ?')->execute($strLinks, $intId);
		}

		$this->logger?->info(sprintf('[Adressen-Verwaltung] %d Adressen online (%d Insert-Tags, %d Inhaltselemente)', \count($arrAdresse), $intTags, $intElemente));
	}

	/**
	 * Ermittelt die absolute URL der veröffentlichten Seite, zu der ein Artikel
	 * (tl_article/tl_news) gehört. Gibt null zurück, wenn Artikel oder Seite nicht
	 * veröffentlicht sind bzw. keine Seite gefunden wird.
	 *
	 * @param int|string $varPid ID des Artikels/News-Eintrags
	 */
	private function ermittleSeitenUrl(\Contao\Database $db, string $strPtable, $varPid, int $intNow): ?string
	{
		// Artikel/News des Inhaltselements (nur veröffentlicht)
		$objArtikel = $db->prepare("SELECT * FROM " . $strPtable . " WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = 1")
		                 ->execute($varPid, $intNow, $intNow);

		if (!$objArtikel->numRows)
		{
			return null;
		}

		// Seite des Artikels (nur veröffentlicht)
		$objSeite = $db->prepare("SELECT * FROM tl_page WHERE id = ? AND (start = '' OR start < ?) AND (stop = '' OR stop > ?) AND published = 1")
		               ->execute($objArtikel->pid, $intNow, $intNow);

		if (!$objSeite->numRows)
		{
			return null;
		}

		$objPage = \Contao\PageModel::findWithDetails($objSeite->id);

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
			return 'https://' . $objPage->domain . '/' . $objPage->alias;
		}
	}
}
