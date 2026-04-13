# Jukeboxvermietung – Agent-Dokumentation

> Zuletzt aktualisiert: 2026-04-13  
> Sprache der Codebasis: Deutsch (mit englischer UI-Übersetzung)

---

## Projektübersicht

**Jukeboxvermietung** ist eine produktionsreife, mehrsprachige Website (Deutsch/Englisch) zur Vermietung von Jukeboxen. Sie besteht aus einem öffentlichen Frontend (Katalog, Kontaktformular, Anfrageliste) und einem passwortgeschützten Admin-Bereich zur Verwaltung der Jukebox-Daten.

Das Projekt liegt im Unterverzeichnis `jukeboxvermietung/`. Alle Pfade in dieser Dokumentation beziehen sich auf dieses Unterverzeichnis, sofern nicht anders angegeben.

### Technologie-Stack

- **Backend:** PHP 7.4+ (prozedural, kein Framework)
- **Datenbank:** SQLite (`data/jukebox.db`), ursprünglich aus JSON migriert
- **Frontend:** HTML5, Vanilla CSS (`assets/css/style.css`), Vanilla JavaScript (`assets/js/main.js`)
- **Webserver:** Apache (empfohlen, `.htaccess` vorhanden)
- **Mail-Versand:** PHP-native `mail()`-Funktion
- **Abhängigkeiten:** Keine externen PHP-/JS-Pakete (kein Composer, kein npm)

---

## Verzeichnisstruktur

```
jukeboxvermietung/
├── admin/                  # Admin-Bereich (CRUD + Login)
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php       # Übersicht aller Jukeboxen
│   ├── create.php          # Jukebox erstellen
│   ├── edit.php            # Jukebox bearbeiten
│   ├── delete.php          # Löschbestätigung + Löschung
│   └── .htaccess           # Erhöhte PHP-Upload-Limits
├── assets/
│   ├── css/
│   │   ├── style.css       # Hauptstylesheet (~44 KB)
│   │   └── flatpickr-custom.css
│   ├── js/
│   │   └── main.js         # Haupt-JavaScript (~16 KB)
│   └── images/             # Leer; Bilder liegen unter uploads/
├── config/
│   └── config.php          # Zentrale Konfiguration & Bootstrap
├── data/
│   ├── jukebox.db          # SQLite-Datenbank
│   └── jukeboxes.json.backup # Backup der alten JSON-Daten
├── includes/
│   ├── functions.php       # Hilfsfunktionen (Auth, Upload, Meta-Tags, …)
│   ├── language.php        # Übersetzungssystem (DE/EN)
│   ├── database.php        # SQLite-Verbindung & Initialisierung
│   ├── jukebox-model.php   # Datenmodell (CRUD, Anfrageliste)
│   └── ajax.php            # AJAX-Endpunkt für Anfrageliste
├── partials/
│   ├── header.php          # HTML-Head + Navigation + Inquiry-Sidebar
│   └── footer.php          # Footer + Cookie-Hinweis + Script-Tag
├── uploads/jukeboxes/      # Hochgeladene Jukebox-Bilder
├── index.php               # Startseite
├── catalog.php             # Katalog-Übersicht
├── jukebox.php             # Detailseite einer Jukebox
├── process.php             # Mietablauf-Seite
├── contact.php             # Kontaktformular
├── about.php               # Über-uns-Seite
├── faq.php                 # FAQ-Seite
├── impressum.php           # Impressum
├── datenschutz.php         # Datenschutzerklärung
├── 404.php                 # Fehlerseite
└── .htaccess               # Apache-Konfiguration (Security, Caching, Compression)
```

---

## Bootstrap & Einbindung

Jede Seite bindet zuerst `config/config.php` ein. Diese Datei:

1. Startet die PHP-Session (falls nicht aktiv)
2. Definiert Konstanten (`BASE_URL`, `ROOT_PATH`, `ASSETS_URL`, `UPLOADS_URL`, etc.)
3. Lädt Unternehmensdaten, Admin-Zugangsdaten und E-Mail-Einstellungen
4. Bindet die drei zentralen Include-Dateien ein:
   - `includes/functions.php`
   - `includes/language.php`
   - `includes/jukebox-model.php`

