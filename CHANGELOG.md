# Adressen Changelog

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
