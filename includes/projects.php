<section id="projects" class="py-section bg-white" aria-labelledby="projects-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Live Projects</div>
            <h2 id="projects-heading" class="text-display-lg section-title">
                Real-World <span class="gradient-text">Projects</span>
            </h2>
            <p class="section-subtitle">Build production-grade applications that showcase your skills to employers. Click any project to explore the full build — tools, features and professional outcomes.</p>
        </div>

        <div style="display:grid;gap:1.5rem" class="projects-grid">
            <?php foreach ($projectDetails as $p): ?>
            <article class="project-card" data-aos="fade-up" data-project="<?= $p['id'] ?>">
                <div class="project-media">
                    <img src="<?= $p['image'] ?>" alt="<?= $p['title'] ?> project overview" loading="lazy" class="project-img" />
                    <div class="project-media-overlay"></div>
                    <span class="project-domain"><i class="bi bi-broadcast"></i> <?= $p['domain'] ?></span>
                </div>
                <div class="project-info">
                    <h3 class="project-title"><?= $p['title'] ?></h3>
                    <p class="project-desc"><?= $p['tagline'] ?></p>
                    <div class="project-techs">
                        <?php foreach (array_slice($p['tools'], 0, 4) as $t): ?>
                        <span><?= $t ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-actions">
                        <a href="<?= BASE_URL ?>/course/<?= $p['course'] ?>" class="project-btn project-btn-ghost">View Course</a>
                        <button type="button" class="project-btn project-btn-solid project-detail-btn" data-project="<?= $p['id'] ?>">
                            Explore Details <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Project Detail Modal -->
<div class="project-modal" id="projectModal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="project-modal-backdrop" data-pm-close></div>
    <div class="project-modal-dialog" role="document">
        <button type="button" class="project-modal-close" data-pm-close aria-label="Close details">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="project-modal-body" id="projectModalBody"></div>
    </div>
</div>

<style>
    .projects-grid { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .projects-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { .projects-grid { grid-template-columns: repeat(3, 1fr); } }

    .project-card {
        display: flex;
        flex-direction: column;
        border-radius: var(--radius-xl);
        border: 1px solid var(--clr-border);
        overflow: hidden;
        transition: all var(--transition-base);
        background: var(--clr-white);
    }
    .project-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-card-hover);
        border-color: var(--clr-primary);
    }
    .project-media {
        position: relative;
        width: 100%;
        height: 190px;
        overflow: hidden;
        background: var(--clr-bg-section);
    }
    .project-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform var(--transition-slow);
    }
    .project-card:hover .project-img { transform: scale(1.05); }
    .project-media-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15,23,42,0) 40%, rgba(15,23,42,0.55) 100%);
    }
    .project-domain {
        position: absolute;
        left: 0.875rem;
        bottom: 0.75rem;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #fff;
        background: rgba(15,23,42,0.55);
        border: 1px solid rgba(255,255,255,0.25);
        padding: 0.25rem 0.625rem;
        border-radius: var(--radius-full);
        backdrop-filter: blur(4px);
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .project-info {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .project-title {
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 1.0625rem;
        margin-bottom: 0.375rem;
    }
    .project-desc {
        font-size: 0.8125rem;
        color: var(--clr-text-secondary);
        line-height: 1.6;
        margin-bottom: 0.75rem;
        flex: 1;
    }
    .project-techs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        margin-bottom: 1rem;
    }
    .project-techs span {
        font-size: 0.6875rem;
        font-weight: 500;
        padding: 0.25rem 0.625rem;
        border-radius: var(--radius-full);
        background: var(--clr-bg-section);
        color: var(--clr-text-secondary);
        border: 1px solid var(--clr-border);
    }
    .project-actions {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        flex-wrap: wrap;
    }
    .project-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        font-family: var(--font-button);
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.5625rem 0.9375rem;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: none;
    }
    .project-btn-ghost {
        background: var(--clr-white);
        color: var(--clr-text);
        border-color: var(--clr-border);
    }
    .project-btn-ghost:hover {
        border-color: var(--clr-primary);
        color: var(--clr-primary);
        background: var(--clr-primary-light);
    }
    .project-btn-solid {
        background: var(--clr-primary);
        color: #fff;
        box-shadow: var(--shadow-button);
    }
    .project-btn-solid:hover {
        background: var(--clr-primary-dark);
        box-shadow: var(--shadow-button-hover);
    }

    /* ── Modal ─────────────────────────────────────────── */
    .project-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        visibility: hidden;
        transition: opacity var(--transition-base), visibility var(--transition-base);
    }
    .project-modal.open { opacity: 1; visibility: visible; }
    .project-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15,23,42,0.65);
        backdrop-filter: blur(3px);
    }
    .project-modal-dialog {
        position: relative;
        width: 100%;
        max-width: 820px;
        max-height: 88vh;
        overflow-y: auto;
        background: var(--clr-white);
        border-radius: var(--radius-2xl);
        box-shadow: var(--shadow-xl);
        transform: translateY(24px) scale(0.98);
        transition: transform var(--transition-spring);
        -webkit-overflow-scrolling: touch;
    }
    .project-modal.open .project-modal-dialog {
        transform: translateY(0) scale(1);
    }
    .project-modal-close {
        position: sticky;
        top: 0;
        margin-left: auto;
        margin-right: 0.75rem;
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        border: 1px solid var(--clr-border);
        background: var(--clr-white);
        color: var(--clr-text);
        font-size: 1rem;
        cursor: pointer;
        z-index: 2;
        transition: all var(--transition-base);
    }
    .project-modal-close:hover {
        background: var(--clr-danger);
        border-color: var(--clr-danger);
        color: #fff;
    }

    .pm-hero {
        position: relative;
        height: 220px;
        overflow: hidden;
    }
    .pm-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .pm-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15,23,42,0.15) 0%, rgba(15,23,42,0.75) 100%);
    }
    .pm-hero-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #fff;
        background: rgba(15,23,42,0.5);
        border: 1px solid rgba(255,255,255,0.25);
        padding: 0.3rem 0.75rem;
        border-radius: var(--radius-full);
        backdrop-filter: blur(4px);
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .pm-hero-title {
        position: absolute;
        left: 1.5rem;
        right: 1.5rem;
        bottom: 1.25rem;
        color: #fff;
    }
    .pm-hero-title h3 {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1.5rem;
        margin: 0 0 0.25rem;
    }
    .pm-hero-title p {
        font-size: 0.875rem;
        opacity: 0.9;
        margin: 0;
    }
    .pm-content { padding: 1.5rem; }
    .pm-section { margin-bottom: 1.5rem; }
    .pm-section:last-child { margin-bottom: 0; }
    .pm-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--clr-text);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .pm-label i { color: var(--clr-primary); }
    .pm-overview {
        font-size: 0.875rem;
        line-height: 1.7;
        color: var(--clr-text-secondary);
        margin: 0;
    }
    .pm-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .pm-tool {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.375rem 0.8125rem;
        border-radius: var(--radius-full);
        background: var(--clr-primary-light);
        color: var(--clr-primary);
        border: 1px solid rgba(29,78,216,0.2);
    }
    .pm-tool i { font-size: 0.8125rem; }
    .pm-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.625rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    @media (min-width: 640px) { .pm-list { grid-template-columns: repeat(2, 1fr); } }
    .pm-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.8125rem;
        line-height: 1.55;
        color: var(--clr-text);
        background: var(--clr-bg-section);
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-md);
        padding: 0.625rem 0.8125rem;
    }
    .pm-list li i {
        color: var(--clr-success);
        font-size: 0.875rem;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }
    .pm-outcome li i { color: var(--clr-accent); }
    .pm-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 1.125rem 1.5rem;
        border-top: 1px solid var(--clr-border);
        background: var(--clr-bg-section);
        border-radius: 0 0 var(--radius-2xl) var(--radius-2xl);
    }
    .pm-footer-text { font-size: 0.8125rem; color: var(--clr-text-secondary); }
    .pm-footer-text strong { color: var(--clr-text); }
    .pm-footer-cta {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-family: var(--font-button);
        font-weight: 600;
        font-size: 0.875rem;
        color: #fff;
        background: var(--clr-primary);
        padding: 0.625rem 1.25rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        box-shadow: var(--shadow-button);
        transition: all var(--transition-base);
    }
    .pm-footer-cta:hover {
        background: var(--clr-primary-dark);
        box-shadow: var(--shadow-button-hover);
    }

    body.modal-open { overflow: hidden; }
