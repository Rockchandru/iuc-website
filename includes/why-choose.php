<section id="why-choose" class="py-section bg-white" aria-labelledby="why-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Why IUC Edu</div>
            <h2 id="why-heading" class="text-display-lg section-title">
                Why Thousands Choose<br/><span class="gradient-text">IUC Edu</span>
            </h2>
            <p class="section-subtitle">We don't just teach — we build careers, transform lives, and deliver results that speak for themselves.</p>
        </div>

        <div style="display:grid;gap:1.25rem" class="why-grid">
            <?php foreach ($whyChoose as $i => $w): ?>
            <div class="feature-item" data-aos="fade-up" data-aos-delay="<?= ($i % 5) * 80 ?>">
                <div class="feature-icon"><i class="<?= $w['icon'] ?>"></i></div>
                <h3 class="feature-title"><?= $w['title'] ?></h3>
                <p class="feature-desc"><?= $w['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .why-grid { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .why-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .why-grid { grid-template-columns: repeat(3, 1fr); } }
</style>
