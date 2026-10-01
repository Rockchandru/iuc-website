<?php
require_once __DIR__ . '/includes/functions.php';

$pageKey = isset($_GET['page']) ? preg_replace('/[^a-z0-9-]/', '', $_GET['page']) : '';

$landingPages = [
    'computer-training-in-chennai' => [
        'label' => 'Computer Education & Career Skills',
        'title' => 'Computer Courses in Chennai | IT Training Institute | IUC Edu',
        'description' => 'Join practical computer courses and IT training in Chennai at IUC Edu. Compare programming, software, data and digital courses, batches, projects and support.',
        'h1' => 'Computer Courses and IT Training in Chennai',
        'intro' => 'IUC Edu provides instructor-led computer classes for students, freshers, working professionals and career changers in Chennai. Compare programming, software development, data, cloud, testing and digital career courses, then choose classroom or live online training based on the available batch.',
        'sections' => [
            ['Computer Classes for Students and Working Professionals', 'Courses range from beginner programming in C, C++, Python and Java to career-focused training in full stack development, software testing, data science, artificial intelligence, cloud computing, cyber security, UI/UX and digital marketing. Every course page states its curriculum, expected knowledge, duration, learning mode, projects and current fee.'],
            ['Practical IT Training with Projects', 'Classes combine instructor explanations, guided exercises and course-specific project work. Learners can use the individual program pages to compare tools and outcomes instead of relying on one generic course list. Placement assistance, interview practice and flexible batches support career preparation; employment outcomes still depend on the learner, role and employer.'],
            ['Computer Training Centres in Chennai', 'IUC Edu has a head office in C.I.T Nagar near Nandanam and a centre in Kaladipet, Thiruvottiyur. The contact section provides consistent addresses, telephone numbers and map directions. Contact admissions before visiting to confirm which centre and batch currently offers your selected course.'],
            ['How to Choose the Right Computer Course', 'Choose a course by the skill and job direction you want: programming fundamentals, application development, data, testing, infrastructure, design or marketing. Review the detailed syllabus and prerequisites on the recommended course pages. Beginners who are unsure can compare the beginner course hub or request course guidance.'],
        ],
        'courses' => ['c-cpp', 'python', 'java', 'full-stack-java', 'data-science', 'software-testing'],
        'faqs' => [
            ['q' => 'How do I choose a computer course in Chennai?', 'a' => 'Start with your goal, current experience and preferred role. Compare the curriculum, prerequisites, projects, duration and learning mode on each IUC Edu course page, or request course guidance if you are unsure.'],
            ['q' => 'Who can join computer classes at IUC Edu?', 'a' => 'Students, freshers, working professionals and career changers can apply. Eligibility differs by course and is stated on every course page.'],
            ['q' => 'Are these university computer science degree courses?', 'a' => 'No. These are skill-based computer, programming and technology training programs, not university degree programs.'],
            ['q' => 'Where are the IUC Edu training centres in Chennai?', 'a' => 'The head office is in C.I.T Nagar near Nandanam, and the second centre is in Kaladipet, Thiruvottiyur.'],
            ['q' => 'Does the training include practical projects?', 'a' => 'Yes. Courses include hands-on exercises and course-specific projects. The exact project list is available on each course page.'],
        ],
    ],
    'programming-courses-in-chennai' => [
        'label' => 'Learn to Code',
        'title' => 'Programming Courses in Chennai | IUC Edu',
        'description' => 'Learn C, C++, Python, Java and software development through practical programming and coding classes at IUC Edu in Chennai.',
        'h1' => 'Programming and Coding Courses in Chennai',
        'intro' => 'Learn programming through structured lessons, guided practice and software projects. IUC Edu offers coding classes in Chennai for beginners and learners building specialised frontend, backend or full stack development skills.',
        'sections' => [
            ['Start with Programming Fundamentals', 'C and C++ introduce core programming concepts, logic, memory management and object-oriented programming. Python offers an accessible starting point for application development, automation and data-focused learning.'],
            ['Build Software Development Skills', 'Java, Spring Boot, React, Angular and Node.js courses help learners progress into enterprise, frontend and backend development. Full Stack Java combines client-side, server-side, database and deployment skills in one career-focused program.'],
            ['Hands-On Coding Practice', 'Learners work with the languages, frameworks and development tools listed on each course page. Projects are designed to turn concepts into practical portfolio work while mentor support helps learners solve coding problems.'],
        ],
        'courses' => ['c-cpp', 'python', 'java', 'full-stack-java', 'react', 'angular', 'node-js', 'spring-boot'],
        'faqs' => [
            ['q' => 'Which programming course should a beginner start with?', 'a' => 'C and C++ are useful for fundamentals, while Python is a beginner-friendly choice for general programming. Your best option depends on the applications and career path you want to pursue.'],
            ['q' => 'Which programming languages are taught at IUC Edu?', 'a' => 'Available courses cover C, C++, Python, Java and JavaScript-based development with React, Angular and Node.js.'],
            ['q' => 'Can these courses help me build software development skills?', 'a' => 'Yes. The courses combine programming concepts, development tools and projects. Advanced career outcomes depend on the course selected, practice and prior experience.'],
            ['q' => 'How can I check programming course fees?', 'a' => 'Current fees are displayed on the individual course pages. Contact admissions to confirm the fee and batch availability before enrolling.'],
        ],
    ],
    'it-courses-for-beginners' => [
        'label' => 'Beginner-Friendly Learning',
        'title' => 'IT Courses for Beginners | IUC Edu',
        'description' => 'Explore beginner-friendly IT courses at IUC Edu, including programming, software testing, UI/UX, digital marketing and career-focused technology training.',
        'h1' => 'IT Courses for Beginners',
        'intro' => 'Start building practical technology skills with courses that introduce concepts step by step. Selected beginner programs require no prior programming experience, and each course page states the knowledge expected before joining.',
        'sections' => [
            ['Beginner Programming Courses', 'C and C++ help develop programming logic and foundational computer science skills. Python begins with core syntax and gradually introduces application development, while Java covers object-oriented programming and enterprise development concepts.'],
            ['Beginner IT Career Options', 'Software testing, UI/UX design and digital marketing provide entry points into technology roles beyond programming. Learners can compare course modules, tools and project work before choosing a direction.'],
            ['Choose a Course Based on Your Goal', 'Begin with the outcome you want: learn to code, build websites, test software, design digital products or work in online marketing. Review eligibility and curriculum details or speak with a counsellor if you are unsure where to start.'],
        ],
        'courses' => ['c-cpp', 'python', 'java', 'software-testing', 'ui-ux', 'digital-marketing'],
        'faqs' => [
            ['q' => 'Do beginner IT courses require programming experience?', 'a' => 'Selected beginner courses do not require prior programming experience. Check the eligibility shown on the course page before enrolling.'],
            ['q' => 'Which beginner course is best for learning coding?', 'a' => 'Python is a beginner-friendly option, while C and C++ provide a strong foundation in programming logic and computer science concepts.'],
            ['q' => 'Do beginners receive a certificate?', 'a' => 'Yes. Students receive the course-completion certificate described on the selected course page after meeting completion requirements.'],
            ['q' => 'Can working professionals join beginner courses?', 'a' => 'Yes. Morning, evening, weekend and online options are available, subject to the schedule for the selected course.'],
        ],
    ],
    'online-it-courses' => [
        'label' => 'Live Online Training',
        'title' => 'Online Computer & IT Courses | IUC Edu',
        'description' => 'Join live instructor-led online computer and IT courses in programming, data, cloud, software development and digital skills with practical projects and certification.',
        'h1' => 'Online Computer and IT Courses',
        'intro' => 'IUC Edu offers live virtual classroom training for learners who prefer to study remotely. Available online courses combine instructor guidance, practical work, recorded-session access and the course-completion certificate described on each program page.',
        'sections' => [
            ['Live Instructor-Led Classes', 'Online batches are delivered as live virtual classes rather than presented as self-paced university programs. Learners can ask questions, follow guided demonstrations and take part in course exercises with mentor support.'],
            ['Programming, Software and Technology Courses Online', 'Online options cover programming, full stack development, data science, AI and machine learning, software testing, cloud computing, cyber security, UI/UX and digital marketing. Availability may vary by batch.'],
            ['Projects and Course Certification', 'Online learners complete the same course-specific practical work described on the relevant program page. Students receive the applicable IUC Edu completion certificate after meeting the course requirements.'],
        ],
        'courses' => ['python', 'java', 'full-stack-java', 'data-science', 'ai-ml', 'cloud-computing', 'cyber-security', 'digital-marketing'],
        'faqs' => [
            ['q' => 'Are IUC Edu online courses live or recorded?', 'a' => 'Classes are delivered live in a virtual classroom. Recorded-session access is available to support revision and missed-session follow-up.'],
            ['q' => 'Do online IT courses include a certificate?', 'a' => 'Yes. The completion certificate for each online program is described on its individual course page.'],
            ['q' => 'Do online learners complete practical projects?', 'a' => 'Yes. Online courses include the practical exercises and projects listed on the relevant course page.'],
            ['q' => 'How can I confirm an online batch and fee?', 'a' => 'Check the individual course page and contact admissions to confirm the current live-online schedule, fee and seat availability.'],
        ],
    ],
];

