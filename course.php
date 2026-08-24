<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Course Detail Page
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', $_GET['slug']) : '';
$course = getCourseBySlug($slug);

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Course Not Found – IUC Edu';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center"><h1>Course Not Found</h1><p style="color:var(--clr-text-secondary);margin:1rem 0">The course you\'re looking for doesn\'t exist.</p><a href="<?= BASE_URL ?>/" class="btn btn-primary">Back to Home</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $course['title'] . ' – IUC Edu';
$metaDesc = $course['description'];
$ogTitle = $course['title'] . ' – IUC Edu';
$ogDesc = $course['short_desc'];
$ogImage = $course['image'];
$canonical = 'course/' . $slug;

require __DIR__ . '/includes/header.php';
?>

<!-- Course Hero -->
<section class="course-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">Home</a>
            <span class="breadcrumb-sep">/</span>
            <a href="<?= BASE_URL ?>/#courses">Courses</a>
            <span class="breadcrumb-sep">/</span>
            <span><?= $course['title'] ?></span>
        </nav>

        <div class="course-hero-grid">
            <div class="course-hero-content" data-aos="fade-right">
                <div class="section-label"><?= $course['category'] ?></div>
                <h1 class="text-display-lg" style="margin-bottom:0.75rem"><?= $course['title'] ?></h1>
                <p style="font-size:1.0625rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.25rem">
                    <?= $course['description'] ?>
                </p>

                <div class="course-info-grid">
                    <div class="course-info-item">
                        <div class="course-info-value"><i class="bi bi-clock" style="color:var(--clr-primary)"></i> <?= $course['duration'] ?></div>
                        <div class="course-info-label">Duration</div>
                    </div>
                    <div class="course-info-item">
                        <div class="course-info-value"><i class="bi bi-laptop" style="color:var(--clr-accent)"></i> <?= $course['mode'] ?></div>
                        <div class="course-info-label">Training Mode</div>
                    </div>
                    <div class="course-info-item">
                        <div class="course-info-value"><i class="bi bi-bar-chart" style="color:var(--clr-purple)"></i> <?= $course['level'] ?></div>
                        <div class="course-info-label">Level</div>
                    </div>
                    <div class="course-info-item">
                        <div class="course-info-value"><i class="bi bi-star" style="color:var(--clr-warning)"></i> <?= $course['rating'] ?></div>
                        <div class="course-info-label">Rating</div>
                    </div>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:1.5rem">
                    <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg">Apply Now <i class="bi bi-arrow-right"></i></a>
                    <a href="<?= BASE_URL ?>/download-syllabus.php?course=<?= $slug ?>" class="btn btn-outline btn-lg"><i class="bi bi-file-earmark-pdf"></i> Download Syllabus (PDF)</a>
                </div>
            </div>

            <div class="course-hero-image" data-aos="fade-left">
                <img src="<?= $course['image'] ?>" alt="<?= $course['title'] ?>" loading="eager" />
            </div>
        </div>
    </div>
</section>

<!-- Course Overview -->
<section class="py-section bg-white" aria-labelledby="overview-heading">
    <div class="container">
        <div style="display:grid;gap:2.5rem" class="overview-grid">
            <div data-aos="fade-up">
                <h2 id="overview-heading" class="text-display-md section-title">Course <span class="gradient-text">Overview</span></h2>
                <p style="font-size:1rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.5rem"><?= $course['description'] ?></p>

                <h3 style="font-weight:600;font-size:1rem;margin-bottom:0.75rem">Eligibility</h3>
                <p style="font-size:0.9375rem;color:var(--clr-text-secondary);margin-bottom:1.5rem"><?= $course['eligibility'] ?></p>

                <h3 style="font-weight:600;font-size:1rem;margin-bottom:0.75rem">Course Highlights</h3>
                <div style="display:grid;gap:0.5rem">
                    <?php foreach ($course['highlights'] as $h): ?>
                    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.9375rem">
                        <i class="bi bi-check-circle-fill" style="color:var(--clr-accent)"></i> <?= $h ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="sidebar-card">
                    <div style="display:flex;align-items:baseline;gap:0.5rem;margin-bottom:0.25rem">
                        <span class="sidebar-price"><?= $course['price'] ?></span>
                        <span class="sidebar-price-label">or <?= $course['emi'] ?> EMI</span>
                    </div>
                    <p style="font-size:0.75rem;color:var(--clr-text-muted);margin-bottom:1rem">Zero-cost EMI available · Scholarship up to 50%</p>

                    <div class="sidebar-features">
                        <div class="sidebar-feature"><i class="bi bi-check-lg"></i> Industry-recognized certification</div>
                        <div class="sidebar-feature"><i class="bi bi-check-lg"></i> <?= count($course['projects']) ?>+ real-world projects</div>
                        <div class="sidebar-feature"><i class="bi bi-check-lg"></i> Lifetime access to materials</div>
                        <div class="sidebar-feature"><i class="bi bi-check-lg"></i> 1-on-1 mentorship</div>
                        <div class="sidebar-feature"><i class="bi bi-check-lg"></i> 100% placement assistance</div>
                        <div class="sidebar-feature"><i class="bi bi-check-lg"></i> Flexible batch timings</div>
                    </div>

                    <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg btn-block" style="margin-bottom:0.75rem">
                        Enquire Now <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hi!%20I'm%20interested%20in%20<?= urlencode($course['title']) ?>%20at%20IUC%20Edu." target="_blank" rel="noopener" class="btn btn-accent btn-lg btn-block">
                        <i class="bi bi-whatsapp"></i> Chat on WhatsApp
                    </a>

                    <div style="text-align:center;margin-top:1rem;font-size:0.8125rem;color:var(--clr-text-muted)">
                        <i class="bi bi-people"></i> <?= $course['enrolled'] ?> students enrolled
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .overview-grid { grid-template-columns: 1fr; }
    @media (min-width: 1024px) { .overview-grid { grid-template-columns: 1.5fr 1fr; } }
