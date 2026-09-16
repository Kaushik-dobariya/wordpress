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

