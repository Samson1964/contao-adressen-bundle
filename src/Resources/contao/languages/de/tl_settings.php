<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Legenden
 */
$GLOBALS['TL_LANG']['tl_settings']['adressen_legend'] = 'Adressen';
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_legend'] = 'Adressen: Kontroll-E-Mails';

/*
 * Felder
 */
$GLOBALS['TL_LANG']['tl_settings']['adressen_ImageSize'] = array('Bildgröße', 'Größe des Standardbildes');
$GLOBALS['TL_LANG']['tl_settings']['adressen_defaultImage'] = array('Standardbild', 'Standardbild für Adressen');

$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_absender'] = array('Absenderadresse', 'E-Mail-Adresse, von der die Kontroll-E-Mails verschickt werden. Ist das Feld leer, verschickt der Cronjob nichts.');
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_absendername'] = array('Absendername', 'Name, der als Absender angezeigt wird, z.B. „Deutscher Schachbund".');
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_replyto'] = array('Antwortadresse', 'Adresse, an die Antworten gehen sollen. Schreibweise mit Namen ist erlaubt: „DSB-Presse <presse@example.com>". Leer lassen, um die Absenderadresse zu verwenden.');
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_betreff'] = array('Betreff', 'Betreffzeile der Kontroll-E-Mails.');
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_fotourl'] = array('Basis-URL für Fotos', 'Vollständige Adresse der Website inklusive Protokoll, z.B. „https://www.example.com/". Wird dem Dateipfad vorangestellt, damit das Standardfoto in der E-Mail sichtbar ist. Bleibt das Feld leer, wird in der E-Mail kein Foto angezeigt.');
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_live'] = array('Kontroll-E-Mails scharfschalten', 'ACHTUNG: Erst mit diesem Haken gehen die E-Mails an die echten Kontakte. Ohne Haken läuft der Cronjob im Testmodus und schickt alles ausschließlich an den unten eingetragenen Test-Empfänger.');
$GLOBALS['TL_LANG']['tl_settings']['adressen_cron_testempfaenger'] = array('Test-Empfänger', 'Empfänger im Testmodus. Schreibweise mit Namen ist erlaubt: „Max Mustermann <max@example.com>". Ohne Eintrag verschickt der Testmodus nichts.');
