<?php
/**
 * Admin-Dashboard
 */
require_once '../config/config.php';

setSecurityHeaders();

// Login-Check
if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

// Jukeboxen laden
$jukeboxes = getAllJukeboxes();

// Erfolgsmeldungen
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$lang = getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('admin_dashboard_title'); ?> | <?php echo COMPANY_NAME; ?></title>
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
                    <a href="/admin/dashboard.php" class="active"><?php echo __('admin_jukeboxes_title'); ?></a>
                    <a href="/admin/create.php"><?php echo __('admin_create_jukebox'); ?></a>
                </nav>
                <a href="/admin/logout.php" class="btn btn-dark btn-sm"><?php echo __('admin_logout'); ?></a>
            </div>
        </div>
    </div>

    <!-- Admin Main -->
    <main class="admin-main">
        <div class="container">
            <?php if ($success): ?>
            <div style="padding: var(--space-4); background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: var(--radius-md); margin-bottom: var(--space-6);">
                <p style="color: #22c55e; margin-bottom: 0;">
                    <?php 
                    $galleryCount = isset($_GET['gallery']) ? (int)$_GET['gallery'] : 0;
                    if ($success === 'create' && $galleryCount > 0) {
                        echo e(__('admin_success_' . $success)) . ' (mit ' . $galleryCount . ' Galeriebild' . ($galleryCount > 1 ? 'ern' : '') . ')';
                    } elseif ($success === 'update' && $galleryCount > 0) {
                        echo e(__('admin_success_' . $success)) . ' (mit ' . $galleryCount . ' Galeriebild' . ($galleryCount > 1 ? 'ern' : '') . ')';
                    } else {
                        echo e(__('admin_success_' . $success));
                    }
                    ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div style="padding: var(--space-4); background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); margin-bottom: var(--space-6);">
                <p style="color: #ef4444; margin-bottom: 0;"><?php echo e(__('admin_error_' . $error)); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="font-size: var(--text-xl); margin-bottom: 0;"><?php echo __('admin_jukeboxes_title'); ?></h2>
                    <a href="/admin/create.php" class="btn btn-primary btn-sm">
                        + <?php echo __('admin_create_jukebox'); ?>
                    </a>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($jukeboxes)): ?>
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Bild</th>
                                    <th>Name</th>
                                    <th>Hersteller</th>
                                    <th>Preis/Tag</th>
                                    <th>Status</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jukeboxes as $jukebox): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo getJukeboxImageUrl($jukebox['main_image']); ?>" 
                                             alt="" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-md);"
                                             onerror="this.src='https://via.placeholder.com/60x60/242424/D4AF37?text=JB'">
                                    </td>
                                    <td>
                                        <strong><?php echo e($jukebox['name']); ?></strong>
                                        <?php if (!empty($jukebox['featured'])): ?>
                                        <span style="display: inline-block; margin-left: var(--space-2); padding: 2px 8px; background: var(--color-primary); color: var(--color-dark); font-size: 10px; border-radius: var(--radius-full); text-transform: uppercase;">Highlight</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($jukebox['manufacturer']); ?></td>
                                    <td><?php echo number_format($jukebox['price_day'], 0, ',', '.'); ?> €</td>
                                    <td>
                                        <span style="display: inline-block; padding: var(--space-1) var(--space-3); background: <?php echo $jukebox['function_status'] === 'working' ? 'rgba(34, 197, 94, 0.2)' : 'rgba(245, 158, 11, 0.2)'; ?>; color: <?php echo $jukebox['function_status'] === 'working' ? '#22c55e' : '#f59e0b'; ?>; font-size: var(--text-xs); border-radius: var(--radius-full);">
                                            <?php echo getFunctionStatusLabel($jukebox['function_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="/admin/edit.php?id=<?php echo $jukebox['id']; ?>" class="admin-btn admin-btn-edit"><?php echo __('btn_edit'); ?></a>
                                            <a href="/admin/delete.php?id=<?php echo $jukebox['id']; ?>" class="admin-btn admin-btn-delete" onclick="return confirm('<?php echo __('admin_delete_confirm'); ?>')"><?php echo __('btn_delete'); ?></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: var(--space-12);">
                        <p style="color: var(--color-gray-500); margin-bottom: var(--space-6);"><?php echo __('admin_no_jukeboxes'); ?></p>
                        <a href="/admin/create.php" class="btn btn-primary"><?php echo __('admin_create_jukebox'); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
