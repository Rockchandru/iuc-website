<?php
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', $_GET['slug']) : '';
$post = null;
foreach ($blogPosts as $p) {
    if ($p['slug'] === $slug) { $post = $p; break; }
}

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Blog Post Not Found – IUC Edu';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center"><h1>Post Not Found</h1><p style="color:var(--clr-text-secondary);margin:1rem 0">The blog post you\'re looking for doesn\'t exist.</p><a href="<?= BASE_URL ?>/" class="btn btn-primary">Back to Home</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' – IUC Edu Blog';
$metaDesc = $post['excerpt'];
$ogTitle = $post['title'] . ' – IUC Edu Blog';
$ogDesc = $post['excerpt'];
$ogImage = $post['image'];
$canonical = 'blog/' . $slug;

require __DIR__ . '/includes/header.php';
?>

<section class="py-section bg-light" style="padding-top:8rem">
    <div class="container-narrow">
        <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:1.5rem">
            <a href="<?= BASE_URL ?>/" style="color:var(--clr-primary)">Home</a>
            <span style="margin:0 0.5rem;color:var(--clr-text-muted)">/</span>
            <a href="<?= BASE_URL ?>/#blog" style="color:var(--clr-primary)">Blog</a>
            <span style="margin:0 0.5rem;color:var(--clr-text-muted)">/</span>
            <span style="color:var(--clr-text-secondary)"><?= $post['title'] ?></span>
        </nav>

        <article data-aos="fade-up">
            <div style="border-radius:var(--radius-2xl);overflow:hidden;margin-bottom:2rem">
                <img src="<?= $post['image'] ?>" alt="<?= $post['title'] ?>" style="width:100%;height:400px;object-fit:cover" loading="eager" />
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

            <div style="font-size:1rem;line-height:1.8;color:var(--clr-text-secondary)">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>

            <div style="margin-top:3rem;padding:2rem;border-radius:var(--radius-2xl);background:var(--clr-bg-section);text-align:center">
                <h3 style="font-family:var(--font-heading);font-weight:600;font-size:1.125rem;margin-bottom:0.75rem">Ready to Start Your Tech Journey?</h3>
                <p style="font-size:0.9375rem;color:var(--clr-text-secondary);margin-bottom:1.25rem">Get expert training and placement support at IUC Edu.</p>
                <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary">Enquire Now <i class="bi bi-arrow-right"></i></a>
            </div>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
