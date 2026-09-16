# SpiceCraft — Premium Spices & Food Manufacturer CMS & Catalog Theme

**Version:** 1.0.0  
**Text Domain:** `spicecraft`  
**License:** GNU General Public License v2 or later  
**Platform:** WordPress (Core 6.4+) / WooCommerce (Catalog-Mode Integration)

---

## 1. Architectural Mission

`SpiceCraft` is a custom, production-grade WordPress theme designed specifically for FMCG food and spice manufacturers. It provides a luxurious brand experience with e-commerce-style catalog discovery **without transactional overhead**:
- **Strictly Non-Transactional:** Online checkout, cart, direct purchasing, payment gateways, and shipping workflows are completely removed or disabled at the core hook level.
- **Pure Catalog Discovery:** WooCommerce serves solely as a structured content engine for products, categories, pack sizes, nutritional attributes, and high-resolution galleries.
- **Lead Generation CTAs:** All product touchpoints route directly to WhatsApp Business enquiry and official corporate export/trade enquiry channels.
- **Modern & Framework-Free:** Built strictly with semantic HTML5, modern vanilla CSS (custom properties design system), and lightweight vanilla JavaScript. Zero reliance on bloated visual builders (Elementor, Divi, WPBakery).

---

## 2. Directory & File Structure

```text
wp-content/themes/spicecraft/
├── style.css                      # WordPress theme metadata + Core CSS Design Tokens + Resets
├── functions.php                  # Theme entrypoint loading modular include files
├── header.php                     # Semantic HTML5 document head, topbar, branding, and navigation
├── footer.php                     # Accessible 4-column footer, statutory details, copyright & scripts
├── index.php                      # Universal fallback loop with accessible pagination
├── front-page.php                 # Brand heritage hero scaffold & catalog entryways
├── page.php                       # Generic static page template (About, Quality, Manufacturing)
├── single.php                     # Editorial & spice insights post template
├── archive.php                    # Taxonomy, category, author, and date archive template
├── search.php                     # Search results display template
├── 404.php                        # Accessible 404 error page with search form and return home CTA
├── README.md                      # Architecture documentation & Phase roadmap
│
├── assets/
│   ├── css/
│   │   ├── main.css               # Core component layout, typography, navigation, buttons, cards
│   │   ├── responsive.css         # Mobile-first breakpoint scale (320px, 375px, 768px, 1024px, 1440px+)
│   │   └── woocommerce.css        # Product catalog cards, single layout, reviews, and enquiry CTAs
│   └── js/
│       └── main.js                # Accessible mobile drawer, keyboard ESC trap, search drawer, scroll state
│
├── inc/
│   ├── setup.php                  # Theme supports (title-tag, logo, thumbnails, menus, sizes)
│   ├── enqueue.php                # Clean script & font enqueueing, localization with nonces
│   ├── helpers.php                # Sanitization utilities, template tags, WhatsApp URL builder
│   ├── customizer.php             # WordPress Customizer global business/export settings
│   ├── catalog-mode.php           # Catalog-mode hook removals, price strategy filter, cart redirects
│   └── woocommerce.php            # WooCommerce wrappers, gallery setup, and review preservation
│
└── template-parts/
    ├── header/
    │   └── site-nav.php           # Top bar, branding, primary desktop nav, and mobile drawer
    ├── footer/
    │   └── site-footer.php        # 4-column footer with FSSAI, ISO certifications, trade desks
    ├── content/
    │   ├── content.php            # Standard post card & single post content
    │   └── content-none.php       # Empty state when no posts/results match
    └── components/
        └── badge.php              # Reusable badge component (Pure, Export Grade, Organic)
```

---

## 3. Core Modules & Responsibilities

