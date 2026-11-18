/* Header menus: mobile toggle, one dropdown open at a time, close on outside click or Escape. */
(function () {
    var burger = document.querySelector('[data-sg-burger]');
    var nav = document.querySelector('[data-sg-nav]');
    if (burger && nav) {
        burger.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
    var menus = document.querySelectorAll('.sg-dd');
    menus.forEach(function (menu) {
        menu.addEventListener('toggle', function () {
            if (!menu.open) return;
            menus.forEach(function (other) { if (other !== menu) other.open = false; });
        });
    });
    /* Desktop with a mouse: open a dropdown on hover, close it shortly after the pointer leaves.
       Touch, keyboard and the collapsed (burger) menu keep the click behaviour. */
    var hoverQuery = window.matchMedia ? window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 1180px)') : null;
    var header = document.querySelector('.sg-header');
    var canHover = function () { return hoverQuery && hoverQuery.matches && !(header && header.classList.contains('sg-nav-collapsed')); };
    menus.forEach(function (menu) {
        var closeTimer;
        menu.addEventListener('mouseenter', function () {
            if (!canHover()) return;
            clearTimeout(closeTimer);
            if (!menu.open) { menu.open = true; menu.dataset.hoverOpened = String(Date.now()); }
        });
        menu.addEventListener('mouseleave', function () {
            if (!canHover() || !menu.dataset.hoverOpened) return;
            closeTimer = setTimeout(function () { menu.open = false; delete menu.dataset.hoverOpened; }, 180);
        });
        var summary = menu.querySelector('summary');
        if (summary) {
            summary.addEventListener('click', function (event) {
                // A click right after hover-opening would immediately close the menu again; keep it open instead.
                if (canHover() && menu.open && menu.dataset.hoverOpened && Date.now() - Number(menu.dataset.hoverOpened) < 600) {
                    event.preventDefault();
                }
                delete menu.dataset.hoverOpened;
            });
        }
    });
    document.addEventListener('click', function (event) {
        menus.forEach(function (menu) { if (menu.open && !menu.contains(event.target)) menu.open = false; });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') menus.forEach(function (menu) { menu.open = false; });
    });
})();

/* Fit the desktop menu into one row, however many items the admin menu has:
   1. try the tighter layout (.sg-nav-tight);
   2. move items from the end into the "More" dropdown until the row fits;
   3. only if even that fails, collapse into the burger.
   Phones and tablets (where CSS already shows the burger) keep the full list.
   Re-checked on resize and after fonts load. */
(function () {
    var header = document.querySelector('.sg-header');
    var inner = header ? header.querySelector('.sg-header-inner') : null;
    var nav = header ? header.querySelector('[data-sg-nav]') : null;
    var burger = header ? header.querySelector('[data-sg-burger]') : null;
    if (!header || !inner || !nav) return;
    var more = nav.querySelector('[data-sg-more]');
    var moreMenu = more ? more.querySelector('[data-sg-more-menu]') : null;
    var overflows = function () { return inner.scrollWidth > inner.clientWidth + 1; };
    var candidates = function () {
        return Array.prototype.filter.call(nav.children, function (element) {
            return element !== more && !element.classList.contains('sg-nav-actions') && !element.classList.contains('sg-dd-mega');
        });
    };
    var copyInto = function (item, target) {
        var fragment = document.createDocumentFragment();
        var summary = item.querySelector(':scope > summary');
        if (summary) {
            var group = document.createElement('p');
            group.className = 'sg-more-group';
            group.textContent = summary.textContent.trim();
            fragment.appendChild(group);
            item.querySelectorAll('.sg-dd-menu a').forEach(function (link) { fragment.appendChild(link.cloneNode(true)); });
        } else {
            fragment.appendChild(item.cloneNode(true));
        }
        target.insertBefore(fragment, target.firstChild);
    };
    var reset = function () {
        header.classList.remove('sg-nav-collapsed', 'sg-nav-tight');
        candidates().forEach(function (element) { element.classList.remove('sg-overflowed'); });
        if (more) { more.hidden = true; more.open = false; moreMenu.innerHTML = ''; }
    };
    var smallScreen = window.matchMedia ? window.matchMedia('(max-width: 1179px)') : null;
    var fitted = false;
    var fit = function () {
        if (smallScreen && smallScreen.matches) {
            if (fitted) { reset(); fitted = false; }
            return;
        }
        fitted = true;
        reset();
        if (burger && getComputedStyle(burger).display !== 'none') return;
        if (!overflows()) return;
        header.classList.add('sg-nav-tight');
        if (!overflows()) return;
        if (more) {
            more.hidden = false;
            var visible = candidates();
            // Keep at least the first item in the row; move the rest from the end.
            for (var index = visible.length - 1; index >= 1 && overflows(); index--) {
                visible[index].classList.add('sg-overflowed');
                copyInto(visible[index], moreMenu);
            }
            if (!overflows()) return;
            reset();
            header.classList.add('sg-nav-tight');
        }
        header.classList.add('sg-nav-collapsed');
    };
    var timer;
    var frame = 0;
    var schedule = function () {
        if (frame) return;
        frame = window.requestAnimationFrame(function () { frame = 0; fit(); });
    };
    window.addEventListener('resize', function () { clearTimeout(timer); timer = setTimeout(schedule, 120); });
    // Web fonts (Bengali especially) can arrive after the first check and widen the labels.
    if (document.fonts) {
        if (document.fonts.ready) document.fonts.ready.then(schedule);
        if (document.fonts.addEventListener) document.fonts.addEventListener('loadingdone', schedule);
    }
    window.addEventListener('load', schedule);
    fit();
})();

