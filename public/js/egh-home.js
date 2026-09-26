/*
    Homepage rails.

    The destination strip and the customer-comments strip are horizontal
    scrollers with real previous/next buttons. Every card is already in the
    document, so the buttons only move a scroll position: this script never
    requests, adds, removes or replaces a card, and it holds no copy of its own.

    The buttons are rendered `hidden` in the markup and are revealed only once
    this script has confirmed there is something to scroll. A browser without
    JavaScript therefore still gets the full strip (scrollable by touch or
    trackpad) and never a button that would do nothing.
*/
(function () {
    'use strict';

    var initRail = function (rail) {
        /*
         * The rail's buttons sit in the section heading, beside the strip
         * rather than inside it, so they are looked up from the section the
         * rail belongs to. A rail with no controls (the comment strip) simply
         * has nothing to page and is left alone.
         */
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next) {
            return;
        }

        /* One card plus the row gap: the distance a single page moves. */
        var step = function () {
            var card = track.querySelector('[data-egho-rail-item]');

            if (!card) {
                return track.clientWidth;
            }

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');

            return card.getBoundingClientRect().width
                + (Number.isFinite(gap) ? gap : 0);
        };

        var sync = function () {
            var maxScroll = track.scrollWidth - track.clientWidth;
            var canScroll = maxScroll > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');

            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (!canScroll) {
                return;
            }

            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= maxScroll - 2;
        };

        previous.addEventListener('click', function () {
            track.scrollBy({ left: -step(), behavior: 'smooth' });
        });

        next.addEventListener('click', function () {
            track.scrollBy({ left: step(), behavior: 'smooth' });
        });

        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);

        sync();
    };

    var init = function () {
        document.querySelectorAll('[data-egho-rail]').forEach(initRail);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
