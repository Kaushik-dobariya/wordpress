/**
 * SpiceCraft Lead & Product Enquiry Controller
 *
 * Handles modal display, product pre-selection, accessible keyboard focus trapping,
 * client-side validation, duplicate submission prevention, and AJAX submission.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var modal = document.getElementById('sc-enquiry-modal');
		if (!modal) {
			return;
		}

		var modalCloseBtn = document.getElementById('sc-enquiry-modal-close');
		var modalOverlay = modal.querySelector('.sc-enquiry-modal__overlay');
		var modalTitle = document.getElementById('sc-enquiry-modal-title');
		var modalDesc = document.getElementById('sc-enquiry-modal-desc');
		var form = document.getElementById('sc-modal-enquiry-form');
		var lastFocusedElement = null;

		var config = window.spicecraftEnquiryConfig || {
			ajaxUrl: '/wp-admin/admin-ajax.php',
			nonce: '',
			i18n: {
				submitting: 'Submitting Enquiry...',
				submit: 'Submit Enquiry',
				successHeading: 'Thank you for your enquiry!',
				successMessage: 'We have received your request and our team will get back to you shortly.',
				networkError: 'A network error occurred. Please try again or reach out on WhatsApp.',
				invalidEmail: 'Please enter a valid email address.',
				requiredFields: 'Please fill in all required fields.'
			}
		};

		/**
		 * Open the Enquiry Modal
		 *
		 * @param {Object} productData Optional product data object
		 * @param {HTMLElement} triggerEl Element that triggered the modal
		 */
		function openModal(productData, triggerEl) {
			lastFocusedElement = triggerEl || document.activeElement;

			// Populate or clear product context
			if (form) {
				var productIdInput = form.querySelector('.sc-enquiry-product-id');
				var pageUrlInput = form.querySelector('.sc-enquiry-page-url');
				var leadSourceInput = form.querySelector('.sc-enquiry-lead-source');
				var banner = document.getElementById(form.id + '_product_banner');
				var nameDisplay = banner ? banner.querySelector('.sc-enquiry-display-name') : null;
				var skuDisplay = banner ? banner.querySelector('.sc-enquiry-display-sku') : null;
				var packDisplay = banner ? banner.querySelector('.sc-enquiry-display-pack') : null;
				var packInput = form.querySelector('.sc-enquiry-pack-select, .sc-enquiry-pack-input, input[name="pack_size"], select[name="pack_size"]');
				var typeSelect = form.querySelector('select[name="enquiry_type"]');
				var feedback = document.getElementById(form.id + '_feedback');

				// Reset previous feedback & form state
				if (feedback) {
					feedback.style.display = 'none';
					feedback.className = 'sc-enquiry-feedback';
					feedback.innerHTML = '';
				}

				// Restore form fields visibility if previous submission hid them
				var fieldsWrap = form.querySelector('.sc-enquiry-fields');
				var footerWrap = form.querySelector('.sc-enquiry-footer');
				if (fieldsWrap) {
					fieldsWrap.style.display = '';
				}
				if (footerWrap) {
					footerWrap.style.display = '';
				}

				if (productData && productData.id) {
					if (productIdInput) {
						productIdInput.value = productData.id;
					}
					if (pageUrlInput && productData.url) {
						pageUrlInput.value = productData.url;
					}
					if (leadSourceInput) {
						leadSourceInput.value = 'Product Detail Page';
					}
					if (nameDisplay) {
						nameDisplay.textContent = productData.name || '';
					}
					if (skuDisplay) {
						skuDisplay.textContent = productData.sku ? ('SKU: ' + productData.sku) : '';
						skuDisplay.style.display = productData.sku ? '' : 'none';
					}
					if (productData.pack_size) {
						if (packDisplay) {
							packDisplay.textContent = 'Pack: ' + productData.pack_size;
							packDisplay.style.display = 'inline-block';
						}
						if (packInput) {
							packInput.value = productData.pack_size;
						}
					} else {
						if (packDisplay) {
							packDisplay.textContent = '';
							packDisplay.style.display = 'none';
						}
					}
					if (banner) {
						banner.classList.remove('is-hidden');
						banner.classList.add('is-active');
					}
					if (typeSelect) {
						typeSelect.value = 'product';
					}
					if (modalTitle) {
						modalTitle.textContent = 'Enquire About ' + (productData.name || 'This Product');
					}
					if (modalDesc) {
						modalDesc.textContent = 'Direct manufacturer inquiry for bulk supply, private labeling, or export pricing.';
					}
				} else {
					// General trade enquiry
					if (productIdInput) {
						productIdInput.value = '';
					}
					if (leadSourceInput) {
						leadSourceInput.value = 'General Contact / Trade Desk';
					}
					if (banner) {
						banner.classList.remove('is-active');
						banner.classList.add('is-hidden');
					}
					if (typeSelect && typeSelect.value === 'product') {
						typeSelect.value = 'general';
					}
					if (modalTitle) {
						modalTitle.textContent = 'Trade & Business Enquiry';
					}
					if (modalDesc) {
						modalDesc.textContent = 'Connect with our spice manufacturing specialists for commercial distribution, bulk sourcing, or custom specifications.';
					}
				}
			}

			// Show modal
			modal.style.display = 'flex';
			// Force reflow for CSS transition
			void modal.offsetWidth;
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';

			// Accessible focus trap: focus first focusable input
			var firstInput = modal.querySelector('input:not([type="hidden"]):not([tabindex="-1"]), select, textarea');
			if (firstInput) {
				setTimeout(function () {
					firstInput.focus();
				}, 100);
			}
		}

		/**
		 * Close the Enquiry Modal
		 */
		function closeModal() {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';

			setTimeout(function () {
				modal.style.display = 'none';
				if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
					lastFocusedElement.focus();
				}
			}, 250);
		}

		// Event Listeners for Opening Modal
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('.sc-open-enquiry-modal, #sc-open-enquiry-modal-btn');
			if (!trigger) {
				return;
			}
			e.preventDefault();

			// Special Handling: Multi-Product Enquiry from Favourites Page
			if (trigger.id === 'sc-enquire-favourites-btn') {
				var favTitles = [];
				document.querySelectorAll('#sc-favourites-grid .woocommerce-loop-product__title, #sc-favourites-grid .sc-catalog-card__title, #sc-favourites-grid h2, #sc-favourites-grid h3').forEach(function (titleEl) {
					var text = titleEl.textContent.trim();
					if (text && favTitles.indexOf(text) === -1) {
						favTitles.push(text);
					}
				});

				openModal(null, trigger);

				if (form) {
					var leadSourceInput = form.querySelector('.sc-enquiry-lead-source');
					var typeSelect = form.querySelector('select[name="enquiry_type"]');
					var messageTextarea = form.querySelector('textarea[name="message"]');

					if (leadSourceInput) {
						leadSourceInput.value = 'Favourites Shortlist';
					}
					if (typeSelect) {
						typeSelect.value = 'bulk';
					}
					if (modalTitle) {
						modalTitle.textContent = 'Enquiry for Shortlisted Products';
					}
					if (modalDesc) {
						modalDesc.textContent = 'Direct inquiry for institutional bulk supply and export pricing on your saved products.';
					}
					if (messageTextarea && favTitles.length) {
						messageTextarea.value = 'Hello SpiceCraft Sales Team,\n\nI would like to enquire about institutional bulk pricing, specifications, and supply availability for the following shortlisted items:\n- ' + favTitles.join('\n- ') + '\n\nPlease share quotation details.';
					}
				}
				return;
			}

			var productData = null;
			var prodId = trigger.getAttribute('data-product-id');
			if (prodId) {
				var activePack = trigger.getAttribute('data-product-pack') || '';
				if (!activePack) {
					var packLabelEl = document.getElementById('sc-selected-pack-label');
					if (packLabelEl) {
						activePack = packLabelEl.textContent.trim();
					}
				}

				productData = {
					id: prodId,
					name: trigger.getAttribute('data-product-name') || '',
					sku: trigger.getAttribute('data-product-sku') || '',
					url: trigger.getAttribute('data-product-url') || window.location.href,
					pack_size: activePack
				};
			}

			openModal(productData, trigger);
		});

		// Close button click
		if (modalCloseBtn) {
			modalCloseBtn.addEventListener('click', closeModal);
		}

		// Overlay click
		if (modalOverlay) {
			modalOverlay.addEventListener('click', closeModal);
		}

		// Escape key listener & Focus Trap
		document.addEventListener('keydown', function (e) {
			if (!modal.classList.contains('is-open')) {
				return;
			}

			if (e.key === 'Escape' || e.keyCode === 27) {
				closeModal();
				return;
			}

			// Keyboard Tab focus trap inside modal dialog
			if (e.key === 'Tab' || e.keyCode === 9) {
				var focusables = modal.querySelectorAll(
					'button:not([disabled]), input:not([type="hidden"]):not([disabled]):not([tabindex="-1"]), select:not([disabled]), textarea:not([disabled]), a[href]'
				);
				if (!focusables.length) {
					return;
				}

				var first = focusables[0];
				var last = focusables[focusables.length - 1];

				if (e.shiftKey) {
					if (document.activeElement === first) {
						last.focus();
						e.preventDefault();
					}
				} else {
					if (document.activeElement === last) {
						first.focus();
						e.preventDefault();
					}
				}
			}
		});

		// Clear product banner (switch to general)
		modal.addEventListener('click', function (e) {
			var clearBtn = e.target.closest('.sc-enquiry-clear-product');
			if (!clearBtn) {
				return;
			}
			e.preventDefault();

			if (form) {
				var productIdInput = form.querySelector('.sc-enquiry-product-id');
				var banner = document.getElementById(form.id + '_product_banner');
				var typeSelect = form.querySelector('select[name="enquiry_type"]');

				if (productIdInput) {
					productIdInput.value = '';
				}
				if (banner) {
					banner.classList.remove('is-active');
					banner.classList.add('is-hidden');
				}
				if (typeSelect) {
					typeSelect.value = 'general';
				}
				if (modalTitle) {
					modalTitle.textContent = 'Trade & Business Enquiry';
				}
			}
		});

		// Form Submission Handler
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();

				var feedback = document.getElementById(form.id + '_feedback');
				var submitBtn = form.querySelector('.sc-enquiry-submit-btn');
				var nameInput = form.querySelector('input[name="full_name"]');
				var emailInput = form.querySelector('input[name="email"]');
				var phoneInput = form.querySelector('input[name="phone"]');
				var countryInput = form.querySelector('input[name="country"]');
				var messageInput = form.querySelector('textarea[name="message"]');
				var consentInput = form.querySelector('input[name="consent"]');

				// Reset field errors
				form.querySelectorAll('.has-error').forEach(function (el) {
					el.classList.remove('has-error');
				});

				// Client validation
				var errors = [];
				if (!nameInput || !nameInput.value.trim()) {
					errors.push('Full Name is required.');
					if (nameInput) nameInput.classList.add('has-error');
				}

				var emailVal = emailInput ? emailInput.value.trim() : '';
				var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				if (!emailVal) {
					errors.push('Business Email is required.');
					if (emailInput) emailInput.classList.add('has-error');
				} else if (!emailRegex.test(emailVal)) {
					errors.push('Please enter a valid email address.');
					if (emailInput) emailInput.classList.add('has-error');
				}

				if (!phoneInput || !phoneInput.value.trim()) {
					errors.push('Phone / Mobile number is required.');
					if (phoneInput) phoneInput.classList.add('has-error');
				}

				if (!countryInput || !countryInput.value.trim()) {
					errors.push('Country / Destination is required.');
					if (countryInput) countryInput.classList.add('has-error');
				}

				if (!messageInput || !messageInput.value.trim()) {
					errors.push('Please detail your requirements in the message field.');
					if (messageInput) messageInput.classList.add('has-error');
				}

				if (consentInput && !consentInput.checked) {
					errors.push('Please agree to be contacted regarding this enquiry.');
					if (consentInput) consentInput.classList.add('has-error');
				}

				if (errors.length > 0) {
					if (feedback) {
						feedback.style.display = 'block';
						feedback.className = 'sc-enquiry-feedback sc-enquiry-feedback--error';
						var listHtml = '<ul>';
						errors.forEach(function (err) {
							listHtml += '<li>' + err + '</li>';
						});
						listHtml += '</ul>';
						feedback.innerHTML = '<strong>' + (config.i18n.requiredFields || 'Please correct the errors below:') + '</strong>' + listHtml;
						feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
					return;
				}

				// Prevent double submissions: disable button & show spinner
				if (submitBtn) {
					submitBtn.disabled = true;
					submitBtn.classList.add('is-loading');
				}

				// Build FormData
				var formData = new FormData(form);

				fetch(config.ajaxUrl, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
					.then(function (response) {
						return response.json();
					})
					.then(function (data) {
						if (data && data.success) {
							// Success: Hide fields & show confirmation message
							var fieldsWrap = form.querySelector('.sc-enquiry-fields');
							var footerWrap = form.querySelector('.sc-enquiry-footer');
							var banner = document.getElementById(form.id + '_product_banner');

							if (fieldsWrap) {
								fieldsWrap.style.display = 'none';
							}
							if (footerWrap) {
								footerWrap.style.display = 'none';
							}
							if (banner) {
								banner.style.display = 'none';
							}

							if (feedback) {
								feedback.style.display = 'block';
								feedback.className = 'sc-enquiry-feedback sc-enquiry-feedback--success';
								feedback.innerHTML =
									'<div class="sc-enquiry-feedback__icon" aria-hidden="true">' +
										'<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
											'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>' +
											'<polyline points="22 4 12 14.01 9 11.01"></polyline>' +
										'</svg>' +
									'</div>' +
									'<h4 class="sc-enquiry-feedback__title">' + (config.i18n.successHeading || 'Thank you for your enquiry!') + '</h4>' +
									'<p class="sc-enquiry-feedback__message">' + (data.data && data.data.message ? data.data.message : config.i18n.successMessage) + '</p>' +
									'<button type="button" class="sc-enquiry-feedback__close-btn" id="sc-enquiry-done-btn">Done</button>';

								var doneBtn = document.getElementById('sc-enquiry-done-btn');
								if (doneBtn) {
									doneBtn.addEventListener('click', closeModal);
									doneBtn.focus();
								}
							}

							// Reset form so next submission is clean
							form.reset();
						} else {
							// Server error response
							if (submitBtn) {
								submitBtn.disabled = false;
								submitBtn.classList.remove('is-loading');
							}
							if (feedback) {
								feedback.style.display = 'block';
								feedback.className = 'sc-enquiry-feedback sc-enquiry-feedback--error';
								var errMsg = (data && data.data && data.data.message) ? data.data.message : 'Submission failed. Please verify your entries.';
								feedback.innerHTML = '<strong>' + errMsg + '</strong>';
								feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
							}
						}
					})
					.catch(function () {
						if (submitBtn) {
							submitBtn.disabled = false;
							submitBtn.classList.remove('is-loading');
						}
						if (feedback) {
							feedback.style.display = 'block';
							feedback.className = 'sc-enquiry-feedback sc-enquiry-feedback--error';
							feedback.innerHTML = '<strong>' + (config.i18n.networkError || 'A network error occurred. Please try again.') + '</strong>';
							feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
						}
					});
			});
		}
	});
})();