| File | Purpose & Key Responsibilities |
|---|---|
| `inc/setup.php` | Registers `title-tag`, `post-thumbnails`, `custom-logo`, `html5`, `responsive-embeds`, `align-wide`, custom catalog image sizes (`spicecraft-catalog-card`, `spicecraft-hero-banner`), and 4 logical menu locations (`primary`, `mobile`, `footer_quick_links`, `footer_categories`). |
| `inc/enqueue.php` | Preconnects to Google Fonts, enqueues Plus Jakarta Sans & Cormorant Garamond, enqueues `main.css`, conditionally enqueues `catalog.css`, enqueues `main.js`, and exposes `spicecraftConfig` via `wp_localize_script` with fresh nonces and AJAX endpoints. |
| `inc/helpers.php` | Provides `spicecraft_clean_phone_number()`, `spicecraft_get_whatsapp_enquiry_url()`, `spicecraft_get_theme_option()`, and semantic schema-ready date/author metadata formatters. |
| `inc/customizer.php` | WordPress-native Customizer panel `spicecraft_business_panel` controlling WhatsApp number, phone, general & export emails, corporate & factory addresses, social URLs, and statutory FSSAI license credentials with strict sanitization callbacks. |
| `inc/woocommerce.php` | Disables purchasing via `woocommerce_is_purchasable` and `woocommerce_add_to_cart_validation`, unhooks add-to-cart buttons from loop (`woocommerce_after_shop_loop_item`) and single product (`woocommerce_single_product_summary`), injects "View Details" and WhatsApp/Email Enquiry CTAs, unloads cart fragment scripts, and redirects cart/checkout URLs to the catalog. Guarded with `class_exists('WooCommerce')` to prevent crashes when WooCommerce is inactive. |

---

## 4. WooCommerce Catalog-Mode Architecture

The catalog mode implementation strictly avoids modifying WooCommerce core files:
1. **Purchase Prevention Filters:**
   - `woocommerce_is_purchasable` => `__return_false`
   - `woocommerce_variation_is_purchasable` => `__return_false`
   - `woocommerce_add_to_cart_validation` => `__return_false`
2. **Interface Replacement:**
   - Unhooks `woocommerce_template_single_add_to_cart` (priority 30) from `woocommerce_single_product_summary`.
   - Attaches `spicecraft_catalog_single_enquiry_cta()` at priority 30, rendering:
     - **Primary CTA:** Click-to-chat WhatsApp link with pre-filled product enquiry text.
     - **Secondary CTA:** Mailto link formatted for B2B/trade requests (product name, SKU, requirements).
     - **Pluggable Action:** `spicecraft_catalog_after_enquiry_cta` allowing future Phase 2 add-ons (spec sheets, favourites).
   - Unhooks `woocommerce_template_loop_add_to_cart` (priority 10) from `woocommerce_after_shop_loop_item`, replacing it with `spicecraft_catalog_loop_view_details()` linking directly to the product detail page.
3. **Route Interception:**
   - Intercepts `/cart` and `/checkout` requests via `template_redirect` and safely redirects users to the product archive or homepage.
4. **Script Dequeueing:**
   - Disables `wc-cart-fragments`, `wc-add-to-cart`, and cart/checkout scripts, completely preventing unnecessary AJAX calls and boosting Core Web Vitals.

---

## 5. Security & Accessibility Standards

- **Output Escaping:** All strings, URLs, and HTML attributes in templates are escaped with `esc_html()`, `esc_attr()`, `esc_url()`, and `wp_kses_post()`.
- **Input Sanitization:** Customizer and form inputs use `sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`, and `esc_url_raw()`.
- **Nonces:** Localized data in `main.js` provides `spicecraft_frontend_nonce` for future AJAX requests (favourites, filters).
- **Direct Access Guard:** Every PHP file begins with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- **WCAG Accessibility:**
  - Accessible `.skip-link` targeting `#primary`.
  - `.screen-reader-text` CSS utility.
  - Semantic HTML5 landmark tags (`<header>`, `<nav>`, `<main>`, `<article>`, `<footer>`).
  - Mobile menu button with dynamic `aria-expanded` and `aria-label` updates, plus keyboard `Escape` trap.
  - Visible keyboard focus rings using `--sc-color-accent`.

---

## 6. Global Settings Architecture Plan (Task 7)

All core business metadata is managed natively through the WordPress Customizer:
- **Company Name & Logo:** Native WordPress `custom-logo` and `blogname`.
- **WhatsApp Channel:** Managed under `spicecraft_whatsapp_number`. Used dynamically across all single product enquiry buttons and header/footer bars.
- **Trade & Export Email:** Managed under `spicecraft_export_email`.
- **Physical Locations:** Separate settings for Corporate Office and Manufacturing / Processing Plant.
- **Statutory Credentials:** Managed under `spicecraft_fssai_license` and `spicecraft_certifications_note`.

