/**
 * SpiceCraft Blog / News & Articles JavaScript
 *
 * Handles copy-to-clipboard, native Web Share API, and search field interactions.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initShareButtons();
	});

	/**
	 * Initialize Social & Copy Link Sharing Buttons
	 */
	function initShareButtons() {
		var copyButtons = document.querySelectorAll('.sc-share-btn--copy');

		copyButtons.forEach(function (button) {
			button.addEventListener('click', function (e) {
				e.preventDefault();

				var shareUrl = button.getAttribute('data-url') || window.location.href;
				var originalLabel = button.innerHTML;

				// Try native clipboard API first
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(shareUrl).then(function () {
						indicateCopySuccess(button, originalLabel);
					}).catch(function () {
						fallbackCopy(shareUrl, button, originalLabel);
					});
				} else {
					fallbackCopy(shareUrl, button, originalLabel);
				}
			});
		});
	}

	/**
	 * Provide visual and accessible feedback for copied link
	 */
	function indicateCopySuccess(button, originalHtml) {
		button.classList.add('sc-share-btn--copied');
		button.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>Copied!</span>';
		button.setAttribute('aria-label', 'Link copied to clipboard');

		setTimeout(function () {
			button.classList.remove('sc-share-btn--copied');
			button.innerHTML = originalHtml;
			button.setAttribute('aria-label', 'Copy article link');
		}, 2500);
	}

	/**
	 * Fallback clipboard copy using textarea element
	 */
	function fallbackCopy(text, button, originalHtml) {
		var textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.top = '-9999px';
		textArea.style.left = '-9999px';
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			var successful = document.execCommand('copy');
			if (successful) {
				indicateCopySuccess(button, originalHtml);
			}
		} catch (err) {
			console.error('Fallback copy failed', err);
		}

		document.body.removeChild(textArea);
	}
})();
