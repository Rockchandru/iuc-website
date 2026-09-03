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
    $metaDesc = 'The requested IUC Edu course page could not be found.';
    $robotsMeta = 'noindex, follow';
    $canonical = false;
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center"><h1>Course Not Found</h1><p style="color:var(--clr-text-secondary);margin:1rem 0">The course you\'re looking for doesn\'t exist.</p><a href="' . htmlspecialchars(BASE_URL . '/', ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">Back to Home</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$courseSeoMap = [
    'ai-ml' => ['title' => 'AI & Machine Learning Course in Chennai | IUC Edu', 'h1' => 'AI & Machine Learning Course in Chennai', 'description' => 'Learn AI, machine learning, deep learning and generative AI in Chennai with practical projects, expert guidance and placement assistance at IUC Edu.'],
    'data-science' => ['title' => 'Data Science Course in Chennai | IUC Edu', 'h1' => 'Data Science Course in Chennai', 'description' => 'Learn Python, SQL, statistics, machine learning and data visualisation through practical Data Science training in Chennai at IUC Edu.'],
    'python' => ['title' => 'Python Training in Chennai | IUC Edu', 'h1' => 'Python Training in Chennai', 'description' => 'Learn Python from fundamentals to application development with practical exercises, projects, flexible batches and certification at IUC Edu Chennai.'],
    'java' => ['title' => 'Java Training in Chennai | IUC Edu', 'h1' => 'Java Training in Chennai', 'description' => 'Build Core Java, J2EE, Spring Boot and Hibernate skills through instructor-led Java training with projects at IUC Edu in Chennai.'],
    'full-stack-java' => ['title' => 'Full Stack Java Course in Chennai | IUC Edu', 'h1' => 'Full Stack Java Developer Course in Chennai', 'description' => 'Learn frontend, Core Java, Spring Boot, databases and deployment in a practical Full Stack Java course at IUC Edu Chennai.'],
    'spring-boot' => ['title' => 'Spring Boot Training in Chennai | IUC Edu', 'h1' => 'Spring Boot & Microservices Training in Chennai', 'description' => 'Learn Spring Boot, REST APIs, microservices, security and cloud-native deployment through advanced practical training at IUC Edu Chennai.'],
    'react' => ['title' => 'React JS Course in Chennai | IUC Edu', 'h1' => 'React JS Course in Chennai', 'description' => 'Build modern web interfaces with React, Redux, Next.js and TypeScript through practical frontend training at IUC Edu Chennai.'],
    'angular' => ['title' => 'Angular Training in Chennai | IUC Edu', 'h1' => 'Angular Training in Chennai', 'description' => 'Learn Angular, TypeScript, RxJS and frontend application development with guided projects and flexible training at IUC Edu Chennai.'],
    'node-js' => ['title' => 'Node.js Training in Chennai | IUC Edu', 'h1' => 'Node.js Backend Training in Chennai', 'description' => 'Learn Node.js, Express, databases and API development through practical backend programming training at IUC Edu in Chennai.'],
    'ui-ux' => ['title' => 'UI/UX Design Course in Chennai | IUC Edu', 'h1' => 'UI/UX Design Course in Chennai', 'description' => 'Learn user research, wireframing, prototyping and interface design with Figma through practical UI/UX training at IUC Edu Chennai.'],
    'software-testing' => ['title' => 'Software Testing Course in Chennai | IUC Edu', 'h1' => 'Software Testing Course in Chennai', 'description' => 'Learn manual testing, automation, Selenium and QA practices through project-based Software Testing training at IUC Edu Chennai.'],
    'devops' => ['title' => 'DevOps Course in Chennai | IUC Edu', 'h1' => 'DevOps & Cloud Engineering Course in Chennai', 'description' => 'Learn CI/CD, Docker, Kubernetes, Jenkins, Terraform and cloud practices through hands-on DevOps training at IUC Edu Chennai.'],
    'cloud-computing' => ['title' => 'Cloud Computing Course in Chennai | IUC Edu', 'h1' => 'Cloud Computing Course in Chennai', 'description' => 'Build practical AWS and Azure cloud skills, from core services to deployment and architecture, with training at IUC Edu Chennai.'],
    'cyber-security' => ['title' => 'Cyber Security Course in Chennai | IUC Edu', 'h1' => 'Cyber Security & Ethical Hacking Course in Chennai', 'description' => 'Learn network security, ethical hacking, vulnerability testing and security tools through practical cyber security training at IUC Edu Chennai.'],
    'digital-marketing' => ['title' => 'Digital Marketing Course in Chennai | IUC Edu', 'h1' => 'Digital Marketing & SEO Course in Chennai', 'description' => 'Learn SEO, Google Ads, social media and analytics through a practical Digital Marketing course with live guidance at IUC Edu Chennai.'],
    'c-cpp' => ['title' => 'C & C++ Programming Course in Chennai | IUC Edu', 'h1' => 'C & C++ Programming Course in Chennai', 'description' => 'Build programming fundamentals with C, C++, data structures, OOP and practical coding exercises at IUC Edu in Chennai.'],
];
$courseSeo = $courseSeoMap[$slug] ?? [
    'title' => $course['short_title'] . ' Course in Chennai | IUC Edu',
    'h1' => $course['title'] . ' Course in Chennai',
    'description' => 'Learn ' . $course['short_title'] . ' through practical online or classroom training, flexible batches and placement assistance at IUC Edu Chennai.',
];

