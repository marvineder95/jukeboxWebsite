<?php
/**
 * Jukebox löschen
 */
require_once '../config/config.php';

setSecurityHeaders();

// Login-Check
if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

// CSRF-Token prüfen
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCsrfToken($_POST['csrf_token'] ?? '')) {
    // GET-Request: Bestätigungsseite anzeigen
    $id = $_GET['id'] ?? '';
    $jukebox = getJukeboxById($id);
    
    if (!$jukebox) {
        redirect('/admin/dashboard.php');
    }
    
    $lang = getCurrentLanguage();
    ?>
<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('admin_delete_jukebox'); ?> | <?php echo COMPANY_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="admin-header-inner">
                <a href="/admin/dashboard.php" class="logo">
                    <span class="logo-icon">🎵</span>
                    <span><?php echo __('admin_title'); ?></span>
                </a>
                <nav class="admin-nav">
                    <a href="/admin/dashboard.php"><?php echo __('admin_jukeboxes_title'); ?></a>
                    <a href="/admin/create.php"><?php echo __('admin_create_jukebox'); ?></a>
                </nav>
                <a href="/admin/logout.php" class="btn btn-dark btn-sm"><?php echo __('admin_logout'); ?></a>
            </div>
        </div>
    </div>
    
    <!-- Admin Main -->
    <main class="admin-main">
        <div class="container">
            <div class="admin-card" style="max-width: 500px; margin: 0 auto;">
                <div class="admin-card-body" style="text-align: center;">
                    <div style="font-size: 4rem; margin-bottom: var(--space-4);">⚠️</div>
                    <h2 style="margin-bottom: var(--space-4);">Jukebox löschen?</h2>
                    <p style="margin-bottom: var(--space-6);">
                        Möchten Sie die Jukebox <strong>"<?php echo e($jukebox['name']); ?>"</strong> wirklich löschen?<br>
                        Diese Aktion kann nicht rückgängig gemacht werden.
                    </p>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="id" value="<?php echo e($jukebox['id']); ?>">
                        <div style="display: flex; gap: var(--space-4); justify-content: center;">
                            <a href="/admin/dashboard.php" class="btn btn-dark">Abbrechen</a>
                            <button type="submit" class="btn btn-primary" style="background: #ef4444;">Ja, löschen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
    <?php
    exit;
}

// POST-Request: Löschen durchführen
$id = $_POST['id'] ?? '';

if ($id && deleteJukebox($id)) {
    redirect('/admin/dashboard.php?success=delete');
} else {
    redirect('/admin/dashboard.php?error=delete');
}