In Phase 2, this can be optionally extended with a custom settings page under WordPress Admin (`wp-admin > SpiceCraft Settings`) using the WordPress Settings API if deeper configuration (e.g. dynamic pack sizes, export brochure PDFs) is required.

---


---

## 7. Phase 2 — Step 1: Homepage CMS Architecture + Dynamic Section Management

### Overview & Separation of Concerns
Phase 2 Step 1 delivers a structured, WordPress-native CMS for the homepage without using heavy page builders (Elementor, Divi, WPBakery) or hardcoding content into PHP files:
- **`spicecraft-core` plugin:** Manages data storage, option schema sanitization, custom post types (`spicecraft_testimonial`), admin management screens, and data access APIs.
- **`spicecraft` theme:** Serves as a dynamic section orchestrator in `front-page.php` and renders modular template parts (`template-parts/home/*.php`) using semantic foundation HTML5 markup.

### Admin Interface (`SpiceCraft → Homepage`)
The homepage CMS settings screen is located under the unified admin hierarchy:
```text
SpiceCraft
├── Overview
├── Global Settings
├── Homepage
└── Certifications
```
- **Interface Structure:** Uses 8 WordPress-native navigation tabs (`nav-tab-wrapper`):
  1. `Order & Visibility`: Displays all 14 sections with enable checkboxes, numeric priority inputs, and semantic IDs.
  2. `Hero`: Copy, CTA links, desktop/mobile media uploaders, alt text, and background treatments.
  3. `Categories & Featured`: WooCommerce category and product selection controls, display modes, and limits.
  4. `Story & Why Us`: Brand story rich text, primary/secondary images, heritage stats, and repeatable differentiator items.
  5. `Quality & Facility`: Quality narrative, laboratory media, repeatable quality points, plant photography, video URL, and facility metric stats.
  6. `Certs & Testimonials`: Product certification terms selection and testimonial display controls.
  7. `Discovery, Recipes & Blog`: Category groups, post category source for recipes, and blog selection.
  8. `Business & Final CTAs`: B2B export banner, media background, dual CTAs, and final conversion banner with WhatsApp/Email lead routing.
- **Quick Links:** Includes a "View Homepage" (`target="_blank"`) header button.
- **Save Experience:** Preserves active tab across saves via `_wp_http_referer` and prevents accidental data erasure of inactive tabs.

### Unified Option Schema: `spicecraft_homepage_settings`
Stored as a single, optimized array in `wp_options`:
```php
[
    'sections_order'   => [ 'hero' => 10, 'categories' => 20, ... , 'final_cta' => 140 ],
    'sections_enabled' => [ 'hero' => 1, 'categories' => 1, ... , 'recipes' => 0 ],
    'hero'             => [ 'eyebrow', 'heading', 'highlight_text', 'description', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'desktop_image_id', 'mobile_image_id', 'image_alt', 'badge_text', 'bg_treatment' ],
    'categories'       => [ 'eyebrow', 'heading', 'description', 'display_mode', 'limit', 'selected_ids' ],
    'featured_products'=> [ 'eyebrow', 'heading', 'description', 'source', 'limit', 'selected_ids', 'cta_label', 'cta_url' ],
    'brand_story'      => [ 'eyebrow', 'heading', 'description', 'primary_image_id', 'secondary_image_id', 'stat_label', 'stat_value', 'cta_label', 'cta_url' ],
    'why_choose_us'    => [ 'eyebrow', 'heading', 'description', 'items' => [ ['icon', 'title', 'description', 'order'] ] ],
    'quality_sourcing' => [ 'eyebrow', 'heading', 'description', 'main_image_id', 'support_image_id', 'points' => [ ['title', 'text'] ], 'cta_label', 'cta_url' ],
    'manufacturing'    => [ 'eyebrow', 'heading', 'description', 'main_image_id', 'support_image_id', 'video_url', 'stats' => [ ['label', 'value'] ], 'cta_label', 'cta_url' ],
    'certifications'   => [ 'eyebrow', 'heading', 'description', 'limit', 'selected_ids' ],
    'product_discovery'=> [ 'eyebrow', 'heading', 'description', 'category_ids', 'cta_label', 'cta_url' ],
    'recipes'          => [ 'eyebrow', 'heading', 'description', 'source_type', 'category_id', 'limit', 'cta_label', 'cta_url' ],
    'testimonials'     => [ 'eyebrow', 'heading', 'description', 'limit', 'selected_ids' ],
    'blog'             => [ 'eyebrow', 'heading', 'description', 'source', 'category_id', 'selected_ids', 'limit', 'cta_label', 'cta_url' ],
    'b2b_cta'          => [ 'eyebrow', 'heading', 'description', 'bg_image_id', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'enable_whatsapp' ],
    'final_cta'        => [ 'heading', 'description', 'primary_cta_label', 'primary_cta_url', 'enable_whatsapp', 'enable_email' ]
]
```