</style>

<!-- Technologies & Tools -->
<section class="py-section bg-light" aria-labelledby="techs-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <h2 id="techs-heading" class="text-display-md section-title">Technologies & <span class="gradient-text">Tools</span></h2>
            <p class="section-subtitle">Master these industry-standard technologies and tools in this program.</p>
        </div>

        <div style="display:grid;gap:0.75rem" class="course-tech-grid" data-aos="fade-up">
            <?php foreach ($course['technologies'] as $t): ?>
            <div class="company-logo-item"><?= $t ?></div>
            <?php endforeach; ?>
            <?php foreach ($course['tools'] as $t): ?>
            <div class="company-logo-item"><?= $t ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .course-tech-grid { grid-template-columns: repeat(3, 1fr); }
    @media (min-width: 640px) { .course-tech-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 1024px) { .course-tech-grid { grid-template-columns: repeat(5, 1fr); } }
</style>

<!-- Module-wise Curriculum -->
<section class="py-section bg-white" aria-labelledby="curriculum-heading">
    <div class="container-narrow">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Curriculum</div>
            <h2 id="curriculum-heading" class="text-display-md section-title">Module-Wise <span class="gradient-text">Curriculum</span></h2>
            <p class="section-subtitle">A structured, industry-reviewed curriculum designed to take you from beginner to job-ready professional.</p>
        </div>

        <div data-aos="fade-up">
            <?php foreach ($course['syllabus'] as $i => $module): ?>
            <div class="module-item <?= $i === 0 ? 'open' : '' ?>">
                <div class="module-header">
                    <div class="module-header-left">
                        <span class="module-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                        <span class="module-title"><?= $module[0] ?></span>
                    </div>
                    <span class="module-toggle"><i class="bi bi-plus-circle"></i></span>
                </div>
                <div class="module-body">
                    <p style="font-size:0.875rem;color:var(--clr-text-secondary);line-height:1.6"><?= $module[1] ?></p>
                    <div class="module-topics">
                        <?php foreach (explode(', ', $module[1]) as $topic): ?>
                        <span class="module-topic"><?= trim($topic) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Projects -->
<section class="py-section bg-light" aria-labelledby="projects-heading">
    <div class="container-narrow">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Projects</div>
            <h2 id="projects-heading" class="text-display-md section-title">Hands-On <span class="gradient-text">Projects</span></h2>
            <p class="section-subtitle">Build a professional portfolio with real-world projects that demonstrate your skills.</p>
        </div>

        <div style="display:grid;gap:1rem" data-aos="fade-up">
            <?php foreach ($course['projects'] as $i => $project): ?>
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border)">
                <div style="width:2rem;height:2rem;border-radius:var(--radius-md);background:var(--clr-primary-light);color:var(--clr-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:0.8125rem"><?= $i + 1 ?></div>
                <span style="font-weight:500;font-size:0.9375rem"><?= $project ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Certification -->
