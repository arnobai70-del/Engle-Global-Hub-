/*
    Flow-button enhancement for the public site.

    The reference component supplied for this project is a React/shadcn button,
    but the application itself is Laravel Blade. Rebuilding the public site in
    React only for one interaction would add a second frontend architecture, so
    this small progressive enhancement recreates the same interaction on the
    existing semantic buttons and CTA links.

    Icon-only controls (carousel arrows, swap buttons, close buttons, etc.) are
    intentionally excluded: turning those into wide text buttons would reduce
    usability. Buttons added later by existing booking/search scripts are picked
    up by the MutationObserver as well.
*/
(function () {
    'use strict';

    var ACTION_SELECTOR = [
        'button',
        'a.site-button',
        'a.egho-promo-cta',
        'a.egho-panel-cta',
        'a.egho-section-link',
        'a.egho-app-store',
        'a.egho-journey-cta',
        'a[class*="-button"]',
        'a[class*="-cta"]'
    ].join(',');

    var SKIP_SELECTOR = [
        '[data-flow-skip]',
        '.egho-rail-button',
        '.egho-swap',
        '.egho-nav button',
        '.egho-nav-more button',
        '.site-menu-toggle',
        '.site-modal-close',
        '.flight-offer-select-icon',
        '[aria-label][class*="close"]'
    ].join(',');

    var transparent = function (value) {
        return value === 'transparent'
            || value === 'rgba(0, 0, 0, 0)'
            || value === 'rgba(0,0,0,0)';
    };

    var strongBackground = function (value) {
        return value && !transparent(value) && value !== 'rgb(255, 255, 255)';
    };

    var arrow = function (side) {
        var wrapper = document.createElement('span');
        wrapper.className = 'egh-flow-button__arrow egh-flow-button__arrow--' + side;
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.innerHTML = '<svg viewBox="0 0 24 24" focusable="false"><path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path></svg>';
        return wrapper;
    };

    var enhance = function (element) {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        if (element.dataset.flowEnhanced === 'true' || element.matches(SKIP_SELECTOR)) {
            return;
        }

        var label = (element.textContent || '').replace(/\s+/g, ' ').trim();

        /* Icon-only controls retain their compact native treatment. */
        if (!label) {
            element.dataset.flowEnhanced = 'skip';
            return;
        }

        var computed = window.getComputedStyle(element);
        var baseBackground = computed.backgroundColor;
        var baseColor = computed.color;
        var baseBorder = computed.borderTopColor || 'rgba(11, 37, 69, .3)';
        var hoverFill = strongBackground(baseBackground) ? '#082d56' : '#0b63f6';

        /* Keep destructive actions visually destructive. */
        if (
            element.classList.contains('danger')
            || element.classList.contains('is-danger')
            || element.classList.contains('destructive')
            || element.dataset.variant === 'danger'
        ) {
            hoverFill = '#8f1d17';
        }

        element.style.setProperty('--egh-flow-base-bg', baseBackground);
        element.style.setProperty('--egh-flow-base-color', baseColor);
        element.style.setProperty('--egh-flow-base-border', baseBorder);
        element.style.setProperty('--egh-flow-fill', hoverFill);
        element.style.setProperty('--egh-flow-pad-left', computed.paddingLeft || '16px');
        element.style.setProperty('--egh-flow-pad-right', computed.paddingRight || '16px');

        var content = document.createElement('span');
        content.className = 'egh-flow-button__content';

        while (element.firstChild) {
            content.appendChild(element.firstChild);
        }

        var circle = document.createElement('span');
        circle.className = 'egh-flow-button__circle';
        circle.setAttribute('aria-hidden', 'true');

        element.appendChild(arrow('left'));
        element.appendChild(content);
        element.appendChild(circle);
        element.appendChild(arrow('right'));
        element.classList.add('egh-flow-button');
        element.dataset.flowEnhanced = 'true';
    };

    var scan = function (root) {
        if (!(root instanceof Element || root instanceof Document)) {
            return;
        }

        if (root instanceof Element && root.matches(ACTION_SELECTOR)) {
            enhance(root);
        }

        root.querySelectorAll(ACTION_SELECTOR).forEach(enhance);
    };

    var init = function () {
        scan(document);

        if (!window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        scan(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