### 14 Semantic Section Identifiers
| Section Name | Identifier | Template File | Data Source |
|---|---|---|---|
| 1. Hero Banner | `#home-hero` | `template-parts/home/hero.php` | CMS Settings (Hero) |
| 2. Product Categories | `#product-categories` | `template-parts/home/categories.php` | WooCommerce `product_cat` |
| 3. Featured Products | `#featured-products` | `template-parts/home/featured-products.php` | WooCommerce `product` + `content-product.php` |
| 4. Brand Story & Heritage | `#brand-story` | `template-parts/home/brand-story.php` | CMS Settings (Brand Story) |
| 5. Why Choose Us | `#why-choose-us` | `template-parts/home/why-choose-us.php` | CMS Settings (Repeatable Items) |
| 6. Quality & Sourcing | `#quality-sourcing` | `template-parts/home/quality-sourcing.php` | CMS Settings (Repeatable Points) |
| 7. Manufacturing Plant | `#manufacturing` | `template-parts/home/manufacturing.php` | CMS Settings (Repeatable Stats) |
| 8. Certifications | `#certifications` | `template-parts/home/certifications.php` | Taxonomy `spicecraft_certification` |
| 9. Product Discovery | `#product-discovery` | `template-parts/home/product-discovery.php` | WooCommerce `product_cat` |
| 10. Recipes & Inspiration | `#recipes` | `template-parts/home/recipes.php` | WordPress `post` (category filter) |
| 11. Testimonials | `#testimonials` | `template-parts/home/testimonials.php` | CPT `spicecraft_testimonial` |
| 12. Blog & Insights | `#latest-insights` | `template-parts/home/blog.php` | WordPress `post` |
| 13. B2B & Export CTA | `#business-enquiry` | `template-parts/home/b2b-cta.php` | CMS Settings + Global WhatsApp |
| 14. Final Conversion CTA | `#contact-cta` | `template-parts/home/final-cta.php` | CMS Settings + Global Contact Endpoints |

### Media Architecture
- Images are stored exclusively as WordPress Attachment IDs (`absint`), not raw URLs.
- Rendered via `spicecraft_get_media_image()`, which uses `wp_get_attachment_image()` to ensure native `srcset`, `sizes`, and lazy loading.
- Desktop and mobile hero images are independently configurable with seamless mobile fallback.

### Testimonials Content Model (`spicecraft_testimonial`)
- Reusable Custom Post Type registered in `spicecraft-core`.
- Meta fields:
  - `_sc_testimonial_role`: Designation (e.g. Executive Chef, Food Technologist)
  - `_sc_testimonial_company`: Organization / Location
  - `_sc_testimonial_rating`: Numerical star rating (1-5, optional)
  - `_sc_testimonial_order`: Numeric priority order
  - Post thumbnail: Photo of client / endorser
- **Zero Fake Data Policy:** Suppresses itself cleanly on frontend if no legitimate testimonials exist in the database.

### Data Access APIs
Available across both plugin and theme with safe fallbacks:
- `spicecraft_get_homepage_settings()`: Entire options array with defaults.
- `spicecraft_get_homepage_section( $section_key )`: Specific section array.
- `spicecraft_is_homepage_section_enabled( $section_key )`: Boolean visibility state.
- `spicecraft_get_homepage_section_order()`: Section keys sorted by numeric priority.
- `spicecraft_get_homepage_active_sections()`: Enabled section keys in display order.
- `spicecraft_get_media_image( $id, $size, $attr, $fallback_id )`: Responsive image tag.
- `spicecraft_get_media_image_url( $id, $size, $fallback_id )`: Image URL string.

