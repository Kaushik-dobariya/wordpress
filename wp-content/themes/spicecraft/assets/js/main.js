/**
 * SpiceCraft - Vanilla JavaScript Architecture
 *
 * Lightweight, accessible frontend behaviors without framework bloat.
 * Handles mobile drawer toggle, search drawer toggle, dropdown submenus,
 * keyboard accessibility traps, and header scroll state.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Access localized WordPress parameters safely
    const config = window.spicecraftConfig || {};

    // =========================================================================
    // 1. Mobile Menu Toggle & Keyboard Trap & Submenu Expansion
    // =========================================================================
    const menuToggle = document.querySelector('.sc-menu-toggle');
    const mobileDrawer = document.querySelector('.sc-mobile-drawer');

    if (menuToggle && mobileDrawer) {
        menuToggle.addEventListener('click', function () {
            const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
            mobileDrawer.classList.toggle('is-active');
            document.body.classList.toggle('sc-menu-open');

            const label = !isExpanded 
                ? (config.i18n && config.i18n.menuClose ? config.i18n.menuClose : 'Close Navigation Menu')
                : (config.i18n && config.i18n.menuOpen ? config.i18n.menuOpen : 'Open Navigation Menu');
            menuToggle.setAttribute('aria-label', label);
        });

        // Close mobile drawer on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mobileDrawer.classList.contains('is-active')) {
                menuToggle.setAttribute('aria-expanded', 'false');
                mobileDrawer.classList.remove('is-active');
                document.body.classList.remove('sc-menu-open');
                menuToggle.focus();
            }
        });

        // Mobile submenu tap to expand
        const mobileParentItems = mobileDrawer.querySelectorAll('.menu-item-has-children > a');
        mobileParentItems.forEach(function (parentLink) {
            parentLink.addEventListener('click', function (e) {
                const parentLi = parentLink.parentElement;
                const subMenu = parentLi.querySelector('.sub-menu');
                if (subMenu) {
                    e.preventDefault();
                    parentLi.classList.toggle('is-open');
                    subMenu.style.display = parentLi.classList.contains('is-open') ? 'block' : 'none';
                }
            });
        });
    }

    // =========================================================================
    // 2. Accessible Header Search Toggle
    // =========================================================================
    const searchToggle = document.querySelector('.sc-search-toggle');
    const searchDrawer = document.getElementById('header-search-drawer');

    if (searchToggle && searchDrawer) {
        searchToggle.addEventListener('click', function () {
            const isExpanded = searchToggle.getAttribute('aria-expanded') === 'true';
            searchToggle.setAttribute('aria-expanded', !isExpanded);
            searchDrawer.classList.toggle('is-active');

            if (!isExpanded) {
                const searchField = searchDrawer.querySelector('input[type="search"]');
                if (searchField) {
                    setTimeout(function () { searchField.focus(); }, 100);
                }
            }
        });

        // Close search drawer on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchDrawer.classList.contains('is-active')) {
                searchToggle.setAttribute('aria-expanded', 'false');
                searchDrawer.classList.remove('is-active');
                searchToggle.focus();
            }
        });
    }

    // =========================================================================
    // 3. Dropdown Submenu Keyboard Accessibility
    // =========================================================================
    const menuItemsWithChildren = document.querySelectorAll('.sc-nav-menu .menu-item-has-children');
    menuItemsWithChildren.forEach(function (item) {
        const link = item.querySelector(':scope > a');
        if (link) {
            // Add aria-haspopup to inform screen readers
            link.setAttribute('aria-haspopup', 'true');
            link.setAttribute('aria-expanded', 'false');

            item.addEventListener('focusin', function () {
                link.setAttribute('aria-expanded', 'true');
                item.classList.add('focus-visible');
            });

            item.addEventListener('focusout', function (e) {
                if (!item.contains(e.relatedTarget)) {
                    link.setAttribute('aria-expanded', 'false');
                    item.classList.remove('focus-visible');
                }
            });
        }
    });

    // =========================================================================
    // 4. Header Scroll State (Sticky Elevation)
    // =========================================================================
    const header = document.querySelector('.site-header');
    if (header) {
        const handleScroll = function () {
            if (window.scrollY > 20) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    // =========================================================================
    // 5. Interactive Pack Size Selector & Dynamic WhatsApp CTA
    // =========================================================================
    const packSelector = document.getElementById('sc-pack-selector');
    const singleProduct = document.querySelector('.sc-single-product');

    if (packSelector && singleProduct) {
        const packButtons = packSelector.querySelectorAll('.sc-pack-pill');
        const packLabel = document.getElementById('sc-selected-pack-label');
        const whatsappBtn = document.getElementById('sc-whatsapp-enquiry-cta');
        const emailBtn = document.getElementById('sc-email-enquiry-cta');
        const productName = singleProduct.getAttribute('data-product-name') || document.title;

        packButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                // Update selection state
                packButtons.forEach(function (btn) {
                    btn.classList.remove('is-selected');
                    btn.setAttribute('aria-checked', 'false');
                });
                button.classList.add('is-selected');
                button.setAttribute('aria-checked', 'true');

                const selectedSize = button.getAttribute('data-pack-size');
                if (packLabel) {
                    packLabel.textContent = selectedSize;
                }

                // Update WhatsApp CTA href dynamically
                if (whatsappBtn) {
                    const currentHref = whatsappBtn.getAttribute('href');
                    if (currentHref && currentHref.includes('wa.me')) {
                        const url = new URL(currentHref);
                        const sku = singleProduct.getAttribute('data-product-sku') || '';
                        const lines = [];
                        lines.push('Hello, I am interested in ' + productName + '.');
                        lines.push('');
                        lines.push('Product:');
                        lines.push(productName);
                        if (sku) {
                            lines.push('');
                            lines.push('SKU:');
                            lines.push(sku);
                        }
                        if (selectedSize) {
                            lines.push('');
                            lines.push('Pack Size:');
                            lines.push(selectedSize);
                        }
                        lines.push('');
                        lines.push('Please share more information.');
                        url.searchParams.set('text', lines.join('\n'));
                        whatsappBtn.setAttribute('href', url.toString());
                    }
                }

                // Update Email CTA href dynamically
                if (emailBtn) {
                    const mailtoHref = emailBtn.getAttribute('href');
                    if (mailtoHref && mailtoHref.startsWith('mailto:')) {
                        const emailParts = mailtoHref.split('?');
                        const baseEmail = emailParts[0];
                        const searchParams = new URLSearchParams(emailParts[1] || '');
                        let body = searchParams.get('body') || '';
                        
                        // Replace or insert pack size line
                        body = body.replace(/Interested Pack Size \/ Packaging: .*/, 'Interested Pack Size / Packaging: ' + selectedSize);
                        searchParams.set('body', body);
                        emailBtn.setAttribute('href', baseEmail + '?' + searchParams.toString());
                    }
                }
            });
        });
    }

    // =========================================================================
    // 6. Product Card Favourite Button (Visual UI Foundation)
    // =========================================================================
    document.addEventListener('click', function (e) {
        const favBtn = e.target.closest('.sc-product-card__favourite');
        if (!favBtn) return;

        e.preventDefault();
        e.stopPropagation();

        const isFav = favBtn.classList.toggle('is-favourite');
        const card = favBtn.closest('.sc-product-card');
        const titleEl = card ? card.querySelector('.sc-product-card__title a') : null;
        const prodName = titleEl ? titleEl.textContent.trim() : 'Product';

        if (isFav) {
            favBtn.setAttribute('aria-label', 'Remove ' + prodName + ' from favourites');
            favBtn.setAttribute('title', 'Saved to Favourites');
        } else {
            favBtn.setAttribute('aria-label', 'Add ' + prodName + ' to favourites');
            favBtn.setAttribute('title', 'Save to Favourites');
        }
    });

    // =========================================================================
    // 7. Single Product Details Tabs Navigation
    // =========================================================================
    const tabsContainer = document.getElementById('product-details-sections');
    if (tabsContainer) {
        const tabButtons = tabsContainer.querySelectorAll('.sc-tab-btn');
        const tabPanels = tabsContainer.querySelectorAll('.sc-tab-panel');

        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('aria-controls');

                // Deactivate all
                tabButtons.forEach(function (b) {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-selected', 'false');
                });
                tabPanels.forEach(function (p) {
                    p.classList.remove('is-active');
                    p.hidden = true;
                });

                // Activate target
                btn.classList.add('is-active');
                btn.setAttribute('aria-selected', 'true');
                const targetPanel = document.getElementById(targetId);
                if (targetPanel) {
                    targetPanel.classList.add('is-active');
                    targetPanel.hidden = false;
                }
            });
        });

        // Smooth scroll to reviews link
        const reviewLinks = document.querySelectorAll('.sc-rating-count-link, .sc-scroll-to-reviews');
        reviewLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const reviewsTabBtn = document.getElementById('tab-btn-reviews');
                if (reviewsTabBtn) {
                    reviewsTabBtn.click();
                }
                const reviewsSection = document.getElementById('tab-reviews');
                if (reviewsSection) {
                    reviewsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // =========================================================================
    // 8. Mobile Filter Toggle Placeholder
    // =========================================================================
    const filterBtn = document.getElementById('sc-mobile-filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function () {
            const isExpanded = filterBtn.getAttribute('aria-expanded') === 'true';
            filterBtn.setAttribute('aria-expanded', !isExpanded);
            // Toggle active state on category nav for mobile focus
            const catNav = document.querySelector('.sc-category-nav');
            if (catNav) {
                catNav.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});
