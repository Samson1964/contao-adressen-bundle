# Adressen Changelog

## Version 4.1.1 (2026-07-29) - mit Claude Code

* Fix: Die Statussymbole in der Spalte „Aktiv" der Adressliste wurden viel zu groß
  dargestellt. Die vier SVG-Dateien `grau.svg`, `gelb_rahmen.svg`, `gruen_rahmen.svg` und
  `rot_rahmen.svg` trugen intrinsisch `width="512" height="512"` (das Modul-Icon `icon.svg`
  dagegen korrekt 16). Sie sind jetzt auf `width="16" height="16"` gesetzt, das `viewBox`
  bleibt unverändert.
  Das bisher in `tl_adressen::addIcon()` mitgegebene `width="16" height="16"` half nur
  unter Contao 5: dort hat das Attribut Vorrang, unter Contao 4.13 gewinnt dagegen die in
  der Datei hinterlegte Größe — deshalb war das Symbol dort 512 Pixel groß.
* Change: Da die Größe jetzt in den Dateien selbst steht, gibt `addIcon()` kein
  `width`/`height`-Attribut mehr mit. Das Markup enthielt vorher doppelte Attribute.

**Nach dem Update `contao:assets:install` ausführen** (bzw. den Ordner
`public/bundles/contaoadressen/` aktualisieren), sonst liegen die alten SVG-Dateien weiter
im Web-Verzeichnis.

## Version 4.1.0 (2026-07-29) - mit Claude Code

Die Einstellungen des Kontroll-Cronjobs stecken nicht mehr als Konstanten im Quelltext,
sondern stehen im Backend unter *System → Einstellungen → „Adressen: Kontroll-E-Mails"*.

### ⚠️ Update-Hinweise

* **Der Cronjob läuft nach dem Update zunächst im Testmodus und verschickt gar nichts,
  bis die Einstellungen gepflegt sind.** Das ist Absicht: Der frühere Zustand
  (`TESTMODUS = false` im Quelltext) hätte beim nächsten Quartalslauf ungefragt an alle
  echten Kontakte gemailt.
* Zu setzen sind mindestens **Absenderadresse** und – solange nicht scharfgeschaltet
  wird – der **Test-Empfänger**. Fehlt eines von beidem, bricht der Cronjob ab und
  schreibt eine Meldung ins Log (`contao.cron`).
* Erst der Haken **„Kontroll-E-Mails scharfschalten"** sendet an die echten Kontakte.

### Neue Einstellungen (tl_settings)

| Feld | Bedeutung |
|---|---|
| `adressen_cron_absender` | Absenderadresse; ohne sie verschickt der Cronjob nichts |
| `adressen_cron_absendername` | Absendername, dient zugleich als Grußformel am Ende der E-Mail |
| `adressen_cron_replyto` | Antwortadresse (leer = Absenderadresse) |
| `adressen_cron_betreff` | Betreffzeile |
| `adressen_cron_fotourl` | Basis-URL der Website für die Foto-Anzeige (leer = kein Foto in der E-Mail) |
| `adressen_cron_live` | Sicherheitsschalter: erst mit Haken gehen E-Mails an die echten Kontakte |
| `adressen_cron_testempfaenger` | Empfänger im Testmodus |

### Änderungen

* Change: Die Konstanten `TESTMODUS`, `TEST_EMPFAENGER`, `ABSENDER`, `ABSENDER_NAME`,
  `ANTWORT_AN`, `BETREFF` und `FOTO_BASIS_URL` in `Cron\KontrolliereAdressen` wurden durch
  die obigen Einstellungen ersetzt.
* Change: Der Schalter ist umgedreht – statt `TESTMODUS` (Standard im Code: `false` =
  scharf) gibt es jetzt `adressen_cron_live` (Standard: nicht gesetzt = Testmodus). Eine
  nicht gepflegte Installation kann damit keine ungewollten E-Mails auslösen.
* Change: Die fest verdrahtete Grußformel „Deutscher Schachbund e.V. /
  Öffentlichkeitsarbeit" entfällt; stattdessen wird der eingestellte Absendername
  ausgegeben.
* Change: Anrede und Einleitung der E-Mail stehen jetzt als
  `$GLOBALS['TL_LANG']['MSC']['adressen_cron_anrede']` bzw. `…_einleitung` in der
  Sprachdatei und lassen sich damit projektweise überschreiben, ohne den Cronjob
  anzufassen.