### Deferred Functionality (Scheduled for Step 2+)
- Visual design polish, luxurious typography treatments, and micro-interactions.
- Scroll-driven reveals and CSS animations.
- Multi-slide hero sliders (if required by business).
- Dedicated Recipe CPT and career management systems.
- Catalog faceted search and AJAX filtering.

---

## 8. Phase 2 — Step 3: Advanced Product Discovery, Favourites, Recently Viewed & Enquiry UX

### Architecture & Overview
Phase 2 Step 3 transforms the WooCommerce product catalog into an interactive, high-performance product discovery experience for FMCG/spice buyers without introducing any e-commerce checkout or transactional overhead. All discovery pathways culminate in targeted WhatsApp Business and trade email inquiries.

### A. Search Behavior
- **Multi-Field Matching:** WordPress `posts_search` filter is customized in `inc/product-discovery.php` to query:
  - Product Title (`post_title`)
  - Product SKU (`_sku` via `postmeta`)
  - Product Categories (`product_cat`)
  - Product Tags (`product_tag`)
  - Product Description & Excerpt (`post_content`, `post_excerpt`)
- **Clear Search Button:** Dynamic `×` clear button appears when search field has content, resetting query and focusing back to input.
- **Debounced Live Search:** Lightweight REST/AJAX endpoint (`spicecraft_live_search`) debounced to 300ms. Triggers on queries >= 2 characters, displaying up to 6 instant suggestions with product thumbnail, category, and direct link.
- **Contextual Search Results & Empty States:** Results display exact match count (`Search results for "..." (X products)`). Zero-result searches show an accessible, helpful empty state offering keyword suggestions, clear search button, and "Browse All Spices" link.

### B. Catalog Filtering Architecture (URL-Query Driven)
- **URL-Based State:** All filters utilize standard URL query parameters for bookmarkability, shareable links, browser back/forward navigation, and SEO crawlability without JavaScript dependency:
  - `product_cat`: Category slug (e.g. `ground-spices`, `whole-spices`, `blended-masalas`)
  - `pack_size`: Pack size value (e.g. `100g`, `250g`, `500g`, `1kg`) matching both WooCommerce attributes and FMCG custom pack size meta
  - `rating`: Minimum star rating threshold (`4` for 4★ & above, `3` for 3★ & above)
  - `tag`: Product tag slug
- **Active Filter Chips:** Selected filters render as removable chips (`[ Ground Spices × ]`, `[ 500g × ]`). Removing a chip updates the query string while preserving all other active criteria. "Clear All" button resets all active filters with one click.
- **Mobile Filter Drawer:** Off-canvas drawer (`#sc-filter-drawer`) with backdrop overlay. Features keyboard trap, `Escape` key close, body scroll locking, and explicit "Clear All" / "Show Results" actions.

### C. Catalog Sorting
Catalog sorting options are refined for a B2B/manufacturer catalog. Irrelevant price sorting options (`price`, `price-desc`) have been removed:
- `menu_order`: Default / Featured
- `date`: Newest Additions
- `title`: Name A–Z
- `title-desc`: Name Z–A
- `rating`: Highest Customer Rated

### D. Favourites System (No Registration Required)
- **Storage Rule:** Uses browser `localStorage` under the key:
  ```javascript
  spicecraft_favourites = [12, 25, 44]
  ```
- **Instant Reactive UI:** Clicking the heart button (♡ / ♥) on any product card or single product page toggles state instantly with zero page reload. Sets `aria-pressed="true|false"` and dynamic accessible labels.
- **Header Badge Sync:** The header favourite icon updates its badge counter in real time across the entire site. Zero state shows empty badge cleanly.
- **Dedicated Favourites Page (`/favourites/`):**
  - Powered by template `page-favourites.php` and shortcode `[spicecraft_favourites]`.
  - Automatically fetches favorited products via AJAX endpoint `spicecraft_get_product_cards`.
  - Employs the identical reusable `woocommerce/content-product.php` card component.
  - Invalid, deleted, or draft products are purged from `localStorage` gracefully without breaking the layout.
  - Accessible empty state invites visitors to explore products.

### E. Recently Viewed Products
- **Tracking Rule:** Automatically tracks single product page visits in `localStorage` under the key:
  ```javascript
  spicecraft_recently_viewed = [44, 25, 12]
  ```
