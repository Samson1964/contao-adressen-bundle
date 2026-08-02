# Offene Tickets

## Deploy

* **4.0.0 und 4.1.0 auf das Livesystem bringen.** Reihenfolge: hochladen →
  `contao:migrate` (entfernt `tl_adressen.addImage`, `prozentx`, `prozenty`, verbreitert
  `iban` auf 34 Zeichen) → **Produktions-Cache neu bauen** (Service-IDs und
  `services.yaml` haben sich geändert).
* **Nach dem Deploy die Cron-Einstellungen pflegen:** *System → Einstellungen →
  „Adressen: Kontroll-E-Mails"*. Die Werte aus dem Altskript `/php/adressen/check.php`:

  | Feld | Wert |
  |---|---|
  | Absenderadresse | `server@schachbund.de` |
  | Absendername | `Deutscher Schachbund` |
  | Antwortadresse | `Deutscher Schachbund e.V. <adressen@schachbund.de>` |
  | Betreff | `[Deutscher Schachbund] Adressen-Überprüfung` |
  | Grußformel | `Deutscher Schachbund e.V.<br>Öffentlichkeitsarbeit` |
  | Basis-URL für Fotos | `https://www.schachbund.de/` |
  | Test-Empfänger | `Frank Hoppe <webmaster@schachbund.de>` |

  Den Haken „Kontroll-E-Mails scharfschalten" erst setzen, wenn eine Testmail geprüft ist.

* **Hetzner-Cronjobs umstellen und Altskripte entfernen.** Die beiden curl-Aufrufe auf
  `/php/adressen/extract.php` und `/php/adressen/check.php` durch einen einzigen
  Contao-Cron ersetzen (`* * * * * php .../vendor/bin/contao-console contao:cron`) und den
  Ordner `/php/adressen/` löschen. **Frist: 1. Oktober 2026, 04:00** – bis dahin würde das
  alte `check.php` (`$debugmode = false`) erneut an alle echten Kontakte schreiben, ohne
  den Sicherheitsschalter des Bundles zu beachten.

## Fehler

* (derzeit nichts offen)

## Verbesserungen

* **`ce_adressen_default.html5` prüfen.** Das Template ist eine nahezu identische Kopie von
  `ce_adressen.html5`. Wenn es niemand als `customTpl` nutzt, kann es entfallen.
* **Klassennamen mit Unterstrich.** `Classes\Adressen_Backend` und
  `Classes\Adressen_Frontend` verstoßen gegen die CamelCase-Regel aus der `CLAUDE.md`.
  Eine Umbenennung wäre ein Bruch für fremden Code, der die Klassen referenziert, und
  gehört daher in einen Major-Sprung.

## Für Contao 6 vormerken

* Der Insert-Tag `{{adresse::ID}}` hängt am Hook `replaceInsertTags`, der ab Contao 5.2 als
  veraltet gilt und in Contao 6 wegfällt. Ersatz ist das Attribut `#[AsInsertTag]` — das
  gibt es aber erst ab Contao 5.1 und wäre mit Contao 4.13 nicht kompatibel. Umstellen,
  sobald die 4.13-Unterstützung aufgegeben wird.

## Erledigt

* ~~Restliche fest verdrahtete Texte der Kontroll-E-Mail in die Sprachdatei holen.~~
  Erledigt (4.3.0): Feldbeschriftungen unter
  `$GLOBALS['TL_LANG']['MSC']['adressen_cron_felder']`, übrige Textbausteine unter
  `…['adressen_cron_texte']`. Der erzeugte E-Mail-Text wurde danach erneut gegen das
  Altskript geprüft – wortgleich.
* ~~Bedienoberfläche des Suchmoduls übersetzbar machen.~~ Erledigt (4.3.0): alle
  Beschriftungen unter `$GLOBALS['TL_LANG']['MSC']['adressen_suche']`.
* ~~In der Backend-Auflistung sind die Icons in der Spalte "Aktiv" zu groß geraten. Bitte
  auf 16x16 reduzieren.~~ Erledigt (4.1.1): Die vier Status-SVGs `grau.svg`,
  `gelb_rahmen.svg`, `gruen_rahmen.svg` und `rot_rahmen.svg` trugen intrinsisch
  `width="512" height="512"` (das Modul-Icon `icon.svg` dagegen korrekt 16). Jetzt auf 16
  gesetzt, `viewBox` unverändert. Das bisher in `addIcon()` mitgegebene
  `width="16" height="16"` half nur unter Contao 5 — dort hat das Attribut Vorrang, unter
  Contao 4.13 gewinnt die Dateigröße. In beiden Versionen nachgemessen: alle vier
  Statussymbole erzeugen jetzt genau eine Größenangabe `16x16`.

* ~~Die drei SVG-Dateien `gelb.svg`, `gruen.svg` und `rot.svg` (Varianten ohne Rahmen)
  werden von keiner Stelle im Code referenziert.~~ Erledigt (4.1.2): entfernt. Über die
  Git-Historie bleiben sie wiederherstellbar.
