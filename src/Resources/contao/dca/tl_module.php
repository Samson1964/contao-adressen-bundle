<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Paletten der Frontend-Module.
 *
 * Hinweis: Die früher verwendeten Felder "align" und "space" gibt es in
 * Contao 4/5 nicht mehr, dafür ist "customTpl" hinzugekommen.
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['adressen_wertungsreferenten'] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['adressen_suche'] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID';
