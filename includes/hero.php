<section id="home" class="hero" aria-label="Hero">
    <div class="hero-bg-shape" aria-hidden="true"></div>
    <div class="hero-bg-shape-2" aria-hidden="true"></div>

    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <div class="hero-badge fade-in">
                    <span class="hero-badge-dot"></span>
                    New Batches Starting Soon — Limited Seats
                </div>

                <h1 class="text-display-xl hero-title fade-in">
                    Launch Your<br/>
                    <span class="gradient-text">Dream Tech Career</span><br/>
                    with IUC Edu
                </h1>

                <p class="hero-description fade-in">
                    Chennai's most trusted IT institute since 1997 — delivering industry-aligned programs with
                    expert mentors, live projects, and <strong>98% placement assistance</strong>
                    for 25,000+ graduates.
                </p>

                <div class="hero-actions fade-in">
                    <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg" data-track-event="admission">
                        Get Free Counselling <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/#courses" class="btn btn-outline btn-lg">
                        <i class="bi bi-play-circle"></i> Explore Courses
                    </a>
                    <a href="#" class="btn btn-ghost btn-lg">
                        <i class="bi bi-download"></i> Brochure
                    </a>
                </div>

                <div class="hero-trust fade-in">
                    <div class="avatar-group">
                        <?php $avatars = ['1531123897727-8f129e1688ce','1573497019940-1c28c88b4f3e','1635402689379-545b134e58ed','1519085360753-af0119f7cbe7','1560250097-0b93528c311a']; ?>
                        <?php foreach ($avatars as $aid): ?>
                        <img src="https://images.unsplash.com/photo-<?= $aid ?>?w=64&q=80" alt="Student" class="avatar-group-item" loading="lazy" />
                        <?php endforeach; ?>
                        <span class="avatar-group-more">+</span>
                    </div>
                    <div>
                        <div style="color:var(--clr-warning);font-size:0.875rem">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <span style="font-size:0.8125rem;color:var(--clr-text-secondary)">
                            <strong style="color:var(--clr-text)">4.9/5</strong> from 10,000+ reviews
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.375rem;font-size:0.8125rem;color:var(--clr-text-secondary)">
                        <i class="bi bi-patch-check" style="color:var(--clr-primary)"></i>
                        ISO Certified
                    </div>
                </div>
            </div>

            <div class="hero-image-wrapper">
                <div class="hero-image">
                    <img src="assets/images/about-education.jpg" alt="Students at IUC Edu" loading="eager" />
                </div>
                <div class="hero-float-card hero-float-card-1" data-aos="fade-right" data-aos-delay="300">
                    <div class="hero-float-icon" style="background:var(--clr-accent-light);color:var(--clr-accent-dark)">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <div class="hero-float-value">98%</div>
                        <div class="hero-float-label">Placement Rate</div>
                    </div>
                </div>
                <div class="hero-float-card hero-float-card-2" data-aos="fade-left" data-aos-delay="500">
                    <div class="hero-float-icon" style="background:var(--clr-primary-light);color:var(--clr-primary)">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="hero-float-value">25k+</div>
                        <div class="hero-float-label">Graduates</div>
                    </div>
                </div>
                <div class="hero-float-card hero-float-card-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="hero-float-icon" style="background:#fef3c7;color:#d97706">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <div class="hero-float-value">4.9</div>
                        <div class="hero-float-label">Student Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted Companies Bar -->
<section style="padding:2.5rem 0;background:var(--clr-bg-section);border-top:1px solid var(--clr-border);border-bottom:1px solid var(--clr-border)" aria-label="Hiring partners">
    <div class="container">
        <p class="text-label" style="text-align:center;margin-bottom:1.5rem">Our Graduates Are Hired By</p>
        <div class="ticker-wrap" data-aos="fade-in">
            <div class="ticker-track" style="animation-duration:25s">
                <?php for ($j = 0; $j < 2; $j++): ?>
                <?php foreach ($companies as $c): ?>
                <div class="ticker-item">
                    <span style="font-family:var(--font-heading);font-weight:700;font-size:1.125rem;color:var(--clr-text-muted);white-space:nowrap"><?= $c ?></span>
                </div>
                <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section style="padding:3rem 0;background:var(--clr-white)" aria-label="Key statistics">
    <div class="container">
        <div class="grid-6">
            <?php foreach ($stats as $s): ?>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="<?= array_search($s, $stats) * 80 ?>">
                <div class="stat-icon"><i class="<?= $s['icon'] ?>"></i></div>
                <div class="stat-number" data-counter="<?= $s['value'] ?>" data-suffix="<?= $s['suffix'] ?>"><?= $s['value'] ?><?= $s['suffix'] ?></div>
                <div class="stat-label"><?= $s['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
