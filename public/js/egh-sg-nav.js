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
    var hoverQuery = window.matchMedia ? window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 1281px)') : null;
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

/* Collapse the desktop menu into the burger whenever its items do not fit the header
   (for example longer Bengali labels). Re-checked on resize and after fonts load. */
(function () {
    var header = document.querySelector('.sg-header');
    var inner = header ? header.querySelector('.sg-header-inner') : null;
    if (!header || !inner) return;
    var fit = function () {
        header.classList.remove('sg-nav-collapsed');
        if (inner.scrollWidth > inner.clientWidth + 1) header.classList.add('sg-nav-collapsed');
    };
    var timer;
    window.addEventListener('resize', function () { clearTimeout(timer); timer = setTimeout(fit, 120); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
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
