<section id="companies" class="py-section-sm bg-white" aria-labelledby="companies-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Hiring Companies</div>
            <h2 id="companies-heading" class="text-display-md section-title">
                Our Graduates Work at <span class="gradient-text">Top Companies</span>
            </h2>
        </div>

        <div style="display:grid;gap:0.75rem" class="companies-grid" data-aos="fade-up">
            <?php foreach ($companies as $c): ?>
            <div class="company-logo-item">
                <?= $c ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .companies-grid { grid-template-columns: repeat(3, 1fr); }
    @media (min-width: 640px) { .companies-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 768px) { .companies-grid { grid-template-columns: repeat(5, 1fr); } }
    @media (min-width: 1024px) { .companies-grid { grid-template-columns: repeat(6, 1fr); } }
</style>
