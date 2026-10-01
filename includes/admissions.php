<section id="admissions" class="py-section bg-white" aria-labelledby="admissions-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Admissions</div>
            <h2 id="admissions-heading" class="text-display-lg section-title">
                How to <span class="gradient-text">Enroll</span>
            </h2>
            <p class="section-subtitle">Simple 3-step admission process to start your tech career journey at IUC Edu.</p>
        </div>

        <div style="display:grid;gap:1.5rem" class="admissions-grid" data-aos="fade-up">
            <div class="admission-step">
                <div class="admission-step-num">01</div>
                <div class="admission-step-icon"><i class="bi bi-chat-dots"></i></div>
                <h3 class="admission-step-title">Free Career Counselling</h3>
                <p class="admission-step-desc">Talk to our expert career counsellors who will understand your background, goals, and recommend the right program for you.</p>
            </div>
            <div class="admission-step">
                <div class="admission-step-num">02</div>
                <div class="admission-step-icon"><i class="bi bi-file-earmark-text"></i></div>
                <h3 class="admission-step-title">Enroll & Pay Fees</h3>
                <p class="admission-step-desc">Complete your enrollment with our simple online registration. Choose from zero-cost EMI options, scholarships, or upfront payment.</p>
            </div>
            <div class="admission-step">
                <div class="admission-step-num">03</div>
                <div class="admission-step-icon"><i class="bi bi-laptop"></i></div>
                <h3 class="admission-step-title">Start Learning</h3>
                <p class="admission-step-desc">Get immediate access to our learning portal, class recordings, and join your batch. Begin your transformation from Day 1.</p>
            </div>
        </div>

        <div style="text-align:center;margin-top:2.5rem" data-aos="fade-up">
            <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg apply-now-trigger" data-track-event="admission">Apply Now <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<style>
    .admissions-grid { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .admissions-grid { grid-template-columns: repeat(3, 1fr); } }
    .admission-step {
        text-align: center;
        padding: 2rem 1.5rem;
        border-radius: var(--radius-2xl);
        background: var(--clr-white);
        border: 1px solid var(--clr-border);
        position: relative;
        transition: all var(--transition-base);
    }
    .admission-step:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-card-hover);
        border-color: var(--clr-primary);
    }
    .admission-step-num {
        font-family: var(--font-heading);
        font-size: 3rem;
        font-weight: 800;
        color: var(--clr-primary-light);
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .admission-step-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: var(--radius-xl);
        background: var(--clr-primary-light);
        color: var(--clr-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin: 0 auto 1rem;
    }
    .admission-step-title {
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 1.0625rem;
        margin-bottom: 0.5rem;
    }
    .admission-step-desc {
        font-size: 0.875rem;
        color: var(--clr-text-secondary);
        line-height: 1.6;
    }
</style>
