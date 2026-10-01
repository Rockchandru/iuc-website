<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$seoTitle = $pageTitle ?? SITE_NAME . ' – IT Training Institute in Chennai';
$seoDescription = $metaDesc ?? 'Explore industry-focused IT courses in Chennai with live projects, expert mentors, flexible batches and placement assistance from IUC Edu.';
$seoRobots = $robotsMeta ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$seoOgTitle = $ogTitle ?? $seoTitle;
$seoOgDescription = $ogDesc ?? $seoDescription;
$seoOgImage = $ogImage ?? SITE_URL . '/assets/images/about-education.jpg';
$seoOgImageAlt = $ogImageAlt ?? SITE_NAME . ' IT training institute in Chennai';
$seoOgType = $ogType ?? 'website';
$canonicalUrl = null;

if (!isset($canonical) || $canonical !== false) {
    $canonicalPath = trim((string) ($canonical ?? ''), '/');
    $canonicalUrl = SITE_URL . ($canonicalPath === '' ? '/' : '/' . $canonicalPath);
}

$organizationId = SITE_URL . '/#organization';
$branchOrganizationId = SITE_URL . '/#thiruvottiyur-centre';
$websiteId = SITE_URL . '/#website';
$schemaGraph = [
    [
        '@type' => ['EducationalOrganization', 'LocalBusiness'],
        '@id' => $organizationId,
        'name' => SITE_NAME,
        'alternateName' => 'IUC Computers',
        'url' => SITE_URL . '/',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => SITE_URL . '/assets/images/Brandlogo.png',
            'width' => 836,
            'height' => 450,
        ],
        'description' => 'Computer and IT training institute in Chennai offering programming, software and career-focused technology courses with practical training.',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '#1&2 Gold Nest Apts, 2nd Main Road, C.I.T Nagar',
            'addressLocality' => 'Chennai',
            'addressRegion' => 'Tamil Nadu',
            'postalCode' => '600035',
            'addressCountry' => 'IN',
        ],
        'telephone' => '+91' . SITE_PHONE,
        'email' => SITE_EMAIL,
        'priceRange' => '₹₹',
        'areaServed' => ['@type' => 'City', 'name' => 'Chennai'],
        'hasMap' => 'https://www.google.com/maps/search/?api=1&query=Gold%20Nest%20Apartments%2C%202nd%20Main%20Road%2C%20C.I.T%20Nagar%2C%20Nandanam%2C%20Chennai%2C%20Tamil%20Nadu%20600035',
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+91' . SITE_PHONE,
            'contactType' => 'admissions',
            'areaServed' => 'IN',
            'availableLanguage' => ['English', 'Tamil'],
        ],
        'department' => ['@id' => $branchOrganizationId],
        'sameAs' => [FACEBOOK_URL, INSTAGRAM_URL, LINKEDIN_URL, YOUTUBE_URL],
    ],
    [
        '@type' => ['EducationalOrganization', 'LocalBusiness'],
        '@id' => $branchOrganizationId,
        'name' => 'IUC Edu - Thiruvottiyur Centre',
        'url' => SITE_URL . '/#contact',
        'parentOrganization' => ['@id' => $organizationId],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '19/11 Balakrishna Colony 1st Street, Kaladipet, Thiruvottiyur',
            'addressLocality' => 'Chennai',
            'addressRegion' => 'Tamil Nadu',
            'postalCode' => '600019',
            'addressCountry' => 'IN',
        ],
        'telephone' => BRANCH_LANDLINE,
        'priceRange' => '₹₹',
        'hasMap' => 'https://www.google.com/maps/search/?api=1&query=IUC%20Computers%2C%20Kaladipet%2C%20Thiruvottiyur%2C%20Chennai%2C%20Tamil%20Nadu%20600019',
    ],
    [
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'url' => SITE_URL . '/',
        'name' => SITE_NAME,
        'publisher' => ['@id' => $organizationId],
        'inLanguage' => 'en-IN',
    ],
];

if ($canonicalUrl !== null) {
    $schemaGraph[] = [
        '@type' => $schemaPageType ?? 'WebPage',
        '@id' => $canonicalUrl . '#webpage',
        'url' => $canonicalUrl,
        'name' => $seoTitle,
        'description' => $seoDescription,
        'isPartOf' => ['@id' => $websiteId],
        'about' => ['@id' => $organizationId],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => $seoOgImage,
        ],
        'inLanguage' => 'en-IN',
    ];
}