$programmingCourseSlugs = ['c-cpp', 'python', 'java', 'full-stack-java', 'spring-boot', 'react', 'angular', 'node-js'];
$courseHubUrl = in_array($slug, $programmingCourseSlugs, true) ? 'programming-courses-in-chennai' : 'computer-training-in-chennai';
$courseHubLabel = in_array($slug, $programmingCourseSlugs, true) ? 'Programming courses in Chennai' : 'Computer training courses in Chennai';

$courseFaqs = [
    ['q' => 'What will I learn in the ' . $course['short_title'] . ' course?', 'a' => $course['description']],
    ['q' => 'Who can join this ' . $course['short_title'] . ' training?', 'a' => $course['eligibility']],
    ['q' => 'Is ' . $course['short_title'] . ' training available online and in Chennai?', 'a' => 'The listed training mode is ' . $course['mode'] . '. Contact IUC Edu to confirm the current online or classroom batch before enrolling.'],
    ['q' => 'Will I receive a certificate after completing the course?', 'a' => 'Yes. The certification for this program is: ' . $course['certification'] . '.'],
    ['q' => 'How can I check the next batch and course fee?', 'a' => 'Use the enquiry form, call +91 ' . SITE_PHONE . ' or visit an IUC Edu Chennai centre to confirm the current schedule, fee and seat availability.'],
];

$pageTitle = $courseSeo['title'];
$metaDesc = $courseSeo['description'];
$ogTitle = $pageTitle;
$ogDesc = $metaDesc;
$ogImage = $course['image'];
$ogImageAlt = $course['title'] . ' training course at IUC Edu in Chennai';
$canonical = 'course/' . $slug;
$courseUrl = SITE_URL . '/' . $canonical;
$structuredData = [
    [
        '@type' => 'Course',
        '@id' => $courseUrl . '#course',
        'name' => $course['title'],
        'description' => $course['description'],
        'url' => $courseUrl,
        'image' => $course['image'],
        'provider' => ['@id' => SITE_URL . '/#organization'],
        'educationalLevel' => $course['level'],
        'courseMode' => $course['mode'],
        'inLanguage' => 'en-IN',
    ],
    [
        '@type' => 'BreadcrumbList',
        '@id' => $courseUrl . '#breadcrumb',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Courses', 'item' => SITE_URL . '/#courses'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $course['title'], 'item' => $courseUrl],
        ],
    ],
    [
        '@type' => 'FAQPage',
        '@id' => $courseUrl . '#faq',
        'mainEntity' => array_map(static function ($faq) {
            return [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ];
        }, $courseFaqs),
    ],
];

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
                <h1 class="text-display-lg" style="margin-bottom:0.75rem"><?= htmlspecialchars($courseSeo['h1'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p style="font-size:1.0625rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.25rem">
                    <?= $course['description'] ?>
                </p>

                <div style="display:flex;flex-wrap:wrap;gap:0.5rem 1rem;margin-bottom:1.25rem;font-size:0.875rem" aria-label="Related training guides">
                    <a href="<?= BASE_URL ?>/<?= $courseHubUrl ?>" style="color:var(--clr-primary);font-weight:600"><?= $courseHubLabel ?></a>
                    <?php if (stripos($course['level'], 'Beginner') !== false): ?>
                    <a href="<?= BASE_URL ?>/it-courses-for-beginners" style="color:var(--clr-primary);font-weight:600">IT courses for beginners</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/online-it-courses" style="color:var(--clr-primary);font-weight:600">Online IT course options</a>
                </div>

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
                <img src="<?= $course['image'] ?>" alt="<?= htmlspecialchars($courseSeo['h1'], ENT_QUOTES, 'UTF-8') ?> at IUC Edu" loading="eager" fetchpriority="high" />
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
            $relatedCourseMap = [
                'ai-ml' => ['python', 'data-science', 'cloud-computing'],
                'data-science' => ['python', 'ai-ml', 'cloud-computing'],
                'python' => ['c-cpp', 'data-science', 'ai-ml'],
                'java' => ['c-cpp', 'full-stack-java', 'spring-boot'],
                'full-stack-java' => ['java', 'spring-boot', 'angular'],
                'spring-boot' => ['java', 'full-stack-java', 'node-js'],
                'react' => ['angular', 'node-js', 'full-stack-java'],
                'angular' => ['react', 'node-js', 'full-stack-java'],
                'node-js' => ['react', 'angular', 'full-stack-java'],
                'ui-ux' => ['react', 'angular', 'digital-marketing'],
                'software-testing' => ['java', 'python', 'devops'],
                'devops' => ['cloud-computing', 'spring-boot', 'cyber-security'],
                'cloud-computing' => ['devops', 'cyber-security', 'ai-ml'],
                'cyber-security' => ['cloud-computing', 'devops', 'software-testing'],
                'digital-marketing' => ['ui-ux', 'data-science', 'python'],
                'c-cpp' => ['python', 'java', 'full-stack-java'],
            ];
            $related = [];
            foreach ($relatedCourseMap[$slug] ?? [] as $relatedSlug) {
                if (isset($courses[$relatedSlug])) {
                    $related[$relatedSlug] = $courses[$relatedSlug];
                }
            }
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
                                <a href="<?= BASE_URL ?>/course/<?= $rSlug ?>" class="btn btn-sm btn-ghost">View <?= htmlspecialchars($rCourse['short_title'], ENT_QUOTES, 'UTF-8') ?> course</a>
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
            <?php foreach ($courseFaqs as $i => $faq): ?>
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
