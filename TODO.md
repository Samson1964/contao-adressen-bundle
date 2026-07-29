# Offene Tickets

## Deploy

* **4.0.0 und 4.1.0 auf das Livesystem bringen.** Reihenfolge: hochladen →
  `contao:migrate` (entfernt `tl_adressen.addImage`, `prozentx`, `prozenty`, verbreitert
  `iban` auf 34 Zeichen) → **Produktions-Cache neu bauen** (Service-IDs und
  `services.yaml` haben sich geändert).
* **Nach dem Deploy die Cron-Einstellungen pflegen:** *System → Einstellungen →
  „Adressen: Kontroll-E-Mails"*. Ohne Absenderadresse und Test-Empfänger verschickt der
  Kontroll-Cronjob nichts. Erst der Haken „Kontroll-E-Mails scharfschalten" sendet an die
  echten Kontakte. Dabei klären, ob die Adressen auf `schachbund.de` oder `schachbund.com`
  lauten sollen — im Quelltext standen bis 4.0.0 beide Varianten nebeneinander.

## Fehler

* (derzeit nichts offen)

## Verbesserungen

* **Restliche fest verdrahtete Texte der Kontroll-E-Mail in die Sprachdatei holen.** Mit
  4.1.0 sind Anrede und Einleitung nach `$GLOBALS['TL_LANG']['MSC']['adressen_cron_*']`
  gewandert. Noch im Quelltext von `Cron\KontrolliereAdressen` stehen: die Feldnamen der
  Auflistung (`Name`, `Vorname`, `Telefon 1` …), die Hinweise zum Standardfoto, der Satz
  „Ihre Adresse wird auf folgenden Seiten angezeigt:", der Spambot-Hinweis und
  „Dies ist eine automatisch generierte E-Mail.".
* **Bedienoberfläche des Suchmoduls übersetzbar machen.** `adresse_ergebnisse.html5`
  enthält die Beschriftungen („Suchbegriff:", „Mitglied in", „Verknüpfung", „Suchen",
  „Wir haben X Datensätze …") direkt im Template statt in einer Sprachdatei.
* **`ce_adressen_default.html5` prüfen.** Das Template ist eine nahezu identische Kopie von
  `ce_adressen.html5`. Wenn es niemand als `customTpl` nutzt, kann es entfallen.

## Für Contao 6 vormerken

* Der Insert-Tag `{{adresse::ID}}` hängt am Hook `replaceInsertTags`, der ab Contao 5.2 als
  veraltet gilt und in Contao 6 wegfällt. Ersatz ist das Attribut `#[AsInsertTag]` — das
  gibt es aber erst ab Contao 5.1 und wäre mit Contao 4.13 nicht kompatibel. Umstellen,
  sobald die 4.13-Unterstützung aufgegeben wird.

## Erledigt

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