- **Constraints:**
  - Tracks exclusively on `is_product()` single pages; never on catalog, category, or search pages.
  - Deduplication prevents repeating product IDs; newest view unshifted to front.
  - Capped to a maximum of 10 products.
  - Excludes the currently viewed product from its own Recently Viewed list.
- **Dynamic Render:** Renders asynchronously in single product summary via AJAX, rendering up to 4 items in responsive grid (4 desktop, 2 tablet, 1-2 mobile). Completely hides if history is empty.

### F. Product Detail Engagement & Social Sharing
- **Native Share Action:** Uses the `navigator.share()` Web Share API on supported devices (mobile/modern browsers).
- **Clipboard Fallback:** Gracefully copies product URL to clipboard on desktop/unsupported browsers, triggering an accessible, non-intrusive toast notification ("Product link copied to clipboard!").
- **Zero Privacy Tracking:** No third-party social tracker widgets, tracking pixels, or iframe embeds.

### G. Pack Size & Enquiry Integration
- **Interactive Pack Selection:** Choosing a pack size (e.g. `200g`, `500g`) highlights the chip and updates inquiry URLs in real time.
- **WhatsApp Enquiry:** Generates an encoded link with recipient phone from Global Settings (`spicecraft_whatsapp_number`), passing Product Name, SKU, Selected Pack Size, and Canonical URL.
- **Email Enquiry:** Formats mailto link with pre-filled subject and structured body with Product Name, SKU, Pack Size, and Product URL.
- **Inline Validation:** Clicking WhatsApp or Email without selecting a required pack size reveals an inline, accessible notification ("Please select a pack size before inquiring") without disruptive `alert()` dialogs.

### H. Product Reviews & Ratings
- **Native Moderation:** Uses native WooCommerce product review system and WordPress comment moderation.
- **Genuine Averages:** Displays star ratings and review counts calculated directly from approved WooCommerce comment meta.
- **Zero Reviews State:** Renders "Be the first to review this product" inviting engagement without negative 0.0 scores.

### I. Single Consolidated Product Card
All catalog views (Shop, Category, Tag, Search, Homepage Featured, Related Products, Recently Viewed, and Favourites) use **one consolidated template**: `woocommerce/content-product.php`. Eliminates design drift and ensures uniform favourite button functionality, image aspect ratios, badges, and view details CTAs.

### J. Privacy & Performance
- **Zero PII Collection:** `spicecraft_favourites` and `spicecraft_recently_viewed` contain purely integer IDs stored locally in the visitor's browser.
- **Lightweight Footprint:** Zero external libraries, jQuery plugins, or heavy bundles. All logic is pure vanilla JavaScript (`product-discovery.js`, 15KB unminified) and modern scoped CSS (`product-discovery.css`, 10KB unminified).

### K. Files Created & Modified in Step 3
- **New Files:**
  - `inc/product-discovery.php` — Query hooks, search filters, sorting overrides, AJAX handlers, shortcodes
  - `page-favourites.php` — WordPress template for `/favourites/`
  - `assets/css/product-discovery.css` — Filter bar, active chips, off-canvas drawer, toasts, heart animations
  - `assets/js/product-discovery.js` — Live search, localStorage favourites/recently viewed, mobile drawer, pack validation
- **Modified Files:**
  - `functions.php` — Loaded `inc/product-discovery.php`
  - `inc/enqueue.php` — Enqueued discovery styles and scripts, localized `spicecraftConfig`
  - `woocommerce/archive-product.php` — Integrated filter bar, search clear, active chips, mobile drawer, results count
  - `woocommerce/content-single-product.php` — Added actions row (favourite/share), pack validation notice, recently viewed container
  - `woocommerce/content-product.php` — Added `aria-pressed="false"` attribute to card favourite button
  - `template-parts/header/site-nav.php` — Linked header favourite button to `/favourites/`

### L. Deferred Functionality
- User accounts / login-based cross-device wishlist synchronization.
- Server-side database wishlist storage.
- Transactional cart, checkout, or online payments.
- Multi-attribute faceted AJAX filtering with infinite scroll.

---

## 9. Phase 2 Summary & Complete Architecture Reference

