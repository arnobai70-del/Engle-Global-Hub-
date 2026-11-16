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
