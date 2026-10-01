/**
 * SpiceCraft Recipe Interactive Engine
 *
 * Handles:
 * - Recipe archive filter dropdown auto-submit & mobile off-canvas drawer
 * - Interactive ingredient checklist state (client-side only)
 * - Accessible Web Share API with clipboard copy fallback and toast
 * - Accessible keyboard navigation & ARIA state management
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initRecipeFilters();
		initMobileDrawer();
		initIngredientChecklist();
		initShareToolbar();
	});

	/**
	 * 1. Recipe Archive Filters
	 */
	function initRecipeFilters() {
		var filterForm = document.getElementById('sc-recipe-filter-form');
		if (!filterForm) {
			return;
		}

		// Auto-submit on desktop dropdown change
		var dropdowns = filterForm.querySelectorAll('.sc-filter-select');
		dropdowns.forEach(function (select) {
			select.addEventListener('change', function () {
				filterForm.submit();
			});
		});

		// Clear search button
		var clearBtn = document.getElementById('sc-clear-search-btn');
		var searchInput = document.getElementById('sc-recipe-search-input');
		if (clearBtn && searchInput) {
			clearBtn.addEventListener('click', function () {
				searchInput.value = '';
				filterForm.submit();
			});
		}
	}

	/**
	 * 2. Mobile Filter Off-Canvas Drawer
	 */
	function initMobileDrawer() {
		var drawer = document.getElementById('sc-recipe-filter-drawer');
		var openBtn = document.querySelector('.sc-filter-drawer-open');
		if (!drawer || !openBtn) {
			return;
		}

		var backdrop = drawer.querySelector('.sc-filter-drawer__backdrop');
		var closeBtn = drawer.querySelector('.sc-filter-drawer__close');
		var applyBtn = drawer.querySelector('.sc-filter-drawer__apply');
		var drawerBody = document.getElementById('sc-mobile-filter-body');
		var filterForm = document.getElementById('sc-recipe-filter-form');

		// Populate drawer body from desktop filter fields if empty
		if (drawerBody && filterForm && drawerBody.children.length === 0) {
			var desktopDropdowns = filterForm.querySelector('.sc-discovery-dropdowns');
			if (desktopDropdowns) {
				var clonedDropdowns = desktopDropdowns.cloneNode(true);
				// Update IDs to avoid duplication
				var clonedSelects = clonedDropdowns.querySelectorAll('select');
				clonedSelects.forEach(function (sel) {
					sel.id = 'mobile-' + sel.id;
				});
				var clonedLabels = clonedDropdowns.querySelectorAll('label');
				clonedLabels.forEach(function (lbl) {
					if (lbl.htmlFor) {
						lbl.htmlFor = 'mobile-' + lbl.htmlFor;
					}
				});
				drawerBody.appendChild(clonedDropdowns);
			}
		}

		function openDrawer() {
			drawer.classList.add('is-active');
			drawer.setAttribute('aria-hidden', 'false');
			openBtn.setAttribute('aria-expanded', 'true');
			document.body.style.overflow = 'hidden';
			if (closeBtn) {
				closeBtn.focus();
			}
		}

		function closeDrawer() {
			drawer.classList.remove('is-active');
			drawer.setAttribute('aria-hidden', 'true');
			openBtn.setAttribute('aria-expanded', 'false');
			document.body.style.overflow = '';
			openBtn.focus();
		}

		openBtn.addEventListener('click', openDrawer);

		if (closeBtn) {
			closeBtn.addEventListener('click', closeDrawer);
		}

		if (backdrop) {
			backdrop.addEventListener('click', closeDrawer);
		}

		// Close on Escape key
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && drawer.classList.contains('is-active')) {
				closeDrawer();
			}
		});

		// Apply button in mobile drawer
		if (applyBtn && filterForm && drawerBody) {
			applyBtn.addEventListener('click', function () {
				var mobileSelects = drawerBody.querySelectorAll('select');
				mobileSelects.forEach(function (mSel) {
					var origId = mSel.id.replace('mobile-', '');
					var origSel = document.getElementById(origId);
					if (origSel) {
						origSel.value = mSel.value;
					}
				});
				closeDrawer();
				filterForm.submit();
			});
		}
	}

	/**
	 * 3. Client-Side Interactive Ingredient Checklist
	 */
	function initIngredientChecklist() {
		var checkboxes = document.querySelectorAll('.sc-ingredient-checkbox');
		if (!checkboxes || checkboxes.length === 0) {
			return;
		}

		// Identify current recipe post ID for optional local state
		var recipeArticle = document.querySelector('article.sc-single-recipe');
		var storageKey = '';
		if (recipeArticle && recipeArticle.id) {
			storageKey = 'spicecraft_ing_' + recipeArticle.id;
		}

		// Restore saved state if available
		var savedState = {};
		if (storageKey && window.localStorage) {
			try {
				var stored = localStorage.getItem(storageKey);
				if (stored) {
					savedState = JSON.parse(stored);
				}
			} catch (err) {
				savedState = {};
			}
		}

		checkboxes.forEach(function (cb, idx) {
			var row = cb.closest('.sc-ingredient-item');
			var itemKey = 'item_' + idx;

			// Restore checkmark if stored
			if (savedState[itemKey]) {
				cb.checked = true;
				if (row) {
					row.classList.add('is-checked');
				}
			}

			cb.addEventListener('change', function () {
				if (row) {
					if (cb.checked) {
						row.classList.add('is-checked');
						savedState[itemKey] = true;
					} else {
						row.classList.remove('is-checked');
						delete savedState[itemKey];
					}
				}

				if (storageKey && window.localStorage) {
					try {
						localStorage.setItem(storageKey, JSON.stringify(savedState));
					} catch (e) {
						// Quota or disabled, fail silently
					}
				}
			});
		});
	}

	/**
	 * 4. Share Toolbar & Copy Link Fallback
	 */
	function initShareToolbar() {
		var shareBtn = document.querySelector('.sc-share-btn');
		var toast = document.getElementById('sc-share-toast');

		if (!shareBtn) {
			return;
		}

		shareBtn.addEventListener('click', function (e) {
			e.preventDefault();

			var title = shareBtn.getAttribute('data-title') || document.title;
			var url = shareBtn.getAttribute('data-url') || window.location.href;

			if (navigator.share) {
				navigator.share({
					title: title,
					text: title + ' - SpiceCraft Master Kitchen Recipe',
					url: url
				}).catch(function (err) {
					if (err.name !== 'AbortError') {
						copyToClipboard(url, toast);
					}
				});
			} else {
				copyToClipboard(url, toast);
			}
		});
	}

	function copyToClipboard(text, toast) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(function () {
				showToast(toast);
			}).catch(function () {
				fallbackCopy(text, toast);
			});
		} else {
			fallbackCopy(text, toast);
		}
	}

	function fallbackCopy(text, toast) {
		var textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.top = '0';
		textarea.style.left = '0';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.focus();
		textarea.select();

		try {
			var success = document.execCommand('copy');
			if (success) {
				showToast(toast);
			}
		} catch (e) {
			// Copy failed
		}
		document.body.removeChild(textarea);
	}

	function showToast(toast) {
		if (!toast) {
			return;
		}
		toast.classList.add('is-visible');
		setTimeout(function () {
			toast.classList.remove('is-visible');
		}, 3200);
	}

})();