if (!empty($structuredData) && is_array($structuredData)) {
    $additionalSchemas = isset($structuredData['@type']) ? [$structuredData] : $structuredData;
    foreach ($additionalSchemas as $additionalSchema) {
        if (is_array($additionalSchema) && isset($additionalSchema['@type'])) {
            $schemaGraph[] = $additionalSchema;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-H9L990V9Z2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-H9L990V9Z2');
    </script>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $escape($seoTitle) ?></title>
    <meta name="description" content="<?= $escape($seoDescription) ?>" />
    <meta name="robots" content="<?= $escape($seoRobots) ?>" />
    <?php if ($canonicalUrl !== null): ?>
    <link rel="canonical" href="<?= $escape($canonicalUrl) ?>" />
    <?php endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" />

    <!-- Open Graph -->
    <meta property="og:locale" content="en_IN" />
    <meta property="og:type" content="<?= $escape($seoOgType) ?>" />
    <?php if ($canonicalUrl !== null): ?>
    <meta property="og:url" content="<?= $escape($canonicalUrl) ?>" />
    <?php endif; ?>
    <meta property="og:site_name" content="<?= $escape(SITE_NAME) ?>" />
    <meta property="og:title" content="<?= $escape($seoOgTitle) ?>" />
    <meta property="og:description" content="<?= $escape($seoOgDescription) ?>" />
    <meta property="og:image" content="<?= $escape($seoOgImage) ?>" />
    <meta property="og:image:alt" content="<?= $escape($seoOgImageAlt) ?>" />
    <?php if (!empty($articlePublishedTime)): ?>
    <meta property="article:published_time" content="<?= $escape($articlePublishedTime) ?>" />
    <?php endif; ?>
    <?php if (!empty($articleAuthor)): ?>
    <meta property="article:author" content="<?= $escape($articleAuthor) ?>" />
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= $escape($seoOgTitle) ?>" />
    <meta name="twitter:description" content="<?= $escape($seoOgDescription) ?>" />
    <meta name="twitter:image" content="<?= $escape($seoOgImage) ?>" />
    <meta name="twitter:image:alt" content="<?= $escape($seoOgImageAlt) ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?>" />
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>

    <!-- Analytics Tracker (tracks visitors, sessions & UTM campaigns) -->
    <script defer src="<?= BASE_URL ?>/assets/js/tracker.js?v=<?= @filemtime(__DIR__ . '/../assets/js/tracker.js') ?>"></script>

    <!-- Structured Data -->
    <script type="application/ld+json">
    <?= json_encode(
        ['@context' => 'https://schema.org', '@graph' => $schemaGraph],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>
    </script>
</head>
<body>

<!-- Navbar -->
<header class="navbar" role="banner">
    <div class="container navbar-inner">
        <a href="<?= BASE_URL ?>/" class="navbar-logo" aria-label="<?= SITE_NAME ?> - Home">
            <!-- <img src="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" alt="" class="navbar-logo-mark" aria-hidden="true" /> -->
            <img src="<?= BASE_URL ?>/assets/images/Brandlogo.png" alt="" class="navbar-logo-mark" width="836" height="450" aria-hidden="true" />
           <!--  <div class="logo-text ">
                <span class="logo-iuc">IUC</span>
                <span class="logo-edutech">Computers</span>
            </div> -->
        </a>

        <nav class="navbar-links" aria-label="Main navigation">
            <?php foreach ($navLinks as $link): ?>
            <?php if ($link[1] === 'courses'): ?>
            <div class="nav-course-menu">
                <button type="button" class="nav-link nav-course-toggle" aria-expanded="false" aria-controls="desktop-course-list">Courses <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                <div class="nav-course-list" id="desktop-course-list">
                    <a href="<?= BASE_URL ?>/#courses">All Courses</a>
                    <?php foreach ($courses as $menuSlug => $menuCourse): ?>
                    <a href="<?= BASE_URL ?>/course/<?= rawurlencode($menuSlug) ?>"><?= htmlspecialchars($menuCourse['short_title'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/#<?= $link[1] ?>" class="nav-link"><?= $link[0] ?></a>
            <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="navbar-actions">
            <a href="<?= BASE_URL ?>/admin/" class="btn btn-ghost btn-sm" title="Admin Login" rel="nofollow">
                <i class="bi bi-person-gear"></i> <span class="admin-login-label">Admin</span>
            </a>
            <a href="tel:+91<?= SITE_PHONE ?>" class="btn btn-ghost btn-sm">
                <i class="bi bi-telephone"></i> +91 <?= SITE_PHONE ?>
            </a>
            <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-sm">
                Enquire Now <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <button class="mobile-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation" aria-hidden="true">
    <button class="mobile-menu-close" aria-label="Close menu">&times;</button>
    <?php foreach ($navLinks as $link): ?>
    <?php if ($link[1] === 'courses'): ?>
    <details class="mobile-course-menu">
    <summary class="mobile-menu-link">Courses <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
    <div class="mobile-course-list">
        <a href="<?= BASE_URL ?>/#courses" class="mobile-menu-link">All Courses</a>
        <?php foreach ($courses as $menuSlug => $menuCourse): ?>
        <a href="<?= BASE_URL ?>/course/<?= rawurlencode($menuSlug) ?>" class="mobile-menu-link"><?= htmlspecialchars($menuCourse['short_title'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </div>
    </details>
    <?php else: ?>
    <a href="<?= BASE_URL ?>/#<?= $link[1] ?>" class="mobile-menu-link"><?= $link[0] ?></a>
    <?php endif; ?>
    <?php endforeach; ?>
    <a href="tel:+91<?= SITE_PHONE ?>" class="btn btn-ghost btn-lg" style="margin-top:1rem">
        <i class="bi bi-telephone"></i> +91 <?= SITE_PHONE ?>
    </a>
    <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg" style="margin-top:0.5rem">
        Enquire Now <i class="bi bi-arrow-right"></i>
    </a>
</div>

<main>
