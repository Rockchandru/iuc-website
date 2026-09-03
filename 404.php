<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = 'Page Not Found – IUC Edu';
$metaDesc = 'The requested page could not be found. Explore IUC Edu courses or contact our Chennai training centre for assistance.';
$robotsMeta = 'noindex, follow';
$canonical = false;
require __DIR__ . '/includes/header.php';
?>
<div class="container" style="padding:8rem 0;text-align:center">
    <h1 class="text-display-xl" style="color:var(--clr-primary);margin-bottom:0.5rem">404</h1>
    <h2 class="text-display-md" style="margin-bottom:1rem">Page Not Found</h2>
    <p style="font-size:1.0625rem;color:var(--clr-text-secondary);margin-bottom:2rem">The page you're looking for doesn't exist or has been moved.</p>
    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:center">
        <a href="<?= BASE_URL ?>/" class="btn btn-primary btn-lg">Back to Home</a>
        <a href="<?= BASE_URL ?>/#contact" class="btn btn-outline btn-lg">Contact Us</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
