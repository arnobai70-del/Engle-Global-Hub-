/* Homepage destination/comment rails with smooth, seamless autoplay. */
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
        var autoplayEnabled = Number.isFinite(autoplayDelay) && autoplayDelay >= 2200;
        var autoplayTimer = null;
        var interactionTimer = null;
        var interacting = false;
        var animating = false;
        var animationFrame = null;
        var originalItems = Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));
        var cloneStart = 0;

        /*
         * Autoplay rails receive one visual copy of the original cards. This
         * lets the strip move from the final destination to the first one
         * without the long backwards jump that made the old carousel feel
         * abrupt. Clones are presentation-only and hidden from assistive tech.
         */
        if (autoplayEnabled && originalItems.length > 1) {
            originalItems.forEach(function (item) {
                var clone = item.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.removeAttribute('role');
                clone.classList.add('is-rail-clone');
                track.appendChild(clone);
            });
            rail.classList.add('is-infinite-rail');
        }

        var allItems = function () {
            return Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));
        };

        var step = function () {
            var card = originalItems[0];
            if (!card) return track.clientWidth;

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');
            return card.getBoundingClientRect().width + (Number.isFinite(gap) ? gap : 0);
        };

        var updateCloneStart = function () {
            if (!autoplayEnabled || originalItems.length < 2) {
                cloneStart = 0;
                return;
            }

            var firstClone = track.querySelector('.is-rail-clone');
            cloneStart = firstClone ? firstClone.offsetLeft : 0;
        };

        var maxScroll = function () {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        };

        var markActiveCard = function () {
            var items = originalItems;
            if (!items.length) return;

            var trackRect = track.getBoundingClientRect();
            var targetX = trackRect.left + Math.min(trackRect.width * 0.18, 120);
            var closest = null;
            var closestDistance = Infinity;

            allItems().forEach(function (item) {
                item.classList.remove('is-rail-active');
            });

            items.forEach(function (item) {
                var rect = item.getBoundingClientRect();
                var distance = Math.abs(rect.left - targetX);
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
            }, 520);
        };

        var normalizeLoopPosition = function () {
            if (!autoplayEnabled || cloneStart <= 0) return;

            if (track.scrollLeft >= cloneStart - 1) {
                track.scrollLeft = track.scrollLeft - cloneStart;
            }
        };

        var sync = function () {
            updateCloneStart();
            var canScroll = autoplayEnabled ? cloneStart > 4 : maxScroll() > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (autoplayEnabled && canScroll) {
                previous.disabled = false;
                next.disabled = false;
            } else {
                previous.disabled = !canScroll || track.scrollLeft <= 2;
                next.disabled = !canScroll || track.scrollLeft >= maxScroll() - 2;
            }

            if (!animating) markActiveCard();
        };

        var easeInOutCubic = function (progress) {
            return progress < 0.5
                ? 4 * progress * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 3) / 2;
        };

        var animateScroll = function (target, direction, done) {
            if (animationFrame !== null) {
                window.cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }

            var start = track.scrollLeft;
            var distance = target - start;

            if (Math.abs(distance) < 1 || (reducedMotion && reducedMotion.matches)) {
                track.scrollLeft = target;
                normalizeLoopPosition();
                sync();
                if (done) done();
                return;
            }

            var duration = 980;
            var startedAt = null;
            animating = true;
            rail.classList.add('is-rail-moving');
            rail.classList.toggle('is-moving-next', direction === 'next');
            rail.classList.toggle('is-moving-prev', direction === 'prev');

            var finish = function () {
                track.scrollLeft = target;
                normalizeLoopPosition();
                animating = false;
                animationFrame = null;
                rail.classList.remove('is-rail-moving', 'is-moving-next', 'is-moving-prev');
                sync();
                if (done) done();
            };

            var frame = function (timestamp) {
                if (startedAt === null) startedAt = timestamp;
                var progress = Math.min(1, (timestamp - startedAt) / duration);
                track.scrollLeft = start + (distance * easeInOutCubic(progress));

                if (progress < 1) {
                    animationFrame = window.requestAnimationFrame(frame);
                } else {
                    finish();
                }
            };

            animationFrame = window.requestAnimationFrame(frame);
        };

        var goPrevious = function () {
            if (animating) return;
            updateCloneStart();

            if (autoplayEnabled && cloneStart > 0 && track.scrollLeft <= 2) {
                /* Same visual position in the cloned sequence, then slide left. */
                track.scrollLeft = cloneStart;
            }

            pulseButton(previous);
            animateScroll(Math.max(0, track.scrollLeft - step()), 'prev', function () {
                if (autoplayEnabled && cloneStart > 0 && track.scrollLeft >= cloneStart - 1) {
                    track.scrollLeft -= cloneStart;
                }
            });
        };

        var goNext = function () {
            if (animating) return;
            updateCloneStart();

            var limit = autoplayEnabled && cloneStart > 0 ? cloneStart : maxScroll();
            var target = Math.min(limit, track.scrollLeft + step());
            pulseButton(next);
            animateScroll(target, 'next');
        };

        var autoplayTick = function () {
            if (interacting || animating || document.hidden || originalItems.length < 2) return;
            goNext();
        };

        var stopAutoplay = function () {
            if (autoplayTimer !== null) {
                window.clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        };

        var startAutoplay = function () {
            stopAutoplay();
            updateCloneStart();

            if (!autoplayEnabled || cloneStart <= 4) return;
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
            }, 1000);
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

        track.addEventListener('scroll', function () {
            if (!animating) sync();
        }, { passive: true });
        track.addEventListener('pointerdown', beginInteraction, { passive: true });
        track.addEventListener('pointerup', endInteraction, { passive: true });
        track.addEventListener('pointercancel', endInteraction, { passive: true });
        rail.addEventListener('focusin', beginInteraction);
        rail.addEventListener('focusout', endInteraction);

        window.addEventListener('resize', function () {
            if (animationFrame !== null) {
                window.cancelAnimationFrame(animationFrame);
                animationFrame = null;
                animating = false;
                rail.classList.remove('is-rail-moving', 'is-moving-next', 'is-moving-prev');
            }
            normalizeLoopPosition();
            sync();
            startAutoplay();
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopAutoplay();
            } else {
                startAutoplay();
            }
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
