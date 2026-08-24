<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $pageTitle ?? SITE_NAME . ' – Premier IT Training Institute in Chennai' ?></title>
    <meta name="description" content="<?= $metaDesc ?? 'IUC Edu – Premier IT training institute in Chennai. 50+ programs in AI/ML, Full Stack, Data Science, Cyber Security, Cloud Computing & Digital Marketing with 98% placement support.' ?>" />
    <meta name="keywords" content="IT training institute Chennai, programming courses, AI ML course, data science course, full stack development, cyber security training, digital marketing course, IUC Edu" />
    <link rel="canonical" href="<?= SITE_URL ?>/<?= $canonical ?? '' ?>" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" />

    <!-- Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= SITE_URL ?>/<?= $canonical ?? '' ?>" />
    <meta property="og:title" content="<?= $ogTitle ?? SITE_NAME . ' – Launch Your Tech Career' ?>" />
    <meta property="og:description" content="<?= $ogDesc ?? '50+ IT programs with 98% placement support. Expert mentors & globally recognized certifications.' ?>" />
    <meta property="og:image" content="<?= $ogImage ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80' ?>" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= $ogTitle ?? SITE_NAME . ' – Launch Your Tech Career' ?>" />
    <meta name="twitter:description" content="<?= $ogDesc ?? 'Premier IT training with 98% placement support.' ?>" />
    <meta name="twitter:image" content="<?= $ogImage ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80' ?>" />

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
    <script src="<?= BASE_URL ?>/assets/js/tracker.js?v=<?= @filemtime(__DIR__ . '/../assets/js/tracker.js') ?>"></script>

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "IUC Edu",
        "url": "<?= SITE_URL ?>",
        "description": "Premier IT training institute in Chennai with 50+ programs and 98% placement support.",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "#1&2 Gold Nest Apts, 2nd Main Road, C.I.T Nagar",
            "addressLocality": "Chennai",
            "postalCode": "600035",
            "addressCountry": "IN"
        },
        "telephone": "+91<?= SITE_PHONE ?>",
        "email": "<?= SITE_EMAIL ?>",
        "sameAs": ["<?= FACEBOOK_URL ?>", "<?= INSTAGRAM_URL ?>", "<?= LINKEDIN_URL ?>"],
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "IT Training Programs",
            "itemListElement": [
                <?php $ci = 0; foreach(array_slice($courses, 0, 12) as $courseSlug => $courseEntry): ?>
                {"@type": "Course", "name": "<?= $courseEntry['title'] ?>"}<?= ++$ci < min(12, count($courses)) ? ',' : '' ?>
                <?php endforeach; ?>
            ]
        }
    }
    </script>
</head>
<body>

<!-- Navbar -->
<header class="navbar" role="banner">
    <div class="container navbar-inner">
        <a href="<?= BASE_URL ?>/" class="navbar-logo" aria-label="<?= SITE_NAME ?> - Home">
            <!-- <img src="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" alt="" class="navbar-logo-mark" aria-hidden="true" /> -->
            <img src="<?= BASE_URL ?>/assets/images/Brandlogo.png" alt="" class="navbar-logo-mark" aria-hidden="true" />
           <!--  <div class="logo-text ">
                <span class="logo-iuc">IUC</span>
                <span class="logo-edutech">Computers</span>
            </div> -->
        </a>

        <nav class="navbar-links" aria-label="Main navigation">
            <?php foreach ($navLinks as $link): ?>
            <a href="<?= BASE_URL ?>/#<?= $link[1] ?>" class="nav-link"><?= $link[0] ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="navbar-actions">
            <a href="<?= BASE_URL ?>/admin/" class="btn btn-ghost btn-sm" title="Admin Login">
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
<div class="mobile-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
    <button class="mobile-menu-close" aria-label="Close menu">&times;</button>
    <?php foreach ($navLinks as $link): ?>
    <a href="<?= BASE_URL ?>/#<?= $link[1] ?>" class="mobile-menu-link"><?= $link[0] ?></a>
    <?php endforeach; ?>
    <a href="tel:+91<?= SITE_PHONE ?>" class="btn btn-ghost btn-lg" style="margin-top:1rem">
        <i class="bi bi-telephone"></i> +91 <?= SITE_PHONE ?>
    </a>
    <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg" style="margin-top:0.5rem">
        Enquire Now <i class="bi bi-arrow-right"></i>
    </a>
</div>

<main>
