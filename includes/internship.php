<section id="internship" class="py-section bg-light" aria-labelledby="internship-heading">
    <div class="container">
        <div style="display:grid;gap:2.5rem;align-items:center" class="internship-grid">
            <div data-aos="fade-right">
                <div class="section-label">Internship Program</div>
                <h2 id="internship-heading" class="text-display-lg section-title">
                    Kickstart Your Career<br/>
                    <span class="gradient-text">with Industry Internship</span>
                </h2>
                <p style="font-size:1rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.5rem">
                    Our 3-month internship program bridges the gap between academic learning and industry expectations.
                    Work on real client projects under expert mentorship and build a professional portfolio.
                </p>

                <div style="display:grid;gap:0.75rem;margin-bottom:1.5rem" class="internship-benefits">
                    <?php $benefits = [
                        ['bi bi-clock', '3 Months Duration', 'Full-time immersive experience'],
                        ['bi bi-patch-check', 'Internship Certificate', 'Recognized completion certificate'],
                        ['bi bi-folder', 'Real Projects', 'Work on live client projects'],
                        ['bi bi-person-circle', 'Expert Mentoring', '1-on-1 guidance from industry experts'],
                        ['bi bi-briefcase', 'Hiring Support', 'Direct referrals to partner companies'],
                        ['bi bi-cash-stack', 'Stipend Opportunity', 'Performance-based stipend for top interns'],
                    ]; ?>
                    <?php foreach ($benefits as $b): ?>
                    <div style="display:flex;gap:0.75rem;align-items:flex-start">
                        <div style="width:2rem;height:2rem;border-radius:var(--radius-md);background:var(--clr-accent-light);color:var(--clr-accent-dark);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.875rem">
                            <i class="<?= $b[0] ?>"></i>
                        </div>
                        <div>
                            <strong style="font-size:0.9375rem"><?= $b[1] ?></strong>
                            <p style="font-size:0.8125rem;color:var(--clr-text-secondary)"><?= $b[2] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary">Apply for Internship <i class="bi bi-arrow-right"></i></a>
            </div>

            <div data-aos="fade-left">
                <div style="border-radius:var(--radius-2xl);overflow:hidden;box-shadow:var(--shadow-lg)">
                    <img src="assets/images/internship program.jpg" alt="Students working on live projects in the IUC Edu internship program" width="1535" height="1024" style="width:100%;height:400px;object-fit:cover" loading="lazy" />
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .internship-grid { grid-template-columns: 1fr; }
    @media (min-width: 768px) { .internship-grid { grid-template-columns: 1fr 1fr; } }
</style>
