/* Homepage news ticker and destination/comment rails with seamless autoplay. */
(function () {
    'use strict';

    var reducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)')
        : null;

    var initTicker = function (ticker) {
        var viewport = ticker.querySelector('.egho-news-ticker-viewport');
        var track = ticker.querySelector('.egho-news-ticker-track');
        var seed = track ? track.querySelector('span') : null;

        if (!viewport || !track || !seed || ticker.dataset.eghoTickerInit === '1') {
            return;
        }

        ticker.dataset.eghoTickerInit = '1';
        ticker.classList.add('is-ticker-enhanced');

        var configuredSpeed = parseFloat(ticker.getAttribute('data-egho-ticker-speed') || '48');
        var speed = Number.isFinite(configuredSpeed) ? Math.max(38, configuredSpeed) : 48;
        var segmentWidth = 0;
        var rafId = null;
        var lastFrame = null;

        var ensureCopies = function () {
            segmentWidth = seed.getBoundingClientRect().width;
            if (!(segmentWidth > 1)) return;

            var requiredWidth = viewport.clientWidth + (segmentWidth * 3);
            var guard = 0;

            while (track.scrollWidth < requiredWidth && guard < 24) {
                var clone = seed.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.classList.add('is-news-clone');
                track.appendChild(clone);
                guard += 1;
            }
        };

        var normalizePosition = function () {
            if (!(segmentWidth > 1)) return;

            while (viewport.scrollLeft >= segmentWidth) {
                viewport.scrollLeft -= segmentWidth;
            }
        };

        var frame = function (timestamp) {
            if (lastFrame === null) lastFrame = timestamp;
            var elapsed = Math.min(50, Math.max(0, timestamp - lastFrame));
            lastFrame = timestamp;

            if (
                !document.hidden
                && !(reducedMotion && reducedMotion.matches)
                && segmentWidth > 1
            ) {
                viewport.scrollLeft += speed * (elapsed / 1000);
                normalizePosition();
            }

            rafId = window.requestAnimationFrame(frame);
        };

        ensureCopies();
        viewport.scrollLeft = 0;
        rafId = window.requestAnimationFrame(frame);

        window.addEventListener('resize', function () {
            viewport.scrollLeft = 0;
            ensureCopies();
            lastFrame = null;
        });

        document.addEventListener('visibilitychange', function () {
            lastFrame = null;
        });

        window.addEventListener('beforeunload', function () {
            if (rafId !== null) window.cancelAnimationFrame(rafId);
        }, { once: true });
    };

    var initRail = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        var track = rail.querySelector('[data-egho-rail-track]');
        var previous = scope.querySelector('[data-egho-rail-prev]');
        var next = scope.querySelector('[data-egho-rail-next]');

        if (!track || !previous || !next || rail.dataset.eghoRailInit === '1') {
            return;
        }

        rail.dataset.eghoRailInit = '1';

        var autoplayMode = rail.getAttribute('data-egho-autoplay') || '';
        var continuous = autoplayMode === 'continuous';
        var configuredSpeed = parseFloat(rail.getAttribute('data-egho-speed') || '58');
        var speed = Number.isFinite(configuredSpeed) ? Math.max(58, configuredSpeed) : 58;
        var originalItems = Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));
        var cloneStart = 0;
        var dragging = false;
        var manualAnimating = false;
        var pauseUntil = 0;
        var rafId = null;
        var lastFrame = null;
        var activeSyncAt = 0;

        if (continuous && originalItems.length > 1) {
            originalItems.forEach(function (item) {
                var clone = item.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.removeAttribute('role');
                clone.classList.add('is-rail-clone');
                track.appendChild(clone);
            });
            rail.classList.add('is-infinite-rail', 'is-continuous-rail');
        }

        var allItems = function () {
            return Array.prototype.slice.call(track.querySelectorAll('[data-egho-rail-item]'));
        };

        var updateCloneStart = function () {
            if (!continuous || originalItems.length < 2) {
                cloneStart = 0;
                return;
            }

            var firstClone = track.querySelector('.is-rail-clone');
            var firstOriginal = originalItems[0];
            cloneStart = firstClone && firstOriginal
                ? firstClone.offsetLeft - firstOriginal.offsetLeft
                : 0;
        };

        var step = function () {
            var card = originalItems[0];
            if (!card) return track.clientWidth;

            var styles = window.getComputedStyle(track);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');
            return card.getBoundingClientRect().width + (Number.isFinite(gap) ? gap : 0);
        };

        var maxScroll = function () {
            return Math.max(0, track.scrollWidth - track.clientWidth);
        };

        var normalizeLoopPosition = function () {
            updateCloneStart();
            if (!continuous || cloneStart <= 1) return;

            while (track.scrollLeft >= cloneStart) {
                track.scrollLeft -= cloneStart;
            }
            while (track.scrollLeft < 0) {
                track.scrollLeft += cloneStart;
            }
        };

        var markActiveCard = function () {
            if (!originalItems.length) return;

            var trackRect = track.getBoundingClientRect();
            var targetX = trackRect.left + Math.min(trackRect.width * 0.18, 120);
            var closest = null;
            var closestDistance = Infinity;

            allItems().forEach(function (item) {
                item.classList.remove('is-rail-active');
            });

            originalItems.forEach(function (item) {
                var rect = item.getBoundingClientRect();
                var distance = Math.abs(rect.left - targetX);
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closest = item;
                }
            });

            if (closest) closest.classList.add('is-rail-active');
        };

        var syncControls = function () {
            updateCloneStart();
            var canScroll = continuous
                ? cloneStart > 1
                : maxScroll() > 4;

            rail.setAttribute('data-egho-rail-ready', 'true');
            previous.hidden = !canScroll;
            next.hidden = !canScroll;

            if (continuous && canScroll) {
                previous.disabled = false;
                next.disabled = false;
            } else {
                previous.disabled = !canScroll || track.scrollLeft <= 2;
                next.disabled = !canScroll || track.scrollLeft >= maxScroll() - 2;
            }

            markActiveCard();
        };

        var pulseButton = function (button) {
            if (!button) return;
            button.classList.remove('is-rail-pulse');
            void button.offsetWidth;
            button.classList.add('is-rail-pulse');
            window.setTimeout(function () {
                button.classList.remove('is-rail-pulse');
            }, 380);
        };

        var easeInOutCubic = function (progress) {
            return progress < 0.5
                ? 4 * progress * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 3) / 2;
        };

        var manualMove = function (direction) {
            if (manualAnimating) return;

            updateCloneStart();
            var amount = step();

            if (continuous && cloneStart > 1 && direction < 0 && track.scrollLeft < amount) {
                track.scrollLeft += cloneStart;
            }

            var start = track.scrollLeft;
            var target = start + (direction * amount);

            if (!continuous) {
                target = Math.max(0, Math.min(maxScroll(), target));
            }

            manualAnimating = true;
            pauseUntil = performance.now() + 900;
            rail.classList.add('is-rail-moving');
            rail.classList.toggle('is-moving-next', direction > 0);
            rail.classList.toggle('is-moving-prev', direction < 0);

            var duration = 520;
            var startedAt = null;

            var frame = function (timestamp) {
                if (startedAt === null) startedAt = timestamp;
                var progress = Math.min(1, (timestamp - startedAt) / duration);
                track.scrollLeft = start + ((target - start) * easeInOutCubic(progress));

                if (progress < 1) {
                    window.requestAnimationFrame(frame);
                    return;
                }

                track.scrollLeft = target;
                normalizeLoopPosition();
                manualAnimating = false;
                rail.classList.remove('is-rail-moving', 'is-moving-next', 'is-moving-prev');
                syncControls();
            };

            window.requestAnimationFrame(frame);
        };

        previous.addEventListener('click', function () {
            pulseButton(previous);
            manualMove(-1);
        });

        next.addEventListener('click', function () {
            pulseButton(next);
            manualMove(1);
        });

        track.addEventListener('pointerdown', function () {
            dragging = true;
            pauseUntil = performance.now() + 900;
        }, { passive: true });

        var endPointerInteraction = function () {
            dragging = false;
            pauseUntil = performance.now() + 900;
            normalizeLoopPosition();
            syncControls();
        };

        track.addEventListener('pointerup', endPointerInteraction, { passive: true });
        track.addEventListener('pointercancel', endPointerInteraction, { passive: true });
        track.addEventListener('touchend', endPointerInteraction, { passive: true });

        track.addEventListener('scroll', function () {
            if (!continuous && !manualAnimating) syncControls();
        }, { passive: true });

        var autoplayFrame = function (timestamp) {
            if (lastFrame === null) lastFrame = timestamp;
            var elapsed = Math.min(50, Math.max(0, timestamp - lastFrame));
            lastFrame = timestamp;

            if (
                continuous
                && !document.hidden
                && !dragging
                && !manualAnimating
                && timestamp >= pauseUntil
                && !(reducedMotion && reducedMotion.matches)
                && cloneStart > 1
            ) {
                track.scrollLeft += speed * (elapsed / 1000);
                normalizeLoopPosition();
            }

            if (timestamp - activeSyncAt > 140) {
                activeSyncAt = timestamp;
                markActiveCard();
            }

            rafId = window.requestAnimationFrame(autoplayFrame);
        };

        window.addEventListener('resize', function () {
            normalizeLoopPosition();
            syncControls();
            lastFrame = null;
        });

        document.addEventListener('visibilitychange', function () {
            lastFrame = null;
        });

        syncControls();
        rafId = window.requestAnimationFrame(autoplayFrame);

        window.addEventListener('beforeunload', function () {
            if (rafId !== null) window.cancelAnimationFrame(rafId);
        }, { once: true });
    };

    var init = function () {
        document.querySelectorAll('.egho-news-ticker').forEach(initTicker);
        document.querySelectorAll('[data-egho-rail]').forEach(initRail);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
