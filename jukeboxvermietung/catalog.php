<?php
/**
 * Jukebox-Katalog
 */
require_once 'config/config.php';

setSecurityHeaders();

$page = 'catalog';
$metaData = [
    'url' => BASE_URL . 'catalog.php'
];

// Sortierung
$sort = $_GET['sort'] ?? 'order';
$order = $_GET['order'] ?? 'ASC';
$allowedSorts = ['name', 'price_day', 'order'];
$allowedOrders = ['ASC', 'DESC'];

if (!in_array($sort, $allowedSorts, true)) $sort = 'order';
if (!in_array($order, $allowedOrders, true)) $order = 'ASC';

// Jukeboxen laden
$jukeboxes = getAllJukeboxes($sort, $order);

include PARTIALS_PATH . 'header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?php echo __('catalog_title'); ?></h1>
        <p><?php echo __('catalog_subtitle'); ?></p>
    </div>
</section>

<!-- Catalog Section -->
<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="catalog-filters reveal">
            <div>
                <span style="color: var(--color-gray-500); font-size: var(--text-sm);">
                    <?php echo count($jukeboxes); ?> <?php echo getCurrentLanguage() === 'de' ? 'Jukeboxen' : 'Jukeboxes'; ?>
                </span>
            </div>
            <div style="display: flex; gap: var(--space-3); align-items: center;">
                <label style="color: var(--color-gray-500); font-size: var(--text-sm);">
                    <?php echo __('catalog_sort'); ?>:
                </label>
                <select class="form-select" style="width: auto; min-width: 150px;" onchange="window.location.href='?sort=' + this.value + '&order=<?php echo e($order); ?>'">
                    <option value="order" <?php echo $sort === 'order' ? 'selected' : ''; ?>><?php echo __('sort_name_asc'); ?></option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>><?php echo __('sort_name_asc'); ?></option>
                    <option value="price_day" <?php echo $sort === 'price_day' ? 'selected' : ''; ?>><?php echo __('sort_price_asc'); ?></option>
                </select>
            </div>
        </div>
        
        <?php if (!empty($jukeboxes)): ?>
        <div class="jukebox-grid">
            <?php foreach ($jukeboxes as $jukebox): ?>
            <article class="jukebox-card reveal">
                <div class="jukebox-card-image">
                    <img src="<?php echo e(getJukeboxImageUrl($jukebox['main_image'])); ?>" 
                         alt="<?php echo e(getLocalizedValue($jukebox, 'name')); ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1514525253440-b393452e8d26?w=600&q=80'">
                    <?php if (!empty($jukebox['featured'])): ?>
                    <span class="jukebox-card-badge">Highlight</span>
                    <?php endif; ?>
                    <div class="jukebox-card-overlay">
                        <a href="<?php echo BASE_URL; ?>jukebox.php?id=<?php echo e($jukebox['id']); ?>" class="btn btn-primary">
                            <?php echo __('view_details'); ?>
                        </a>
                    </div>
                </div>
                <div class="jukebox-card-content">
                    <div class="jukebox-card-header">
                        <div>
                            <h3 class="jukebox-card-title"><?php echo e(getLocalizedValue($jukebox, 'name')); ?></h3>
                            <p class="jukebox-card-subtitle"><?php echo e($jukebox['manufacturer']); ?> <?php echo e($jukebox['model']); ?></p>
                        </div>
                        <div class="jukebox-card-price">
                            <?php echo formatPrice($jukebox['price_day']); ?>
                        </div>
                    </div>
                    <p class="jukebox-card-description">
                        <?php echo e(getLocalizedValue($jukebox, 'short_description')); ?>
                    </p>
                    <div class="jukebox-card-actions">
                        <a href="<?php echo BASE_URL; ?>jukebox.php?id=<?php echo e($jukebox['id']); ?>" class="btn btn-dark btn-sm">
                            <?php echo __('view_details'); ?>
                        </a>
                        <button class="btn btn-primary btn-sm inquiry-btn" 
                                data-jukebox-id="<?php echo e($jukebox['id']); ?>"
                                data-text-add="<?php echo e(__('add_to_inquiry')); ?>"
                                data-text-remove="<?php echo e(__('remove_from_inquiry')); ?>">
                            <?php echo __('add_to_inquiry'); ?>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center reveal" style="padding: var(--space-16) 0;">
            <p style="font-size: var(--text-xl); color: var(--color-gray-500);">
                <?php echo __('catalog_empty'); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-content reveal">
            <h2><?php echo __('cta_title'); ?></h2>
            <p><?php echo __('cta_text'); ?></p>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-primary btn-lg">
                <?php echo __('cta_button'); ?>
            </a>
        </div>
    </div>
</section>

<?php include PARTIALS_PATH . 'footer.php'; ?>
