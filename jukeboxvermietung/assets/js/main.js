/**
 * Jukeboxvermietung - Haupt-JavaScript
 * Interaktivität, Animationen, Anfrageliste
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisierung
    initHeader();
    initMobileMenu();
    initInquiryList();
    initFAQ();
    initScrollAnimations();
    initCookieNotice();
    initSmoothScroll();
    initGallery();
});

// ============================================
// HEADER SCROLL-EFFEKT
// ============================================
function initHeader() {
    const header = document.querySelector('.header');
    if (!header) return;
    
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        // Header-Hintergrund bei Scroll
        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    }, { passive: true });
}

// ============================================
// MOBILES MENÜ
// ============================================
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMobile = document.querySelector('.nav-mobile');
    
    if (!menuToggle || !navMobile) return;
    
    menuToggle.addEventListener('click', function() {
        this.classList.toggle('active');
        navMobile.classList.toggle('active');
        document.body.style.overflow = navMobile.classList.contains('active') ? 'hidden' : '';
    });
    
    // Menü schließen bei Klick auf Link
    const mobileLinks = navMobile.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
            menuToggle.classList.remove('active');
            navMobile.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
}

// ============================================
// ANFRAGELISTE (WARENKORB)
// ============================================
function initInquiryList() {
    updateInquiryBadge();
    initInquiryButtons();
    initInquirySidebar();
}

// Anfrageliste aus Cookie laden
function getInquiryList() {
    const cookie = document.cookie.split('; ').find(row => row.startsWith('jukebox_inquiry='));
    if (cookie) {
        try {
            return JSON.parse(decodeURIComponent(cookie.split('=')[1]));
        } catch (e) {
            return [];
        }
    }
    return [];
}

// Anfrageliste speichern
function saveInquiryList(list) {
    const json = JSON.stringify(list);
    const expires = new Date();
    expires.setDate(expires.getDate() + 30);
    document.cookie = `jukebox_inquiry=${encodeURIComponent(json)}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
}

// Zur Anfrageliste hinzufügen
function addToInquiryList(jukeboxId) {
    const list = getInquiryList();
    if (!list.includes(jukeboxId)) {
        list.push(jukeboxId);
        saveInquiryList(list);
        updateInquiryBadge();
        showNotification('Zur Anfrageliste hinzugefügt');
        return true;
    }
    showNotification('Bereits in der Anfrageliste', 'info');
    return false;
}

// Aus Anfrageliste entfernen
function removeFromInquiryList(jukeboxId) {
    let list = getInquiryList();
    list = list.filter(id => id !== jukeboxId);
    saveInquiryList(list);
    updateInquiryBadge();
    updateInquirySidebar();
    
    // Button-Status aktualisieren
    const button = document.querySelector(`[data-jukebox-id="${jukeboxId}"].inquiry-btn`);
    if (button) {
        updateInquiryButton(button, false);
    }
}

// Badge aktualisieren
function updateInquiryBadge() {
    const list = getInquiryList();
    const badges = document.querySelectorAll('.inquiry-badge-count');
    
    badges.forEach(badge => {
        badge.textContent = list.length;
        badge.style.display = list.length > 0 ? 'flex' : 'none';
    });
}

// Anfrage-Buttons initialisieren
function initInquiryButtons() {
    const buttons = document.querySelectorAll('.inquiry-btn');
    const list = getInquiryList();
    
    buttons.forEach(button => {
        const jukeboxId = button.dataset.jukeboxId;
        const isInList = list.includes(jukeboxId);
        
        updateInquiryButton(button, isInList);
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.classList.contains('in-list')) {
                removeFromInquiryList(jukeboxId);
                updateInquiryButton(this, false);
            } else {
                addToInquiryList(jukeboxId);
                updateInquiryButton(this, true);
            }
        });
    });
}

// Button-Status aktualisieren
function updateInquiryButton(button, inList) {
    if (inList) {
        button.classList.add('in-list');
        button.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
            <span>${button.dataset.textRemove || 'Entfernen'}</span>
        `;
    } else {
        button.classList.remove('in-list');
        button.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span>${button.dataset.textAdd || 'Zur Anfrage'}</span>
        `;
    }
}

// Anfrage-Sidebar
function initInquirySidebar() {
    const toggle = document.querySelector('.inquiry-badge');
    const sidebar = document.querySelector('.inquiry-sidebar');
    const overlay = document.querySelector('.inquiry-sidebar-overlay');
    const closeBtn = document.querySelector('.inquiry-sidebar-close');
    
    if (!toggle || !sidebar) return;
    
    function open() {
        sidebar.classList.add('active');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateInquirySidebar();
    }
    
    function close() {
        sidebar.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    toggle.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    if (overlay) overlay.addEventListener('click', close);
}

// Sidebar-Inhalt aktualisieren
function updateInquirySidebar() {
    const container = document.querySelector('.inquiry-sidebar-body');
    if (!container) return;
    
    const list = getInquiryList();
    
    if (list.length === 0) {
        container.innerHTML = `
            <div class="inquiry-empty">
                <div class="inquiry-empty-icon">🎵</div>
                <p>Ihre Anfrageliste ist leer.</p>
                <a href="catalog.php" class="btn btn-primary mt-4">Jukeboxen entdecken</a>
            </div>
        `;
        return;
    }
    
    // Jukebox-Daten vom Server laden
    fetch(`includes/ajax.php?action=getInquiryItems&ids=${list.join(',')}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.items.map(item => `
                    <div class="inquiry-item">
                        <div class="inquiry-item-image">
                            <img src="${item.image}" alt="${item.name}">
                        </div>
                        <div class="inquiry-item-info">
                            <div class="inquiry-item-title">${item.name}</div>
                            <div class="inquiry-item-price">${item.price}</div>
                        </div>
                        <button class="inquiry-item-remove" onclick="removeFromInquiryList('${item.id}')" title="Entfernen">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                `).join('');
            }
        })
        .catch(() => {
            container.innerHTML = '<p class="text-center">Fehler beim Laden der Daten.</p>';
        });
}

// ============================================
// FAQ AKKORDEON
// ============================================
function initFAQ() {
    const items = document.querySelectorAll('.faq-item');
    
    items.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');
            
            // Alle schließen
            items.forEach(i => i.classList.remove('active'));
            
            // Aktuelles öffnen wenn es geschlossen war
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

// ============================================
// SCROLL-ANIMATIONEN
// ============================================
function initScrollAnimations() {
    const reveals = document.querySelectorAll('.reveal');
    
    if (reveals.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    reveals.forEach(el => observer.observe(el));
}

// ============================================
// COOKIE-HINWEIS
// ============================================
function initCookieNotice() {
    const notice = document.querySelector('.cookie-notice');
    if (!notice) return;
    
    // Prüfen ob bereits akzeptiert
    if (document.cookie.includes('cookies_accepted=true')) {
        return;
    }
    
    // Anzeigen
    setTimeout(() => {
        notice.classList.add('active');
    }, 1000);
    
    // Akzeptieren-Button
    const acceptBtn = notice.querySelector('.cookie-accept');
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            const expires = new Date();
            expires.setFullYear(expires.getFullYear() + 1);
            document.cookie = `cookies_accepted=true; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
            notice.classList.remove('active');
        });
    }
}

// ============================================
// SMOOTH SCROLL
// ============================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ============================================
// BILDGALERIE
// ============================================
function initGallery() {
    const mainImage = document.querySelector('.detail-main-image img');
    const thumbnails = document.querySelectorAll('.detail-thumbnail');
    
    if (!mainImage || thumbnails.length === 0) return;
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const img = this.querySelector('img');
            mainImage.src = img.src;
            mainImage.alt = img.alt;
            
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// ============================================
// NOTIFICATIONS
// ============================================
function showNotification(message, type = 'success') {
    // Bestehende Notification entfernen
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    // Neue erstellen
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
    `;
    
    // Styles
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: ${type === 'success' ? '#22c55e' : '#3b82f6'};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Animation
    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(-50%) translateY(0)';
    });
    
    // Entfernen
    setTimeout(() => {
        notification.style.transform = 'translateX(-50%) translateY(100px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ============================================
// FORMULAR-VALIDIERUNG
// ============================================
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        const formGroup = field.closest('.form-group');
        
        // Bestehende Fehler entfernen
        const existingError = formGroup?.querySelector('.form-error');
        if (existingError) existingError.remove();
        field.classList.remove('error');
        
        if (!value) {
            isValid = false;
            field.classList.add('error');
            
            if (formGroup) {
                const error = document.createElement('span');
                error.className = 'form-error';
                error.textContent = 'Dieses Feld ist erforderlich.';
                formGroup.appendChild(error);
            }
        }
        
        // E-Mail-Validierung
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                field.classList.add('error');
                
                if (formGroup) {
                    const error = document.createElement('span');
                    error.className = 'form-error';
                    error.textContent = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
                    formGroup.appendChild(error);
                }
            }
        }
    });
    
    return isValid;
}

// Formulare initialisieren
document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });
});

// ============================================
// LAZY LOADING FÜR BILDER
// ============================================
if ('IntersectionObserver' in window) {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}
