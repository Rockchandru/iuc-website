<section id="ai-course" class="py-section bg-light" aria-labelledby="ai-heading">
    <div class="container">
        <div style="display:grid;gap:2.5rem;align-items:center" class="ai-grid">
            <div data-aos="fade-right">
                <div class="section-label">Featured Program</div>
                <h2 id="ai-heading" class="text-display-lg section-title">
                    Artificial Intelligence &<br/>
                    <span class="gradient-text">Machine Learning</span>
                </h2>
                <p style="font-size:1rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.25rem">
                    Master the most in-demand skills of the decade. From Python fundamentals to advanced Deep Learning,
                    Generative AI, and prompt engineering — this program covers everything you need to become an AI professional.
                </p>

                <div style="display:grid;gap:0.5rem;margin-bottom:1.5rem" class="ai-highlights">
                    <?php $aiHighlights = [
                        ['bi bi-robot', 'Generative AI & Prompt Engineering'],
                        ['bi bi-diagram-3', 'Neural Networks & Deep Learning'],
                        ['bi bi-cloud', 'MLOps & Cloud Deployment'],
                        ['bi bi-briefcase', 'Placement Assistance'],
                        ['bi bi-patch-check', 'Industry Certification'],
                    ]; ?>
                    <?php foreach ($aiHighlights as $h): ?>
                    <div style="display:flex;align-items:center;gap:0.625rem;font-size:0.9375rem">
                        <i class="<?= $h[0] ?>" style="color:var(--clr-accent);font-size:1rem"></i>
                        <span><?= $h[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:0.75rem">
                    <a href="<?= BASE_URL ?>/course/ai-ml" class="btn btn-primary">View Full Curriculum <i class="bi bi-arrow-right"></i></a>
                    <a href="<?= BASE_URL ?>/download-syllabus.php?course=ai-ml" class="btn btn-outline"><i class="bi bi-download"></i> Download Syllabus</a>
                </div>
            </div>

            <div data-aos="fade-left" style="position:relative">
                <div style="border-radius:var(--radius-2xl);overflow:hidden;box-shadow:var(--shadow-lg)">
                    <img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=700&q=80" alt="Artificial intelligence and machine learning course at IUC Edu Chennai" style="width:100%;height:380px;object-fit:cover" loading="lazy" />
                </div>
                <div style="position:relative;background:var(--clr-white);padding:0.75rem 1rem;border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:1rem" data-aos="fade-up" data-aos-delay="300">
                    <div style="display:flex;gap:0.75rem">
                        <?php foreach (['Python', 'TensorFlow', 'PyTorch'] as $t): ?>
                        <span class="course-tag"><?= $t ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .ai-grid { grid-template-columns: 1fr; }
    @media (min-width: 768px) { .ai-grid { grid-template-columns: 1fr 1fr; } }
</style>
