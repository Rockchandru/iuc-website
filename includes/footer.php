</main>

<!-- Footer -->
<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="IUC Edu computer education institute" class="footer-logo" width="836" height="450" style="margin-bottom:0.75rem" loading="lazy" />
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
                    <a href="<?= BASE_URL ?>/computer-training-in-chennai" class="footer-link">Computer Training in Chennai</a>
                    <a href="<?= BASE_URL ?>/programming-courses-in-chennai" class="footer-link">Programming Courses</a>
                    <a href="<?= BASE_URL ?>/it-courses-for-beginners" class="footer-link">Beginner IT Courses</a>
                    <a href="<?= BASE_URL ?>/online-it-courses" class="footer-link">Online IT Courses</a>
                    <a href="<?= BASE_URL ?>/blog" class="footer-link">Learning Blog</a>
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
                <span class="footer-link" style="margin-right:1rem">Privacy Policy</span>
                <span class="footer-link" style="margin-right:1rem">Terms of Service</span>
                <span class="footer-link">Refund Policy</span>
            </div>
        </div>
    </div>
</footer>

<?php require __DIR__ . '/application-modal.php'; ?>

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
    if (window.AOS) {
        AOS.init({
            duration: 600,
            easing: 'ease-out-quad',
            once: true,
            offset: 60,
        });
    } else {
        document.querySelectorAll('[data-aos]').forEach(function (element) {
            element.style.opacity = '1';
            element.style.transform = 'none';
        });
    }
</script>
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= @filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>

</body>
</html>
