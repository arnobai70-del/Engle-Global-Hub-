/*
    Flow-button progressive enhancement for the public site.

    This intentionally keeps the application's existing button/link DOM intact.
    The previous implementation moved every child node into a new wrapper and
    forced display/background styles, which could break existing icons, loading
    states, widths and JavaScript that expected the original children. This
    version only wraps direct text nodes for the small label translation and
    appends decorative layers. Existing child elements stay where they are.
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
        'a[class*="-cta"]',
        '[data-flow-button]'
    ].join(',');

    var SKIP_SELECTOR = [
        '[data-flow-skip]',
        '.egho-rail-button',
        '.egho-swap',
        '.site-menu-toggle',
        '.site-modal-close',
        '.flight-offer-select-icon',
        '[data-egho-rail-prev]',
        '[data-egho-rail-next]',
        '[class*="close"]',
        '[class*="icon-only"]',
        '[class*="toggle"]'
    ].join(',');

    var DECORATION_SELECTOR = [
        '.egh-flow-button__circle',
        '.egh-flow-button__arrow',
        '.egh-flow-button__label'
    ].join(',');

    var transparent = function (value) {
        return value === 'transparent'
            || value === 'rgba(0, 0, 0, 0)'
            || value === 'rgba(0,0,0,0)';
    };

    var strongBackground = function (element, computed) {
        var backgroundImage = computed.backgroundImage || 'none';
        var backgroundColor = computed.backgroundColor || 'transparent';

        return backgroundImage !== 'none'
            || (!transparent(backgroundColor) && backgroundColor !== 'rgb(255, 255, 255)');
    };

    var arrow = function (side) {
        var wrapper = document.createElement('span');
        wrapper.className = 'egh-flow-button__arrow egh-flow-button__arrow--' + side;
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.innerHTML = '<svg viewBox="0 0 24 24" focusable="false"><path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path></svg>';
        return wrapper;
    };

    var circle = function () {
        var node = document.createElement('span');
        node.className = 'egh-flow-button__circle';
        node.setAttribute('aria-hidden', 'true');
        return node;
    };

    var visibleLabel = function (element) {
        return (element.textContent || '').replace(/\s+/g, ' ').trim();
    };

    var shouldSkip = function (element) {
        if (!(element instanceof HTMLElement) || element.matches(SKIP_SELECTOR)) {
            return true;
        }

        var label = visibleLabel(element);
        if (!label) {
            return true;
        }

        /*
         * Small labelled controls are normally pagination / icon controls. Do
         * not turn them into wide CTA buttons just because they contain a
         * single character such as ‹, ›, + or ×.
         */
        if (label.length <= 2) {
            var rect = element.getBoundingClientRect();
            if ((rect.width && rect.width <= 56) || element.hasAttribute('aria-label')) {
                return true;
            }
        }

        return false;
    };

    var wrapDirectText = function (element) {
        Array.prototype.slice.call(element.childNodes).forEach(function (node) {
            if (node.nodeType !== Node.TEXT_NODE || !node.nodeValue || !node.nodeValue.trim()) {
                return;
            }

            var label = document.createElement('span');
            label.className = 'egh-flow-button__label';
            label.textContent = node.nodeValue;
            element.replaceChild(label, node);
        });
    };

    var findNativeRightArrow = function (element) {
        var children = Array.prototype.filter.call(element.children, function (child) {
            return !child.matches(DECORATION_SELECTOR);
        });
        var last = children[children.length - 1];

        if (
            last
            && last.tagName
            && last.tagName.toLowerCase() === 'svg'
            && last.getAttribute('aria-hidden') === 'true'
        ) {
            last.classList.add('egh-flow-button__native-right');
            return last;
        }

        return null;
    };

    var ensureDecorations = function (element) {
        if (!(element instanceof HTMLElement) || shouldSkip(element)) {
            return;
        }

        wrapDirectText(element);

        if (!element.querySelector(':scope > .egh-flow-button__circle')) {
            element.insertBefore(circle(), element.firstChild);
        }

        if (!element.querySelector(':scope > .egh-flow-button__arrow--left')) {
            element.appendChild(arrow('left'));
        }

        var nativeRight = findNativeRightArrow(element);
        if (!nativeRight && !element.querySelector(':scope > .egh-flow-button__arrow--right')) {
            element.appendChild(arrow('right'));
        }
    };

    var enhance = function (element) {
        if (!(element instanceof HTMLElement) || shouldSkip(element)) {
            return;
        }

        if (element.dataset.flowEnhanced !== 'true') {
            var computed = window.getComputedStyle(element);
            var hoverFill = strongBackground(element, computed) ? '#082d56' : '#0b63f6';

            if (
                element.classList.contains('danger')
                || element.classList.contains('is-danger')
                || element.classList.contains('destructive')
                || element.dataset.variant === 'danger'
            ) {
                hoverFill = '#8f1d17';
            }

            element.style.setProperty('--egh-flow-fill', hoverFill);
            element.style.setProperty('--egh-flow-base-color', computed.color || '#0b2545');
            element.style.setProperty('--egh-flow-pad-left', computed.paddingLeft || '14px');
            element.style.setProperty('--egh-flow-pad-right', computed.paddingRight || '14px');
            element.classList.add('egh-flow-button');
            element.dataset.flowEnhanced = 'true';
        }

        ensureDecorations(element);
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

    var repairClosestButton = function (node) {
        var element = node instanceof Element ? node : node.parentElement;
        var button = element && element.closest ? element.closest('.egh-flow-button') : null;

        if (button) {
            ensureDecorations(button);
        }
    };

    var init = function () {
        scan(document);

        if (!window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                repairClosestButton(mutation.target);

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
