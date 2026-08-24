<section id="placements" class="py-section bg-white" aria-labelledby="placement-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Placement Assistance</div>
            <h2 id="placement-heading" class="text-display-lg section-title">
                98% Placement <span class="gradient-text">Success Rate</span>
            </h2>
            <p class="section-subtitle">Our dedicated placement team works with you from day one until you land your dream job.</p>
        </div>

        <div style="display:grid;gap:1.25rem;margin-bottom:2.5rem" class="placement-grid">
            <?php $placement = [
                ['bi bi-file-earmark-text', 'Resume Preparation', 'Professional resume crafted by industry experts highlighting your skills and projects.'],
                ['bi bi-linkedin', 'LinkedIn Optimization', 'Profile optimization to attract recruiters and build your professional brand.'],
                ['bi bi-chat-dots', 'Mock Interviews', 'Technical, HR, and managerial rounds with detailed feedback and improvement plans.'],
                ['bi bi-laptop', 'Technical Interview', 'Live coding sessions, system design discussions, and problem-solving practice.'],
                ['bi bi-people', 'HR Interview', 'Communication skills, salary negotiation, and workplace readiness training.'],
                ['bi bi-building', 'Hiring Partners', 'Access to 300+ hiring partners including MNCs and product-based companies.'],
            ]; ?>
            <?php foreach ($placement as $i => $p): ?>
            <div style="padding:1.25rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);display:flex;gap:1rem;transition:all var(--transition-base)" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>" class="placement-item">
                <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:var(--clr-primary-light);color:var(--clr-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.125rem">
                    <i class="<?= $p[0] ?>"></i>
                </div>
                <div>
                    <h3 style="font-weight:600;font-size:0.9375rem;margin-bottom:0.25rem"><?= $p[1] ?></h3>
                    <p style="font-size:0.8125rem;color:var(--clr-text-secondary);line-height:1.5"><?= $p[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Placement Stats -->
        <div style="display:grid;gap:1.25rem;text-align:center" class="placement-stats-grid" data-aos="fade-up">
            <?php $placementStats = [
                ['98%', 'Placement Rate'],
                ['300+', 'Hiring Partners'],
                ['25k+', 'Graduates Placed'],
                ['140%', 'Avg. Salary Hike'],
            ]; ?>
            <?php foreach ($placementStats as $ps): ?>
            <div style="padding:1.5rem;border-radius:var(--radius-lg);background:var(--clr-bg-section)">
                <div style="font-family:var(--font-heading);font-size:1.75rem;font-weight:700;color:var(--clr-primary)"><?= $ps[0] ?></div>
                <div style="font-size:0.875rem;color:var(--clr-text-secondary)"><?= $ps[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .placement-grid { grid-template-columns: 1fr; }
    .placement-stats-grid { grid-template-columns: repeat(2, 1fr); }
    @media (min-width: 640px) {
        .placement-grid { grid-template-columns: repeat(2, 1fr); }
        .placement-stats-grid { grid-template-columns: repeat(4, 1fr); }
    }
    .placement-item:hover { border-color: var(--clr-primary); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
</style>
