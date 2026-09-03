<?php
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', $_GET['slug']) : '';
$post = null;
foreach ($blogPosts as $p) {
    if ($p['slug'] === $slug) { $post = $p; break; }
}

if ($slug === '') {
    $pageTitle = 'IT Career & Computer Learning Blog | IUC Edu';
    $metaDesc = 'Read practical guides on programming, data science, AI, computer skills and IT careers from IUC Edu in Chennai.';
    $ogTitle = $pageTitle;
    $ogDesc = $metaDesc;
    $canonical = 'blog';
    $blogUrl = SITE_URL . '/blog';
    $schemaPageType = 'CollectionPage';
    $structuredData = [
        [
            '@type' => 'Blog',
            '@id' => $blogUrl . '#blog',
            'url' => $blogUrl,
            'name' => 'IUC Edu IT Career & Computer Learning Blog',
            'description' => $metaDesc,
            'publisher' => ['@id' => SITE_URL . '/#organization'],
            'blogPost' => array_map(static function ($entry) {
                return ['@id' => SITE_URL . '/blog/' . $entry['slug'] . '#article'];
            }, $blogPosts),
            'inLanguage' => 'en-IN',
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $blogUrl . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $blogUrl],
            ],
        ],
    ];

    require __DIR__ . '/includes/header.php';
    ?>
    <section class="py-section bg-light" style="padding-top:8rem" aria-labelledby="blog-index-heading">
        <div class="container">
            <div class="section-header section-header-center">
                <div class="section-label">Learning Resources</div>
                <h1 id="blog-index-heading" class="text-display-lg section-title">IT Career &amp; <span class="gradient-text">Computer Learning Blog</span></h1>
                <p class="section-subtitle">Practical guidance for learners exploring programming, data, AI and technology careers.</p>
            </div>
            <div class="blog-grid">
                <?php foreach ($blogPosts as $entry): ?>
                <article class="card blog-card" data-aos="fade-up">
                    <div class="blog-card-image">
                        <img src="<?= htmlspecialchars($entry['image'], ENT_QUOTES, 'UTF-8') ?>" alt="Featured image for <?= htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" />
                    </div>
                    <div class="blog-card-body">
                        <span class="section-label"><?= htmlspecialchars($entry['category'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h2 class="blog-card-title"><a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($entry['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
                        <p class="blog-card-excerpt"><?= htmlspecialchars($entry['excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="blog-card-meta">
                            <span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($entry['date'], ENT_QUOTES, 'UTF-8') ?></span>
                            <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($entry['slug'], ENT_QUOTES, 'UTF-8') ?>" style="color:var(--clr-primary);margin-left:auto;font-weight:600">Read article <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <style>
        .blog-grid { display:grid; grid-template-columns:1fr; gap:1.5rem; }
        .blog-card-title a { color:var(--clr-text); }
        .blog-card-title a:hover { color:var(--clr-primary); }
        @media (min-width:640px) { .blog-grid { grid-template-columns:repeat(2, 1fr); } }
        @media (min-width:1024px) { .blog-grid { grid-template-columns:repeat(3, 1fr); } }
    </style>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Blog Post Not Found – IUC Edu';
    $metaDesc = 'The requested IUC Edu blog article could not be found.';
    $robotsMeta = 'noindex, follow';
    $canonical = false;
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center"><h1>Post Not Found</h1><p style="color:var(--clr-text-secondary);margin:1rem 0">The blog post you\'re looking for doesn\'t exist.</p><a href="' . htmlspecialchars(BASE_URL . '/', ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">Back to Home</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$blogCourseMap = [
    'top-programming-languages-2026' => 'python',
    'crack-data-science-interviews' => 'data-science',
    'future-of-ai-generative-ai-2026' => 'ai-ml',
];
$relatedCourseSlug = $blogCourseMap[$slug] ?? null;
$relatedCourse = $relatedCourseSlug && isset($courses[$relatedCourseSlug]) ? $courses[$relatedCourseSlug] : null;

$pageTitle = ($post['seo_title'] ?? $post['title']) . ' | IUC Edu';
$metaDesc = $post['seo_description'] ?? $post['excerpt'];
$ogTitle = $pageTitle;
$ogDesc = $metaDesc;
$ogImage = $post['image'];
$ogImageAlt = 'Featured image for ' . $post['title'];
$ogType = 'article';
$canonical = 'blog/' . $slug;
$postUrl = SITE_URL . '/' . $canonical;
$publishedDate = date('Y-m-d', strtotime($post['date']));
$articlePublishedTime = $publishedDate;
$articleAuthor = $post['author'];
$authorSchema = $post['author'] === 'IUC Edu Team'
    ? ['@type' => 'Organization', 'name' => SITE_NAME, '@id' => SITE_URL . '/#organization']
    : ['@type' => 'Person', 'name' => $post['author']];
$structuredData = [
    [
        '@type' => 'BlogPosting',
        '@id' => $postUrl . '#article',
        'headline' => $post['title'],
        'description' => $post['excerpt'],
        'image' => $post['image'],
        'datePublished' => $publishedDate,
        'dateModified' => $publishedDate,
        'author' => $authorSchema,
        'publisher' => ['@id' => SITE_URL . '/#organization'],
        'mainEntityOfPage' => ['@id' => $postUrl . '#webpage'],
        'inLanguage' => 'en-IN',
    ],
    [
        '@type' => 'BreadcrumbList',
        '@id' => $postUrl . '#breadcrumb',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => SITE_URL . '/blog'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $postUrl],
        ],
    ],
];

require __DIR__ . '/includes/header.php';
?>

<section class="py-section bg-light" style="padding-top:8rem">
    <div class="container-narrow">
        <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:1.5rem">
            <a href="<?= BASE_URL ?>/" style="color:var(--clr-primary)">Home</a>
            <span style="margin:0 0.5rem;color:var(--clr-text-muted)">/</span>
            <a href="<?= BASE_URL ?>/blog" style="color:var(--clr-primary)">Blog</a>
            <span style="margin:0 0.5rem;color:var(--clr-text-muted)">/</span>
            <span style="color:var(--clr-text-secondary)"><?= $post['title'] ?></span>
        </nav>

        <article data-aos="fade-up">
            <div style="border-radius:var(--radius-2xl);overflow:hidden;margin-bottom:2rem">
                <img src="<?= $post['image'] ?>" alt="Featured image for <?= $post['title'] ?>" style="width:100%;height:400px;object-fit:cover" loading="eager" fetchpriority="high" />
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
                <span class="section-label" style="margin-bottom:0"><?= $post['category'] ?></span>
                <span style="font-size:0.875rem;color:var(--clr-text-muted);display:flex;align-items:center;gap:0.375rem">
                    <i class="bi bi-calendar3"></i> <?= $post['date'] ?>
                </span>
                <span style="font-size:0.875rem;color:var(--clr-text-muted);display:flex;align-items:center;gap:0.375rem">
                    <i class="bi bi-person"></i> <?= $post['author'] ?>
                </span>
            </div>

            <h1 class="text-display-lg section-title"><?= $post['title'] ?></h1>

            <div class="blog-article-content" style="font-size:1rem;line-height:1.8;color:var(--clr-text-secondary)">
                <?= renderBlogContent($post['content']) ?>
            </div>

            <div style="margin-top:3rem;padding:2rem;border-radius:var(--radius-2xl);background:var(--clr-bg-section);text-align:center">
                <h3 style="font-family:var(--font-heading);font-weight:600;font-size:1.125rem;margin-bottom:0.75rem">Ready to Start Your Tech Journey?</h3>
                <p style="font-size:0.9375rem;color:var(--clr-text-secondary);margin-bottom:1.25rem">Get expert training and placement support at IUC Edu.</p>
                <?php if ($relatedCourse): ?>
                <a href="<?= BASE_URL ?>/course/<?= htmlspecialchars($relatedCourseSlug, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline">Explore <?= htmlspecialchars($relatedCourse['short_title'], ENT_QUOTES, 'UTF-8') ?> training</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary">Enquire Now <i class="bi bi-arrow-right"></i></a>
            </div>
        </article>
    </div>
</section>

<style>
    .blog-article-content p { margin-bottom:1.15rem; }
    .blog-article-content h2 { color:var(--clr-text); font-family:var(--font-heading); font-size:1.35rem; font-weight:700; margin:2rem 0 0.75rem; }
    .blog-article-content ul { padding-left:1.4rem; margin:0 0 1.25rem; }
    .blog-article-content li { margin-bottom:0.45rem; }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
