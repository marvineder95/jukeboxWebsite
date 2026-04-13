<?php
/**
 * Jukeboxvermietung - Hauptkonfiguration
 * 
 * Diese Datei enthält alle zentralen Konfigurationseinstellungen.
 * Bei Umzug zu world4yoU oder anderem Hosting hier anpassen.
 */

// ============================================
// SESSION-SICHERHEIT
// ============================================

// Sichere Session-Cookie-Einstellungen
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Session-Start (wenn noch nicht aktiv)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// BASIS-KONFIGURATION
// ============================================

// Protokoll automatisch erkennen (http/https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Basis-URL der Webseite
define('BASE_URL', '');

// Physische Pfade
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('PARTIALS_PATH', ROOT_PATH . 'partials/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('DATA_PATH', ROOT_PATH . 'data/');

// Öffentliche Pfade relativ zur Domainwurzel
define('ASSETS_URL', '/assets/');
define('UPLOADS_URL', '/uploads/');

// ============================================
// ADMIN-KONFIGURATION
// ============================================

// Admin-Zugangsdaten werden aus einer geschützten Datei geladen
// (data/ liegt hinter .htaccess und ist nicht öffentlich erreichbar)
$adminConfigFile = DATA_PATH . 'admin_config.php';

if (!file_exists($adminConfigFile)) {
    // Beim ersten Aufruf: Konfigurationsdatei mit zufälligem Passwort erstellen
    $randomPassword = bin2hex(random_bytes(16));
    $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
    $configContent = "<?php\n" .
        "// AUTOMATISCH GENERIERT - NICHT MANUELL BEARBEITEN\n" .
        "// Zuletzt aktualisiert: " . date('Y-m-d H:i:s') . "\n" .
        "define('ADMIN_USERNAME', 'admin');\n" .
        "define('ADMIN_PASSWORD_HASH', '" . $passwordHash . "');\n";
    
    file_put_contents($adminConfigFile, $configContent);
    
    // Sicherstellen, dass die Datei nicht öffentlich lesbar ist
    chmod($adminConfigFile, 0600);
    
    // Hinweis für den Administrator (nur beim ersten Aufruf sichtbar)
    define('ADMIN_SETUP_NOTICE', true);
    define('ADMIN_SETUP_PASSWORD', $randomPassword);
} else {
    define('ADMIN_SETUP_NOTICE', false);
    define('ADMIN_SETUP_PASSWORD', '');
}

require_once $adminConfigFile;

// Session-Timeout in Sekunden (30 Minuten)
define('SESSION_TIMEOUT', 1800);

// Maximale Login-Versuche vor Sperre
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 Minuten

// ============================================
// E-MAIL-KONFIGURATION
// ============================================

// Empfänger für Anfragen
define('MAIL_RECIPIENT', 'office@transportpeter.at');

// Absender für System-Mails
define('MAIL_SENDER', 'noreply@jukeboxvermietung.at');

// E-Mail-Betreff für neue Anfragen
define('MAIL_SUBJECT_PREFIX', '[Jukebox-Anfrage] ');

// ============================================
// UNTERNEHMENS-DATEN
// ============================================

define('COMPANY_NAME', 'Jukeboxvermietung');
define('COMPANY_STREET', 'Musterstraße 123');
define('COMPANY_ZIP', '1010');
define('COMPANY_CITY', 'Wien');
define('COMPANY_COUNTRY', 'Österreich');

// Fixe Liefergebiete
const DELIVERY_COUNTRIES = ['AT', 'IT', 'DE', 'CH'];
const DELIVERY_COUNTRIES_NAMES = [
    'AT' => 'Österreich',
    'IT' => 'Italien', 
    'DE' => 'Deutschland',
    'CH' => 'Schweiz'
];
define('COMPANY_PHONE', '+43 1 234 56 78');
define('COMPANY_EMAIL', 'office@transportpeter.at');
define('COMPANY_WEB', 'www.jukeboxvermietung.at');

// ============================================
// FUNKTIONEN EINBINDEN
// ============================================

require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'language.php';
require_once INCLUDES_PATH . 'jukebox-model.php';