/* Duplicate-submission guard: once a POST form is submitted, its submit buttons are
   disabled and show a "Sending…" label, so a double click cannot send it twice. */
(function () {
    var label = document.body.getAttribute('data-sending-label') || 'Sending…';
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || (form.method || '').toLowerCase() !== 'post' || event.defaultPrevented) return;
        if (form.dataset.submitted === '1') { event.preventDefault(); return; }
        form.dataset.submitted = '1';
        form.setAttribute('aria-busy', 'true');
        // Wait one tick so the browser has already collected the clicked button's name and value.
        setTimeout(function () {
            form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach(function (button) {
                button.disabled = true;
                if (button.tagName === 'BUTTON') { button.dataset.label = button.textContent; button.textContent = label; }
            });
        }, 0);
    });
    // Restore buttons when the page is shown again from the back/forward cache.
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) return;
        document.querySelectorAll('form[data-submitted="1"]').forEach(function (form) {
            form.dataset.submitted = ''; form.removeAttribute('aria-busy');
            form.querySelectorAll('button[disabled], input[type="submit"][disabled]').forEach(function (button) {
                button.disabled = false; if (button.dataset.label) button.textContent = button.dataset.label;
            });
        });
    });
})();

/* Study in Singapore: filter the course list by level, and print only the document checklist. */
(function () {
    var filter = document.querySelector('[data-course-filter]');
    if (filter) {
        var buttons = filter.querySelectorAll('button[data-level]');
        var courses = document.querySelectorAll('[data-course-level]');
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var level = button.getAttribute('data-level');
                buttons.forEach(function (other) { other.setAttribute('aria-pressed', other === button ? 'true' : 'false'); });
                courses.forEach(function (course) { course.hidden = level !== '' && course.getAttribute('data-course-level') !== level; });
            });
        });
    }
    document.querySelectorAll('[data-print-checklist]').forEach(function (button) {
        button.addEventListener('click', function () {
            document.body.classList.add('v2-print-checklist');
            window.print();
            setTimeout(function () { document.body.classList.remove('v2-print-checklist'); }, 500);
        });
    });
})();

/* Header gets a deeper shadow once the page scrolls, so it reads as floating above content. */
(function () {
    var bar = document.querySelector('.sg-site-header');
    if (!bar) return;
    var ticking = false;
    var update = function () { bar.classList.toggle('is-scrolled', window.scrollY > 8); ticking = false; };
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; window.requestAnimationFrame(update); } }, { passive: true });
    // First check once the page has loaded (reading scrollY earlier would force an extra layout).
    if (document.readyState === 'complete') { update(); } else { window.addEventListener('load', update); }
})();

/* Scroll reveal: section headings, cards and steps below the fold fade up as they enter the
   viewport, siblings slightly staggered. Skipped when the visitor prefers reduced motion. */
(function () {
    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var selector = [
        '.v2-section .v2-head', '.v2-section .v2-head-center', '.v2-services > *', '.v2-timeline > li',
        '.v2-costs > *', '.v2-guides > *', '.v2-facts > *', '.v2-fact-tiles > *', '.v2-team > *', '.v2-stories > *',
        '.v2-news > *', '.v2-values > *', '.v2-trust li', '.v2-cta-band', '.v2-reqs > *', '.v2-steps-list > li',
        '.v2-directory > *', '.v2-course', '.v2-contact-card', '.v2-section .v2-card', '.v2-living > *', '.v2-split > *',
        '.sg-section .sg-card', '.sg-steps > *', '.sg-tiles > *'
    ].join(',');
    // IntersectionObserver reports after the browser's own layout, so nothing here forces a layout:
    // its first report says which blocks start on screen (left alone) and which start below (hidden, then revealed).
    var seen = typeof WeakSet === 'function' ? new WeakSet() : null;
    if (!seen) return;
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            var element = entry.target;
            if (!seen.has(element)) {
                seen.add(element);
                if (entry.isIntersecting || entry.boundingClientRect.top < 0 || (element.parentElement && element.parentElement.closest('.egh-reveal'))) {
                    observer.unobserve(element);
                    return;
                }
                var position = element.parentElement ? Array.prototype.indexOf.call(element.parentElement.children, element) : 0;
                element.style.setProperty('--egh-reveal-delay', (Math.min(position, 5) * 0.08) + 's');
                element.classList.add('egh-reveal');
                return;
            }
            if (!entry.isIntersecting) return;
            observer.unobserve(element);
            element.classList.add('is-visible');
            // Hand transitions back to the element's own hover styles once it has arrived.
            setTimeout(function () {
                element.classList.remove('egh-reveal', 'is-visible');
                element.style.removeProperty('--egh-reveal-delay');
            }, 1400);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    document.querySelectorAll(selector).forEach(function (element) { observer.observe(element); });
})();
