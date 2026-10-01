<section id="courses" class="py-section bg-white" aria-labelledby="courses-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Our Programs</div>
            <h2 id="courses-heading" class="text-display-lg section-title">
                Popular <span class="gradient-text">Courses</span>
            </h2>
            <p class="section-subtitle">Industry-aligned courses designed with top employers to give you the exact skills that land high-paying jobs.</p>
        </div>

        <!-- Category Filters -->
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;margin-bottom:2rem" data-aos="fade-up">
            <button class="btn btn-sm" style="background:var(--clr-primary);color:#fff;border:none" data-filter="all" aria-selected="true">All Programs</button>
            <?php foreach ($courseCategories as $cat): ?>
            <button class="btn btn-sm btn-ghost" data-filter="<?= $cat['id'] ?>" aria-selected="false"><?= $cat['name'] ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Course Grid -->
        <div style="display:grid;gap:1.5rem" class="courses-grid">
            <?php $idx = 0; foreach ($courses as $slug => $course): $idx++; ?>
            <article class="card course-card" data-filter-item="<?= $course['category'] ?>" data-course-url="<?= BASE_URL ?>/course/<?= rawurlencode($slug) ?>" tabindex="0" role="link" aria-label="View <?= htmlspecialchars($course['short_title'], ENT_QUOTES, 'UTF-8') ?> course" data-aos="fade-up" data-aos-delay="<?= ($idx % 6) * 80 ?>">
                <div class="course-card-image">
                    <img src="<?= $course['image'] ?>" alt="<?= $course['title'] ?>" loading="lazy" />
                    <span class="course-card-badge" style="background:<?= $course['badge_color'] ?>"><?= $course['badge'] ?></span>
                </div>
                <div class="course-card-body">
                    <h3 style="font-size:1rem;font-weight:600;margin-bottom:0.25rem"><?= $course['short_title'] ?></h3>
                    <p style="font-size:0.8125rem;color:var(--clr-text-secondary);line-height:1.5;margin-bottom:0.5rem">
                        <?= excerpt($course['short_desc'], 90) ?>
                    </p>
                    <div class="course-meta">
                        <span><i class="bi bi-clock"></i> <?= $course['duration'] ?></span>
                        <span><i class="bi bi-laptop"></i> <?= explode('/', $course['mode'])[0] ?></span>
                        <span><i class="bi bi-bar-chart"></i> <?= $course['level'] ?></span>
                    </div>
                    <div class="course-tags">
                        <?php foreach (array_slice($course['technologies'], 0, 4) as $tech): ?>
                        <span class="course-tag"><?= $tech ?></span>
                        <?php endforeach; ?>
                        <?php if (count($course['technologies']) > 4): ?>
                        <span class="course-tag">+<?= count($course['technologies']) - 4 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="course-card-footer">
                        <div class="course-price-row">
                            <span class="course-price"><?= $course['price'] ?></span>
                            <span class="course-emi"><i class="bi bi-shield-check"></i> No-Cost EMI · <?= $course['emi'] ?></span>
                        </div>
                        <div class="course-actions">
                            <a href="<?= BASE_URL ?>/course/<?= $slug ?>" class="btn btn-sm btn-ghost">Learn More</a>
                            <a href="<?= BASE_URL ?>/#contact" class="btn btn-sm btn-primary apply-now-trigger" data-course="<?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8') ?>">Apply Now</a>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .courses-grid { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .courses-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .courses-grid { grid-template-columns: repeat(3, 1fr); } }
</style>
