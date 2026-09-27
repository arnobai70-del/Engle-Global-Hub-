/*
    Homepage rails.

    Destination rails keep their real previous/next controls and can autoplay.
    Autoplay is intentionally driven by the rail's own data attributes so it
    behaves consistently across Windows/browser animation preferences.
*/
(function () {
    'use strict';

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next) {
            return;
        }

        var timer = null;
        var direction = 1;
        var pointerActive = false;
        var delay = parseInt(rail.getAttribute('data-egho-auto-delay') || '2600', 10);

        if (!Number.isFinite(delay) || delay < 1400) {
            delay = 2600;
        }

        var step = function () {
            var card = track.querySelector('[data-egho-rail-item]');
            if (!card) return Math.max(1, track.clientWidth);

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');
            return card.getBoundingClientRect().width + (Number.isFinite(gap) ? gap : 0);
        };

        var limit = function () {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        };

        var sync = function () {
            var max = limit();
            var canScroll = max > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (!canScroll) return;
            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= max - 2;
        };

        var move = function (dir) {
            var max = limit();
            var target = Math.max(0, Math.min(max, track.scrollLeft + (dir * step())));
            track.scrollTo({ left: target, behavior: 'smooth' });
        };

        var stop = function () {
            if (timer !== null) {
                window.clearTimeout(timer);
                timer = null;
            }
        };

        var schedule = function (wait) {
            stop();

            if (!rail.hasAttribute('data-egho-auto') || limit() <= 4 || pointerActive) {
                return;
            }

            timer = window.setTimeout(function tick() {
                timer = null;

                if (document.hidden || pointerActive) {
                    schedule(delay);
                    return;
                }

                var max = limit();
                if (track.scrollLeft >= max - 3) {
                    direction = -1;
                } else if (track.scrollLeft <= 3) {
                    direction = 1;
                }

                move(direction);
                schedule(delay);
            }, typeof wait === 'number' ? wait : delay);
        };

        previous.addEventListener('click', function () {
            direction = -1;
            move(-1);
            schedule(delay);
        });

        next.addEventListener('click', function () {
            direction = 1;
            move(1);
            schedule(delay);
        });

        rail.addEventListener('pointerdown', function () {
            pointerActive = true;
            stop();
        });

        var releasePointer = function () {
            if (!pointerActive) return;
            pointerActive = false;
            schedule(900);
        };

        window.addEventListener('pointerup', releasePointer);
        window.addEventListener('pointercancel', releasePointer);

        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', function () {
            sync();
            schedule(delay);
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) stop();
            else schedule(700);
        });

        sync();
        schedule(900);
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