* Change: Der Cronjob protokolliert jetzt auch, warum er nichts getan hat (fehlende
  Absenderadresse, fehlender Test-Empfänger), und nennt im Testmodus den Empfänger.
* Fix: Die Basis-URL für Fotos wird sauber mit dem Dateipfad verbunden (`rtrim`/`/`),
  vorher konnten doppelte oder fehlende Schrägstriche entstehen.

## Version 4.0.0 (2026-07-29) - mit Claude Code

Das Bundle läuft jetzt sowohl unter **Contao 4.13** als auch unter **Contao 5** (getestet
gegen Contao 5.7.7) und setzt **PHP 8.1** voraus. Alle Klassen wurden auf
`declare(strict_types=1);` umgestellt, die Kommentare sind auf Deutsch.

### ⚠️ Update-Hinweise

* `contao:migrate` schlägt das Entfernen der Spalten **`tl_adressen.addImage`**,
  **`tl_adressen.prozentx`** und **`tl_adressen.prozenty`** vor. Diese Felder standen in
  keiner Palette, waren also nie im Backend erreichbar und immer leer – der Datenverlust
  ist keiner. Die Fotoausgabe richtet sich jetzt allein nach `tl_adressen.singleSRC`.
* `tl_adressen.iban` wird von `varchar(22)` auf `varchar(34)` verbreitert (maximale
  IBAN-Länge). Bestehende Werte bleiben erhalten.
* Nach dem Update den **Produktions-Cache neu aufbauen**, weil sich die Service-IDs und
  die Konfiguration geändert haben.
* Die Service-Datei heißt jetzt `Resources/config/services.yaml` (vorher `.yml`), die
  Cron-Services `schachbulle.adressen.cron.extrahieren` bzw.
  `schachbulle.adressen.cron.kontrollieren`. Die alten IDs bleiben als Alias erhalten.

### Contao 4.13 + Contao 5

* Add: `ContaoAdressenBundle::isContao5()` erkennt die Contao-Version über
  `Composer\InstalledVersions`.
* Fix: `config.dataContainer` war fest auf `\Contao\DC_Table::class` gesetzt. Contao 4.13
  erwartet dort den Kurznamen `'Table'` – die DCA-Dateien setzen den Wert jetzt
  versionsabhängig.
* Fix: Die Kurzschreibweise der Operationen (`'!edit'`, `'!all'`) gibt es erst ab
  Contao 5. Für Contao 4.13 werden vollständige Operations-Arrays gesetzt.
* Fix: `\Contao\DataContainer::SORT_INITIAL_LETTER_ASC` durch den Zahlenwert `1` ersetzt,
  der in beiden Versionen gilt.
* Add: `Classes\Kompatibilitaet::insertTagsErsetzen()` kapselt den Unterschied zwischen
  dem Service `contao.insert_tag.parser` (ab Contao 5) und
  `Controller::replaceInsertTags()` (Contao 4.13).
* Change: `composer.json` verlangt `contao/core-bundle: ^4.13 || ^5.0` und `php: ^8.1`.
  Die nie genutzten Abhängigkeiten `codefog/contao-haste`,
  `menatwork/contao-multicolumnwizard-bundle` und `doctrine/doctrine-cache-bundle` wurden
  entfernt.

### Behobene Fehler