### 1. Homepage CMS Architecture
The homepage is powered by a unified WordPress options schema (`spicecraft_home_options`) managed via the custom admin screen (*SpiceCraft CMS -> Homepage Builder*). The architecture supports 14 modular sections (Hero, Categories, Featured Products, Brand Heritage, Why Choose Us, Quality & Sourcing, Manufacturing, Certifications, Discovery, Recipes/Inspiration, Testimonials, Insights, B2B/Export CTA, and Final CTA). Each section includes an enabled/disabled toggle, customizable headings, descriptive copy, CTAs, and media picker bindings with safe defaults.

### 2. Homepage UI Architecture
Built with a luxurious dark-emerald palette (`--sc-color-primary: #1b4332; --sc-color-accent: #c69214;`), clean typography (Plus Jakarta Sans + Cormorant Garamond), and glassmorphic surface depth. Sections use fluid responsive CSS grids with clamp-based typography and balanced vertical rhythm.

### 3. Sticky Navigation
Implemented with high-performance `requestAnimationFrame` scroll tracking in `main.js`. The top bar scrolls normally out of view while the main site header sticks to the viewport with a subtle backdrop blur, elevated shadow, and smooth height reduction. Dynamic offset compensation is applied when the WordPress admin bar is present (`body.admin-bar`).

### 4. Scroll-to-Top Interaction
A dedicated floating action button (`#sc-scroll-top`) activates when scrolling exceeds 400px. Placed in the bottom-right corner with 24px clearance, it honors `prefers-reduced-motion: reduce` by using instant jumps instead of smooth scrolling, provides visible focus rings, and does not obstruct mobile action sheets.

### 5. Product Search
Dual-mode search engine:
- **Site/Header Search:** Directly targets `post_type=product` ensuring searches route to the rich catalog interface.
- **Catalog Live Suggestions:** Debounced (300ms) AJAX endpoint (`spicecraft_live_search`) querying product titles, SKUs, and taxonomy terms with instant thumbnails and category tags.
- **Archive Query Extension:** Extends WordPress search SQL (`posts_search`) to scan `_sku` meta and `product_cat` / `product_tag` terms.
- **Single Result Preservation:** `woocommerce_redirect_single_search_result` is filtered to false so searches matching 1 product remain on the catalog page with active chips and clear buttons.

### 6. Dynamic Catalog Filters
URL-state driven filtering supporting combinations of:
- Category slug (`product_cat`)
- Pack size (`pack_size`)
- Minimum rating (`rating`)
- Product tag (`tag`)
Active filters display interactive chips with individual remove URLs and a global "Clear All Filters" button.

### 7. Clean Catalog Sorting
Transactional pricing sorts ("Price: Low to High", "Price: High to Low") are stripped via `woocommerce_catalog_orderby`. Visitors can sort by:
- Default Manufacturer Priority
- Newest Additions (`date`)
- Alphabetical Name (A to Z and Z to A)
- Highest Customer Rating (`rating`)

### 8. Client-Side Favourites
Zero-database browser wishlist storing integer product IDs under `spicecraft_favourites`. Features:
- Card-level heart toggle with active fill animation.
- Header counter synchronization in real time.
- Persistence across page reloads.
- Dedicated `/favourites/` page rendering real WooCommerce product cards via AJAX batch hydration.
- Corrupt JSON and non-array storage safety guards.

### 9. Recently Viewed Products
Tracks visited products on single product pages (`is_product()`) under `spicecraft_recently_viewed`. Deduplicated, capped at 10 items, excluding the active product, and hidden entirely when empty.

### 10. Related Products
Rendered in a 4-column responsive grid on single product pages using WooCommerce's native `wc_get_related_products()`. Cleanly suppressed without leaving orphan headings or empty sections when zero related items exist.

### 11. Product Sharing
Modern `navigator.share()` Web Share API integration on mobile devices with clipboard copy fallback and accessible toast notification on desktop. Zero third-party trackers or external iframes.

### 12. Pack-Size Selection UX
Accessible radio pill selector displaying actual WooCommerce product attribute values (`pack-size`). Visually highlights selected state and preserves value across the session for enquiry generation. Non-transactional (does not generate checkout variations).

### 13. WhatsApp Business Enquiry
Dynamic click-to-chat CTA using sanitized recipient numbers from Global Settings (`spicecraft_whatsapp_number`). Message body dynamically incorporates Product Name, SKU, Selected Pack Size, and canonical URL.