**Wichtig:** Neue PHP-Seiten müssen immer mit `require_once 'config/config.php';` (bzw. `../config/config.php` im Admin-Bereich) beginnen.

---

## Admin-Bereich

### Zugang
- URL: `/admin/login.php`
- Benutzername und Passwort-Hash sind in `config/config.php` hinterlegt.
- Aktueller Hash: bcrypt (`$2y$12$…`)
- Session-Timeout: 30 Minuten (1.800 Sekunden)

### CRUD-Workflow
1. **Dashboard** (`dashboard.php`): Tabellarische Übersicht aller Jukeboxen mit Bild, Name, Preis, Funktionsstatus, Aktionen.
2. **Create** (`create.php`): Formular mit Upload für Hauptbild + Galeriebilder (max. 5 MB, erlaubt: JPG, PNG, WebP).
3. **Edit** (`edit.php`): Gleiches Formular wie Create, aber mit bestehenden Daten vorbelegt. Bestehende Galeriebilder können einzeln entfernt werden.
4. **Delete** (`delete.php`): Bei GET wird eine Bestätigungsseite angezeigt; bei POST wird die Jukebox inkl. Bilddateien gelöscht.

---

## Datenmodell

### Tabelle `jukeboxes` (SQLite)
Wichtige Felder:
- `id` (TEXT PRIMARY KEY) – wird als `jb_` + Hex-String generiert
- `name`, `name_en`, `manufacturer`, `model`, `year`
- `short_description`, `short_description_en`
- `description`, `description_en`
- `music_format`, `condition`, `function_status`, `power_connection`, `dimensions` (jeweils mit `_en`-Variante)
- `price_day` (REAL)
- `featured` (INTEGER 0/1)
- `order` (INTEGER) – Sortierreihenfolge
- `main_image` (TEXT)
- `gallery_images` (TEXT, JSON-Array)
- `created_at`, `updated_at`

### Migration
Beim ersten Aufruf prüft `jukebox-model.php` automatisch, ob eine alte `data/jukeboxes.json` existiert. Falls ja und die SQLite-Tabelle ist leer, werden die Daten migriert und die JSON-Datei zu `.backup` umbenannt.

---

## Übersetzungssystem

- Sprachen sind in `includes/language.php` definiert.
- Verfügbare Sprachen: `['de', 'en']`
- Standard: `de`
- Sprachumschaltung erfolgt per GET-Parameter `?lang=en` und wird in der Session gespeichert.
- Kurzform für Übersetzungen: `__('schlüssel', ['platzhalter' => 'wert'])`
- Alle Texte (Meta-Tags, UI, Formularlabels, Admin-Texte) liegen in einem großen assoziativen Array pro Sprache.

---

## Frontend-Features

### Anfrageliste (Inquiry List)
- Funktioniert wie ein Warenkorb: Besucher können mehrere Jukeboxen zur Anfrageliste hinzufügen.
- Gespeichert in einem Cookie (`jukebox_inquiry`, 30 Tage Laufzeit, `SameSite=Lax`).
- Wird in der Sidebar rechts angezeigt (Toggle über Icon in der Header-Navigation).
- Beim Absenden des Kontaktformulars wird die Liste automatisch in die E-Mail eingefügt und danach geleert.

### Kontaktformular (`contact.php`)
- Felder: Vorname, Nachname, Firma, E-Mail, Telefon, Land, Veranstaltungsort, Zeitraum, Mietdauer, Nachricht, DSGVO-Checkbox
- Sicherheit: CSRF-Token + Honeypot-Feld (`website`)
- Validierung serverseitig in PHP
- Versand via `mail()` an `MAIL_RECIPIENT`

### Bilder
- Hauptbild und Galeriebilder werden nach `uploads/jukeboxes/` geschrieben.
- Erlaubte Formate: JPG, PNG, WebP
- Maximale Dateigröße: 5 MB (Frontend-Hinweis), adminseitig über `.htaccess` auf 10 M hochgesetzt
- Fehlende Bilder werden durch einen Unsplash-Placeholder ersetzt.

---

