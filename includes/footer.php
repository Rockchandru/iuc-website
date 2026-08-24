</main>

<!-- Footer -->
<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>" class="footer-logo" style="margin-bottom:0.75rem" />
                <p>Premier IT training institute in Chennai delivering world-class technology education with 98% placement support. 25,000+ careers launched and counting.</p>
                <div class="footer-social">
                    <a href="<?= FACEBOOK_URL ?>" aria-label="Facebook" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
                    <a href="<?= INSTAGRAM_URL ?>" aria-label="Instagram" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
                    <a href="<?= LINKEDIN_URL ?>" aria-label="LinkedIn" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i></a>
                    <a href="<?= YOUTUBE_URL ?>" aria-label="YouTube" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="footer-heading">Quick Links</h4>
                <div class="footer-links">
                    <?php foreach ($navLinks as $link): ?>
                    <a href="<?= BASE_URL ?>/#<?= $link[1] ?>" class="footer-link"><?= $link[0] ?></a>
                    <?php endforeach; ?>
                    <a href="<?= BASE_URL ?>/sitemap.xml" class="footer-link">Sitemap</a>
                </div>
            </div>

            <!-- Popular Courses -->
            <div>
                <h4 class="footer-heading">Top Courses</h4>
                <div class="footer-links">
                    <?php $topCourses = array_slice($courses, 0, 6); ?>
                    <?php foreach ($topCourses as $courseSlug => $courseEntry): ?>
                    <a href="<?= BASE_URL ?>/course/<?= $courseSlug ?>" class="footer-link"><?= $courseEntry['short_title'] ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="footer-heading">Contact Us</h4>
                <div class="footer-links" style="gap:0.75rem">
                    <a href="tel:+91<?= SITE_PHONE ?>" class="footer-link">
                        <i class="bi bi-telephone me-1"></i> +91 <?= SITE_PHONE ?>
                    </a>
                    <a href="tel:<?= HEAD_OFFICE_LANDLINE ?>" class="footer-link">
                        <i class="bi bi-telephone-outbound me-1"></i> <?= HEAD_OFFICE_LANDLINE ?>
                    </a>
                    <a href="tel:<?= BRANCH_LANDLINE ?>" class="footer-link">
                        <i class="bi bi-telephone-outbound me-1"></i> <?= BRANCH_LANDLINE ?>
                    </a>
                    <a href="mailto:<?= SITE_EMAIL ?>" class="footer-link">
                        <i class="bi bi-envelope me-1"></i> <?= SITE_EMAIL ?>
                    </a>
                    <span class="footer-link" style="cursor:default;line-height:1.5">
                        <i class="bi bi-geo-alt me-1"></i> <?= SITE_ADDRESS ?>
                    </span>
                    <span class="footer-link" style="cursor:default;line-height:1.5">
                        <i class="bi bi-geo-alt me-1"></i> <?= SITE_BRANCH ?>
                    </span>

                    <!-- Newsletter -->
                    <div style="margin-top:0.75rem">
                        <h4 class="footer-heading" style="margin-bottom:0.75rem">Newsletter</h4>
                        <form class="newsletter-form" data-validate>
                            <input type="email" placeholder="Your email" required aria-label="Email for newsletter" />
                            <button type="submit"><i class="bi bi-send"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</span>
            <div>
                <a href="#" class="footer-link" style="margin-right:1rem">Privacy Policy</a>
                <a href="#" class="footer-link" style="margin-right:1rem">Terms of Service</a>
                <a href="#" class="footer-link">Refund Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp Float -->
<a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hi!%20I'm%20interested%20in%20IUC%20Edu%20programs.%20Please%20guide%20me." class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <span class="whatsapp-tooltip">Chat with us on WhatsApp</span>
    <i class="bi bi-whatsapp"></i>
</a>

<!-- Back to Top -->
<a href="#" class="back-to-top" aria-label="Back to top">
    <i class="bi bi-chevron-up"></i>
</a>

<!-- Scripts -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 600,
        easing: 'ease-out-quad',
        once: true,
        offset: 60,
    });
</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

</body>
</html>
