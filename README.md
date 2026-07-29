# Adressen

Adressen-Verwaltung für **Contao 4.13** und **Contao 5**.

Das Bundle pflegt zentrale Kontaktdatensätze und gibt sie an beliebiger Stelle der Website
aus – als Inhaltselement, über einen Insert-Tag oder über zwei Frontend-Module.

## Voraussetzungen

| | |
|---|---|
| PHP | ^8.1 |
| Contao | ^4.13 \|\| ^5.0 |

## Installation

```bash
composer require schachbulle/contao-adressen-bundle
```

Anschließend die Datenbank aktualisieren:

```bash
vendor/bin/contao-console contao:migrate
```

## Funktionen

### Backend-Modul „Adressen"

Unter *Inhalte → Adressen* werden die Datensätze gepflegt: Name, Anschrift, bis zu vier
Telefon- und zwei Faxnummern, sechs E-Mail-Adressen, Bankverbindung, Homepage und Profile
in sozialen Netzwerken, ein Foto sowie ein öffentlicher Visitenkarten-Text.

Für jede Angabe lässt sich getrennt festlegen, ob sie im Frontend erscheinen darf
(`*_view`-Schalter). Ein Statussymbol in der Liste zeigt, ob eine Adresse aktiv und ob sie
irgendwo auf der Website eingebunden ist.

Über die Kopfleiste stehen ein **CSV-Import** und ein **CSV-Export** bereit. Beim Import
werden nur Spalten übernommen, die es in `tl_adressen` wirklich gibt; die erste Zeile der
Datei muss die Spaltennamen enthalten.

Zusätzlich gibt es einen Spezialfilter, der alle Adressen anzeigt, die sich eine
E-Mail-Adresse mit einem anderen Datensatz teilen.

### Kategorien

Unter *Adressen → Kategorien* (`tl_adressen_categories`) werden frei definierbare
Kategorien gepflegt, die einer Adresse zugewiesen werden können. Das Suchmodul kann darauf
einschränken.

### Inhaltselement „Adresse"

Gibt eine ausgewählte Adresse aus. Einstellbar sind eine Funktion/ein Amt (erscheint vor
dem Namen), ein Zusatztext, die Beschränkung auf einzelne E-Mail-Adressen sowie ein
abweichendes Foto und Bildformat.

### Insert-Tag

```
{{adresse::ID}}
{{adresse::ID::Funktion}}
{{adresse::ID::Funktion::Funktionsinfo}}
```

Zusätzlich kann das Foto gesteuert werden:

```
{{adresse::12::Präsident::foto=0}}        Foto ausblenden
{{adresse::12::Präsident::foto=120,90}}   Foto in abweichender Größe
```

### Frontend-Module

| Modul | Beschreibung |
|---|---|
| **Adressensuche** (`adressen_suche`) | Volltextsuche über alle Adressfelder, optional eingeschränkt auf Kategorien. Der Parameter `email=1` schaltet eine kompakte Ansicht frei, aus der sich die ausgewählten E-Mail-Adressen in die Zwischenablage kopieren lassen. |
| **Wertungsreferenten** (`adressen_wertungsreferenten`) | Tabelle der Verbände/Bezirke mit dem jeweils zuständigen Referenten. |

URL-Parameter der Suche: `s` (Suchbegriff), `funktion[]` (Kategorie-IDs), `join`
(`and`/`or`), `email` (kompakte Ansicht).

### Cronjobs

| Service | Intervall | Aufgabe |
|---|---|---|
| `schachbulle.adressen.cron.extrahieren` | täglich | Ermittelt, auf welchen veröffentlichten Seiten jede Adresse eingebunden ist, und schreibt die URLs nach `tl_adressen.links`. |
| `schachbulle.adressen.cron.kontrollieren` | 1. Jan/Apr/Jul/Okt, 04:00 Uhr | Verschickt an alle aktiven, eingebundenen Adressen eine E-Mail mit den gespeicherten Daten und der Bitte um Korrekturmeldungen. |

> **Achtung:** Der Kontroll-Cronjob verschickt E-Mails an echte Empfänger. Er tut das erst,
> wenn in den Einstellungen der Haken **„Kontroll-E-Mails scharfschalten"** gesetzt ist.
> Ohne diesen Haken gehen alle E-Mails ausschließlich an den eingetragenen Test-Empfänger.

### Systemeinstellungen

Unter *System → Einstellungen → Adressen* lassen sich ein Standardbild und die Bildgröße
festlegen, die verwendet werden, wenn eine Adresse kein eigenes Foto hat.

Unter *System → Einstellungen → Adressen: Kontroll-E-Mails* wird der Kontroll-Cronjob
konfiguriert:

| Feld | Bedeutung |
|---|---|
| Absenderadresse | Ohne Eintrag verschickt der Cronjob nichts. |
| Absendername | Wird als Absender angezeigt und dient als Grußformel am Ende der E-Mail. |
| Antwortadresse | Leer lassen, um die Absenderadresse zu verwenden. Schreibweise mit Namen erlaubt. |
| Betreff | Betreffzeile der E-Mail. |
| Basis-URL für Fotos | Vollständige Adresse der Website. Leer lassen, um kein Foto anzuzeigen. |
| Kontroll-E-Mails scharfschalten | Erst mit Haken gehen die E-Mails an die echten Kontakte. |
| Test-Empfänger | Empfänger im Testmodus. |

Fehlt die Absenderadresse – oder im Testmodus der Test-Empfänger – bricht der Cronjob ab
und schreibt eine Meldung in das Log `contao.cron`.

Anrede und Einleitungstext der E-Mail stehen in der Sprachdatei
(`$GLOBALS['TL_LANG']['MSC']['adressen_cron_anrede']` und `…_einleitung`) und lassen sich
über eine eigene Sprachdatei im Projekt überschreiben.

## Templates

| Template | Verwendung |
|---|---|
| `ce_adressen.html5` | Inhaltselement „Adresse" |
| `ce_adressen_default.html5` | Alternative für das Inhaltselement (über `customTpl` wählbar) |
| `ce_adressen_inserttag.html5` | Ausgabe des Insert-Tags |
| `adresse_ergebnisse.html5` | Modul „Adressensuche" |
| `mod_adressen_referenten.html5` | Modul „Wertungsreferenten" |

## Entwicklung

```bash
vendor/bin/phpunit          # Unit-Tests (tests/)
```

Die Tests laufen auch ohne `composer install` im Bundle – der Bootstrap
(`tests/bootstrap.php`) registriert dann einen eigenen PSR-4-Autoloader und bindet
optional eine Contao-Referenzinstallation ein (Pfad über die Umgebungsvariable
`CONTAO_TEST_DIR`).

Konventionen:

* `declare(strict_types=1);` in jeder PHP-Datei
* Kommentare, Dokumentation und DCA-Labels auf **Deutsch**
* API-Unterschiede zwischen Contao 4.13 und Contao 5 werden über
  `ContaoAdressenBundle::isContao5()` bzw. `Classes\Kompatibilitaet` gekapselt

## Lizenz

LGPL-3.0-or-later

**Frank Hoppe**