## Build- & Deployment-Prozess

### Kein Build-System
Dies ist eine klassische PHP-Website ohne Build-Schritt. Änderungen an PHP-, CSS- oder JS-Dateien sind direkt aktiv.

### Deployment
1. Alle Dateien auf den Webserver kopieren (z. B. per FTP).
2. Schreibrechte für folgende Verzeichnisse sicherstellen:
   - `/uploads/` (und Unterverzeichnisse)
   - `/data/` (SQLite-DB muss beschreibbar sein)
3. `config/config.php` anpassen:
   - Admin-Passwort-Hash ändern
   - E-Mail-Empfänger und Absender eintragen
   - Unternehmensdaten aktualisieren

---

## Code-Style & Konventionen

- **Sprache:** Kommentare und Dokumentation werden auf Deutsch verfasst.
- **Formatierung:** 4 Leerzeichen Einrückung, keine feste Zeilenlängenbegrenzung.
- **PHP-Tags:** Kurze öffnende Tags `<?php` überall verwendet.
- **Ausgabeescaping:** Benutzergenerierte Inhalte werden mit `e($string)` (Wrapper für `htmlspecialchars()`) escaped.
- **Input-Sanitization:** `sanitizeInput()` (trim, stripslashes, htmlspecialchars) wird im Datenmodell verwendet.
- **SQL:** Prepared Statements mit benannten Platzhaltern (`:name`) über PDO.
- ** Konstanten:** Großbuchstaben mit Unterstrich (`SESSION_TIMEOUT`, `MAIL_RECIPIENT`).

---

## Testing

- Es gibt **kein automatisiertes Test-Setup** (kein PHPUnit, kein JS-Test-Framework).
- Qualitätssicherung erfolgt manuell durch:
  1. Formular-Validierung auf der Kontaktseite testen
  2. Admin-Login/Logout testen
  3. Jukebox anlegen, bearbeiten, löschen und im Frontend prüfen
  4. Sprachwechsel (DE ↔ EN) auf verschiedenen Seiten testen
  5. Anfrageliste (hinzufügen, entfernen, Cookie-Überleben) testen
  6. Bild-Upload mit verschiedenen Dateigrößen/Formaten testen

---

## Sicherheitsaspekte

| Maßnahme | Umsetzung |
|----------|-----------|
| **Authentifizierung** | Session-basiert, bcrypt-Hash für Admin-Passwort |
| **Session-Timeout** | 30 Minuten Inaktivität (`SESSION_TIMEOUT`) |
| **CSRF-Schutz** | Tokens in allen Formularen (Admin + Kontakt) |
| **Spam-Schutz** | Honeypot-Feld im Kontaktformular |
| **XSS-Prävention** | `htmlspecialchars()` bei allen Ausgaben |
| **Upload-Sicherheit** | MIME-Type-Prüfung, Dateigrößenlimit, zufälliger Dateiname |
| **Verzeichnisschutz** | `.htaccess` blockiert Zugriff auf `/config/`, `/includes/`, `/data/` sowie `.json`, `.log`, `.md` |
| **HTTPS** | Optional in `.htaccess` vorbereitet (auskommentiert) |

---

## Wichtige Hinweise für Agenten

1. **Keine neuen Abhängigkeiten einführen** ohne Rücksprache. Das Projekt ist bewusst abhängigkeitsfrei gehalten.
2. **Neue PHP-Seiten** müssen `config/config.php` laden und `partials/header.php` + `partials/footer.php` einbinden, um konsistentes Layout und funktionierende Navigation zu gewährleisten.
3. **Datenbank-Schema-Änderungen** müssen in `includes/database.php` (Funktion `initDatabase()`) und in `includes/jukebox-model.php` (INSERT/UPDATE-Statements) synchron gepflegt werden.
4. **Neue Texte** gehören in `includes/language.php` in beide Spracharrays (`de` und `en`).
5. **Bilder** sollten im Format 800–1200 px Breite und unter 5 MB gehalten werden, damit der Upload reibungslos funktioniert.
6. **Admin-Seiten** müssen zu Beginn `isAdminLoggedIn()` prüfen und ggf. auf `login.php` umleiten.