</style>

<script>
(function () {
    var data = <?= json_encode($projectDetails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var modal = document.getElementById('projectModal');
    var body = document.getElementById('projectModalBody');

    function openModal(id) {
        var p = data[id];
        if (!p) return;
        body.innerHTML = [
            '<div class="pm-hero">',
                '<img src="' + p.image + '" alt="' + p.title + ' overview" />',
                '<div class="pm-hero-overlay"></div>',
                '<span class="pm-hero-badge"><i class="bi bi-broadcast"></i> Live Project</span>',
                '<div class="pm-hero-title">',
                    '<h3>' + p.title + '</h3>',
                    '<p>' + p.tagline + '</p>',
                '</div>',
            '</div>',
            '<div class="pm-content">',
                '<div class="pm-section">',
                    '<div class="pm-label"><i class="bi bi-info-circle"></i> Project Overview</div>',
                    '<p class="pm-overview">' + p.overview + '</p>',
                '</div>',
                '<div class="pm-section">',
                    '<div class="pm-label"><i class="bi bi-tools"></i> Tools & Technologies Used</div>',
                    '<div class="pm-tools">' + p.tools.map(function (t) {
                        return '<span class="pm-tool"><i class="bi bi-dot"></i>' + t + '</span>';
                    }).join('') + '</div>',
                '</div>',
                '<div class="pm-section">',
                    '<div class="pm-label"><i class="bi bi-gear-wide-connected"></i> Key Features</div>',
                    '<ul class="pm-list">' + p.features.map(function (f) {
                        return '<li><i class="bi bi-check-circle-fill"></i>' + f + '</li>';
                    }).join('') + '</ul>',
                '</div>',
                '<div class="pm-section">',
                    '<div class="pm-label"><i class="bi bi-award"></i> Professional Outcomes</div>',
                    '<ul class="pm-list pm-outcome">' + p.outcomes.map(function (o) {
                        return '<li><i class="bi bi-stars"></i>' + o + '</li>';
                    }).join('') + '</ul>',
                '</div>',
            '</div>',
            '<div class="pm-footer">',
                '<span class="pm-footer-text">Learn to build this with <strong>' + p.course_name + '</strong></span>',
                '<a class="pm-footer-cta" href="' + BASE_URL + '/course/' + p.course + '">Join This Program <i class="bi bi-arrow-right"></i></a>',
            '</div>'
        ].join('');
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-project]');
        if (trigger) {
            openModal(trigger.getAttribute('data-project'));
            return;
        }
        if (e.target.closest('[data-pm-close]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });
})();
</script>
