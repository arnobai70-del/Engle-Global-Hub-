/* Homepage destination/comment rails with visible paging animation and autoplay. */
(function () {
    'use strict';

    var reducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)')
        : null;

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next || rail.dataset.eghoRailInit === '1') {
            return;
        }

        rail.dataset.eghoRailInit = '1';

        var autoplayDelay = parseInt(rail.getAttribute('data-egho-autoplay') || '0', 10);
        var autoplayTimer = null;
        var interactionTimer = null;
        var interacting = false;
        var animating = false;
        var items = Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));

        var step = function () {
            var card = items[0];
            if (!card) return track.clientWidth;

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');
            return card.getBoundingClientRect().width + (Number.isFinite(gap) ? gap : 0);
        };

        var maxScroll = function () {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        };

        var markActiveCard = function () {
            if (!items.length) return;

            var trackRect = track.getBoundingClientRect();
            var targetX = trackRect.left + Math.min(trackRect.width * 0.18, 120);
            var closest = null;
            var closestDistance = Infinity;

            items.forEach(function (item) {
                var rect = item.getBoundingClientRect();
                var distance = Math.abs(rect.left - targetX);
                item.classList.remove('is-rail-active');
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closest = item;
                }
            });

            if (closest) closest.classList.add('is-rail-active');
        };

        var pulseButton = function (button) {
            if (!button) return;
            button.classList.remove('is-rail-pulse');
            void button.offsetWidth;
            button.classList.add('is-rail-pulse');
            window.setTimeout(function () {
                button.classList.remove('is-rail-pulse');
            }, 560);
        };

        var sync = function () {
            var limit = maxScroll();
            var canScroll = limit > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;
            previous.disabled = !canScroll || track.scrollLeft <= 2;
            next.disabled = !canScroll || track.scrollLeft >= limit - 2;

            if (!animating) markActiveCard();
        };

        var animateScroll = function (target, direction, done) {
            var start = track.scrollLeft;
            var distance = target - start;

            if (Math.abs(distance) < 1) {
                track.scrollLeft = target;
                sync();
                if (done) done();
                return;
            }

            if (reducedMotion && reducedMotion.matches) {
                track.scrollLeft = target;
                sync();
                if (done) done();
                return;
            }

            var duration = 720;
            var startedAt = null;
            animating = true;
            rail.classList.add('is-rail-moving');
            rail.classList.toggle('is-moving-next', direction === 'next');
            rail.classList.toggle('is-moving-prev', direction === 'prev');

            var frame = function (timestamp) {
                if (startedAt === null) startedAt = timestamp;
                var progress = Math.min(1, (timestamp - startedAt) / duration);
                var eased = 1 - Math.pow(1 - progress, 3);
                track.scrollLeft = start + (distance * eased);

                if (progress < 1) {
                    window.requestAnimationFrame(frame);
                    return;
                }

                track.scrollLeft = target;
                animating = false;
                rail.classList.remove('is-rail-moving', 'is-moving-next', 'is-moving-prev');
                sync();
                if (done) done();
            };

            window.requestAnimationFrame(frame);
        };

        var goPrevious = function () {
            if (animating) return;
            pulseButton(previous);
            animateScroll(Math.max(0, track.scrollLeft - step()), 'prev');
        };

        var goNext = function () {
            if (animating) return;
            var limit = maxScroll();
            var target = Math.min(limit, track.scrollLeft + step());
            pulseButton(next);
            animateScroll(target, 'next');
        };

        var autoplayTick = function () {
            if (interacting || animating || document.hidden || maxScroll() <= 4) return;

            var limit = maxScroll();
            if (track.scrollLeft >= limit - 2) {
                pulseButton(previous);
                animateScroll(0, 'prev');
            } else {
                goNext();
            }
        };

        var stopAutoplay = function () {
            if (autoplayTimer !== null) {
                window.clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        };

        var startAutoplay = function () {
            stopAutoplay();
            if (!Number.isFinite(autoplayDelay) || autoplayDelay < 2200 || maxScroll() <= 4) return;
            autoplayTimer = window.setInterval(autoplayTick, autoplayDelay);
        };

        var beginInteraction = function () {
            interacting = true;
            if (interactionTimer !== null) window.clearTimeout(interactionTimer);
        };

        var endInteraction = function () {
            if (interactionTimer !== null) window.clearTimeout(interactionTimer);
            interactionTimer = window.setTimeout(function () {
                interacting = false;
            }, 800);
        };

        previous.addEventListener('click', function () {
            beginInteraction();
            goPrevious();
            endInteraction();
            startAutoplay();
        });

        next.addEventListener('click', function () {
            beginInteraction();
            goNext();
            endInteraction();
            startAutoplay();
        });

        track.addEventListener('scroll', sync, { passive: true });
        track.addEventListener('pointerdown', beginInteraction, { passive: true });
        track.addEventListener('pointerup', endInteraction, { passive: true });
        track.addEventListener('pointercancel', endInteraction, { passive: true });
        rail.addEventListener('focusin', beginInteraction);
        rail.addEventListener('focusout', endInteraction);

        window.addEventListener('resize', function () {
            sync();
            startAutoplay();
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) startAutoplay();
        });

        sync();
        startAutoplay();
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
