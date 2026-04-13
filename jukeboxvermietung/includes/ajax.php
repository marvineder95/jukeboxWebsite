<?php
/**
 * AJAX-Endpunkte
 */
require_once '../config/config.php';

// Sicherheits-Header setzen
setSecurityHeaders();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getInquiryItems':
        // CSRF-ähnlicher Schutz: Origin/Referer prüfen
        $allowedOrigin = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $allowedOrigin .= $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, $allowedOrigin) !== 0) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['success' => false, 'error' => 'Invalid origin'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit;
        }
        
        // Jukebox-Daten für Anfrageliste laden
        $ids = $_GET['ids'] ?? '';
        $idArray = explode(',', $ids);
        
        $items = [];
        foreach ($idArray as $id) {
            $id = trim($id);
            if (empty($id)) continue;
            
            $jukebox = getJukeboxById($id);
            if ($jukebox) {
                $items[] = [
                    'id' => $jukebox['id'],
                    'name' => getLocalizedValue($jukebox, 'name'),
                    'price' => formatPrice($jukebox['price_day']),
                    'image' => getJukeboxImageUrl($jukebox['main_image'])
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'items' => $items], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        break;
        
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'Unknown action'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