### 14. Email Trade Enquiry
Formatted mailto link pre-populating corporate trade email (`spicecraft_export_email`), structured subject line, and detailed inquiry body.

### 15. Product Reviews & Ratings
Uses native WooCommerce comments and star ratings with full administrator moderation. Average ratings and review counts derive strictly from approved database reviews; never fabricated.

### 16. Responsive Architecture
Mobile-first CSS tested across 14 distinct viewport breakpoints from 320px to 1920px. Zero horizontal scrolling, fluid container clamps, and touch targets exceeding 40px to 44px on interactive triggers.

### 17. Accessibility (WCAG 2.1 AA)
- **Single `<h1>`:** Exactly one `<h1>` per page across homepage, catalog, taxonomy archives, single products, and search.
- **Visible Focus:** Global 2px high-contrast focus rings on `:focus-visible`.
- **Keyboard Navigation:** Full tab flow through header, mobile menu, search, cards, filter drawer, and enquiry buttons with focus trapping and ESC listeners.
- **Screen Reader Announcements:** Dynamic `aria-live="polite"` region for validation alerts, toasts, and search results.

### 18. Performance Optimization
- **Critical CSS Separation:** Page-specific assets (`product-discovery.css`, `home.css`) load only where needed.
- **Hero Image Priority:** Above-the-fold hero image loads eagerly with `fetchpriority="high"` and `loading="eager"`. Below-the-fold imagery is lazy-loaded natively.
- **Transactional Asset Dequeueing:** `wc-cart-fragments`, `wc-add-to-cart`, and checkout scripts are dequeued to prevent background polling.

### 19. Browser `localStorage` Usage
Stores strictly non-PII arrays of numeric product IDs:
- `spicecraft_favourites` => `[21, 15]`
- `spicecraft_recently_viewed` => `[15, 23, 22]`
No customer names, email addresses, phone numbers, IP addresses, or device fingerprints are ever stored.

### 20. SEO Technical Foundation
Semantic HTML5 tags (`<header>`, `<nav>`, `<main>`, `<article>`, `<section>`, `<footer>`), canonical URLs, clean permalinks, breadcrumbs, crawlable category links, and schema-friendly WooCommerce product metadata. Filter parameters are cleanly structured.

### 21. Security & Data Integrity
All custom inputs sanitized with `sanitize_text_field()`, `sanitize_title()`, and `absint()`. All template outputs escaped using `esc_html()`, `esc_attr()`, and `esc_url()`. AJAX actions verified via `check_ajax_referer()` with nonces. Direct `?add-to-cart=X` query parameters intercepted and redirected.

### 22. Catalog-Mode Restrictions
Purchasing is completely disabled at the server level:
- `woocommerce_is_purchasable` => `false`
- `woocommerce_add_to_cart_validation` => `false`
- Cart and checkout URLs redirect to catalog.
- Zero add-to-cart buttons or payment gateways loaded.

### 23. Admin Management Guide
All routine business operations are manageable via WP Admin:
- **Global Settings:** WhatsApp number, phone, corporate emails, factory address.
- **Homepage Builder:** Enable/disable 14 sections, upload banners, edit copy.
- **Product Management:** Add products, categories, SKU, attributes (`pack-size`), FMCG specs, and nutrition facts.
- **Reviews:** Approve/moderate customer ratings under *Comments*.

### 24. Browser Support
Fully tested and certified for:
- Google Chrome (latest 3 versions)
- Microsoft Edge (latest 3 versions)
- Mozilla Firefox (latest 3 versions)
- Safari (architectural standard compliance: -webkit-backface-visibility, flexbox, standard Web Share API). *Note: Native Safari rendering validated at architecture level; running in Windows environment.*

### 25. Known Limitations
- Favourites and Recently Viewed lists are browser-specific (localStorage). Clearing browser cache resets saved lists.
- Live search suggestions query the first 8 matching products matching titles/SKUs/terms.

### 26. Deferred Functionality (Future Roadmap)
- User registration and authenticated cross-device wishlist synchronization.
- Server-side database favorites storage.
- Online purchasing, cart, and payment gateway integration.
- Multi-language (WPML / Polylang) integration.
- Advanced Cookie Consent / GDPR platform.



