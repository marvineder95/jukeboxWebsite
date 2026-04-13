<?php
/**
 * Kontaktseite mit Formular
 */
require_once 'config/config.php';

setSecurityHeaders();

$page = 'contact';

// Formular-Verarbeitung
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate-Limiting prüfen
    if (isFormRateLimited('contact', 3, 300)) {
        $formError = 'form_error_send';
    } else {
        // Honeypot-Prüfung (Spam-Schutz)
        if (!empty($_POST['website'])) {
            // Honeypot ausgefüllt = Spam
            $formError = 'Spam erkannt.';
        } else {
            // CSRF-Token prüfen
            if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $formError = 'Sicherheitsfehler. Bitte laden Sie die Seite neu.';
            } else {
                // Daten sammeln und validieren
                $firstname = sanitizeInput($_POST['firstname'] ?? '');
                $lastname = sanitizeInput($_POST['lastname'] ?? '');
                $name = trim($firstname . ' ' . $lastname);
                $company = sanitizeInput($_POST['company'] ?? '');
                $email = sanitizeInput($_POST['email'] ?? '');
                $phone = sanitizePhone($_POST['phone'] ?? '');
                $country = sanitizeInput($_POST['country'] ?? '');
                $location = sanitizeInput($_POST['location'] ?? '');
                $date = sanitizeInput($_POST['date'] ?? '');
                $date_start = sanitizeInput($_POST['date_start'] ?? '');
                $date_end = sanitizeInput($_POST['date_end'] ?? '');
                // Fallback wenn JavaScript nicht aktiviert war
                if (empty($date_start) && !empty($date)) {
                    $dateParts = explode(' - ', $date);
                    $date_start = $dateParts[0] ?? $date;
                    $date_end = $dateParts[1] ?? '';
                }
                $duration = sanitizeInput($_POST['duration'] ?? '');
                $message = sanitizeInput($_POST['message'] ?? '');
                $privacy = isset($_POST['privacy']) ? true : false;
                $jukeboxes = isset($_POST['jukeboxes']) && is_array($_POST['jukeboxes']) ? $_POST['jukeboxes'] : [];
                
                // Validierung
                $errors = [];
                
                if (empty($firstname)) $errors[] = 'firstname';
                if (empty($lastname)) $errors[] = 'lastname';
                if (empty($email) || !isValidEmail($email)) $errors[] = 'email';
                if (empty($phone)) $errors[] = 'phone';
                if (empty($country)) $errors[] = 'country';
                if (empty($location)) $errors[] = 'location';
                if (empty($date) && empty($date_start)) $errors[] = 'date';
                if (empty($duration)) $errors[] = 'duration';
                if (!$privacy) $errors[] = 'privacy';
                
                // E-Mail auf Header-Injection prüfen
                if (preg_match('/[\r\n]/', $email)) {
                    $errors[] = 'email';
                }
                
                if (empty($errors)) {
                    // E-Mail erstellen
                    $subject = MAIL_SUBJECT_PREFIX . 'Neue Anfrage von ' . $name;
                    
                    // Jukebox-Namen laden
                    $jukeboxNames = [];
                    foreach ($jukeboxes as $jbId) {
                        $jbId = sanitizeInput($jbId);
                        $jb = getJukeboxById($jbId);
                        if ($jb) {
                            $jukeboxNames[] = getLocalizedValue($jb, 'name');
                        }
                    }
                    
                    // E-Mail-Body
                    $body = "Neue Jukebox-Anfrage\n\n";
                    $body .= "Name: $name\n";
                    if ($company) $body .= "Firma: $company\n";
                    $body .= "E-Mail: $email\n";
                    $body .= "Telefon: $phone\n";
                    $countryName = $country === 'other' 
                        ? (getCurrentLanguage() === 'de' ? 'Anderes Land' : 'Other country')
                        : (DELIVERY_COUNTRIES_NAMES[$country] ?? $country);
                    $body .= "Land: $countryName\n";
                    $body .= "Veranstaltungsort: $location\n";
                    if (!empty($date_end) && $date_start !== $date_end) {
                        $body .= "Zeitraum: $date_start bis $date_end\n";
                    } else {
                        $body .= "Datum: $date_start\n";
                    }
                    $body .= "Mietdauer: $duration\n";
                    
                    if (!empty($jukeboxNames)) {
                        $body .= "\nGewählte Jukeboxen:\n";
                        foreach ($jukeboxNames as $jbName) {
                            $body .= "- $jbName\n";
                        }
                    }
                    
                    if ($message) {
                        $body .= "\nNachricht:\n$message\n";
                    }
                    
                    $body .= "\n---\n";
                    $body .= "Gesendet am: " . date('d.m.Y H:i') . "\n";
                    $body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
                    
                    // Header sicher erstellen
                    $headers = "From: " . MAIL_SENDER . "\r\n";
                    $headers .= "Reply-To: " . $email . "\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    
                    // E-Mail senden
                    if (mail(MAIL_RECIPIENT, $subject, $body, $headers)) {
                        $formSuccess = true;
                        // Anfrageliste leeren
                        clearInquiryList();
                        recordFormSubmission('contact');
                    } else {
                        $formError = 'form_error_send';
                    }
                } else {
                    $formError = 'form_error_message';
                }
            }
        }
    }
}