if (!isset($landingPages[$pageKey])) {
    http_response_code(404);
    $pageTitle = 'Training Page Not Found | IUC Edu';
    $metaDesc = 'The requested IUC Edu training page could not be found.';
    $robotsMeta = 'noindex, follow';
    $canonical = false;
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center"><h1>Training Page Not Found</h1><p style="color:var(--clr-text-secondary);margin:1rem 0">Explore our current computer and IT courses.</p><a href="' . htmlspecialchars(BASE_URL . '/#courses', ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">View Courses</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$page = $landingPages[$pageKey];
$pageTitle = $page['title'];
$metaDesc = $page['description'];
$ogTitle = $pageTitle;
$ogDesc = $metaDesc;
$ogImage = SITE_URL . '/assets/images/about-education.jpg';
$ogImageAlt = $page['h1'] . ' at IUC Edu';
$canonical = $pageKey;
$pageUrl = SITE_URL . '/' . $pageKey;
$schemaPageType = 'CollectionPage';

$itemList = [];
foreach ($page['courses'] as $position => $courseSlug) {
    if (!isset($courses[$courseSlug])) {
        continue;
    }
    $itemList[] = [
        '@type' => 'ListItem',
        'position' => $position + 1,
        'url' => SITE_URL . '/course/' . $courseSlug,
        'name' => $courses[$courseSlug]['title'],
    ];
}

$structuredData = [
    [
        '@type' => 'BreadcrumbList',
        '@id' => $pageUrl . '#breadcrumb',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $page['h1'], 'item' => $pageUrl],
        ],
    ],
    [
        '@type' => 'ItemList',
        '@id' => $pageUrl . '#courses',
        'name' => $page['h1'] . ' course list',
        'itemListElement' => $itemList,
    ],
    [
        '@type' => 'FAQPage',
        '@id' => $pageUrl . '#faq',
        'mainEntity' => array_map(static function ($faq) {
            return [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ];
        }, $page['faqs']),
    ],
];

