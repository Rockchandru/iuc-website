<?php
$branches = [
    [
        'name'    => 'Head Office',
        'tag'     => 'CIT Nagar · Nandanam',
        'address' => SITE_ADDRESS,
        'query'   => 'Gold Nest Apartments, 2nd Main Road, C.I.T Nagar, Nandanam, Chennai, Tamil Nadu 600035',
        'icon'    => 'bi-buildings',
        'primary' => true,
    ],
    [
        'name'    => 'Thiruvottiyur Branch',
        'tag'     => 'Kaladipet · Near Rajakadai',
        'address' => '19/11, Balakrishna Colony 1st Street, Near Rajakadai, Kaladipet, Thiruvottiyur, Chennai – 600 019',
        'query'   => 'IUC Computers, 19/11, Balakrishna Colony 1st Street, Near Rajakadai, Kaladipet, Thiruvottiyur, Chennai, Tamil Nadu 600019',
        'icon'    => 'bi-geo-alt',
        'primary' => false,
    ],
];
foreach ($branches as &$b) {
    $b['mapsUrl']  = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($b['query']);
    $b['embedUrl'] = 'https://maps.google.com/maps?q=' . rawurlencode($b['query']) . '&t=&z=16&ie=UTF8&iwloc=&output=embed';
}
unset($b);
?>

