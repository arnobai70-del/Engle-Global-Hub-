/* Homepage destination carousel with deterministic timed autoplay. */
(function () {
    'use strict';

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next || rail.dataset.eghoRailInit === '1') {
            return;
        }

        var originalItems = Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));
        if (originalItems.length < 2) {
            previous.hidden = true;
            next.hidden = true;
            rail.setAttribute('data-egho-rail-ready', 'true');
            return;
        }

        rail.dataset.eghoRailInit = '1';
        rail.classList.add('is-infinite-rail');

        originalItems.forEach(function (item) {
            var clone = item.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.removeAttribute('role');
            clone.classList.add('is-rail-clone');
            track.appendChild(clone);
        });

        var interval = parseInt(rail.getAttribute('data-egho-interval') || '2200', 10);
        interval = Number.isFinite(interval) ? Math.max(1600, interval) : 2200;

        var autoplay = rail.getAttribute('data-egho-autoplay') === 'step';
        var cloneStart = 0;
        var moving = false;
        var dragging = false;
        var autoplayTimer = null;
        var activeTimer = null;

        var allItems = function () {
            return Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));
        };

        var cardStep = function () {
            var first = originalItems[0];
            if (!first) return track.clientWidth;

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');
            return first.getBoundingClientRect().width + (Number.isFinite(gap) ? gap : 0);
        };

        var updateCloneStart = function () {
            var firstClone = track.querySelector('.is-rail-clone');
            var firstOriginal = originalItems[0];
            cloneStart = firstClone && firstOriginal
                ? firstClone.offsetLeft - firstOriginal.offsetLeft
                : 0;
        };

        var normalize = function () {
            updateCloneStart();
            if (cloneStart <= 1) return;

            while (track.scrollLeft >= cloneStart) {
                track.scrollLeft -= cloneStart;
            }

            while (track.scrollLeft < 0) {
                track.scrollLeft += cloneStart;
            }
        };

        var markActiveCard = function () {
            var items = allItems();
            var trackRect = track.getBoundingClientRect();
            var focusX = trackRect.left + Math.min(trackRect.width * 0.22, 150);
            var nearest = null;
            var distance = Infinity;

            items.forEach(function (item) {
                item.classList.remove('is-rail-active');
                var rect = item.getBoundingClientRect();
                var delta = Math.abs(rect.left - focusX);
                if (delta < distance) {
                    distance = delta;
                    nearest = item;
                }
            });

            if (nearest) nearest.classList.add('is-rail-active');
        };

        var setMovingClass = function (direction, on) {
            rail.classList.toggle('is-rail-moving', on);
            rail.classList.toggle('is-moving-next', on && direction > 0);
            rail.classList.toggle('is-moving-prev', on && direction < 0);
        };

        var animateTo = function (target, direction, done) {
            if (moving) return;

            var start = track.scrollLeft;
            var distance = target - start;

            if (Math.abs(distance) < 1) {
                track.scrollLeft = target;
                normalize();
                markActiveCard();
                if (done) done();
                return;
            }

            moving = true;
            setMovingClass(direction, true);

            var duration = 700;
            var startedAt = null;

            var ease = function (progress) {
                return progress < 0.5
                    ? 4 * progress * progress * progress
                    : 1 - Math.pow(-2 * progress + 2, 3) / 2;
            };

            var frame = function (timestamp) {
                if (startedAt === null) startedAt = timestamp;

                var progress = Math.min(1, (timestamp - startedAt) / duration);
                track.scrollLeft = start + (distance * ease(progress));
                markActiveCard();

                if (progress < 1) {
                    window.requestAnimationFrame(frame);
                    return;
                }

                track.scrollLeft = target;
                normalize();
                moving = false;
                setMovingClass(direction, false);
                markActiveCard();
                if (done) done();
            };

            window.requestAnimationFrame(frame);
        };

        var clearAutoplay = function () {
            if (autoplayTimer !== null) {
                window.clearTimeout(autoplayTimer);
                autoplayTimer = null;
            }
        };

        var moveOne;

        var scheduleAutoplay = function (delay) {
            clearAutoplay();

            if (!autoplay) {
                return;
            }

            autoplayTimer = window.setTimeout(function () {
                if (document.hidden || dragging || moving) {
                    scheduleAutoplay(interval);
                    return;
                }

                moveOne(1, true);
            }, typeof delay === 'number' ? delay : interval);
        };

        moveOne = function (direction, fromAutoplay) {
            if (moving) return;

            clearAutoplay();
            updateCloneStart();

            var amount = cardStep();

            if (direction < 0 && cloneStart > 1 && track.scrollLeft < amount * 0.55) {
                track.scrollLeft += cloneStart;
            }

            animateTo(track.scrollLeft + (direction * amount), direction, function () {
                if (!fromAutoplay) {
                    rail.classList.add('is-rail-manual');
                    if (activeTimer !== null) window.clearTimeout(activeTimer);
                    activeTimer = window.setTimeout(function () {
                        rail.classList.remove('is-rail-manual');
                    }, 900);
                }
                scheduleAutoplay(interval);
            });
        };

        previous.addEventListener('click', function () {
            moveOne(-1, false);
        });

        next.addEventListener('click', function () {
            moveOne(1, false);
        });

        track.addEventListener('pointerdown', function () {
            dragging = true;
            clearAutoplay();
        }, { passive: true });

        var endPointerInteraction = function () {
            dragging = false;
            normalize();
            markActiveCard();
            scheduleAutoplay(900);
        };

        track.addEventListener('pointerup', endPointerInteraction, { passive: true });
        track.addEventListener('pointercancel', endPointerInteraction, { passive: true });
        track.addEventListener('touchend', endPointerInteraction, { passive: true });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                normalize();
                markActiveCard();
                scheduleAutoplay(700);
            } else {
                clearAutoplay();
            }
        });

        window.addEventListener('resize', function () {
            normalize();
            markActiveCard();
        });

        previous.hidden = false;
        next.hidden = false;
        previous.disabled = false;
        next.disabled = false;
        rail.setAttribute('data-egho-rail-ready', 'true');
        rail.classList.add('is-motion-ready');

        normalize();
        markActiveCard();
        scheduleAutoplay(900);

        window.addEventListener('beforeunload', function () {
            clearAutoplay();
            if (activeTimer !== null) window.clearTimeout(activeTimer);
        }, { once: true });
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