* Fix: **Sicherheitslücke im CSV-Import.** `Adressen_Backend::importAdressen()` baute das
  `INSERT`-Statement per String-Verkettung aus Spaltennamen und Werten der hochgeladenen
  Datei zusammen (nur mit `addslashes()` „geschützt"). Jetzt werden die Spaltennamen gegen
  die DCA-Definition geprüft und alle Werte als Platzhalter gebunden.
* Fix: **Sicherheitslücke im Suchmodul.** `Suche::compile()` verkettete den URL-Parameter
  `funktion[]` ungeprüft in die `WHERE`-Klausel. Der Parameter wird jetzt auf ganze Zahlen
  gefiltert und als Platzhalter gebunden.
* Fix: Das Foto im Insert-Tag `{{adresse::ID}}` funktionierte nicht mehr – der Code fragte
  die Felder `addBild`, `bild` und `size` ab, die es in `tl_adressen` gar nicht (mehr)
  gibt. Jetzt wird `singleSRC` per `FilesModel::findByUuid()` ausgelesen.
* Fix: Der Bildausschnitt wurde aus `prozentx`/`prozenty` als Modus `"0_0"` gebaut – ein
  Wert, den Contao 5 nur noch als veralteten Legacy-Modus akzeptiert. Ersetzt durch den
  regulären Zuschneide-Modus `crop`.
* Fix: Das Inhaltselement setzte nie die Template-Variable `adresse`, die
  `ce_adressen.html5` ausgibt – die Anschrift fehlte in der Frontend-Ausgabe komplett.
* Fix: `ContentElements\Adresse::compile()` prüfte mit `if($objAdresse)` auf einen Treffer.
  Das Result-Objekt ist immer „wahr"; jetzt wird `numRows` geprüft.
* Fix: Die Palette von `tl_adressen` enthielt das nicht existierende Feld `published`, die
  von `tl_content` die nicht existierenden Felder `adresse_alttemplate`, `adresse_tpl`,
  `guest` (ab Contao 5) und `space` (seit Contao 4). Für ein abweichendes Template dient
  jetzt das Contao-Standardfeld `customTpl`.
* Fix: Die Paletten von `tl_module` enthielten die seit Contao 4 entfernten Felder `align`
  und `space`; `customTpl` fehlte.
* Fix: `tl_adressen::addIcon()` hatte zwei unerreichbare Zweige – deaktivierte, aber
  eingebundene Adressen bekamen dasselbe Symbol wie unbenutzte. Alle vier Kombinationen
  aus „aktiv" und „eingebunden" werden jetzt korrekt unterschieden.
* Fix: `Cron\ExtrahiereAdressen` suchte die Seite eines News-Beitrags über `tl_news.pid` –
  das ist aber das Archiv, nicht die Seite. Die Zielseite wird jetzt über
  `tl_news_archive.jumpTo` ermittelt. Fehlt das News-Bundle, wird die Tabelle übersprungen
  statt einen SQL-Fehler auszulösen.
* Fix: `Cron\KontrolliereAdressen` übergab die Empfänger als komma-getrennten String an
  `Email::sendTo()`; Namen mit Komma hätten die Liste zerrissen. Jetzt wird das Array
  direkt übergeben.
* Fix: `Funktionen::getFunktionen()` wurde als `options_callback` mit dem DataContainer als
  Argument aufgerufen und hätte mit einem Typ-Hint eine Ausnahme geworfen. Die Methode ist
  jetzt parameterlos, für die Suche gibt es `getAktiveFunktionen()`.
* Fix: Im Template `adresse_ergebnisse.html5` wurde die nie definierte Variable `$ja`
  abgefragt (PHP-8-Warnung) und das JavaScript über den Contao-3-Pfad
  `system/modules/adressen/assets/js/` eingebunden.

### Beseitigte Warnungen und Deprecations

* Fix: `$this->Database` löst ab Contao 5.2 eine Deprecation aus. In allen Klassen und
  DCA-Callbacks durch `Database::getInstance()` ersetzt, die `$this->import('Database')`
  -Aufrufe entfielen damit.
* Fix: Undefinierte Variablen `$telefon`, `$telefon_fest`, `$telefon_mobil`, `$telefax` und
  `$email` in `Modules\Wertungsreferenten`, wenn die zugehörigen `*_view`-Schalter aus
  waren.
* Fix: Undefinierter Array-Index in `Modules\Wertungsreferenten::compile()` für Verbände
  ohne zugeordneten Referenten.
* Fix: Dynamische Properties (`$this->linken`, `$this->Adresstemplate`) – seit PHP 8.2
  veraltet. Ersetzt durch deklarierte, typisierte Properties.
* Fix: URL-Parameter wurden ungeprüft an `strtolower()`/`trim()` übergeben; wird `s` oder
  `join` als Array übergeben, gab es eine „Array to string conversion"-Warnung.
* Fix: `fgetcsv()` bekam `null` als Länge übergeben (ab PHP 8.4 veraltet) – jetzt `0`.

### Aufräumarbeiten

* Add: `Classes\Adressdaten` bündelt die Aufbereitung eines Datensatzes (Name, Anschrift,
  Telefon-/Fax-/E-Mail-Listen, Mobilfunkerkennung, `tel:`-Links). Die Logik war vorher in
  `ContentElements\Adresse`, `Classes\Adressen_Frontend` und `Modules\Wertungsreferenten`
  dreifach kopiert.
* Delete: `Classes\Wertungsreferenten` – die Klasse war nirgends registriert (das Modul
  nutzt `Modules\Wertungsreferenten`) und damit toter Code.
* Delete: Die Templates `adresse_default-neu.html5` (enthielt einen
  `showTemplateVars()`-Debugaufruf), `adresse_default.html5` und `adresse_suche.html5`
  wurden von keiner Klasse mehr verwendet.
* Delete: `tl_adressen::pagePicker()` verwies auf `contao/page.php` aus Contao 3.
* Delete: Der nicht eingebundene Bibliotheks-Ballast `clipboard.min.js`; `adressen.js`
  kommt jetzt ohne jQuery aus (Contao 4/5 bindet jQuery nicht mehr standardmäßig ein) und
  nutzt die Clipboard-API mit Rückfallebene.
* Delete: In `Modules\Wertungsreferenten` war die Verbandsliste ein zweites Mal fest
  einprogrammiert; jetzt wird – wie in der DCA – `$GLOBALS['TL_LANG']['tl_adressen']['verbaende']`
  genutzt. Ebenso entfiel die tote Referats-Liste in `Funktionen::getFunktionen()`.
* Delete: Veraltete Sprachschlüssel für die längst entfernten Felder `adressen`,
  `telefone`, `emails` und `published`.
* Change: Ausgaben in Templates und Modulen werden über `StringUtil::specialchars()`
  maskiert, externe Links erhalten `rel="noopener"`, E-Mail-Adressen in der Trefferliste
  werden über `{{email::}}` gegen Spambots verschleiert.

### Tests

* Add: `tests/` mit 37 PHPUnit-Tests für `Classes\Adressdaten` (Namensaufbau,
  Anschrift, Mobilfunkerkennung, `tel:`-Links, `*_view`-Schalter, E-Mail-Whitelist) und die
  Bundle-Klasse, dazu `phpunit.xml.dist` und ein Bootstrap, der auch ohne
  `composer install` funktioniert.

## Version 3.1.2 (2026-06-20)

*Nachgetragen am 2026-07-29: Diese Notizen lagen nur in einer unversionierten Arbeitskopie
unter `F:\Claude\contao-adressen-bundle`. Der beschriebene Code steckt bereits im Commit zu
3.1.0, es fehlte lediglich die Dokumentation.*

* Fix: `System::import() failed because class "BackendUser" is not a valid class name or does not exist.` beim Bearbeiten eines Adressen-Inhaltselements im Backend. In Contao 5 gibt es die globalen Klassen-Aliase nicht mehr, daher scheitert das unqualifizierte `$this->import('BackendUser', 'User')` im Konstruktor von `tl_content_adresse`. Der Konstruktor wurde komplett entfernt — `$this->User` wurde nirgends genutzt und die Basisklasse `\Contao\Backend` übernimmt die Initialisierung selbst. (Verifiziert in Contao 5.7.7: Klasse instanziiert wieder fehlerfrei.)
* Note: Die übrigen `$this->import('Database')`-Aufrufe sind **nicht** betroffen, da der Eltern-Konstruktor den Schlüssel `Database` bereits vorbelegt und der erneute Import den Block überspringt. Sie funktionieren weiterhin, sollten für Contao 6 aber auf `\Contao\Database::getInstance()` umgestellt werden. *(Mit 4.0.0 erledigt.)*

## Version 3.1.1 (2026-06-20)

*Ebenfalls am 2026-07-29 aus der Arbeitskopie nachgetragen.*

Beim Integrationstest in einer frischen **Contao 5.7.7** (Symfony 7.4, PHP 8.3.31, MariaDB 10.3.16) entdeckt und behoben:

* Fix: `symfony/dependency-injection: ^6.4` ließ sich nicht mit Contao 5.7 installieren, da diese Symfony **7.4** nutzt. Constraint auf `^6.4 || ^7.0` erweitert.
* Fix: Den `_instanceof`-Block für `Symfony\Component\DependencyInjection\ContainerAwareInterface` aus der `services.yml` entfernen — dieses Interface wurde in Symfony 7 entfernt und von keinem Service des Bundles genutzt. *(Der Block war im Commit zu 3.1.0 noch enthalten und wurde erst mit 4.0.0 tatsächlich entfernt.)*

Erfolgreich verifiziert: `composer require` (inkl. Auflösung von codefog/contao-haste 5.4.2 und menatwork/contao-multicolumnwizard-bundle 3.6.15), Container-Kompilierung (`cache:clear`), Registrierung beider Cronjobs (`contao:cron:list` zeigt `adressen_extrahieren` = @daily und `adressen_kontrollieren` = `0 4 1 */3 *`) sowie `contao:migrate` (legt `tl_adressen` und `tl_adressen_categories` fehlerfrei an).

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
