<div class="application-modal" id="application-modal" role="dialog" aria-modal="true" aria-labelledby="application-modal-title" aria-hidden="true">
    <div class="application-modal-backdrop" data-close-application></div>
    <div class="application-modal-dialog">
        <button type="button" class="application-modal-close" aria-label="Close enquiry form" data-close-application>&times;</button>
        <h2 id="application-modal-title">Apply for a Course</h2>
        <p class="application-modal-intro">Share your details and our team will contact you.</p>
        <div class="application-modal-feedback" role="status" aria-live="polite" tabindex="-1" hidden></div>
        <form method="POST" action="<?= BASE_URL ?>/#contact" data-validate>
            <input type="hidden" name="modal_submit" value="1" />
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
            <input type="hidden" name="enquiry_request_key" value="<?= htmlspecialchars(bin2hex(random_bytes(16)), ENT_QUOTES, 'UTF-8') ?>" />
            <?php foreach (['page_url', 'landing_page', 'referrer', 'visitor_id', 'session_id', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $field): ?>
            <input type="hidden" name="<?= $field ?>" value="" />
            <?php endforeach; ?>
            <div class="form-group">
                <label for="apply-name" class="form-label">Full Name *</label>
                <input type="text" id="apply-name" name="name" class="form-input" autocomplete="name" required />
            </div>
            <div class="form-row application-modal-row">
                <div class="form-group">
                    <label for="apply-phone" class="form-label">Phone Number *</label>
                    <input type="tel" id="apply-phone" name="phone" class="form-input" autocomplete="tel" required />
                </div>
                <div class="form-group">
                    <label for="apply-email" class="form-label">Email Address *</label>
                    <input type="email" id="apply-email" name="email" class="form-input" autocomplete="email" required />
                </div>
            </div>
            <div class="form-group">
                <label for="apply-course" class="form-label">Course Interest *</label>
                <select id="apply-course" name="course" class="form-input form-select" required>
                    <option value="" selected disabled>Select a program...</option>
                    <?php foreach ($courses as $courseOption): ?>
                    <option value="<?= htmlspecialchars($courseOption['title'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($courseOption['title'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Other">Other / Not Sure Yet</option>
                </select>
            </div>
            <div class="form-group">
                <label for="apply-message" class="form-label">Message (Optional)</label>
                <textarea id="apply-message" name="message" class="form-input" rows="3"></textarea>
            </div>
            <div class="form-group enquiry-whatsapp-consent">
                <label>
                    <input type="checkbox" name="whatsapp_opt_in" value="1" required />
                    <span>Send my requested course information and enquiry follow-up to this phone number on WhatsApp.</span>
                </label>
            </div>
            <div class="form-group">
                <label for="apply-captcha" class="form-label">Security Verification *</label>
                <div class="application-captcha">
                    <input type="text" id="apply-captcha" name="captcha" class="form-input" placeholder="Enter code" inputmode="numeric" maxlength="4" required />
                    <strong aria-label="Security code"><?= htmlspecialchars((string) $_SESSION['captcha'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
            <button type="submit" name="contact_submit" class="btn btn-primary btn-lg btn-block">Submit Enquiry <i class="bi bi-arrow-right"></i></button>
        </form>
    </div>
</div>
