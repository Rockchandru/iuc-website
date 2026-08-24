<section id="technologies" class="py-section bg-light" aria-labelledby="tech-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Technologies Covered</div>
            <h2 id="tech-heading" class="text-display-lg section-title">
                Tools & <span class="gradient-text">Technologies</span>
            </h2>
            <p class="section-subtitle">Learn the latest tools and technologies used by top companies worldwide.</p>
        </div>

        <div style="display:grid;gap:1rem" class="tech-grid">
            <?php foreach ($technologies as $t): ?>
            <div class="company-logo-item" data-aos="fade-up" data-aos-delay="<?= array_search($t, $technologies) * 40 ?>">
                <i class="<?= $t['icon'] ?>" style="font-size:1.25rem;margin-right:0.5rem"></i>
                <?= $t['name'] ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .tech-grid { grid-template-columns: repeat(2, 1fr); }
    @media (min-width: 640px) { .tech-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 768px) { .tech-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 1024px) { .tech-grid { grid-template-columns: repeat(6, 1fr); } }
</style>