require __DIR__ . '/includes/header.php';
$h = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<section class="course-hero seo-landing-hero">
    <div class="container-narrow">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= BASE_URL ?>/">Home</a>
            <span class="breadcrumb-sep">/</span>
            <span><?= $h($page['h1']) ?></span>
        </nav>
        <div class="section-label"><?= $h($page['label']) ?></div>
        <h1 class="text-display-lg section-title"><?= $h($page['h1']) ?></h1>
        <p class="seo-landing-intro"><?= $h($page['intro']) ?></p>
        <div class="seo-landing-actions">
            <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg">Get Course Guidance <i class="bi bi-arrow-right"></i></a>
            <a href="<?= BASE_URL ?>/#courses" class="btn btn-outline btn-lg">Browse All Courses</a>
        </div>
    </div>
</section>

<section class="py-section bg-white" aria-label="Training information">
    <div class="container-narrow seo-copy-stack">
        <?php foreach ($page['sections'] as $section): ?>
        <article data-aos="fade-up">
            <h2 class="text-display-md section-title"><?= $h($section[0]) ?></h2>
            <p><?= $h($section[1]) ?></p>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="py-section bg-light" aria-labelledby="landing-courses-heading">
    <div class="container">
        <div class="section-header section-header-center">
            <div class="section-label">Available Programs</div>
            <h2 id="landing-courses-heading" class="text-display-md section-title">Recommended <span class="gradient-text">Courses</span></h2>
        </div>
        <div class="seo-landing-course-grid">
            <?php foreach ($page['courses'] as $courseSlug): ?>
                <?php if (!isset($courses[$courseSlug])) continue; $entry = $courses[$courseSlug]; ?>
                <article class="card seo-landing-course-card" data-aos="fade-up">
                    <h3><a href="<?= BASE_URL ?>/course/<?= $h($courseSlug) ?>"><?= $h($entry['title']) ?></a></h3>
                    <p><?= $h($entry['short_desc']) ?></p>
                    <div class="course-meta">
                        <span><i class="bi bi-clock"></i> <?= $h($entry['duration']) ?></span>
                        <span><i class="bi bi-laptop"></i> <?= $h($entry['mode']) ?></span>
                        <span><i class="bi bi-bar-chart"></i> <?= $h($entry['level']) ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>/course/<?= $h($courseSlug) ?>" class="btn btn-sm btn-ghost">View <?= $h($entry['short_title']) ?> course <i class="bi bi-arrow-right"></i></a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="faq" class="py-section bg-white" aria-labelledby="landing-faq-heading">
    <div class="container-narrow">
        <div class="section-header section-header-center">
            <div class="section-label">FAQ</div>
            <h2 id="landing-faq-heading" class="text-display-md section-title">Questions About <span class="gradient-text"><?= $h($page['h1']) ?></span></h2>
        </div>
        <?php foreach ($page['faqs'] as $index => $faq): ?>
        <div class="accordion-item">
            <details <?= $index === 0 ? 'open' : '' ?>>
                <summary><?= $h($faq['q']) ?><span class="accordion-icon"><i class="bi bi-plus"></i></span></summary>
                <div class="accordion-body"><?= $h($faq['a']) ?></div>
            </details>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
    .seo-landing-hero { padding-top:8rem; }
    .seo-landing-intro { max-width:760px; color:var(--clr-text-secondary); font-size:1.0625rem; line-height:1.8; }
    .seo-landing-actions { display:flex; flex-wrap:wrap; gap:0.75rem; margin-top:1.5rem; }
    .seo-copy-stack { display:grid; gap:2.5rem; }
    .seo-copy-stack article { padding-bottom:2rem; border-bottom:1px solid var(--clr-border); }
    .seo-copy-stack article:last-child { padding-bottom:0; border-bottom:0; }
    .seo-copy-stack p { color:var(--clr-text-secondary); font-size:1rem; line-height:1.8; }
    .seo-landing-course-grid { display:grid; grid-template-columns:1fr; gap:1.25rem; }
    .seo-landing-course-card { padding:1.5rem; }
    .seo-landing-course-card h3 { font-size:1.0625rem; font-weight:700; margin-bottom:0.5rem; }
    .seo-landing-course-card h3 a { color:var(--clr-text); }
    .seo-landing-course-card h3 a:hover { color:var(--clr-primary); }
    .seo-landing-course-card p { color:var(--clr-text-secondary); font-size:0.875rem; line-height:1.6; margin-bottom:0.75rem; }
    .seo-landing-course-card .course-meta { margin-bottom:1rem; }
    @media (min-width:640px) { .seo-landing-course-grid { grid-template-columns:repeat(2, 1fr); } }
    @media (min-width:1024px) { .seo-landing-course-grid { grid-template-columns:repeat(3, 1fr); } }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
