/*
    Homepage rails.

    Destination and testimonial rails keep their real previous/next controls.
    Rails marked with `data-egho-auto` also move automatically, one card at a
    time. Autoplay keeps running while the pointer is merely hovering over the
    cards, pauses only during an active pointer interaction / hidden tab, and is
    disabled for users who prefer reduced motion.
*/
(function () {
    'use strict';

    var reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next) {
            return;
        }

        var autoTimer = null;
        var autoDirection = 1;
        var pointerActive = false;
        var autoDelay = parseInt(rail.getAttribute('data-egho-auto-delay') || '2600', 10);

        if (!Number.isFinite(autoDelay) || autoDelay < 1600) {
            autoDelay = 2600;
        }

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

        var maxScroll = function () {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        };

        var sync = function () {
            var limit = maxScroll();
            var canScroll = limit > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (!canScroll) {
                return;
            }

            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= limit - 2;
        };

        var move = function (direction) {
            var limit = maxScroll();
            var target = track.scrollLeft + (direction * step());

            target = Math.max(0, Math.min(limit, target));

            track.scrollTo({
                left: target,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        };

        var stopAuto = function () {
            if (autoTimer !== null) {
                window.clearTimeout(autoTimer);
                autoTimer = null;
            }
        };

        var scheduleAuto = function (delay) {
            stopAuto();

            if (
                reduceMotion
                || !rail.hasAttribute('data-egho-auto')
                || maxScroll() <= 4
                || pointerActive
            ) {
                return;
            }

            autoTimer = window.setTimeout(function tick() {
                autoTimer = null;

                if (document.hidden || pointerActive) {
                    scheduleAuto(autoDelay);
                    return;
                }

                var limit = maxScroll();

                if (track.scrollLeft >= limit - 3) {
                    autoDirection = -1;
                } else if (track.scrollLeft <= 3) {
                    autoDirection = 1;
                }

                move(autoDirection);
                scheduleAuto(autoDelay);
            }, typeof delay === 'number' ? delay : autoDelay);
        };

        previous.addEventListener('click', function () {
            autoDirection = -1;
            move(-1);
            scheduleAuto(autoDelay);
        });

        next.addEventListener('click', function () {
            autoDirection = 1;
            move(1);
            scheduleAuto(autoDelay);
        });

        /*
         * Do not pause on hover. A normal desktop user often leaves the mouse
         * over the carousel while reading it, which made the earlier autoplay
         * look broken. Pause only while the user is actively dragging/touching.
         */
        rail.addEventListener('pointerdown', function () {
            pointerActive = true;
            stopAuto();
        });

        window.addEventListener('pointerup', function () {
            if (!pointerActive) {
                return;
            }

            pointerActive = false;
            scheduleAuto(1200);
        });

        window.addEventListener('pointercancel', function () {
            if (!pointerActive) {
                return;
            }

            pointerActive = false;
            scheduleAuto(1200);
        });

        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', function () {
            sync();
            scheduleAuto(autoDelay);
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopAuto();
            } else {
                scheduleAuto(900);
            }
        });

        sync();
        scheduleAuto(1400);
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
