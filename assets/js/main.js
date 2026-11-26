/**
 * ArchonDigital — Main Client Interactions (Vanilla JavaScript)
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.getElementById('top-navbar');
    const navCollapseEl = document.getElementById('primaryNavMenu');
    const navLinks = Array.from(document.querySelectorAll('.ad-nav-link[href^="#"]'));
    const backToTopBtn = document.getElementById('back-to-top');
    const videoStage = document.getElementById('video-stage');
    const videoPlayBtn = document.getElementById('video-play-trigger');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* 1. Sticky Navbar Scroll State & Back-to-Top Visibility */
    function handleScroll() {
      const scrollY = window.scrollY || window.pageYOffset;

      if (navbar) {
        if (scrollY > 24) {
          navbar.classList.add('is-scrolled');
        } else {
          navbar.classList.remove('is-scrolled');
        }
      }

      if (backToTopBtn) {
        if (scrollY > 420) {
          backToTopBtn.classList.add('is-visible');
        } else {
          backToTopBtn.classList.remove('is-visible');
        }
      }
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();

    /* 2. Back-to-Top Button Action */
    if (backToTopBtn) {
      backToTopBtn.addEventListener('click', function () {
        window.scrollTo({
          top: 0,
          behavior: prefersReducedMotion ? 'auto' : 'smooth'
        });
      });
    }

    /* 3. Active Navigation Highlighting via IntersectionObserver */
    const sectionTargets = navLinks
      .map(function (link) {
        const targetId = link.getAttribute('href');
        if (!targetId || targetId === '#') return null;
        const section = document.querySelector(targetId);
        return section ? { link: link, section: section, id: targetId } : null;
      })
      .filter(Boolean);

    function setActiveLink(activeId) {
      navLinks.forEach(function (link) {
        const isMatch = link.getAttribute('href') === activeId;
        link.classList.toggle('active', isMatch);
        if (isMatch) {
          link.setAttribute('aria-current', 'page');
        } else {
          link.removeAttribute('aria-current');
        }
      });
    }

    if ('IntersectionObserver' in window && sectionTargets.length > 0) {
      const observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              setActiveLink('#' + entry.target.id);
            }
          });
        },
        {
          rootMargin: '-25% 0px -60% 0px',
          threshold: 0
        }
      );

      sectionTargets.forEach(function (item) {
        observer.observe(item.section);
      });
    }

    /* 4. Mobile Menu Auto-Close on Navigation Click */
    if (navCollapseEl) {
      const allMobileCloseLinks = navCollapseEl.querySelectorAll('a[href^="#"], .btn-ad-primary');
      allMobileCloseLinks.forEach(function (anchor) {
        anchor.addEventListener('click', function () {
          if (window.innerWidth < 992 && navCollapseEl.classList.contains('show')) {
            if (window.bootstrap && window.bootstrap.Collapse) {
              const collapseInstance = window.bootstrap.Collapse.getOrCreateInstance(navCollapseEl);
              collapseInstance.hide();
            } else {
              navCollapseEl.classList.remove('show');
            }
          }
        });
      });
    }

    /* 5. YouTube Shorts Click-to-Play (Thumbnail-First Loading) */
    if (videoStage && videoPlayBtn) {
      videoPlayBtn.addEventListener('click', function () {
        const videoId = videoStage.getAttribute('data-video-id') || 'p29KRuvG0WE';
        const videoTitle = videoStage.getAttribute('data-video-title') || 'Presentasi Website ArchonDigital';

        const iframe = document.createElement('iframe');
        iframe.className = 'ad-video-iframe';
        iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0';
        iframe.title = videoTitle;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.loading = 'eager';

        videoStage.innerHTML = '';
        videoStage.appendChild(iframe);
      });
    }

    /* 6. Image Error Fallback Handler */
    const images = document.querySelectorAll('img[data-fallback-label]');
    images.forEach(function (img) {
      img.addEventListener('error', function () {
        if (img.dataset.fallbackApplied === 'true') return;
        img.dataset.fallbackApplied = 'true';

        const parent = img.parentElement;
        if (!parent) return;

        const fallback = document.createElement('div');
        fallback.className = 'ad-img-fallback';
        fallback.setAttribute('role', 'img');
        fallback.setAttribute('aria-label', img.alt || 'Pratinjau visual ArchonDigital');

        const titleEl = document.createElement('strong');
        titleEl.textContent = img.getAttribute('data-fallback-label') || 'ArchonDigital';
        titleEl.style.color = '#FFFFFF';
        titleEl.style.marginBottom = '0.35rem';

        const subEl = document.createElement('span');
        subEl.textContent = img.alt || 'Pratinjau antarmuka website';

        fallback.appendChild(titleEl);
        fallback.appendChild(subEl);

        img.style.display = 'none';
        parent.appendChild(fallback);
      });
    });
  });
})();
