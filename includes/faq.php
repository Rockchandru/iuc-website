<section id="faq" class="py-section bg-light" aria-labelledby="faq-heading">
    <div class="container-narrow">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">FAQ</div>
            <h2 id="faq-heading" class="text-display-lg section-title">
                Frequently Asked <span class="gradient-text">Questions</span>
            </h2>
            <p class="section-subtitle">Everything you need to know about IUC Edu programs.</p>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;margin-bottom:1.5rem" data-aos="fade-up">
            <?php $faqCategories = [
                'All' => 'faq',
                'Admissions' => 'admissions',
                'Fees' => 'courses',
                'Placements' => 'placements',
                'Internship' => 'internship',
                'Certificate' => 'certificates',
                'Eligibility' => 'courses',
                'Batches' => 'batches',
            ]; ?>
            <?php foreach ($faqCategories as $fc => $target): ?>
            <a href="<?= BASE_URL ?>/#<?= $target ?>" class="btn btn-sm btn-ghost"><?= $fc ?></a>
            <?php endforeach; ?>
        </div>

        <div data-aos="fade-up">
            <?php foreach ($faqs as $i => $faq): ?>
            <div class="accordion-item">
                <details <?= $i === 0 ? 'open' : '' ?>>
                    <summary>
                        <span style="display:flex;align-items:center;gap:0.75rem">
                            <span style="width:1.5rem;height:1.5rem;border-radius:var(--radius-sm);background:var(--clr-primary-light);color:var(--clr-primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;flex-shrink:0"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                            <?= $faq['q'] ?>
                        </span>
                        <span class="accordion-icon"><i class="bi bi-plus"></i></span>
                    </summary>
                    <div class="accordion-body">
                        <?= $faq['a'] ?>
                    </div>
                </details>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:2rem" data-aos="fade-up">
            <p style="font-size:0.9375rem;color:var(--clr-text-secondary);margin-bottom:1rem">Still have questions? We're here to help.</p>
            <div style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:center">
                <a href="tel:+91<?= SITE_PHONE ?>" class="btn btn-outline"><i class="bi bi-telephone"></i> Call Us</a>
                <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" rel="noopener" class="btn btn-accent"><i class="bi bi-whatsapp"></i> WhatsApp Us</a>
                <a href="/#contact" class="btn btn-primary">Contact Us <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
