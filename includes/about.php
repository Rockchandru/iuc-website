<section id="about" class="py-section bg-light" aria-labelledby="about-heading">
    <div class="container">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">About IUC Edu</div>
            <h2 id="about-heading" class="text-display-lg section-title">
                More Than an Institute —<br/>
                <span class="gradient-text">Your Career Partner</span>
            </h2>
            <p class="section-subtitle">Since 1997, we've been transforming careers through industry-aligned education. Our commitment to excellence has made Chennai's most trusted IT training institute.</p>
        </div>

        <div style="display:grid;gap:2.5rem;align-items:center" class="about-grid">
            <div data-aos="fade-right">
                <div style="border-radius:var(--radius-2xl);overflow:hidden;box-shadow:var(--shadow-lg);position:relative;width:100%;max-width:700px" class="about-image-wrap">
                    <img style="width:100%;height:auto;min-height:280px;max-height:700px;object-fit:cover" src="<?= BASE_URL ?>/assets/images/Established1997.jpg" alt="IUC Edu computer education centre established in Chennai in 1997" width="1535" height="1024" loading="lazy" />
                    <div style="position:absolute;bottom:0;left:0;right:0;padding:1.25rem 1.5rem;background:linear-gradient(180deg,transparent,rgba(6,13,31,.85));color:#fff;display:flex;align-items:center;gap:1rem">
                        <div style="width:3rem;height:3rem;border-radius:var(--radius-lg);background:linear-gradient(135deg,var(--clr-primary),var(--clr-accent));display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0"><i class="bi bi-mortarboard"></i></div>
                        <div>
                            <div style="font-weight:700;font-size:0.9375rem">Established 1997 · Chennai</div>
                            <div style="font-size:0.75rem;opacity:0.85">Head Office: C.I.T Nagar, Nandanam</div>
                        </div>
                    </div>
                </div>
            </div>

            <div data-aos="fade-left">
                <p style="font-size:1.0625rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1rem">
                    Founded in 1997 in the heart of Chennai's C.I.T Nagar, IUC Edu began as a focused computer education centre with a single classroom and a clear belief — that practical, job-ready skills matter more than rote learning. Nearly three decades later, that small classroom has grown into a trusted IT training institute with multiple centres across the city, 50+ programs, and a legacy of 25,000+ careers launched.
                </p>
                <p style="font-size:1.0625rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.5rem">
                    Every course we teach is co-designed with hiring partners like TCS, Infosys, Wipro and Amazon, so our graduates step straight from the classroom into the workplace with real projects, live labs and globally recognised certifications. Our curriculum evolves with the industry — because your career deserves more than a syllabus.
                </p>

                <div style="display:grid;gap:1rem;margin-bottom:2rem">
                    <div style="display:flex;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="hover-card">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:var(--clr-primary-light);color:var(--clr-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.125rem"><i class="bi bi-bullseye"></i></div>
                        <div>
                            <h3 style="font-weight:600;font-size:0.9375rem;margin-bottom:0.25rem">Our Mission</h3>
                            <p style="font-size:0.875rem;color:var(--clr-text-secondary);line-height:1.5">Make world-class IT education accessible, practical, and transformative for every learner regardless of background.</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="hover-card">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:var(--clr-accent-light);color:var(--clr-accent-dark);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.125rem"><i class="bi bi-eye"></i></div>
                        <div>
                            <h3 style="font-weight:600;font-size:0.9375rem;margin-bottom:0.25rem">Our Vision</h3>
                            <p style="font-size:0.875rem;color:var(--clr-text-secondary);line-height:1.5">To be the region's most trusted launchpad for tech careers, seamlessly bridging academia and industry.</p>
                        </div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/#courses" class="btn btn-primary">Explore Our Courses <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Stats Strip -->
    <div class="container" style="margin-top:3.5rem">
        <div class="about-stats" data-aos="fade-up">
            <?php
            $stats = [
                ['25000+', 'Graduates Placed', 'bi-people-fill'],
                ['50+',     'IT Programs',      'bi-journal-code'],
                ['98%',     'Placement Rate',   'bi-briefcase-fill'],
                ['300+',    'Hiring Partners',  'bi-buildings-fill'],
                ['28+',     'Years of Service', 'bi-award-fill'],
                ['140%',    'Avg. Salary Hike', 'bi-graph-up-arrow'],
            ];
            foreach ($stats as $s): ?>
            <div class="stat-cell">
                <div class="stat-icon"><i class="bi <?= $s[2] ?>"></i></div>
                <div class="stat-value"><?= $s[0] ?></div>
                <div class="stat-label"><?= $s[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Our Journey -->
    <div class="container" style="margin-top:3.5rem">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Our Journey</div>
            <h2 class="text-display-lg section-title">From One Classroom to <span class="gradient-text">Three Decades of Impact</span></h2>
            <p class="section-subtitle">What began as a single classroom in C.I.T Nagar is today one of Chennai's most trusted names in IT education.</p>
        </div>

        <div class="journey-grid">
            <div class="journey-item" data-aos="fade-up">
                <div class="journey-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <h3>Where We Started</h3>
                <p>Our story began in 1997 at #1&2 Gold Nest Apts, 2nd Main Road, C.I.T Nagar, Chennai — with one classroom, a handful of students, and a mission to make every learner employable through hands-on, practical training.</p>
            </div>
            <div class="journey-item" data-aos="fade-up" data-aos-delay="100">
                <div class="journey-icon"><i class="bi bi-building-fill"></i></div>
                <h3>Growing Across Chennai</h3>
                <p>Driven by demand and trust, we expanded to a second centre in Thiruvottiyur — making quality IT training accessible to learners across the city while keeping the same mentor-led, project-first approach.</p>
            </div>
            <div class="journey-item" data-aos="fade-up" data-aos-delay="200">
                <div class="journey-icon"><i class="bi bi-patch-check-fill"></i></div>
                <h3>Industry Recognition</h3>
                <p>Today we're an ISO certified institute and a Microsoft &amp; Google partner, trusted by 300+ hiring companies including TCS, Infosys, Wipro and Amazon for fresh, job-ready talent.</p>
            </div>
            <div class="journey-item" data-aos="fade-up" data-aos-delay="300">
                <div class="journey-icon"><i class="bi bi-trophy-fill"></i></div>
                <h3>A Legacy of Careers</h3>
                <p>25,000+ graduates, a 98% placement rate and a 140% average salary hike later, our promise remains unchanged — every student who walks through our doors walks out career-ready.</p>
            </div>
        </div>
    </div>

    <!-- Founder -->
    <div class="container" style="margin-top:3.5rem">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">Meet the Founder</div>
            <h2 class="text-display-lg section-title">The Vision Behind <span class="gradient-text">IUC Edu</span></h2>
            <p class="section-subtitle">A mentor first, an entrepreneur second — our founder still leads classrooms personally.</p>
        </div>

        <div class="founder-card" data-aos="fade-up">
            <div class="founder-photo">
                <img src="<?= BASE_URL ?>/assets/images/mentors/Tamilselvi.png" alt="Tamil Selvi S — Founder and lead instructor at IUC Edu" width="465" height="536" loading="lazy" />
            </div>
            <div class="founder-info">
                <span class="founder-tag"><i class="bi bi-star-fill"></i> Founder &amp; Lead Instructor</span>
                <h3>Tamil Selvi S</h3>
                <p class="founder-line">IUC Edu · 20+ Years in Technology Education</p>
                <p style="font-size:0.9375rem;color:var(--clr-text-secondary);line-height:1.7">
                    Tamil Selvi founded IUC Edu in 1997 with a simple conviction — that theoretical degrees alone don't build careers, skills do. With over two decades of experience across full-stack development and cloud technologies, she has personally mentored thousands of students, designed 12+ flagship programs, and built a faculty culture where every mentor invests personally in every student's growth.
                </p>
                <p style="font-size:0.9375rem;color:var(--clr-text-secondary);line-height:1.7">
                    She still teaches, reviews curriculum with industry experts, and leads the institute's mission to make world-class IT education accessible to every learner in Chennai.
                </p>
                <div class="founder-chips">
                    <span class="chip"><i class="bi bi-patch-check-fill"></i> Full Stack &amp; Cloud</span>
                    <span class="chip"><i class="bi bi-mortarboard-fill"></i> 20+ Years Experience</span>
                    <span class="chip"><i class="bi bi-book-fill"></i> 12+ Programs Designed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mentors -->
    <div class="container" style="margin-top:3.5rem">
        <div class="section-header section-header-center" data-aos="fade-up">
            <div class="section-label">World-Class Faculty</div>
            <h2 class="text-display-lg section-title">Learn From <span class="gradient-text">Industry Experts</span></h2>
            <p class="section-subtitle">Our mentors bring decades of real-world experience from the world's top technology companies.</p>
        </div>

        <div class="mentor-grid">
            <?php
            $mentors = [
                ['Tamilselvi.png',     'Tamil Selvi S',       'Founder &amp; Lead Instructor', 'Full Stack &amp; Cloud',   'Full Stack &amp; Cloud',    '20+ Years', '12+ Courses'],
                ['Kannan_Advisor.jpg', 'Kannan',              'Senior Data Science Mentor',    'AI/ML &amp; Python',       'AI/ML · Python',           '12 Years',  '8+ Courses'],
                ['Suresh.jpg', 'Suresh',   'Digital Marketing Expert',          'Digital Marketing', 'Digital Marketing · Content Strategy',     '15 Years',  '6+ Courses'],
                ['Kanniappan.jpg',     'Kanniappan',          'UI/UX Design Lead',             'Design Systems &amp; Figma', 'Design Systems · Figma',   '10 Years',  '5+ Courses'],
            ];
            foreach ($mentors as $m): ?>
            <article class="mentor-card" data-aos="fade-up">
                <div class="mentor-photo">
                    <img src="<?= BASE_URL ?>/assets/images/mentors/<?= $m[0] ?>" alt="<?= htmlspecialchars($m[1]) ?> — IUC Edu mentor" loading="lazy" />
                </div>
                <h3 class="mentor-name"><?= $m[1] ?></h3>
                <p class="mentor-role"><?= $m[2] ?></p>
                <span class="mentor-badge"><i class="bi bi-patch-check-fill"></i> <?= $m[3] ?></span>
                <div class="mentor-stats">
                    <div>
                        <div class="ms-value"><?= $m[5] ?></div>
                        <div class="ms-label">Experience</div>
                    </div>
                    <div class="ms-divider"></div>
                    <div>
                        <div class="ms-value"><?= $m[6] ?></div>
                        <div class="ms-label">Programs Taught</div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CTA -->
    <div class="container" style="margin-top:3.5rem">
        <div class="about-cta" data-aos="fade-up">
            <div>
                <h2 style="font-size:clamp(1.5rem,3vw,2.25rem);font-weight:700;line-height:1.2;margin-bottom:0.5rem;color:#fff">Ready to Start Your Journey?</h2>
                <p style="font-size:1rem;opacity:0.9">Join 25,000+ graduates who chose IUC Edu and transformed their careers.</p>
            </div>
            <a href="<?= BASE_URL ?>/#contact" class="btn btn-primary btn-lg">Get Free Counselling <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<style>
    @media (min-width: 768px) {
        .about-grid { grid-template-columns: 1fr 1fr; }
    }
    .hover-card:hover { border-color: var(--clr-primary); box-shadow: var(--shadow-sm); transform: translateX(4px); }

    .about-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    @media (min-width: 768px) { .about-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 1024px) { .about-stats { grid-template-columns: repeat(6, 1fr); } }
    .stat-cell { position: relative; text-align: center; padding: 1.5rem 1rem; background: var(--clr-white); border: 1px solid var(--clr-border); border-radius: var(--radius-2xl); box-shadow: var(--shadow-sm); overflow: hidden; transition: transform var(--transition-base), box-shadow var(--transition-base); }
    .stat-cell::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--clr-primary), var(--clr-accent)); }
    .stat-cell:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .stat-icon { width: 2.75rem; height: 2.75rem; margin: 0 auto 0.75rem; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--clr-primary), var(--clr-accent)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 12px rgba(29,78,216,0.25); }
    .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1.1; color: var(--clr-primary-dark); font-family: var(--font-heading); }
    .stat-label { font-size: 0.75rem; color: var(--clr-text-muted); margin-top: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em; }

    .journey-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; margin-top: 2.5rem; }
    @media (min-width: 768px) { .journey-grid { grid-template-columns: 1fr 1fr; } }
    @media (min-width: 1024px) { .journey-grid { grid-template-columns: repeat(4, 1fr); } }
    .journey-item { padding: 1.5rem; border-radius: var(--radius-2xl); background: var(--clr-white); border: 1px solid var(--clr-border); box-shadow: var(--shadow-sm); transition: transform var(--transition-base), box-shadow var(--transition-base); }
    .journey-item:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .journey-icon { width: 3rem; height: 3rem; border-radius: var(--radius-lg); background: var(--clr-primary-light); color: var(--clr-primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 1rem; }
    .journey-item h3 { font-size: 1.0625rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--clr-text); }
    .journey-item p { font-size: 0.875rem; color: var(--clr-text-secondary); line-height: 1.6; }

    .founder-card { display: grid; grid-template-columns: 1fr; gap: 2rem; padding: 2rem; border-radius: var(--radius-2xl); background: var(--clr-white); border: 1px solid var(--clr-border); box-shadow: var(--shadow-lg); }
    @media (min-width: 768px) { .founder-card { grid-template-columns: 280px 1fr; align-items: center; } }
    .founder-photo { border-radius: var(--radius-2xl); overflow: hidden; box-shadow: var(--shadow-md); }
    .founder-photo img { width: 100%; height: 100%; max-height: 380px; object-fit: cover; }
    .founder-tag { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--clr-primary); background: var(--clr-primary-light); padding: 0.4rem 0.8rem; border-radius: 999px; margin-bottom: 0.75rem; }
    .founder-info h3 { font-size: 1.75rem; font-weight: 800; color: var(--clr-text); margin-bottom: 0.25rem; font-family: var(--font-heading); }
    .founder-line { font-size: 0.875rem; color: var(--clr-accent-dark); font-weight: 600; margin-bottom: 1rem; }
    .founder-chips { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-top: 1.25rem; }
    .chip { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8125rem; font-weight: 600; color: var(--clr-text-secondary); background: var(--clr-bg-light); border: 1px solid var(--clr-border); padding: 0.5rem 0.9rem; border-radius: 999px; }
    .chip i { color: var(--clr-accent); }

    .mentor-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-top: 2.5rem; }
    @media (min-width: 640px) { .mentor-grid { grid-template-columns: 1fr 1fr; } }
    @media (min-width: 1024px) { .mentor-grid { grid-template-columns: repeat(4, 1fr); } }
    .mentor-card { text-align: center; padding: 1.75rem 1.5rem; border-radius: var(--radius-2xl); background: var(--clr-white); border: 1px solid var(--clr-border); box-shadow: var(--shadow-sm); transition: transform var(--transition-base), box-shadow var(--transition-base); }
    .mentor-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .mentor-photo { width: 6.5rem; height: 6.5rem; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; border: 3px solid var(--clr-primary-light); box-shadow: var(--shadow-sm); }
    .mentor-photo img { width: 100%; height: 100%; object-fit: cover; }
    .mentor-name { font-size: 1.0625rem; font-weight: 700; color: var(--clr-text); margin-bottom: 0.25rem; }
    .mentor-role { font-size: 0.8125rem; color: var(--clr-text-secondary); margin-bottom: 0.75rem; }
    .mentor-badge { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--clr-primary); background: var(--clr-primary-light); padding: 0.3rem 0.7rem; border-radius: 999px; margin-bottom: 1rem; }
    .mentor-stats { display: flex; align-items: center; justify-content: center; gap: 1.25rem; border-top: 1px solid var(--clr-border); padding-top: 1rem; }
    .ms-value { font-size: 1.0625rem; font-weight: 800; color: var(--clr-primary); font-family: var(--font-heading); }
    .ms-label { font-size: 0.6875rem; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
    .ms-divider { width: 1px; height: 2rem; background: var(--clr-border); }

    .about-cta { display: flex; flex-direction: column; align-items: center; gap: 1.5rem; text-align: center; padding: 3rem 2rem; border-radius: var(--radius-2xl); background: linear-gradient(135deg, var(--clr-primary-dark), var(--clr-primary), var(--clr-accent)); color: #fff; box-shadow: var(--shadow-xl); }
    @media (min-width: 768px) { .about-cta { flex-direction: row; justify-content: space-between; text-align: left; } }
</style>