<section class="py-section bg-white" aria-labelledby="cert-heading">
    <div class="container-narrow" style="text-align:center" data-aos="fade-up">
        <div style="font-size:2.5rem;color:var(--clr-primary);margin-bottom:1rem"><i class="bi bi-patch-check"></i></div>
        <h2 id="cert-heading" class="text-display-md section-title">Get <span class="gradient-text">Certified</span></h2>
        <p style="font-size:1.0625rem;color:var(--clr-text-secondary);max-width:560px;margin:0 auto 1rem"><?= $course['certification'] ?></p>
        <p style="font-size:0.9375rem;color:var(--clr-text-secondary)">Our certifications are recognized by leading companies and designed to boost your career prospects.</p>
    </div>
</section>

<!-- Career Opportunities -->
<section class="py-section bg-light" aria-labelledby="career-heading">
    <div class="container-narrow">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Career Opportunities</div>
            <h2 id="career-heading" class="text-display-md section-title">Your <span class="gradient-text">Career Path</span></h2>
            <p class="section-subtitle">Roles you can target after completing this program.</p>
        </div>

        <div style="display:grid;gap:0.75rem" data-aos="fade-up">
            <?php foreach ($course['career'] as $c): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border)">
                <span style="font-weight:500;font-size:0.9375rem"><?= explode(' – ', $c)[0] ?></span>
                <span style="font-weight:700;color:var(--clr-primary);font-size:0.9375rem"><?= explode(' – ', $c)[1] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-section bg-white" style="text-align:center" aria-label="Enroll CTA">
    <div class="container-narrow" data-aos="fade-up">
        <h2 class="text-display-md section-title">Ready to Start Your <span class="gradient-text">Journey?</span></h2>
        <p style="font-size:1rem;color:var(--clr-text-secondary);margin-bottom:1.5rem">Join <?= $course['enrolled'] ?> students who have already enrolled in this program.</p>
        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:center">
            <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg">Apply Now <i class="bi bi-arrow-right"></i></a>
            <a href="tel:+91<?= SITE_PHONE ?>" class="btn btn-outline btn-lg"><i class="bi bi-telephone"></i> Call Us</a>
        </div>
    </div>
</section>

<!-- Related Courses -->
<section class="py-section bg-light" aria-labelledby="related-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <h2 id="related-heading" class="text-display-md section-title">Related <span class="gradient-text">Courses</span></h2>
        </div>

        <div style="display:grid;gap:1.5rem" class="related-courses-grid" data-aos="fade-up">
            <?php
            $related = getCoursesByCategory($course['category']);
            $related = array_filter($related, function($k) use ($slug) { return $k !== $slug; }, ARRAY_FILTER_USE_KEY);
            $related = array_slice($related, 0, 3);
            ?>
            <?php if (count($related) > 0): ?>
                <?php foreach ($related as $rSlug => $rCourse): ?>
                <article class="card course-card">
                    <div class="course-card-image">
                        <img src="<?= $rCourse['image'] ?>" alt="<?= $rCourse['title'] ?>" loading="lazy" />
                    </div>
                    <div class="course-card-body">
                        <h3 style="font-weight:600;font-size:0.9375rem;margin-bottom:0.25rem"><?= $rCourse['short_title'] ?></h3>
                        <p style="font-size:0.8125rem;color:var(--clr-text-secondary);margin-bottom:0.5rem"><?= excerpt($rCourse['short_desc'], 80) ?></p>
                        <div class="course-card-footer">
                            <div class="course-price-row">
                                <span class="course-price" style="font-size:1rem"><?= $rCourse['price'] ?></span>
                                <span class="course-emi">No-Cost EMI · <?= $rCourse['emi'] ?></span>
                            </div>
                            <div class="course-actions">
                                <a href="<?= BASE_URL ?>/course/<?= $rSlug ?>" class="btn btn-sm btn-ghost">Learn More</a>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;color:var(--clr-text-muted);grid-column:1/-1">No related courses found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    .related-courses-grid { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .related-courses-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .related-courses-grid { grid-template-columns: repeat(3, 1fr); } }
</style>

<!-- FAQ for this course -->
<section class="py-section bg-white" aria-labelledby="faq-heading">
    <div class="container-narrow">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">FAQ</div>
            <h2 id="faq-heading" class="text-display-md section-title">Frequently Asked <span class="gradient-text">Questions</span></h2>
        </div>

        <div data-aos="fade-up">
            <?php foreach (array_slice($faqs, 0, 5) as $i => $faq): ?>
            <div class="accordion-item">
                <details>
                    <summary>
                        <?= $faq['q'] ?>
                        <span class="accordion-icon"><i class="bi bi-plus"></i></span>
                    </summary>
                    <div class="accordion-body"><?= $faq['a'] ?></div>
                </details>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
