<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

use Schachbulle\ContaoAdressenBundle\Classes\Adressen_Backend;
use Schachbulle\ContaoAdressenBundle\Classes\Adressen_Frontend;
use Schachbulle\ContaoAdressenBundle\Classes\Suche;
use Schachbulle\ContaoAdressenBundle\ContentElements\Adresse;
use Schachbulle\ContaoAdressenBundle\Modules\Wertungsreferenten;

/*
 * Backend-Modul
 */
$GLOBALS['BE_MOD']['content']['adressen'] = array
(
	'tables'      => array('tl_adressen', 'tl_adressen_categories'),
	'icon'        => 'bundles/contaoadressen/images/icon.svg',
	'import'      => array(Adressen_Backend::class, 'importAdressen'),
	'export'      => array(Adressen_Backend::class, 'exportAdressen'),
);

/*
 * Frontend-Module
 */
$GLOBALS['FE_MOD']['adressen'] = array
(
	'adressen_wertungsreferenten' => Wertungsreferenten::class,
	'adressen_suche'              => Suche::class,
);

/*
 * Insert-Tag {{adresse::ID}} in den Hooks anmelden
 *
 * Hinweis: Der Hook "replaceInsertTags" ist ab Contao 5.2 als veraltet
 * markiert, funktioniert dort aber weiterhin. Das Attribut #[AsInsertTag]
 * gibt es erst ab Contao 5.1 und wäre mit Contao 4.13 nicht kompatibel.
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array(Adressen_Frontend::class, 'adresse_ersetzen');

/*
 * Inhaltselemente
 */
$GLOBALS['TL_CTE']['includes']['adressen'] = Adresse::class;
