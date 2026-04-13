<?php
/**
 * Landingpage / Startseite
 */
require_once 'config/config.php';

setSecurityHeaders();

$page = 'home';
$metaData = [
    'url' => BASE_URL
];

// Featured Jukeboxen laden
$featuredJukeboxes = getFeaturedJukeboxes(3);

include PARTIALS_PATH . 'header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-decoration hero-decoration-1"></div>
    <div class="hero-decoration hero-decoration-2"></div>
    
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span>✨</span>
                <span><?php echo getCurrentLanguage() === 'de' ? 'Komplettservice in AT, IT, DE & CH' : 'Full Service in AT, IT, DE & CH'; ?></span>
            </div>
            
            <h1 class="hero-title">
                <span class="line"><?php echo __('hero_title'); ?></span>
            </h1>
            
            <p class="hero-subtitle">
                <?php echo __('hero_subtitle'); ?>
            </p>
            
            <div class="hero-actions">
                <a href="<?php echo BASE_URL; ?>catalog.php" class="btn btn-primary btn-lg">
                    <?php echo __('hero_cta_primary'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary btn-lg">
                    <?php echo __('hero_cta_secondary'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <span>Scroll</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12l7 7 7-7"/>
        </svg>
    </div>
</section>

<!-- Featured Jukeboxes Section -->
<section class="section featured">
    <div class="container">
        <div class="section-header reveal">
            <h2><?php echo __('featured_title'); ?></h2>
            <p><?php echo __('featured_subtitle'); ?></p>
        </div>
        
        <?php if (!empty($featuredJukeboxes)): ?>
        <div class="jukebox-grid">
            <?php foreach ($featuredJukeboxes as $jukebox): ?>
            <article class="jukebox-card reveal">
                <div class="jukebox-card-image">
                    <img src="<?php echo getJukeboxImageUrl($jukebox['main_image']); ?>" 
                         alt="<?php echo e(getLocalizedValue($jukebox, 'name')); ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1514525253440-b393452e8d26?w=600&q=80'">
                    <?php if (!empty($jukebox['featured'])): ?>
                    <span class="jukebox-card-badge">Highlight</span>
                    <?php endif; ?>
                    <div class="jukebox-card-overlay">
                        <a href="<?php echo BASE_URL; ?>jukebox.php?id=<?php echo $jukebox['id']; ?>" class="btn btn-primary">
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
                        <a href="<?php echo BASE_URL; ?>jukebox.php?id=<?php echo $jukebox['id']; ?>" class="btn btn-dark btn-sm">
                            <?php echo __('view_details'); ?>
                        </a>
                        <button class="btn btn-primary btn-sm inquiry-btn" 
                                data-jukebox-id="<?php echo $jukebox['id']; ?>"
                                data-text-add="<?php echo __('add_to_inquiry'); ?>"
                                data-text-remove="<?php echo __('remove_from_inquiry'); ?>">
                            <?php echo __('add_to_inquiry'); ?>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-8 reveal">
            <a href="<?php echo BASE_URL; ?>catalog.php" class="btn btn-secondary btn-lg">
                <?php echo __('featured_cta'); ?>
            </a>
        </div>
        <?php else: ?>
        <div class="text-center reveal">
            <p><?php echo __('catalog_empty'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Intro Section (Musikgeschichte) -->
<section class="section intro">
    <div class="container">
        <div class="intro-grid">
            <div class="intro-content reveal">
                <h2><?php echo __('intro_title'); ?></h2>
                <p><?php echo __('intro_text'); ?></p>
                
                <div class="intro-features">
                    <div class="intro-feature">
                        <div class="intro-feature-icon">🚚</div>
                        <div>
                            <h4><?php echo __('intro_feature_1_title'); ?></h4>
                            <p><?php echo __('intro_feature_1_text'); ?></p>
                        </div>
                    </div>
                    <div class="intro-feature">
                        <div class="intro-feature-icon">🛠️</div>
                        <div>
                            <h4><?php echo __('intro_feature_2_title'); ?></h4>
                            <p><?php echo __('intro_feature_2_text'); ?></p>
                        </div>
                    </div>
                    <div class="intro-feature">
                        <div class="intro-feature-icon">⭐</div>
                        <div>
                            <h4><?php echo __('intro_feature_3_title'); ?></h4>
                            <p><?php echo __('intro_feature_3_text'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="intro-visual reveal">
                <div class="intro-image">
                    <img src="<?php echo ASSETS_URL; ?>images/intro-jukebox.jpg" 
                         alt="Vintage Jukebox" 
                         onerror="this.src='https://images.unsplash.com/photo-1514525253440-b393452e8d26?w=800&q=80'">
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="section benefits">
    <div class="container">
        <div class="section-header reveal">
            <h2><?php echo __('benefits_title'); ?></h2>
        </div>
        
        <div class="benefits-grid">
            <div class="benefit-card reveal">
                <div class="benefit-icon">😌</div>
                <h3><?php echo __('benefit_1_title'); ?></h3>
                <p><?php echo __('benefit_1_text'); ?></p>
            </div>
            <div class="benefit-card reveal">
                <div class="benefit-icon">🎯</div>
                <h3><?php echo __('benefit_2_title'); ?></h3>
                <p><?php echo __('benefit_2_text'); ?></p>
            </div>
            <div class="benefit-card reveal">
                <div class="benefit-icon">👁️</div>
                <h3><?php echo __('benefit_3_title'); ?></h3>
                <p><?php echo __('benefit_3_text'); ?></p>
            </div>
            <div class="benefit-card reveal">
                <div class="benefit-icon">🏆</div>
                <h3><?php echo __('benefit_4_title'); ?></h3>
                <p><?php echo __('benefit_4_text'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="section process">
    <div class="container">
        <div class="section-header reveal">
            <h2><?php echo __('process_title'); ?></h2>
        </div>
        
        <div class="process-steps">
            <div class="process-step reveal">
                <div class="process-step-number"><?php echo __('process_step_1_number'); ?></div>
                <h3><?php echo __('process_step_1_title'); ?></h3>
                <p><?php echo __('process_step_1_text'); ?></p>
            </div>
            <div class="process-step reveal">
                <div class="process-step-number"><?php echo __('process_step_2_number'); ?></div>
                <h3><?php echo __('process_step_2_title'); ?></h3>
                <p><?php echo __('process_step_2_text'); ?></p>
            </div>
            <div class="process-step reveal">
                <div class="process-step-number"><?php echo __('process_step_3_number'); ?></div>
                <h3><?php echo __('process_step_3_title'); ?></h3>
                <p><?php echo __('process_step_3_text'); ?></p>
            </div>
            <div class="process-step reveal">
                <div class="process-step-number"><?php echo __('process_step_4_number'); ?></div>
                <h3><?php echo __('process_step_4_title'); ?></h3>
                <p><?php echo __('process_step_4_text'); ?></p>
            </div>
            <div class="process-step reveal">
                <div class="process-step-number"><?php echo __('process_step_5_number'); ?></div>
                <h3><?php echo __('process_step_5_title'); ?></h3>
                <p><?php echo __('process_step_5_text'); ?></p>
            </div>
            <div class="process-step reveal">
                <div class="process-step-number"><?php echo __('process_step_6_number'); ?></div>
                <h3><?php echo __('process_step_6_title'); ?></h3>
                <p><?php echo __('process_step_6_text'); ?></p>
            </div>
        </div>
        
        <div class="text-center mt-8 reveal">
            <a href="<?php echo BASE_URL; ?>process.php" class="btn btn-secondary">
                <?php echo getCurrentLanguage() === 'de' ? 'Mehr zum Ablauf' : 'More about the process'; ?>
            </a>
        </div>
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

<!-- FAQ Preview Section -->
<section class="section faq">
    <div class="container">
        <div class="section-header reveal">
            <h2><?php echo __('faq_preview_title'); ?></h2>
        </div>
        
        <div class="faq-list reveal">
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo __('faq_question_1'); ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo __('faq_answer_1'); ?></p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo __('faq_question_2'); ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo __('faq_answer_2'); ?></p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo __('faq_question_3'); ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo __('faq_answer_3'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-8 reveal">
            <a href="<?php echo BASE_URL; ?>faq.php" class="btn btn-secondary">
                <?php echo __('faq_preview_cta'); ?>
            </a>
        </div>
    </div>
</section>

<?php include PARTIALS_PATH . 'footer.php'; ?>