<section id="contact" class="py-section bg-light" aria-labelledby="contact-heading">
    <div class="container">
        <div style="display:grid;gap:2.5rem" class="contact-grid">
            <!-- Left: Info -->
            <div data-aos="fade-right">
                <div class="section-label">Get in Touch</div>
                <h2 id="contact-heading" class="text-display-lg section-title">
                    Start Your <span class="gradient-text">Journey Today</span>
                </h2>
                <p style="font-size:1rem;color:var(--clr-text-secondary);line-height:1.7;margin-bottom:1.5rem">
                    Fill in your details and our expert career counsellor will reach out within 24 hours to guide you toward the right program — completely free, zero pressure.
                </p>

                <div style="display:grid;gap:0.75rem">
                    <a href="tel:+91<?= SITE_PHONE ?>" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="contact-item">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:#d1fae5;color:#065f46;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem"><i class="bi bi-telephone"></i></div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--clr-text-muted);margin-bottom:0.125rem">Mobile</div>
                            <div style="font-weight:600;font-size:0.9375rem">+91 <?= SITE_PHONE ?></div>
                        </div>
                    </a>
                    <a href="tel:<?= HEAD_OFFICE_LANDLINE ?>" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="contact-item">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:var(--clr-primary-light);color:var(--clr-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem"><i class="bi bi-telephone-outbound"></i></div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--clr-text-muted);margin-bottom:0.125rem">Head Office Landline</div>
                            <div style="font-weight:600;font-size:0.9375rem"><?= HEAD_OFFICE_LANDLINE ?></div>
                        </div>
                    </a>
                    <a href="tel:<?= BRANCH_LANDLINE ?>" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="contact-item">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem"><i class="bi bi-telephone-outbound"></i></div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--clr-text-muted);margin-bottom:0.125rem">Branch Office Landline</div>
                            <div style="font-weight:600;font-size:0.9375rem"><?= BRANCH_LANDLINE ?></div>
                        </div>
                    </a>
                    <a href="mailto:<?= SITE_EMAIL ?>" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="contact-item">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:var(--clr-primary-light);color:var(--clr-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem"><i class="bi bi-envelope"></i></div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--clr-text-muted);margin-bottom:0.125rem">Email</div>
                            <div style="font-weight:600;font-size:0.9375rem"><?= SITE_EMAIL ?></div>
                        </div>
                    </a>
                    <?php foreach ($branches as $branch): ?>
                    <a href="<?= $branch['mapsUrl'] ?>" target="_blank" rel="noopener" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border-radius:var(--radius-lg);background:var(--clr-white);border:1px solid var(--clr-border);transition:all var(--transition-base)" class="contact-item">
                        <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius-md);background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem"><i class="bi <?= $branch['icon'] ?>"></i></div>
                        <div>
                            <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--clr-text-muted);margin-bottom:0.125rem"><?= $branch['primary'] ? 'Head Office' : 'Branch' ?> · <?= $branch['tag'] ?></div>
                            <div style="font-weight:500;font-size:0.875rem;line-height:1.5"><?= $branch['address'] ?></div>
                        </div>
                        <i class="bi bi-arrow-up-right" style="margin-left:auto;color:var(--clr-primary)"></i>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- WhatsApp CTA -->
                <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hi!%20I'm%20interested%20in%20IUC%20Edu%20programs." target="_blank" rel="noopener" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;border-radius:var(--radius-lg);background:linear-gradient(135deg,#25D366,#128C7E);color:#fff;margin-top:1rem;transition:all var(--transition-base)" class="wa-cta">
                    <i class="bi bi-whatsapp" style="font-size:1.5rem"></i>
                    <div>
                        <div style="font-weight:700">Chat on WhatsApp</div>
                        <div style="font-size:0.8125rem;opacity:0.9">Instant responses · 9 AM to 9 PM</div>
                    </div>
                    <i class="bi bi-arrow-right" style="margin-left:auto;font-size:1.125rem"></i>
                </a>
            </div>

            <!-- Right: Form -->
            <div class="contact-form-card" style="padding:2rem;border-radius:var(--radius-2xl);background:var(--clr-white);border:1px solid var(--clr-border);box-shadow:var(--shadow-xl)" data-aos="fade-left">
                <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:0.5rem">Get Free Career Counselling</h3>
                <p style="font-size:0.875rem;color:var(--clr-text-secondary);margin-bottom:1.5rem">Fill in the form below. We'll call you within 24 hours.</p>

                <?php if ($formSuccess): ?>
                <div class="enquiry-thank-you-message" style="padding:1rem;border-radius:var(--radius-lg);background:#d1fae5;color:#065f46;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;font-weight:500">
                    <i class="bi bi-check-circle-fill" style="font-size:1.25rem"></i> Thank you! We'll contact you within 24 hours.
                </div>
                <?php elseif ($formError): ?>
                <div style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#991b1b;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;font-weight:500">
                    <i class="bi bi-exclamation-circle-fill" style="font-size:1.25rem"></i> <?= htmlspecialchars($formError) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/#contact" data-validate>
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                    <input type="hidden" name="enquiry_request_key" value="<?= htmlspecialchars(bin2hex(random_bytes(16)), ENT_QUOTES, 'UTF-8') ?>" />
                    <input type="hidden" name="page_url" value="" />
                    <input type="hidden" name="landing_page" value="" />
                    <input type="hidden" name="referrer" value="" />
                    <input type="hidden" name="visitor_id" value="" />
                    <input type="hidden" name="session_id" value="" />
                    <input type="hidden" name="utm_source" value="" />
                    <input type="hidden" name="utm_medium" value="" />
                    <input type="hidden" name="utm_campaign" value="" />
                    <input type="hidden" name="utm_content" value="" />
                    <input type="hidden" name="utm_term" value="" />

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Rohith Kumar" required />
                    </div>

                    <div style="display:grid;gap:1rem" class="form-row">
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-input" placeholder="+91 XXXXX XXXXX" required />
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="course-interest" class="form-label">Course Interest *</label>
                        <select id="course-interest" name="course" class="form-input form-select" required>
                            <option value="" disabled selected>Select a program…</option>
                            <?php foreach ($courses as $slug => $course): ?>
                            <option value="<?= $course['title'] ?>"><?= $course['title'] ?></option>
                            <?php endforeach; ?>
                            <option value="Other">Other / Not Sure Yet</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message (Optional)</label>
                        <textarea id="message" name="message" class="form-input" placeholder="Any specific questions or requirements…" rows="3"></textarea>
                    </div>

                    <div class="form-group enquiry-whatsapp-consent">
                        <label>
                            <input type="checkbox" name="whatsapp_opt_in" value="1" required />
                            <span>Send my requested course information and enquiry follow-up to this phone number on WhatsApp.</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Security Verification *</label>
                        <div class="application-captcha">
                            <input type="text" name="captcha" class="form-input" placeholder="Enter code" inputmode="numeric" required maxlength="4" />
                            <strong aria-label="Security code"><?= htmlspecialchars((string) $_SESSION['captcha'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </div>

                    <button type="submit" name="contact_submit" class="btn btn-primary btn-lg btn-block">
                        Get Free Counselling <i class="bi bi-arrow-right"></i>
                    </button>

                    <div style="display:flex;align-items:center;justify-content:center;gap:1.5rem;margin-top:1rem;font-size:0.75rem;color:var(--clr-text-muted)">
                        <span><i class="bi bi-shield-check"></i> 256-bit SSL secured</span>
                        <span><i class="bi bi-lock"></i> Data never shared</span>
                        <span><i class="bi bi-clock"></i> Reply within 24 hrs</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Locations -->
    <div class="container" style="margin-top:3.5rem">
        <div style="text-align:center;margin-bottom:2.5rem" data-aos="fade-up">
            <div class="section-label">Visit Us</div>
            <h2 class="text-display-lg section-title" style="margin-bottom:0.5rem">
                Our <span class="gradient-text">Centers</span>
            </h2>
            <p style="font-size:1rem;color:var(--clr-text-secondary);line-height:1.7;max-width:640px;margin:0 auto">
                Walk in at either of our Chennai centers — tap a map to open the exact location in Google Maps.
            </p>
        </div>

        <div class="locations-grid">
            <?php foreach ($branches as $branch): ?>
            <article class="branch-card" data-aos="fade-up" data-aos-delay="100">
                <div class="branch-head">
                    <div class="branch-icon"><i class="bi <?= $branch['icon'] ?>"></i></div>
                    <div>
                        <div class="branch-label"><?= $branch['primary'] ? 'Head Office' : 'Sub Branch' ?> · <?= $branch['tag'] ?></div>
                        <h3><?= $branch['name'] ?></h3>
                    </div>
                    <?php if ($branch['primary']): ?>
                    <span class="branch-badge"><i class="bi bi-star-fill"></i> Main</span>
                    <?php endif; ?>
                </div>

                <a href="<?= $branch['mapsUrl'] ?>" target="_blank" rel="noopener" class="branch-map" aria-label="Open <?= $branch['name'] ?> location in Google Maps">
                    <iframe src="<?= $branch['embedUrl'] ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?= $branch['name'] ?> location map" tabindex="-1" aria-hidden="true"></iframe>
                    <span class="map-overlay">
                        <span class="directions-pill"><i class="bi bi-geo-alt-fill"></i> Get Directions</span>
                    </span>
                </a>

                <div class="branch-body">
                    <p class="branch-address"><i class="bi bi-pin-map"></i> <?= $branch['address'] ?></p>
                    <a href="<?= $branch['mapsUrl'] ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                        <i class="bi bi-box-arrow-up-right"></i> Open in Google Maps
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .contact-grid { grid-template-columns: 1fr; }
    @media (min-width: 768px) {
        .contact-grid { grid-template-columns: 1fr 1fr; }
    }
    .form-row { grid-template-columns: 1fr; }
    @media (min-width: 640px) { .form-row { grid-template-columns: 1fr 1fr; } }
    .contact-item:hover { border-color: var(--clr-primary); box-shadow: var(--shadow-sm); transform: translateX(4px); }
    .wa-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.3); }

    .locations-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
    @media (min-width: 992px) { .locations-grid { grid-template-columns: 1fr 1fr; } }

    .branch-card { border-radius: var(--radius-2xl); background: var(--clr-white); border: 1px solid var(--clr-border); overflow: hidden; box-shadow: var(--shadow-md); transition: transform var(--transition-base), box-shadow var(--transition-base); }
    .branch-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-xl); }
    .branch-head { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 1.5rem 1.25rem; }
    .branch-icon { width: 3rem; height: 3rem; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #fff; background: linear-gradient(135deg, var(--clr-primary), var(--clr-accent)); flex-shrink: 0; }
    .branch-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--clr-accent); }
    .branch-head h3 { font-size: 1.125rem; font-weight: 700; margin: 0; color: var(--clr-text); }
    .branch-badge { margin-left: auto; display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--clr-primary); background: var(--clr-primary-light); padding: 0.3rem 0.6rem; border-radius: 999px; }
    .branch-map { position: relative; display: block; height: 280px; overflow: hidden; }
    .branch-map iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; pointer-events: none; }
    .map-overlay { position: absolute; inset: 0; display: flex; align-items: flex-end; justify-content: center; padding: 1.25rem; background: linear-gradient(180deg, rgba(15,23,42,0) 45%, rgba(15,23,42,0.6) 100%); }
    .directions-pill { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--clr-primary); color: #fff; padding: 0.65rem 1.2rem; border-radius: 999px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 4px 14px rgba(29,78,216,0.45); transition: transform var(--transition-base), background var(--transition-base); }
    .branch-map:hover .directions-pill, .branch-map:focus-visible .directions-pill { transform: translateY(-3px); background: var(--clr-primary-dark); }
    .branch-body { padding: 1.25rem 1.5rem 1.5rem; }
    .branch-address { display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: var(--clr-text-secondary); line-height: 1.6; margin-bottom: 1rem; }
    .branch-address i { color: var(--clr-primary); margin-top: 0.15rem; }
</style>
