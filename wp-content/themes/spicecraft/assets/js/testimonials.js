/**
 * SpiceCraft - Testimonials Progressive Enhancement
 *
 * Lightweight, vanilla JavaScript progressive enhancement for testimonial sliders.
 * Works seamlessly with CSS grid fallbacks without external dependencies.
 * Touch-friendly, keyboard accessible, pauses on hover and focus.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var sliders = document.querySelectorAll('[data-testimonial-slider]');
		if (!sliders.length) {
			return;
		}

		sliders.forEach(function (slider) {
			initTestimonialSlider(slider);
		});
	});

	/**
	 * Initialize an accessible testimonial slider.
	 *
	 * @param {HTMLElement} sliderContainer
	 */
	function initTestimonialSlider(sliderContainer) {
		var track = sliderContainer.querySelector('.sc-testimonials-track') || sliderContainer.querySelector('.sc-testimonials-grid');
		if (!track) {
			return;
		}

		var cards = track.querySelectorAll('.sc-testimonial-card');
		if (cards.length <= 1) {
			return;
		}

		var prevBtn = sliderContainer.querySelector('[data-slider-prev]');
		var nextBtn = sliderContainer.querySelector('[data-slider-next]');
		var currentIndex = 0;
		var autoplayTimer = null;
		var autoplayDelay = parseInt(sliderContainer.getAttribute('data-autoplay-delay'), 10) || 6000;
		var isAutoplay = sliderContainer.getAttribute('data-autoplay') === 'true';

		// Set accessibility attributes
		track.setAttribute('role', 'region');
		track.setAttribute('aria-roledescription', 'carousel');
		track.setAttribute('aria-live', 'polite');

		cards.forEach(function (card, index) {
			card.setAttribute('role', 'group');
			card.setAttribute('aria-roledescription', 'slide');
			card.setAttribute('aria-label', (index + 1) + ' of ' + cards.length);
		});

		function scrollToIndex(index) {
			if (index < 0) {
				index = cards.length - 1;
			} else if (index >= cards.length) {
				index = 0;
			}
			currentIndex = index;

			var targetCard = cards[currentIndex];
			if (targetCard) {
				var leftPos = targetCard.offsetLeft - track.offsetLeft;
				track.scrollTo({
					left: leftPos,
					behavior: 'smooth'
				});
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function (e) {
				e.preventDefault();
				scrollToIndex(currentIndex - 1);
				resetAutoplay();
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function (e) {
				e.preventDefault();
				scrollToIndex(currentIndex + 1);
				resetAutoplay();
			});
		}

		// Keyboard navigation
		sliderContainer.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') {
				e.preventDefault();
				scrollToIndex(currentIndex - 1);
				resetAutoplay();
			} else if (e.key === 'ArrowRight') {
				e.preventDefault();
				scrollToIndex(currentIndex + 1);
				resetAutoplay();
			}
		});

		// Autoplay with hover & focus pause
		function startAutoplay() {
			if (!isAutoplay) return;
			stopAutoplay();
			autoplayTimer = setInterval(function () {
				scrollToIndex(currentIndex + 1);
			}, autoplayDelay);
		}

		function stopAutoplay() {
			if (autoplayTimer) {
				clearInterval(autoplayTimer);
				autoplayTimer = null;
			}
		}

		function resetAutoplay() {
			stopAutoplay();
			startAutoplay();
		}

		sliderContainer.addEventListener('mouseenter', stopAutoplay);
		sliderContainer.addEventListener('mouseleave', startAutoplay);
		sliderContainer.addEventListener('focusin', stopAutoplay);
		sliderContainer.addEventListener('focusout', startAutoplay);

		startAutoplay();
	}
})();
