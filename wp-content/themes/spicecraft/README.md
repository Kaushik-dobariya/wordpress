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

## 7. Roadmap for Subsequent Phases

- **Phase 1 Step 2:** Activate `spicecraft` theme in WordPress, install/configure WooCommerce, verify catalog-mode rendering, and seed essential product categories (Whole Spices, Ground Spices, Blended Masalas, Export Range).
- **Phase 2:** Product Detail Architecture, Custom Attributes (Pack Sizes, Shelf Life, Ingredients, Nutritional Breakdown, Purity Certifications), and dynamic WhatsApp enquiry payload generation.
- **Phase 3:** Category & Subcategory Taxonomy Architecture, faceted discovery filters (Pack size, Form, Category), and AJAX product search.
- **Phase 4:** Browser-based Favourites & Recently Viewed products (Local Storage / Cookie-based, zero login requirement).
- **Phase 5:** Brand Experience & Information Architecture (About Us, Quality & Lab Testing, Manufacturing Facilities, Certifications, Recipes CPT, Careers CPT).
- **Phase 6:** Lead Conversion Optimization, Business / Distributor / Export Enquiry Forms with nonces and reCAPTCHA.
