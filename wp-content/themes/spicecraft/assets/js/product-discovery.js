/**
 * SpiceCraft - Product Discovery, Favourites, Recently Viewed & Search JavaScript
 *
 * Implements:
 * 1. Favourites Manager (localStorage 'spicecraft_favourites', header count, cards sync, /favourites/ page)
 * 2. Recently Viewed Tracker (localStorage 'spicecraft_recently_viewed', single-product exclude current)
 * 3. Mobile Filter Off-Canvas Drawer (accessible focus trapping, body scroll lock, Escape key)
 * 4. Desktop Filter Dropdown auto-submit
 * 5. Live Search suggestions (debounced 300ms AJAX, keyboard navigation)
 * 6. Product Detail Share Action (Web Share API + clipboard copy fallback)
 * 7. Pack Size Selection & Enquiry Validation
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	const config = window.spicecraftConfig || {};
	const ajaxUrl = config.ajaxUrl || '/wp-admin/admin-ajax.php';
	const nonce = config.nonce || '';
	const i18n = config.i18n || {};

	// =========================================================================
	// 1. FAVOURITES MANAGER (localStorage: spicecraft_favourites)
	// =========================================================================
	const FAVOURITES_KEY = 'spicecraft_favourites';

	function getFavourites() {
		try {
			const data = localStorage.getItem(FAVOURITES_KEY);
			if (!data) return [];
			const parsed = JSON.parse(data);
			return Array.isArray(parsed) ? parsed.map(function (id) { return parseInt(id, 10); }).filter(Boolean) : [];
		} catch (e) {
			console.warn('SpiceCraft: localStorage inaccessible or corrupt', e);
			return [];
		}
	}

	function saveFavourites(ids) {
		try {
			const validIds = Array.isArray(ids) ? ids : [];
			const uniqueIds = Array.from(new Set(validIds.map(function (id) { return parseInt(id, 10); }).filter(Boolean)));
			localStorage.setItem(FAVOURITES_KEY, JSON.stringify(uniqueIds));
			updateFavouritesHeaderCount(uniqueIds.length);
			return uniqueIds;
		} catch (e) {
			console.warn('SpiceCraft: failed to save favourites to localStorage', e);
			return ids;
		}
	}

	function updateFavouritesHeaderCount(count) {
		const badgeElements = document.querySelectorAll('.sc-header-favourite .sc-badge-count');
		badgeElements.forEach(function (badge) {
			badge.textContent = count;
		});
	}

	function syncFavouriteButtons() {
		const favs = getFavourites();
		updateFavouritesHeaderCount(favs.length);

		// 1a. Sync Product Cards
		const cardButtons = document.querySelectorAll('.sc-product-card__favourite');
		cardButtons.forEach(function (btn) {
			const prodId = parseInt(btn.getAttribute('data-product-id'), 10);
			const card = btn.closest('.sc-product-card');
			const titleEl = card ? card.querySelector('.sc-product-card__title a') : null;
			const prodName = titleEl ? titleEl.textContent.trim() : 'Product';

			if (favs.includes(prodId)) {
				btn.classList.add('is-favourite');
				btn.setAttribute('aria-pressed', 'true');
				btn.setAttribute('aria-label', 'Remove ' + prodName + ' from favourites');
				btn.setAttribute('title', 'Saved to Favourites');
			} else {
				btn.classList.remove('is-favourite');
				btn.setAttribute('aria-pressed', 'false');
				btn.setAttribute('aria-label', 'Add ' + prodName + ' to favourites');
				btn.setAttribute('title', 'Save to Favourites');
			}
		});

		// 1b. Sync Single Product Favourite Button
		const singleFavBtn = document.querySelector('.sc-single-product__fav-btn');
		if (singleFavBtn) {
			const singleProdId = parseInt(singleFavBtn.getAttribute('data-product-id'), 10);
			const favText = singleFavBtn.querySelector('.sc-fav-text');

			if (favs.includes(singleProdId)) {
				singleFavBtn.classList.add('is-favourite');
				singleFavBtn.setAttribute('aria-pressed', 'true');
				if (favText) {
					favText.textContent = 'Saved in Favourites';
				}
			} else {
				singleFavBtn.classList.remove('is-favourite');
				singleFavBtn.setAttribute('aria-pressed', 'false');
				if (favText) {
					favText.textContent = 'Save to Favourites';
				}
			}
		}
	}

	function toggleFavourite(productId) {
		const id = parseInt(productId, 10);
		if (!id) return;

		let favs = getFavourites();
		if (favs.includes(id)) {
			favs = favs.filter(function (item) { return item !== id; });
		} else {
			favs.push(id);
		}
		saveFavourites(favs);
		syncFavouriteButtons();
	}

	// Delegate click on card favourite buttons
	document.addEventListener('click', function (e) {
		const cardFavBtn = e.target.closest('.sc-product-card__favourite');
		if (cardFavBtn) {
			e.preventDefault();
			e.stopPropagation();
			const prodId = cardFavBtn.getAttribute('data-product-id');
			toggleFavourite(prodId);
			return;
		}

		const singleFavBtn = e.target.closest('.sc-single-product__fav-btn');
		if (singleFavBtn) {
			e.preventDefault();
			const prodId = singleFavBtn.getAttribute('data-product-id');
			toggleFavourite(prodId);
			return;
		}
	});

	// Initial sync on page load
	syncFavouriteButtons();

	// 1c. Favourites Page Renderer
	const favRoot = document.getElementById('sc-favourites-root');
	if (favRoot) {
		const favLoading = document.getElementById('sc-favourites-loading');
		const favGridWrap = document.getElementById('sc-favourites-grid-wrap');
		const favGrid = document.getElementById('sc-favourites-grid');
		const favEmpty = document.getElementById('sc-favourites-empty');
		const favCountLabel = document.getElementById('sc-favourites-count-label');
		const clearBtn = document.getElementById('sc-clear-favourites-btn');

		const storedIds = getFavourites();

		if (storedIds.length === 0) {
			if (favLoading) favLoading.style.display = 'none';
			if (favEmpty) favEmpty.style.display = 'block';
		} else {
			// Fetch rendered cards via AJAX
			const formData = new FormData();
			formData.append('action', 'spicecraft_get_product_cards');
			formData.append('nonce', nonce);
			storedIds.forEach(function (id) {
				formData.append('product_ids[]', id);
			});

			fetch(ajaxUrl, {
				method: 'POST',
				body: formData
			})
			.then(function (res) { return res.json(); })
			.then(function (response) {
				if (favLoading) favLoading.style.display = 'none';

				if (response.success && response.data.count > 0) {
					if (favGrid) favGrid.innerHTML = response.data.html;
					if (favGridWrap) favGridWrap.style.display = 'block';
					if (favCountLabel) {
						favCountLabel.textContent = response.data.count + (response.data.count === 1 ? ' Product Saved' : ' Products Saved');
					}

					// Purge deleted/private product IDs from localStorage if any
					if (response.data.valid_ids) {
						saveFavourites(response.data.valid_ids);
					}

					syncFavouriteButtons();
				} else {
					if (favEmpty) favEmpty.style.display = 'block';
					saveFavourites([]);
				}
			})
			.catch(function (err) {
				console.error('SpiceCraft: Error fetching favourites', err);
				if (favLoading) favLoading.style.display = 'none';
				if (favEmpty) favEmpty.style.display = 'block';
			});
		}

		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				saveFavourites([]);
				if (favGridWrap) favGridWrap.style.display = 'none';
				if (favEmpty) favEmpty.style.display = 'block';
				syncFavouriteButtons();
			});
		}
	}

	// =========================================================================
	// 2. RECENTLY VIEWED TRACKER (localStorage: spicecraft_recently_viewed)
	// =========================================================================
	const RECENTLY_VIEWED_KEY = 'spicecraft_recently_viewed';
	const MAX_RECENT_HISTORY = 10;

	function getRecentlyViewed() {
		try {
			const data = localStorage.getItem(RECENTLY_VIEWED_KEY);
			if (!data) return [];
			const parsed = JSON.parse(data);
			return Array.isArray(parsed) ? parsed.map(function (id) { return parseInt(id, 10); }).filter(Boolean) : [];
		} catch (e) {
			return [];
		}
	}

	function recordProductView(currentId) {
		if (!currentId) return;
		let recent = getRecentlyViewed();
		// Remove if exists to move to top
		recent = recent.filter(function (id) { return id !== currentId; });
		recent.unshift(currentId);
		if (recent.length > MAX_RECENT_HISTORY) {
			recent = recent.slice(0, MAX_RECENT_HISTORY);
		}
		try {
			localStorage.setItem(RECENTLY_VIEWED_KEY, JSON.stringify(recent));
		} catch (e) {
			console.warn('SpiceCraft: failed to store recently viewed item', e);
		}
	}

	const singleProductRoot = document.querySelector('.sc-single-product');
	if (singleProductRoot) {
		const currentProdId = parseInt(singleProductRoot.getAttribute('data-product-id'), 10);
		if (currentProdId) {
			// 2a. Record this product view
			recordProductView(currentProdId);

			// 2b. Populate Recently Viewed section excluding current product
			const recentSection = document.getElementById('sc-recently-viewed');
			const recentGrid = document.getElementById('sc-recently-viewed-grid');

			if (recentSection && recentGrid) {
				const recentIds = getRecentlyViewed().filter(function (id) {
					return id !== currentProdId;
				});

				if (recentIds.length > 0) {
					const displayIds = recentIds.slice(0, 4);
					const formData = new FormData();
					formData.append('action', 'spicecraft_get_product_cards');
					formData.append('nonce', nonce);
					displayIds.forEach(function (id) {
						formData.append('product_ids[]', id);
					});

					fetch(ajaxUrl, {
						method: 'POST',
						body: formData
					})
					.then(function (res) { return res.json(); })
					.then(function (response) {
						if (response.success && response.data.count > 0) {
							recentGrid.innerHTML = response.data.html;
							recentSection.style.display = 'block';
							syncFavouriteButtons();
						}
					})
					.catch(function (err) {
						console.error('SpiceCraft: Error rendering recently viewed', err);
					});
				}
			}
		}
	}

	// =========================================================================
	// 3. MOBILE OFF-CANVAS FILTER DRAWER
	// =========================================================================
	const mobileFilterBtn = document.getElementById('sc-mobile-filter-btn');
	const filterDrawer = document.getElementById('sc-filter-drawer');
	const drawerBackdrop = document.getElementById('sc-filter-drawer-backdrop');
	const closeDrawerBtn = document.getElementById('sc-close-filter-drawer');

	function openFilterDrawer() {
		if (filterDrawer && drawerBackdrop) {
			filterDrawer.classList.add('is-active');
			drawerBackdrop.classList.add('is-active');
			document.body.style.overflow = 'hidden';
			if (mobileFilterBtn) {
				mobileFilterBtn.setAttribute('aria-expanded', 'true');
			}
			if (closeDrawerBtn) {
				closeDrawerBtn.focus();
			}
		}
	}

	function closeFilterDrawer() {
		if (filterDrawer && drawerBackdrop) {
			filterDrawer.classList.remove('is-active');
			drawerBackdrop.classList.remove('is-active');
			document.body.style.overflow = '';
			if (mobileFilterBtn) {
				mobileFilterBtn.setAttribute('aria-expanded', 'false');
				mobileFilterBtn.focus();
			}
		}
	}

	if (mobileFilterBtn) {
		mobileFilterBtn.addEventListener('click', openFilterDrawer);
	}
	if (closeDrawerBtn) {
		closeDrawerBtn.addEventListener('click', closeFilterDrawer);
	}
	if (drawerBackdrop) {
		drawerBackdrop.addEventListener('click', closeFilterDrawer);
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && filterDrawer && filterDrawer.classList.contains('is-active')) {
			closeFilterDrawer();
		}
	});

	// =========================================================================
	// 4. DESKTOP FILTER BAR AUTO-SUBMIT
	// =========================================================================
	const desktopFilterForm = document.getElementById('sc-desktop-filter-form');
	if (desktopFilterForm) {
		const selects = desktopFilterForm.querySelectorAll('.sc-filter-select');
		selects.forEach(function (sel) {
			sel.addEventListener('change', function () {
				desktopFilterForm.submit();
			});
		});
	}

	// =========================================================================
	// 5. LIVE SEARCH SUGGESTIONS
	// =========================================================================
	const searchInput = document.getElementById('sc-catalog-search-input');
	const searchResults = document.getElementById('sc-live-search-results');
	let searchTimeout = null;

	if (searchInput && searchResults) {
		searchInput.addEventListener('input', function () {
			const query = searchInput.value.trim();
			clearTimeout(searchTimeout);

			if (query.length < 2) {
				searchResults.innerHTML = '';
				searchResults.style.display = 'none';
				return;
			}

			searchTimeout = setTimeout(function () {
				const formData = new FormData();
				formData.append('action', 'spicecraft_live_search');
				formData.append('nonce', nonce);
				formData.append('query', query);

				fetch(ajaxUrl, {
					method: 'POST',
					body: formData
				})
				.then(function (res) { return res.json(); })
				.then(function (response) {
					if (response.success && response.data.results && response.data.results.length > 0) {
						let html = '';
						response.data.results.forEach(function (item) {
							html += '<a href="' + item.permalink + '" class="sc-live-search-item" role="option">';
							if (item.thumb) {
								html += '<img src="' + item.thumb + '" alt="" class="sc-live-search-thumb" />';
							} else {
								html += '<div class="sc-live-search-thumb"></div>';
							}
							html += '<div class="sc-live-search-info">';
							html += '<span class="sc-live-search-title">' + item.title + '</span>';
							if (item.category) {
								html += '<span class="sc-live-search-cat">' + item.category + '</span>';
							}
							html += '</div></a>';
						});
						searchResults.innerHTML = html;
						searchResults.style.display = 'block';
					} else {
						searchResults.innerHTML = '<div class="sc-live-search-empty">' + (i18n.noLiveResults || 'No products found') + '</div>';
						searchResults.style.display = 'block';
					}
				})
				.catch(function (err) {
					console.warn('SpiceCraft: Live search error', err);
				});
			}, 300);
		});

		// Close dropdown on click outside
		document.addEventListener('click', function (e) {
			if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
				searchResults.style.display = 'none';
			}
		});

		// Keyboard navigation inside search suggestions
		searchInput.addEventListener('keydown', function (e) {
			const items = searchResults.querySelectorAll('.sc-live-search-item');
			if (!items.length || searchResults.style.display === 'none') return;

			let activeIndex = -1;
			items.forEach(function (item, idx) {
				if (item.classList.contains('is-highlighted')) {
					activeIndex = idx;
				}
			});

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				if (activeIndex >= 0) items[activeIndex].classList.remove('is-highlighted');
				const nextIndex = (activeIndex + 1) % items.length;
				items[nextIndex].classList.add('is-highlighted');
				items[nextIndex].scrollIntoView({ block: 'nearest' });
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				if (activeIndex >= 0) items[activeIndex].classList.remove('is-highlighted');
				const prevIndex = (activeIndex - 1 + items.length) % items.length;
				items[prevIndex].classList.add('is-highlighted');
				items[prevIndex].scrollIntoView({ block: 'nearest' });
			} else if (e.key === 'Enter') {
				if (activeIndex >= 0) {
					e.preventDefault();
					window.location.href = items[activeIndex].getAttribute('href');
				}
			} else if (e.key === 'Escape') {
				searchResults.style.display = 'none';
			}
		});
	}

	// =========================================================================
	// 6. SINGLE PRODUCT SHARE ACTION
	// =========================================================================
	const shareBtn = document.getElementById('sc-share-product-btn');
	const shareToast = document.getElementById('sc-share-toast');

	if (shareBtn) {
		shareBtn.addEventListener('click', function () {
			const title = shareBtn.getAttribute('data-title') || document.title;
			const url = shareBtn.getAttribute('data-url') || window.location.href;

			if (navigator.share) {
				navigator.share({
					title: title,
					url: url
				}).catch(function (err) {
					// User cancelled or share failed, fallback silently
				});
			} else if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(function () {
					showShareToast(i18n.copiedSuccess || 'Product link copied to clipboard!');
				}).catch(function () {
					fallbackCopyText(url);
				});
			} else {
				fallbackCopyText(url);
			}
		});
	}

	function fallbackCopyText(text) {
		const textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.left = '-9999px';
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();
		try {
			document.execCommand('copy');
			showShareToast(i18n.copiedSuccess || 'Product link copied to clipboard!');
		} catch (err) {
			console.warn('SpiceCraft: Copy failed', err);
		}
		document.body.removeChild(textArea);
	}

	function showShareToast(message) {
		if (!shareToast) return;
		shareToast.textContent = message;
		shareToast.style.display = 'inline-block';
		setTimeout(function () {
			shareToast.style.display = 'none';
		}, 2500);
	}

	// =========================================================================
	// 7. PACK SIZE SELECTION & ENQUIRY VALIDATION
	// =========================================================================
	const packSelector = document.getElementById('sc-pack-selector');
	const packValidationNotice = document.getElementById('sc-pack-validation-notice');
	const whatsappEnquiryBtn = document.getElementById('sc-whatsapp-enquiry-cta');
	const emailEnquiryBtn = document.getElementById('sc-email-enquiry-cta');

	if (packSelector) {
		const packPills = packSelector.querySelectorAll('.sc-pack-pill');

		// Hide validation notice when a pack size is clicked
		packPills.forEach(function (pill) {
			pill.addEventListener('click', function () {
				if (packValidationNotice) {
					packValidationNotice.style.display = 'none';
				}
			});
		});

		function validatePackSelection(e) {
			const hasSelection = packSelector.querySelector('.sc-pack-pill.is-selected');
			if (!hasSelection) {
				e.preventDefault();
				if (packValidationNotice) {
					packValidationNotice.style.display = 'flex';
					packValidationNotice.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
				const firstPill = packSelector.querySelector('.sc-pack-pill');
				if (firstPill) {
					firstPill.focus();
				}
				return false;
			}
			return true;
		}

		if (whatsappEnquiryBtn) {
			whatsappEnquiryBtn.addEventListener('click', validatePackSelection);
		}
		if (emailEnquiryBtn) {
			emailEnquiryBtn.addEventListener('click', validatePackSelection);
		}
	}
});
