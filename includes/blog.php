<section id="blog" class="py-section bg-white" aria-labelledby="blog-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Our Blog</div>
            <h2 id="blog-heading" class="text-display-lg section-title">
                Latest <span class="gradient-text">Articles</span>
            </h2>
            <p class="section-subtitle">Stay updated with the latest in technology, career guidance, and industry trends.</p>
        </div>

        <div style="display:grid;gap:1.5rem" class="blog-grid">
            <?php foreach ($blogPosts as $i => $post): ?>
            <article class="blog-card" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="blog-card-image">
                    <img src="<?= $post['image'] ?>" alt="<?= $post['title'] ?>" loading="lazy" />
                </div>
                <div class="blog-card-body">
                    <span class="blog-card-category"><?= $post['category'] ?></span>
                    <h3 class="blog-card-title"><?= $post['title'] ?></h3>
                    <p class="blog-card-excerpt"><?= $post['excerpt'] ?></p>
                    <div class="blog-card-meta">
                        <span><i class="bi bi-calendar3"></i> <?= $post['date'] ?></span>
                        <a href="<?= BASE_URL ?>/blog/<?= $post['slug'] ?>" style="color:var(--clr-primary);margin-left:auto;font-weight:600">
                            Read More <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:2rem" data-aos="fade-up">
            <a href="<?= BASE_URL ?>/#blog" class="btn btn-outline">View All Articles <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<style>
    .blog-grid { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .blog-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .blog-grid { grid-template-columns: repeat(3, 1fr); } }
</style>
