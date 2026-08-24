/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Main JavaScript v3.0
   ═══════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  /* ── 1. Navbar scroll ──────────────────────────────────── */
  const navbar = document.querySelector('.navbar');
  let lastScroll = 0;

  const handleNavbar = () => {
    const scrollY = window.scrollY;
    if (scrollY > 60) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    lastScroll = scrollY;
  };

  window.addEventListener('scroll', handleNavbar, { passive: true });
  handleNavbar();

  /* ── 2. Mobile menu ────────────────────────────────────── */
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileMenu = document.querySelector('.mobile-menu');

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      const isOpen = mobileMenu.classList.toggle('open');
      mobileToggle.classList.toggle('open', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
      mobileToggle.setAttribute('aria-expanded', isOpen);
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        mobileToggle.classList.remove('open');
        document.body.style.overflow = '';
        mobileToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ── 3. Scroll animations (Intersection Observer) ──────── */
  const animateEls = document.querySelectorAll(
    '.fade-in, .fade-in-left, .fade-in-right, .fade-in-scale'
  );

  const animObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          animObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1, rootMargin: '0px 0px -30px 0px' }
  );

  animateEls.forEach(el => animObserver.observe(el));

  /* ── 4. Counter animation ──────────────────────────────── */
  const counters = document.querySelectorAll('[data-counter]');

  const animateCounter = (el) => {
    const target = parseInt(el.dataset.counter, 10);
    const suffix = el.dataset.suffix || '';
    const prefix = el.dataset.prefix || '';
    const duration = 1800;
    const step = 16;
    const increment = target / (duration / step);
    let current = 0;

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = prefix + Math.floor(current).toLocaleString('en-IN') + suffix;
    }, step);
  };

  const counterObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.5 }
  );

  counters.forEach(c => counterObserver.observe(c));

  /* ── 5. Progress bar animation ─────────────────────────── */
  const progressBars = document.querySelectorAll('[data-progress]');

  const progressObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const target = entry.target.dataset.progress || '0';
          entry.target.style.width = target + '%';
          progressObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.3 }
  );

  progressBars.forEach(bar => {
    bar.style.width = '0%';
    if (bar.dataset.progress) {
      progressObserver.observe(bar);
    }
  });

  /* ── 6. Accordion (single open) ────────────────────────── */
  document.querySelectorAll('.accordion-item details').forEach(detail => {
    detail.addEventListener('toggle', function () {
      if (this.open) {
        document.querySelectorAll('.accordion-item details').forEach(other => {
          if (other !== this) other.open = false;
        });
      }
    });
  });

  /* ── 7. Module toggle ──────────────────────────────────── */
  document.querySelectorAll('.module-header').forEach(header => {
    header.addEventListener('click', function () {
      const item = this.parentElement;
      const isOpen = item.classList.contains('open');

      // Close all other modules
      item.parentElement.querySelectorAll('.module-item').forEach(other => {
        if (other !== item) other.classList.remove('open');
      });

      item.classList.toggle('open');
    });
  });

  /* ── 8. Back to top button ─────────────────────────────── */
  const backToTop = document.querySelector('.back-to-top');

  if (backToTop) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 400) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    }, { passive: true });

    backToTop.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ── 9. Smooth anchor scroll ───────────────────────────── */
  document.querySelectorAll('a[href^="#"], a[href^="/#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      const targetId = href.startsWith('/#') ? href.substring(1) : href;
      if (targetId === '#' || targetId === '' || targetId === '/#') return;
      const target = document.querySelector(targetId);
      if (!target) return;
      // Only intercept if the target is on the current page
      if (window.location.pathname === '/' || window.location.pathname === '/index.php' || window.location.pathname === BASE_URL + '/' || window.location.pathname === BASE_URL + '/index.php') {
        e.preventDefault();
        const offset = 80;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
        // Update URL without reload
        if (history.pushState) {
          history.pushState(null, '', targetId);
        }
      }
    });
  });

  /* ── 10. Course filter tabs ────────────────────────────── */
  const filterBtns = document.querySelectorAll('[data-filter]');
  const filterItems = document.querySelectorAll('[data-filter-item]');

  if (filterBtns.length && filterItems.length) {
    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const filter = btn.dataset.filter;

        filterBtns.forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');

        filterItems.forEach(item => {
          const cats = (item.dataset.filterItem || '').split(' ');
          if (filter === 'all' || cats.includes(filter)) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  }

  /* ── 11. Pricing toggle ────────────────────────────────── */
  const pricingSwitch = document.querySelector('.pricing-switch');

  if (pricingSwitch) {
    pricingSwitch.addEventListener('click', () => {
      const isActive = pricingSwitch.classList.toggle('active');
      document.querySelectorAll('.pricing-toggle-label').forEach(lbl => {
        lbl.classList.toggle('active', isActive);
      });
      document.querySelectorAll('[data-pricing]').forEach(el => {
        el.style.display = isActive ? 'none' : '';
      });
      document.querySelectorAll('[data-pricing-yearly]').forEach(el => {
        el.style.display = isActive ? '' : 'none';
      });
    });
  }

  /* ── 12. Active nav link on scroll ─────────────────────── */
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  const activateNav = () => {
    let current = '';
    sections.forEach(sec => {
      if (window.scrollY >= sec.offsetTop - 120) {
        current = sec.id;
      }
    });
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === '#' + current) {
        link.classList.add('active');
      }
    });
  };

  if (sections.length && navLinks.length) {
    window.addEventListener('scroll', activateNav, { passive: true });
  }

  /* ── 13. Stagger animation children ────────────────────── */
  document.querySelectorAll('.stagger-children').forEach(wrapper => {
    wrapper.querySelectorAll(':scope > *').forEach((el, i) => {
      el.style.transitionDelay = (i * 80) + 'ms';
    });
  });

  /* ── 14. Form validation ───────────────────────────────── */
  const forms = document.querySelectorAll('form[data-validate]');

  forms.forEach(form => {
    form.addEventListener('submit', function (e) {
      let valid = true;
      this.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          field.classList.add('error');
          valid = false;
        } else {
          field.classList.remove('error');
        }
      });
      if (!valid) e.preventDefault();
    });

    form.querySelectorAll('.form-input').forEach(field => {
      field.addEventListener('input', () => {
        field.classList.remove('error');
      });
    });
  });

  /* ── 15. Countdown timer ───────────────────────────────── */
  const countdownEl = document.getElementById('countdown');

  if (countdownEl) {
    const target = new Date(countdownEl.dataset.target).getTime();

    const updateCountdown = () => {
      const now = Date.now();
      const diff = target - now;

      if (diff <= 0) {
        document.querySelectorAll('[data-cd]').forEach(el => {
          el.textContent = '00';
        });
        return;
      }

      const days = Math.floor(diff / 86400000);
      const hrs = Math.floor((diff % 86400000) / 3600000);
      const mins = Math.floor((diff % 3600000) / 60000);
      const secs = Math.floor((diff % 60000) / 1000);

      const cd = { days, hrs, mins, secs };
      Object.keys(cd).forEach(key => {
        const el = document.querySelector(`[data-cd="${key}"]`);
        if (el) el.textContent = String(cd[key]).padStart(2, '0');
      });
    };

    updateCountdown();
    setInterval(updateCountdown, 1000);
  }

  /* ── 16. Newsletter form ───────────────────────────────── */
  const newsletterForm = document.querySelector('.newsletter-form');

  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const input = this.querySelector('input');
      const btn = this.querySelector('button');
      if (!input.value.trim()) return;

      btn.textContent = 'Subscribed!';
      btn.disabled = true;
      input.value = '';

      setTimeout(() => {
        btn.textContent = 'Subscribe';
        btn.disabled = false;
      }, 3000);
    });
  }

  /* ── 17. Image lazy load fallback ──────────────────────── */
  if ('loading' in HTMLImageElement.prototype === false) {
    document.querySelectorAll('img[loading="lazy"]').forEach(img => {
      img.src = img.dataset.src || img.src;
    });
  }

  /* ── 18. Testimonial carousel auto-scroll (if .testimonial-scroll) ── */
  const testimonialScroll = document.querySelector('.testimonial-scroll');

  if (testimonialScroll) {
    let scrollPos = 0;
    setInterval(() => {
      const maxScroll = testimonialScroll.scrollWidth - testimonialScroll.clientWidth;
      scrollPos += 340;
      if (scrollPos > maxScroll) scrollPos = 0;
      testimonialScroll.scrollTo({ left: scrollPos, behavior: 'smooth' });
    }, 4000);
  }

  /* ── 19. Animate on page load for hero elements ────────── */
  document.querySelectorAll('.hero-content .fade-in').forEach((el, i) => {
    el.style.transitionDelay = (i * 150) + 'ms';
    setTimeout(() => el.classList.add('visible'), 100);
  });

  /* ── 20. Track outbound links ──────────────────────────── */
  document.querySelectorAll('a[target="_blank"]').forEach(link => {
    link.addEventListener('click', () => {
      if (typeof gtag !== 'undefined') {
        gtag('event', 'click', {
          event_category: 'outbound',
          event_label: link.href,
        });
      }
    });
  });

});
