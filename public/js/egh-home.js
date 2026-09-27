/*
    Homepage rails.

    Popular Destinations uses a duplicated-track infinite carousel so it moves
    continuously with no stop/reverse/jump at the end. Other rails keep the
    existing paged scroll behaviour.
*/
(function () {
    'use strict';

    var railControls = function (rail) {
        var scope = rail.closest('section') || rail.parentElement || document;
        return {
            previous: scope.querySelector('[data-egho-rail-prev]'),
            next: scope.querySelector('[data-egho-rail-next]'),
            nav: scope.querySelector('[data-egho-rail-nav]')
        };
    };

    var initInfiniteRail = function (rail) {
        var track = rail.querySelector('[data-egho-infinite-track]');
        var firstSet = rail.querySelector('[data-egho-infinite-set]');
        var controls = railControls(rail);

        if (!track || !firstSet) {
            return;
        }

        var items = firstSet.querySelectorAll('[data-egho-infinite-item]');
        var speed = parseFloat(rail.getAttribute('data-egho-speed') || '34');
        var offset = 0;
        var setWidth = 0;
        var lastFrame = 0;
        var manualDistance = 0;
        var frameId = null;

        if (!Number.isFinite(speed) || speed <= 0) {
            speed = 34;
        }

        var measure = function () {
            setWidth = firstSet.getBoundingClientRect().width;
        };

        var normalise = function () {
            if (setWidth <= 0) {
                return;
            }

            while (offset <= -setWidth) {
                offset += setWidth;
            }

            while (offset > 0) {
                offset -= setWidth;
            }
        };

        var render = function () {
            track.style.transform = 'translate3d(' + offset.toFixed(3) + 'px,0,0)';
        };

        var cardStep = function () {
            var card = firstSet.querySelector('[data-egho-infinite-item]');
            if (!card) {
                return Math.max(180, rail.clientWidth * 0.7);
            }

            var styles = window.getComputedStyle(firstSet);
            var gap = parseFloat(styles.columnGap || styles.gap || '0');
            return card.getBoundingClientRect().width + (Number.isFinite(gap) ? gap : 0);
        };

        var tick = function (timestamp) {
            if (!lastFrame) {
                lastFrame = timestamp;
            }

            var delta = Math.min((timestamp - lastFrame) / 1000, 0.05);
            lastFrame = timestamp;

            if (!document.hidden && setWidth > 0) {
                if (Math.abs(manualDistance) > 0.5) {
                    var manualSpeed = Math.max(520, speed * 12);
                    var amount = Math.min(Math.abs(manualDistance), manualSpeed * delta);
                    var signedAmount = manualDistance < 0 ? -amount : amount;
                    offset += signedAmount;
                    manualDistance -= signedAmount;
                } else {
                    manualDistance = 0;
                    offset -= speed * delta;
                }

                normalise();
                render();
            }

            frameId = window.requestAnimationFrame(tick);
        };

        var showControls = items.length > 1;
        if (controls.nav) {
            controls.nav.hidden = !showControls;
        }
        if (controls.previous) {
            controls.previous.hidden = !showControls;
            controls.previous.disabled = !showControls;
            controls.previous.addEventListener('click', function () {
                manualDistance += cardStep();
            });
        }
        if (controls.next) {
            controls.next.hidden = !showControls;
            controls.next.disabled = !showControls;
            controls.next.addEventListener('click', function () {
                manualDistance -= cardStep();
            });
        }

        var refresh = function () {
            measure();
            normalise();
            render();
        };

        window.addEventListener('resize', refresh);
        document.addEventListener('visibilitychange', function () {
            lastFrame = 0;
        });

        if (window.ResizeObserver) {
            var observer = new ResizeObserver(refresh);
            observer.observe(firstSet);
            observer.observe(rail);
        }

        refresh();
        rail.setAttribute('data-egho-rail-ready', 'true');
        frameId = window.requestAnimationFrame(tick);

        rail.addEventListener('remove', function () {
            if (frameId !== null) {
                window.cancelAnimationFrame(frameId);
            }
        });
    };

    var initPagedRail = function (rail) {
        var track = rail.querySelector('[data-egho-rail-track]');
        var controls = railControls(rail);
        var previous = controls.previous;
        var next = controls.next;

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
            if (controls.nav) controls.nav.hidden = !canScroll;
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

            timer = window.setTimeout(function tickPaged() {
                timer = null;

                if (document.hidden || pointerActive) {
                    schedule(delay);
                    return;
                }

                var max = limit();
                if (track.scrollLeft >= max - 3) direction = -1;
                else if (track.scrollLeft <= 3) direction = 1;

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

        sync();
        schedule(900);
    };

    var initRail = function (rail) {
        if (rail.hasAttribute('data-egho-infinite-slider')) {
            initInfiniteRail(rail);
            return;
        }

        initPagedRail(rail);
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