// Vorausgewählte Jukeboxen (aus URL oder Cookie)
$preselectedJukeboxes = [];
if (isset($_GET['jukebox'])) {
    $preselectedJukeboxes[] = sanitizeInput($_GET['jukebox']);
} else {
    $preselectedJukeboxes = getInquiryList();
}

$metaData = [
    'url' => BASE_URL . 'contact.php'
];

include PARTIALS_PATH . 'header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?php echo __('contact_title'); ?></h1>
        <p><?php echo __('contact_subtitle'); ?></p>
    </div>
</section>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="reveal">
                <div class="contact-info-card">
                    <h3 style="margin-bottom: var(--space-6);"><?php echo __('contact_info_title'); ?></h3>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">📍</div>
                        <div>
                            <h4><?php echo __('imprint_address'); ?></h4>
                            <p><?php echo COMPANY_STREET; ?><br>
                            <?php echo COMPANY_ZIP; ?> <?php echo COMPANY_CITY; ?><br>
                            <?php echo COMPANY_COUNTRY; ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">📞</div>
                        <div>
                            <h4>Telefon</h4>
                            <p><a href="tel:<?php echo preg_replace('/[^0-9+]/', '', COMPANY_PHONE); ?>"><?php echo COMPANY_PHONE; ?></a></p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <div class="contact-info-icon">✉️</div>
                        <div>
                            <h4>E-Mail</h4>
                            <p><a href="mailto:<?php echo COMPANY_EMAIL; ?>"><?php echo COMPANY_EMAIL; ?></a></p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item" style="margin-top: var(--space-4);">
                        <div class="contact-info-icon" style="background: var(--color-primary); color: var(--color-dark);">🌐</div>
                        <div>
                            <h4>Social Media</h4>
                            <div class="contact-social">
                                <a href="https://instagram.com" target="_blank" rel="noopener" title="Instagram">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a href="https://facebook.com" target="_blank" rel="noopener" title="Facebook">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a href="https://linkedin.com" target="_blank" rel="noopener" title="LinkedIn">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transport Notice -->
                <div style="margin-top: var(--space-6); padding: var(--space-6); background: var(--color-dark-lighter); border-radius: var(--radius-lg); border: 1px solid var(--color-gray-700);">
                    <h4 style="margin-bottom: var(--space-3); color: var(--color-primary);">
                        <?php echo __('transport_notice_title'); ?>
                    </h4>
                    <p style="font-size: var(--text-sm); margin-bottom: 0;">
                        <?php echo __('transport_notice_text'); ?>
                    </p>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="reveal">
                <div class="contact-form">
                    <h3 style="margin-bottom: var(--space-2);"><?php echo __('contact_form_title'); ?></h3>
                    <p style="color: var(--color-gray-500); margin-bottom: var(--space-6);">
                        <?php echo __('contact_form_intro'); ?>
                    </p>
                    
                    <?php if ($formSuccess): ?>
                    <div class="form-success">
                        <h3><?php echo __('form_success_title'); ?></h3>
                        <p><?php echo __('form_success_message'); ?></p>
                    </div>
                    <?php else: ?>
                    
                    <?php if ($formError): ?>
                    <div style="padding: var(--space-4); background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); margin-bottom: var(--space-6);">
                        <p style="color: #ef4444; margin-bottom: 0;"><?php echo __($formError); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" data-validate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <!-- Honeypot-Feld (unsichtbar) -->
                        <div style="position: absolute; left: -9999px;">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        
                        <!-- Selected Jukeboxes -->
                        <?php if (!empty($preselectedJukeboxes)): ?>
                        <div class="form-group">
                            <label class="form-label"><?php echo __('contact_selected_jukeboxes'); ?></label>
                            <div class="selected-jukeboxes">
                                <?php foreach ($preselectedJukeboxes as $jbId): 
                                    $jbId = sanitizeInput($jbId);
                                    $jb = getJukeboxById($jbId);
                                    if ($jb):
                                ?>
                                <span class="selected-jukebox-tag">
                                    <?php echo e(getLocalizedValue($jb, 'name')); ?>
                                    <input type="hidden" name="jukeboxes[]" value="<?php echo e($jbId); ?>">
                                </span>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label class="form-label"><?php echo __('contact_selected_jukeboxes'); ?></label>
                            <p style="color: var(--color-gray-500); font-size: var(--text-sm);">
                                <?php echo __('contact_no_selection'); ?> 
                                <a href="<?php echo BASE_URL; ?>catalog.php"><?php echo __('contact_browse_catalog'); ?></a>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstname" class="form-label"><?php echo __('form_firstname'); ?> *</label>
                                <input type="text" id="firstname" name="firstname" class="form-input" placeholder="<?php echo __('form_firstname_placeholder'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="form-label"><?php echo __('form_lastname'); ?> *</label>
                                <input type="text" id="lastname" name="lastname" class="form-input" placeholder="<?php echo __('form_lastname_placeholder'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="company" class="form-label"><?php echo __('form_company'); ?></label>
                            <input type="text" id="company" name="company" class="form-input" placeholder="<?php echo __('form_company_placeholder'); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label"><?php echo __('form_email'); ?></label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="<?php echo __('form_email_placeholder'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label"><?php echo __('form_phone'); ?></label>
                                <input type="tel" id="phone" name="phone" class="form-input" placeholder="<?php echo __('form_phone_placeholder'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="country" class="form-label"><?php echo getCurrentLanguage() === 'de' ? 'Land' : 'Country'; ?> *</label>
                                <select id="country" name="country" class="form-select" required>
                                    <option value=""><?php echo getCurrentLanguage() === 'de' ? 'Land auswählen' : 'Select country'; ?></option>
                                    <option value="AT"><?php echo DELIVERY_COUNTRIES_NAMES['AT']; ?></option>
                                    <option value="IT"><?php echo DELIVERY_COUNTRIES_NAMES['IT']; ?></option>
                                    <option value="DE"><?php echo DELIVERY_COUNTRIES_NAMES['DE']; ?></option>
                                    <option value="CH"><?php echo DELIVERY_COUNTRIES_NAMES['CH']; ?></option>
                                    <option value="other"><?php echo getCurrentLanguage() === 'de' ? 'Anderes Land (auf Anfrage)' : 'Other country (on request)'; ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location" class="form-label"><?php echo __('form_location'); ?></label>
                                <input type="text" id="location" name="location" class="form-input" placeholder="<?php echo __('form_location_placeholder'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date" class="form-label"><?php echo __('form_date'); ?></label>
                            <div class="date-input-wrapper">
                                <input type="text" id="date" name="date" class="form-input" placeholder="<?php echo __('form_date_placeholder'); ?>" required>
                                <button type="button" class="calendar-icon-btn" id="calendar-trigger" aria-label="Kalender öffnen">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </button>
                            </div>
                            <input type="hidden" id="date_start" name="date_start">
                            <input type="hidden" id="date_end" name="date_end">
                            <p style="font-size: var(--text-xs); color: var(--color-gray-500); margin-top: var(--space-2); margin-bottom: 0;">
                                <?php echo getCurrentLanguage() === 'de' ? 'Wählen Sie einen oder mehrere Tage aus' : 'Select one or more days'; ?>
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration" class="form-label"><?php echo __('form_duration'); ?></label>
                            <input type="text" id="duration" name="duration" class="form-input" placeholder="<?php echo __('form_duration_placeholder'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label"><?php echo __('form_message'); ?></label>
                            <textarea id="message" name="message" class="form-textarea" placeholder="<?php echo __('form_message_placeholder'); ?>" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="privacy" required>
                                <span><?php echo __('form_privacy'); ?></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-full">
                            <?php echo __('form_submit'); ?>
                        </button>
                        
                        <p style="text-align: center; color: var(--color-gray-500); font-size: var(--text-xs); margin-top: var(--space-4);">
                            <?php echo __('form_required'); ?>
                        </p>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flatpickr CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/flatpickr-custom.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Flatpickr initialisieren
    var lang = '<?php echo e(getCurrentLanguage()); ?>';
    
    var datePicker = flatpickr('#date', {
        clickOpens: false,
        mode: 'range',
        minDate: 'today',
        dateFormat: 'd.m.Y',
        locale: lang === 'de' ? 'de' : 'en',
        allowInput: true,
        placeholder: lang === 'de' ? 'TT.MM.JJJJ - TT.MM.JJJJ' : 'DD.MM.YYYY - DD.MM.YYYY',
        onChange: function(selectedDates, dateStr, instance) {
            // Versteckte Felder mit Start- und Enddatum füllen
            if (selectedDates.length === 2) {
                var start = selectedDates[0];
                var end = selectedDates[1];
                
                // Formatieren für versteckte Felder
                var formatDate = function(date) {
                    var d = date.getDate().toString().padStart(2, '0');
                    var m = (date.getMonth() + 1).toString().padStart(2, '0');
                    var y = date.getFullYear();
                    return d + '.' + m + '.' + y;
                };
                
                document.getElementById('date_start').value = formatDate(start);
                document.getElementById('date_end').value = formatDate(end);
                
                // Dauer automatisch berechnen
                var diffTime = Math.abs(end - start);
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                var durationField = document.getElementById('duration');
                if (diffDays === 1) {
                    durationField.value = lang === 'de' ? '1 Tag' : '1 day';
                } else {
                    durationField.value = diffDays + (lang === 'de' ? ' Tage' : ' days');
                }
            } else if (selectedDates.length === 1) {
                document.getElementById('date_start').value = formatDate(selectedDates[0]);
                document.getElementById('date_end').value = '';
            }
        }
    });
    
    // Kalender Icon Button öffnet Datepicker
    document.getElementById('calendar-trigger').addEventListener('click', function() {
        datePicker.open();
    });
    
    // Formatieren Funktion global machen
    window.formatDate = function(date) {
        var d = date.getDate().toString().padStart(2, '0');
        var m = (date.getMonth() + 1).toString().padStart(2, '0');
        var y = date.getFullYear();
        return d + '.' + m + '.' + y;
    };
});
</script>

<?php include PARTIALS_PATH . 'footer.php'; ?>
