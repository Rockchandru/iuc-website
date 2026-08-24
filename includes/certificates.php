<section id="certificates" class="py-section bg-light" aria-labelledby="certificates-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Certification</div>
            <h2 id="certificates-heading" class="text-display-lg section-title">
                Get Industry-<span class="gradient-text">Recognized Certificates</span>
            </h2>
            <p class="section-subtitle">Earn globally recognized certifications that validate your skills and boost your career prospects.</p>
        </div>

        <div style="display:grid;gap:2rem;align-items:start" class="cert-grid" data-aos="fade-up">
            <div style="text-align:center">
                <div class="cert-sample">
                    <div class="cert-sample-inner">
                        <div class="cert-sample-badge">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <div class="cert-sample-title"><?= SITE_NAME ?></div>
                        <div class="cert-sample-sub">Certificate of Completion</div>
                        <div class="cert-sample-line"></div>
                        <div class="cert-sample-name">John Doe</div>
                        <div class="cert-sample-course">Artificial Intelligence & Machine Learning</div>
                        <div class="cert-sample-date">Completed: January 2026</div>
                        <div class="cert-sample-footer">Verified & Recognized by Industry Partners</div>
                    </div>
                </div>
            </div>

            <div>
                <h3 style="font-family:var(--font-heading);font-weight:600;font-size:1.125rem;margin-bottom:1rem">Certifications We Prepare You For</h3>
                <div style="display:grid;gap:0.75rem">
                    <div class="cert-item">
                        <div class="cert-item-icon"><i class="bi bi-microsoft"></i></div>
                        <div>
                            <div class="cert-item-title">Microsoft Certified</div>
                            <div class="cert-item-desc">Azure, Power Platform, AI-900, DP-900</div>
                        </div>
                    </div>
                    <div class="cert-item">
                        <div class="cert-item-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-google"></i></div>
                        <div>
                            <div class="cert-item-title">Google Certified</div>
                            <div class="cert-item-desc">Google Cloud, Digital Marketing, Analytics</div>
                        </div>
                    </div>
                    <div class="cert-item">
                        <div class="cert-item-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-cloud"></i></div>
                        <div>
                            <div class="cert-item-title">AWS Certified</div>
                            <div class="cert-item-desc">Solutions Architect, Cloud Practitioner, DevOps</div>
                        </div>
                    </div>
                    <div class="cert-item">
                        <div class="cert-item-icon" style="background:#fce7f3;color:#be185d"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <div class="cert-item-title">EC-Council CEH</div>
                            <div class="cert-item-desc">Certified Ethical Hacker, CompTIA Security+</div>
                        </div>
                    </div>
                    <div class="cert-item">
                        <div class="cert-item-icon" style="background:#e0e7ff;color:#3730a3"><i class="bi bi-filetype-java"></i></div>
                        <div>
                            <div class="cert-item-title">Oracle Java Certified</div>
                            <div class="cert-item-desc">Java SE 8/11/17, Spring Professional</div>
                        </div>
                    </div>
                </div>
                <p style="font-size:0.875rem;color:var(--clr-text-secondary);margin-top:1rem;line-height:1.6">
                    <i class="bi bi-check-circle" style="color:var(--clr-accent)"></i> All our programs include certification exam preparation. Our students have a 94% first-attempt pass rate.
                </p>
            </div>
        </div>
    </div>
</section>

<style>
    .cert-grid { grid-template-columns: 1fr; }
    @media (min-width: 768px) { .cert-grid { grid-template-columns: 1fr 1fr; } }
    .cert-sample {
        display: inline-block;
        padding: 0.5rem;
        border-radius: var(--radius-2xl);
        background: linear-gradient(135deg, var(--clr-primary), var(--clr-accent));
        max-width: 380px;
        width: 100%;
    }
    .cert-sample-inner {
        background: #fff;
        border-radius: calc(var(--radius-2xl) - 0.25rem);
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
    }
    .cert-sample-badge {
        font-size: 2.5rem;
        color: var(--clr-primary);
        margin-bottom: 0.75rem;
    }
    .cert-sample-title {
        font-family: var(--font-heading);
        font-weight: 800;
        font-size: 1.125rem;
        color: var(--clr-secondary);
    }
    .cert-sample-sub {
        font-size: 0.75rem;
        color: var(--clr-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
    }
    .cert-sample-line {
        width: 3rem;
        height: 3px;
        border-radius: 3px;
        background: var(--clr-primary-gradient);
        margin: 0 auto 1rem;
    }
    .cert-sample-name {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--clr-secondary);
        margin-bottom: 0.25rem;
    }
    .cert-sample-course {
        font-size: 0.8125rem;
        color: var(--clr-text-secondary);
        margin-bottom: 0.75rem;
    }
    .cert-sample-date {
        font-size: 0.75rem;
        color: var(--clr-text-muted);
        margin-bottom: 1.25rem;
    }
    .cert-sample-footer {
        font-size: 0.6875rem;
        color: var(--clr-accent-dark);
        font-weight: 600;
        border-top: 1px solid var(--clr-border);
        padding-top: 1rem;
    }
    .cert-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        border-radius: var(--radius-lg);
        background: var(--clr-white);
        border: 1px solid var(--clr-border);
        transition: all var(--transition-fast);
    }
    .cert-item:hover { border-color: var(--clr-primary); }
    .cert-item-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        background: var(--clr-primary-light);
        color: var(--clr-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1rem;
    }
    .cert-item-title { font-weight: 600; font-size: 0.875rem; }
    .cert-item-desc { font-size: 0.75rem; color: var(--clr-text-secondary); }
</style>
