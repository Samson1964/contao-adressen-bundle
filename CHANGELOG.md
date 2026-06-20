# Adressen Changelog

## Version 3.1.0 (2026-06-20) - mit Claude Code

* Change: Die beiden veralteten Standalone-Skripte `extract.php` und `check.php` wurden als echte Contao-5-Cronjobs neu umgesetzt.
* Add: Cronjob `Cron\ExtrahiereAdressen` (**täglich**) – portiert die Logik von `public/extract.php`: ermittelt alle veröffentlichten Einbindungen (`{{adresse::}}`-Insert-Tags in Text-Elementen sowie Inhaltselemente vom Typ „adressen") und schreibt die Seiten-URLs in `tl_adressen.links`. Das in Contao 5 entfernte `Controller::getPageDetails()` wurde durch `\Contao\PageModel::findWithDetails()` + `getAbsoluteUrl()` (mit Fallback) ersetzt.
* Add: Cronjob `Cron\KontrolliereAdressen` (**vierteljährlich**, 1. Jan/Apr/Jul/Okt um 04:00 Uhr, Cron-Ausdruck `0 4 1 */3 *`) – portiert die Logik von `public/check.php`: verschickt an alle aktiven, eingebundenen Adressen eine Verifizierungs-E-Mail mit den gespeicherten Daten.
* Add: Sicherheits-Schalter `KontrolliereAdressen::TESTMODUS` (Standard **true**) – E-Mails gehen zunächst ausschließlich an den Test-Empfänger (`webmaster@schachbund.de`). Erst nach Umstellen auf `false` werden die echten Kontakte angeschrieben.
* Change: `TL_ROOT` im Foto-Check durch `kernel.project_dir` ersetzt; `\Contao\Email`-Versand unverändert übernommen.
* Change: Die Cron-Intervalle werden in `Resources/config/services.yml` über den Tag `contao.cronjob` (`interval`) gesetzt – das `#[AsCronJob]`-Attribut wurde entfernt, da es ohne `autoconfigure: true` nicht zum `contao.cronjob`-Tag führt (so läuft die Registrierung zuverlässig).
* Change: Beide Cron-Services bekommen `@contao.framework` injiziert und rufen `$framework->initialize()` auf, damit die Legacy-Klassen (`Database`, `PageModel`, `FilesModel`, `Email`) auch im CLI-/Cron-Kontext verfügbar sind.
* Change: Veraltete Messenger-Felder (Google+/ICQ/Yahoo/AIM/MSN) in der Kontroll-E-Mail durch die aktuellen `tl_adressen`-Felder (Instagram/Skype/WhatsApp/Threema/Telegram) ersetzt.
* Delete: Die durch die Cron-Services ersetzten Altskripte `src/Resources/public/extract.php` und `src/Resources/public/check.php` wurden entfernt (ihre Logik liegt vollständig in `Cron\ExtrahiereAdressen` bzw. `Cron\KontrolliereAdressen`).

Vollständige Prüfung und Anpassung an Contao 5 / PHP 8. In Contao 5 entfernte Konstanten, globale Klassen/Funktionen und Controller-Methoden wurden durch die entsprechenden Services bzw. Klassen ersetzt. Alle Quelldateien wurden per `php -l` auf Syntaxfehler geprüft.

### Behobene Fatal Errors (in Contao 5 entfernte APIs)

* Fix: Too few arguments to function `Cron\ExtrahiereAdressen::__construct()` -> Parameter `$logger` mit Defaultwert `= null` versehen
* Fix: Konstante `TL_MODE` (entfernt) ersetzt durch `contao.routing.scope_matcher`->`isBackendRequest()` in `Classes\Suche`, `Modules\Wertungsreferenten` und `Classes\Wertungsreferenten`
* Fix: Konstante `TL_ROOT` (entfernt) ersetzt durch Parameter `kernel.project_dir` in `Classes\Adressen_Frontend` und `Classes\Wertungsreferenten`
* Fix: Konstante `REQUEST_TOKEN` (entfernt) ersetzt durch `contao.csrf.token_manager`->`getDefaultTokenValue()` in `Classes\Adressen_Backend` (Import-Formular) und `tl_content::editAdresse`
* Fix: Konstante `VERSION` (entfernt) in `Classes\Wertungsreferenten` entfernt -> `version_compare(VERSION, '3.2', ...)` war unter Contao 5 ohnehin immer wahr, daher direkt `\Contao\FilesModel::findByUuid()`
* Fix: `Controller::getImage()` (entfernt) ersetzt durch den Service `contao.image.studio` (FigureBuilder -> `getImage()->getImageSrc()`) in `Classes\Adressen_Frontend`
* Fix: `Controller::addImageToTemplate()` (entfernt) ersetzt durch `contao.image.studio` / `Figure::applyLegacyTemplateData()` in `ContentElements\Adresse`, `tl_content::getThumbnail` und `Classes\Wertungsreferenten`
* Fix: `Controller::replaceInsertTags()` (entfernt) ersetzt durch den Service `contao.insert_tag.parser`->`replaceInline()` in `Classes\Adressen_Frontend`
* Fix: Globale Klassen ohne Namespace (`\Input`, `\System`, `\File`, `\Environment`) auf `\Contao\...` umgestellt in `Classes\Adressen_Backend`
* Fix: Globale Funktionen `ampersand()` und `specialchars()` (entfernt) ersetzt durch `\Contao\StringUtil::ampersand()`/`specialchars()` in `Classes\Adressen_Backend` sowie in `tl_adressen` und `tl_content`
* Fix: Backend-URL `contao/main.php?...` (existiert nicht mehr) in `tl_content::editAdresse` durch die Router-Route `contao_backend` ersetzt
* Fix: CSV-Import nutzt jetzt direkt `\Contao\FileUpload` statt der nicht mehr funktionierenden `$this->User->uploader`-Logik

### Weitere Fixes (PHP 8 / Korrektheit)

* Fix: `\Contao\FilesModel::findByPk()` für das UUID-Feld `singleSRC` auf `findByUuid()` korrigiert -> das Foto aus `tl_adressen` wurde bisher nie geladen (`ContentElements\Adresse`, `tl_content::getThumbnail`)
* Change: `unserialize()` durch `\Contao\StringUtil::deserialize(..., true)` ersetzt (vermeidet PHP-8.1-Deprecation bei `null`) in `ContentElements\Adresse`, `Modules\Wertungsreferenten` und `Classes\Wertungsreferenten`
* Change: `$GLOBALS['TL_CONFIG'][...]` durch `\Contao\Config::get()` ersetzt (`adressen_defaultImage`, `adressen_ImageSize`)
* Fix: Nicht initialisierte Variablen vorbelegt (`$telefon`, `$telefon_fest`, `$telefon_mobil`, `$telefax`, `$email`, `$class`, `$content`) -> keine „Undefined variable"-Warnungen unter PHP 8
* Fix: `explode("\n", $row['links'])` gegen `null` abgesichert (PHP 8.1) in `tl_adressen::addIcon`
* Fix: Eigenschaft `$Template` in `Classes\Adressen_Frontend` deklariert -> keine PHP-8.2-Deprecation für dynamische Eigenschaften (Basisklasse `Frontend` besitzt kein `__set`)
* Fix: Undefinierte Variable `$objAdresse` -> `$data` in `Classes\Wertungsreferenten::FormatiereAdresse` (Visitenkarte wurde nie erzeugt)
* Fix: Weiterleitung auf `contao/main.php?act=error` bei fehlender Bildgröße in `ContentElements\Adresse` entfernt -> fehlende Einstellung ist jetzt unkritisch (Bild wird in Originalgröße/ohne Resize ausgegeben)
* Fix: `\Contao\Input::get('key')` statt `$this->Input->get('key')` in `Adressen_Backend::exportAdressen`

### Hinweis (kein Codeeingriff)

* Note: Zugriffe über `$this->Database` bzw. `$this->import('Database')` funktionieren in Contao 5 weiterhin (Backwards-Compatibility), lösen aber ab Contao 5.2 eine Deprecation aus und sollten für Contao 6 auf `\Contao\Database::getInstance()` umgestellt werden.

## Version 3.0.2 (2025-12-18)

* Fix: Attempted to load class "Table" from the global namespace -> TL_DCA dataContainer muß jetzt \Contao\DC_Table::class statt 'Table' sein
* Fix: Call to a member function getData() on null in dca/tl_adressen.php -> aus $session = $this->Session->getData() wird $objSession = \Contao\System::getContainer()->get('request_stack')->getSession(); $session = $objSession->all();
* Fix: Call to a member function setData() on null in dca/tl_adressen.php -> aus $this->Session->setData($session) wird $objSession->replace($session);
* Fix: Undefined constant "Schachbulle\ContaoAdressenBundle\Classes\FE_USER_LOGGED_IN" in src/Classes/Funktionen.php (line 46) -> Konstante FE_USER_LOGGED_IN ersetzt durch \Contao\System::getContainer()->get('contao.security.token_checker')->hasFrontendUser();
* Delete: tl_adressen.adressen, tl_adressen.telefone, tl_adressen.emails -> neues Format mit MCW wurde nicht genutzt
* Change: tl_adressen Spezialfilter an Contao 5/PHP 8 anhnad von Isotope angepaßt
* Change: tl_adressen::getReferenten ersetzt durch Sprachvariable
* Change: Wertungsreferenten::getReferenten ersetzt durch Sprachvariable
* Add: tl_adressen.config.list.sorting.defaultSearchField = 'nachname'
* Change: PNG-Icons ausgetauscht gegen SVG-Icons
* Change: PHP-Dateien (extract, check) in public (Cronjobs) durch Symfony-Services ersetzt
* Add: Cron\ExtrahiereAdressen
* Add: composer.json "symfony/dependency-injection": "^6.4" -> wegen: In ResolveInstanceofConditionalsPass.php line 168: "Symfony\Component\DependencyInjection\ContainerAwareInterface" is set as an "instanceof" conditional, but it does not exist. Siehe auch https://community.contao.org/de/showthread.php?87239-Fehler-nach-Update-von-5-3-auf-5-4&p=587371&viewfull=1#post587371

## Version 3.0.1 (2025-12-15)

* Fix: Aufruf Klasse Config in \Contao\Config geändert

## Version 3.0.0 (2025-12-15)

* Change: Abhängigkeit auf Contao 5 und PHP 8 gesetzt
* Change: Contao-Klassen-Aufrufe um Namespace \Contao ergänzt

## Version 2.3.1 (2025-07-01)

* Fix: Warning: Undefined array key "adressen_ImageSize" in src/ContentElements/Adresse.php (line 160) 
* Fix: Warning: Attempt to read property "path" on null in /src/ContentElements/Adresse.php (line 169) 
* Fix: Warning: Attempt to read property "path" on null in src/Resources/contao/dca/tl_content.php (line 261) 

## Version 2.3.0 (2023-06-18)

* Add: PHP 8 in composer.json als erlaubt eingetragen

## Version 2.2.1 (2023-05-22)

* Change: tl_content.adresse_mails -> blob statt varchar(64) wegen Platzproblemen bei langen Adressen
* Fix: Inhaltselement -> nur bestimmte E-Mail-Adressen anzeigen (keine Anzeige)

## Version 2.2.0 (2023-02-27)

* Add: Abhängigkeit codefog/contao-haste
* Change: tl_adressen.aktiv inkl. Haste-Toggle-Funktion
* Change: tl_adressen_categories.active inkl. Haste-Toggle-Funktion
* Delete: tl_adressen_categories -> alte Toggle-Funktionen
* Change: Inhaltselement Adresse -> zusätzliche Ausgabe des Ortes bei Adressauswahl
* Add: Spezialfilter für das Anzeigen von doppelten E-Mail-Adressen

## Version 2.1.5 (2021-07-06)

* Fix: Auto-Inkrement und primärer Schlüssel id fehlerhaft

## Version 2.1.4 (2021-06-23)

* Fix: tl_content.adresse_altformat von true auf false geändert

## Version 2.1.3 (2021-03-13)

* Fix: $this->zusatz (tl_content.adresse_zusatz) wurde im Template nicht berücksichtigt

## Version 2.1.2 (2021-02-24)

* Change: Alternatives Template umgestellt auf customTpl des Cores

## Version 2.1.1 (2021-02-16)

* Fix: Kein alternatives Bildformat möglich
* Add: Auswahl eines alternativen Bildformats im Inhaltselement

## Version 2.1.0 (2021-01-14)

* Change: Ausgabe Wertungsreferenten umprogrammiert (Tabelle statt Einzeldaten)
* Add: Template mod_adressen_referenten (ersetzt adresse_referenten)
* Add: FE-Modul Wertungsreferenten überarbeitet, allerdings funktioniert odd/even in der Tabelle nicht

## Version 2.0.6 (2021-01-14)

* Fix: Debugausgabe im Template ce_adressen_default entfernt

## Version 2.0.5 (2021-01-13)

* Fix: http:// wird angezeigt im Frontend wenn keine Homepage hinterlegt ist => save_callback im homepage-Feld eingebaut
* Fix: Bild ersetzen funktioniert nicht korrekt. Im Template ce_adressen_default wurde die falsche Variable verwendet.
* Fix: Im Inhaltselement war das Bearbeitungspopup nicht richtig eingebaut

## Version 2.0.4 (2020-12-28)

* Fix: Adressen_Frontend.php falsches Template - ce_adressen_inserttag wiederhergestellt (vorher ce_adressen)

## Version 2.0.3 (2020-12-28)

* Fix: Adressen_Frontend.php falsches Template - ce_adressen statt ce_adressen_default

## Version 2.0.2 (2020-11-07)

* Fix: Falsche Spalte in Palette tl_content - adresse_viewFoto statt adresse_addImage

## Version 2.0.1 (2020-11-05)

* Fix: tl_content Foto anzeigen
* Fix: Template ce_adressen_default

## Version 2.0.0 (2020-11-03)

* Add: In den Einstellungen kann ein Standardbild und die allgemeine Bildgröße gesetzt werden
* Change: Bild-Einstellungen komplett reduziert auf das einzubindende Bild (Abstände, Großansicht, Ausrichtung, Metaangaben, Bild ja/nein entfernt)
* Delete: tl_adressen.alias (wird nicht benötigt)
* Change: Template ce_adressen_default statt adressen_default als Standard für das Inhaltselement gesetzt
* Add: Template ce_adressen_default mit Aufbau von Contao 4
* Change: Alternatives Template in tl_content per Checkbox einschaltbar gemacht
* Change: Templatefilter tl_content ce_adressen_ statt adressen_
* Delete: Messenger ICQ, MSN, AIM, Yahoo, Google+
* Add: Messenger Instagram, Skype, Telegram, WhatsApp, Threema
* Add: tl_adressen.homepage mit http:// vorbelegt
* Fix: Adressenauswahl im Inhaltselement bei Firmen (ohne Vor- und Nachname) war unmöglich

## Version 1.0.2 (2020-07-24)

* Fix adresseClass -> \Schachbulle\ContaoAdressenBundle\ContentElements\Adresse in Wertungsreferenten.php

## Version 1.0.1 (2020-07-03)

* Fix: "Too few arguments to build the query string" beim Speichern einer neuen Adresse. Aufgerufen wurde getFunktionen statt generateAlias

## Version 1.0.0 (2020-06-29)

* Initiale Version für Contao 4 migriert von Version 2.0.0 Contao 3
