<?php
/**
 * SQLite Datenbank-Verwaltung
 * Filebasierte Datenbank für Jukeboxvermietung
 */

// Pfad zur SQLite Datenbank
const DB_PATH = DATA_PATH . 'jukebox.db';

/**
 * Datenbank-Verbindung herstellen
 */
function getDbConnection() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // SQLite für Foreign Keys aktivieren
        $db->exec('PRAGMA foreign_keys = ON');
        
        return $db;
    } catch (PDOException $e) {
        error_log('Datenbank-Fehler: ' . $e->getMessage());
        return null;
    }
}

/**
 * Datenbank-Tabellen initialisieren (falls nicht vorhanden)
 */
function initDatabase() {
    $db = getDbConnection();
    if (!$db) return false;
    
    try {
        // Jukeboxen Tabelle
        $db->exec('
            CREATE TABLE IF NOT EXISTS jukeboxes (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                name_en TEXT,
                manufacturer TEXT,
                model TEXT,
                year INTEGER,
                short_description TEXT,
                short_description_en TEXT,
                description TEXT,
                description_en TEXT,
                music_format TEXT,
                music_format_en TEXT,
                condition TEXT,
                condition_en TEXT,
                function_status TEXT DEFAULT "working",
                power_connection TEXT,
                power_connection_en TEXT,
                dimensions TEXT,
                dimensions_en TEXT,
                price_day REAL DEFAULT 0,
                featured INTEGER DEFAULT 0,
                "order" INTEGER DEFAULT 0,
                main_image TEXT,
                gallery_images TEXT, -- JSON Array
                created_at TEXT,
                updated_at TEXT
            )
        ');
        
        // Index für schnellere Abfragen
        $db->exec('CREATE INDEX IF NOT EXISTS idx_featured ON jukeboxes(featured)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_order ON jukeboxes("order")');
        
        return true;
    } catch (PDOException $e) {
        error_log('Datenbank-Initialisierungsfehler: ' . $e->getMessage());
        return false;
    }
}

/**
 * Migration von JSON zu SQLite durchführen
 * Wird einmalig beim ersten Aufruf ausgeführt
 */
function migrateFromJson() {
    $jsonFile = DATA_PATH . 'jukeboxes.json';
    
    // Prüfen ob JSON-Datei existiert
    if (!file_exists($jsonFile)) {
        return true;
    }
    
    // Prüfen ob bereits Daten in SQLite vorhanden sind
    $db = getDbConnection();
    if (!$db) return false;
    
    try {
        $stmt = $db->query('SELECT COUNT(*) as count FROM jukeboxes');
        $result = $stmt->fetch();
        
        // Wenn bereits Daten vorhanden, nicht migrieren
        if ($result['count'] > 0) {
            return true;
        }
    } catch (PDOException $e) {
        // Tabelle existiert noch nicht, Initialisierung nötig
        initDatabase();
    }
    
    // JSON-Daten laden
    $jsonData = file_get_contents($jsonFile);
    $jukeboxes = json_decode($jsonData, true);
    
    if (empty($jukeboxes) || !is_array($jukeboxes)) {
        return true;
    }
    
    // Daten migrieren
    $db = getDbConnection();
    $db->beginTransaction();
    
    try {
        $stmt = $db->prepare('
            INSERT INTO jukeboxes (
                id, name, name_en, manufacturer, model, year,
                short_description, short_description_en, description, description_en,
                music_format, music_format_en, condition, condition_en, function_status,
                power_connection, power_connection_en, dimensions, dimensions_en,
                price_day, featured, "order", main_image, gallery_images, created_at, updated_at
            ) VALUES (
                :id, :name, :name_en, :manufacturer, :model, :year,
                :short_description, :short_description_en, :description, :description_en,
                :music_format, :music_format_en, :condition, :condition_en, :function_status,
                :power_connection, :power_connection_en, :dimensions, :dimensions_en,
                :price_day, :featured, :order, :main_image, :gallery_images, :created_at, :updated_at
            )
        ');
        
        foreach ($jukeboxes as $jukebox) {
            $stmt->execute([
                ':id' => $jukebox['id'] ?? generateJukeboxId(),
                ':name' => $jukebox['name'] ?? '',
                ':name_en' => $jukebox['name_en'] ?? '',
                ':manufacturer' => $jukebox['manufacturer'] ?? '',
                ':model' => $jukebox['model'] ?? '',
                ':year' => $jukebox['year'] ?? null,
                ':short_description' => $jukebox['short_description'] ?? '',
                ':short_description_en' => $jukebox['short_description_en'] ?? '',
                ':description' => $jukebox['description'] ?? '',
                ':description_en' => $jukebox['description_en'] ?? '',
                ':music_format' => $jukebox['music_format'] ?? '',
                ':music_format_en' => $jukebox['music_format_en'] ?? '',
                ':condition' => $jukebox['condition'] ?? '',
                ':condition_en' => $jukebox['condition_en'] ?? '',
                ':function_status' => $jukebox['function_status'] ?? 'working',
                ':power_connection' => $jukebox['power_connection'] ?? '',
                ':power_connection_en' => $jukebox['power_connection_en'] ?? '',
                ':dimensions' => $jukebox['dimensions'] ?? '',
                ':dimensions_en' => $jukebox['dimensions_en'] ?? '',
                ':price_day' => $jukebox['price_day'] ?? 0,
                ':featured' => !empty($jukebox['featured']) ? 1 : 0,
                ':order' => $jukebox['order'] ?? 0,
                ':main_image' => $jukebox['main_image'] ?? '',
                ':gallery_images' => json_encode($jukebox['gallery_images'] ?? []),
                ':created_at' => $jukebox['created_at'] ?? date('Y-m-d H:i:s'),
                ':updated_at' => $jukebox['updated_at'] ?? date('Y-m-d H:i:s')
            ]);
        }
        
        $db->commit();
        
        // JSON-Datei als Backup umbenennen
        rename($jsonFile, $jsonFile . '.backup');
        
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        error_log('Migrationsfehler: ' . $e->getMessage());
        return false;
    }
}
