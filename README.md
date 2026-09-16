# SpiceCraft — Premium Spices Manufacturer CMS & Product Catalog Website

> **Phase 1 (Foundation Steps 1, 2, 2A, 3, & 4) Architecture & System Documentation**  
> **Status:** Phase 1 Complete · Ready for Production Foundation Closing

---

## 1. Project Purpose
SpiceCraft is a custom, production-grade WordPress CMS and product discovery platform built specifically for an Indian spice and FMCG food manufacturer. The platform delivers an opulent, luxury brand presence paired with modern e-commerce-style product catalog browsing and packaging discovery, while operating on a strictly **non-transactional catalog model**. 

Instead of online retail checkouts, every customer interaction is designed as a conversion conduit for B2B institutional supply, private labeling, domestic retail distribution, and international export trade inquiries via WhatsApp Business and Direct Corporate Trade Desks.

---

## 2. Technology Stack
- **Core Platform:** WordPress 6.x / 7.x
- **Catalog Engine:** WooCommerce 9.x / 11.x (Strict Catalog Mode — non-transactional)
- **Custom Theme:** `spicecraft` (`wp-content/themes/spicecraft/`)
- **Custom Core Plugin:** `spicecraft-core` (`wp-content/plugins/spicecraft-core/`)
- **Frontend Architecture:** Semantic HTML5, Vanilla Modern CSS (CSS Custom Properties Design System), Vanilla ES6+ JavaScript.
- **Typography:** Plus Jakarta Sans (Clean Modern Sans-Serif) & Cormorant Garamond (Editorial Serif).
- **Zero Heavy Dependencies:** No jQuery UI bloat, no React overhead on the frontend, and zero reliance on page builders (Elementor, Divi, WPBakery).

---

## 3. Local Development Environment
- **Web Server:** Apache (XAMPP / Local Environment)
- **PHP Version:** PHP 8.1+ / 8.2+
- **Database:** MySQL / MariaDB (Standard WordPress table structure, no custom SQL tables)
- **WordPress Root:** `e:\NSK-Prectices-etc\Antigravity\wordpress`
- **Active Theme Directory:** `wp-content/themes/spicecraft/`
- **Active Plugin Directory:** `wp-content/plugins/spicecraft-core/`

---

## 4. Theme Architecture (`spicecraft`)
The `spicecraft` theme handles strictly presentation, layout, responsive styling, and template rendering:
```text
wp-content/themes/spicecraft/
├── style.css                      # Design Tokens, Resets, Theme Header
├── functions.php                  # Modular file inclusion & setup entrypoint
├── header.php                     # Accessible document head & navigation include
├── footer.php                     # Accessible 4-column footer include
├── front-page.php                 # Brand heritage hero scaffold & catalog entryways
├── index.php                      # Universal blog & fallback loop
├── page.php                       # Generic static page template with breadcrumbs
├── single.php                     # Single blog post / insight template
├── archive.php                    # Taxonomy & date archive template
├── search.php                     # Search results page
├── searchform.php                 # Accessible HTML5 search form
├── 404.php                        # Branded 404 error page with dual CTAs
│
├── assets/
│   ├── css/
│   │   ├── main.css               # Design system, layout, typography, UI components
│   │   ├── responsive.css         # Breakpoint scale (320px, 375px, 768px, 1024px, 1440px+)
│   │   └── woocommerce.css        # Custom catalog styling, grid, single product UI
│   └── js/
│       └── main.js                # Accessible drawers, ESC handling, pack selector, ratings
│
├── inc/
│   ├── setup.php                  # Theme supports & menu registration
│   ├── enqueue.php                # Asset enqueueing & localization nonces
│   ├── helpers.php                # Template helpers (safe fallbacks to spicecraft-core)
│   ├── customizer.php             # Customizer selective refresh & admin guidance
│   ├── catalog-mode.php           # Purchasing hook removals & route redirection
│   └── woocommerce.php            # WooCommerce wrappers, gallery support, review forms
│
├── template-parts/
│   ├── header/
│   │   └── site-nav.php           # Dynamic top bar, branding logo, desktop & mobile drawer
│   ├── footer/
│   │   └── site-footer.php        # Dynamic 4-column footer, social links, regulatory info
│   ├── content/
│   │   ├── content.php            # Standard post item
│   │   ├── content-search.php     # Search result card with post-type badges
│   │   └── content-none.php       # Empty search/post state
│   └── components/
│       └── badge.php              # Reusable badge component
│
└── woocommerce/
    ├── archive-product.php        # Custom shop & category catalog discovery experience
    ├── content-product.php        # Premium product card with badges and pack preview
    ├── content-single-product.php # 55/45 split hero, FMCG metadata tabs, enquiry engine
    └── single-product-reviews.php # Accessible 5-star customer review moderation
```

---

## 5. `spicecraft-core` Architecture
The `spicecraft-core` plugin encapsulates all business logic, custom FMCG metadata, data sanitization, custom taxonomies, and admin interfaces, ensuring business continuity regardless of frontend theme switching:
```text
wp-content/plugins/spicecraft-core/
├── spicecraft-core.php            # Plugin bootstrap & lifecycle management
│
└── includes/
    ├── helpers/
    │   └── api-helpers.php        # Global data getters, WhatsApp message formatter, phone cleaner
    ├── products/
    │   ├── class-product-meta.php # FMCG meta boxes (Nutrition, Highlights, Specs, Storage, Origin)
    │   └── class-taxonomies.php   # Custom taxonomies (spicecraft_certification)
    └── settings/
        └── class-global-settings.php # Unified Admin: Overview page & Global CMS Settings tabs
```

---

## 6. Global Settings (`SpiceCraft -> Global Settings`)
All company contacts, addresses, regulatory claims, and social links are centrally managed under a unified options array (`spicecraft_global_settings`) in `wp_options`:
- **Company Info:** Brand Name, Registered Statutory Entity Name, Short Description, Primary & Secondary Phones, Business Hours, Office & Plant Addresses, Google Maps Embed URL, and Header CTA Text/URL.
- **WhatsApp & Enquiries:** WhatsApp Business Number, General Inquiry Message, Product WhatsApp Template (with `{product_name}`, `{sku}`, `{pack_size}`, `{product_url}` placeholders), Domestic Sales Email, Export & Institutional Email, Careers Email, and General Desk Email.
- **Branding & Identity:** WordPress Customizer Site Identity shortcut, Optional Footer Logo URL.
- **Social Channels:** Facebook, Instagram, LinkedIn, YouTube, Pinterest, X/Twitter URLs.
- **Regulatory & Compliance:** FSSAI License Number, GSTIN, IEC Code, Certifications Summary Note. *(Strict rule: All fields are optional; empty values are completely omitted on the frontend with zero fabricated numbers).*
- **Footer & Legal:** Footer Description, Custom Copyright Notice, Footer Disclaimer.

---

## 7. WooCommerce Catalog Mode
Enforced at the hook level without modifying any WooCommerce core files:
1. **Disabled Transactions:**
   - `woocommerce_is_purchasable` filtered to `__return_false`.
   - `woocommerce_variation_is_purchasable` filtered to `__return_false`.
   - `woocommerce_add_to_cart_validation` filtered to `__return_false`.
2. **Interface Replacement:**
   - `woocommerce_template_loop_add_to_cart` removed and replaced with accessible `spicecraft_catalog_loop_view_details()` button.
   - `woocommerce_template_single_add_to_cart` removed and replaced with dynamic WhatsApp & Direct Trade Email enquiry actions.
3. **Route Interception:**
   - Visits to `/cart` and `/checkout` are intercepted via `template_redirect` and safely 302-redirected to the product catalog archive (`/shop/`).
4. **Performance & Asset Pruning:**
   - Obsolete transactional scripts (`wc-cart-fragments`, `wc-add-to-cart`, `wc-add-to-cart-variation`, `wc-cart`, `wc-checkout`) are dequeued.
   - WooCommerce "Coming Soon" store lockout is disabled so catalog remains accessible to all public visitors.

---

## 8. Product Data Architecture
Every product in the catalog is enriched with FMCG spice manufacturing attributes:
- **Core Catalog Data:** Title, Excerpt, High-Resolution Gallery, Category, Subcategory.
- **Pack Sizes:** Interactive selection pills on detail page (e.g. 50g, 100g, 250g, 500g, 1kg, 25kg Bulk Export Bags).
- **Product Highlights:** Bullet points (e.g., "100% Pure & Unadulterated", "High Essential Volatile Oil Content", "Zero Added Colors or Preservatives").
- **Quality Certifications:** Assigned via `spicecraft_certification` taxonomy (ISO 22000, HACCP, Halal, US FDA, Spices Board of India).
- **Ingredients & Allergens:** Full botanical and spice composition text.
- **Nutritional Information:** Structured repeatable table (Nutrient Name, Value, Unit) with serving size.
- **Usage & Culinary Guidance:** Chef application recommendations and recipe tips.
- **Storage & Shelf Life:** Storage directions and certified shelf life duration.
- **Origin & Processing Specifications:** Country of Origin, Growing Region, Processing Method, Form, Moisture Content, Sieve Mesh, Packaging Type.

---

## 9. Menu Management
Four distinct WordPress menu locations are registered and fully manageable via `Appearance -> Menus`:
1. `primary`: Desktop Main Header Navigation (with full multi-level dropdown support and keyboard access).
2. `mobile`: Mobile Menu Drawer (with tap-to-expand submenu support and body scroll locking).
3. `footer_1`: Footer Column 2 (Company / Quick Links).
4. `footer_2`: Footer Column 3 (Product Categories / Specialty Spices).

---

## 10. Header & Footer Management
- **Top Bar:** Displays phone, export email, and quick WhatsApp link. If all contact fields are left empty in Global Settings, the top bar is cleanly hidden.
- **Header:** Features Custom Logo (with text fallback), primary navigation, accessible slide-down search drawer, favourites badge placeholder, and dynamic configurable CTA button ("Trade Enquiry").
- **Footer:** 4-column responsive grid:
  - Column 1: Logo / Brand Name, Company Description, Regulatory disclosures (FSSAI, GST, IEC if populated).
  - Column 2: Footer Menu 1.
  - Column 3: Footer Menu 2.
  - Column 4: Address, Preferred Enquiry Email, Direct Phone, WhatsApp link, and SVG Social Icons with accessible aria-labels.
  - Bottom Bar: Dynamic Copyright (`© [Year] [Company Name]`) and legal disclaimer note.

---

## 11. Product Enquiry Flow
1. **Visitor Discovery:** Visitor browses catalog or arrives at single product page.
2. **Pack Size Selection:** Visitor clicks a pack size pill (e.g., "500g Pet Jar" or "25kg Export Bag").
3. **Dynamic Parameter Binding:** JavaScript automatically binds Selected Pack Size, Product Name, SKU, and Product URL to the enquiry CTAs.
4. **Lead Dispatch:**
   - **WhatsApp CTA:** Opens WhatsApp click-to-chat with the formatted template. Lines with unassigned placeholders are omitted cleanly.
   - **Email CTA:** Generates a structured mailto link directed to the configured trade desk email with formal B2B request lines.
   - **Graceful Degradation:** If WhatsApp or email is unconfigured, the corresponding CTA button is hidden.

---

## 12. Custom Meta Keys
Stored in `wp_postmeta` under strict namespacing:
| Meta Key | Description | Data Type |
|---|---|---|
| `_sc_badge_label` | Product card badge text (e.g. "EXPORT GRADE") | String |
| `_sc_badge_style` | Badge color scheme (`primary`, `secondary`, `pure`, `accent`) | String |
| `_sc_pack_sizes` | Array of available packaging weights/types | Serialized Array |
| `_sc_highlights` | Repeatable product feature highlights | Serialized Array |
| `_sc_ingredients` | Ingredients description | Textarea / HTML |
| `_sc_storage_instructions` | Storage instructions | Textarea / HTML |
| `_sc_usage_instructions` | Culinary and industrial usage notes | Textarea / HTML |
| `_sc_shelf_life` | Best before / shelf life duration statement | String |
| `_sc_country_of_origin` | Nation of harvest / manufacture | String |
| `_sc_origin_region` | Agro-climatic belt or spice producing region | String |
| `_sc_serving_size` | Serving size for nutrition table (e.g., "Per 100g") | String |
| `_sc_nutrition_data` | Repeatable nutrient rows `[nutrient, value, unit]` | Serialized Array |
| `_sc_specifications` | Custom technical specification rows `[label, value]` | Serialized Array |

---

## 13. Custom Taxonomies
- `spicecraft_certification` (Product Certifications): Non-hierarchical taxonomy registered for `product` post type. Supports quality standards such as ISO 22000, HACCP, Halal, FSSAI, US FDA Registered, and Spices Board of India. Fully editable under `SpiceCraft -> Certifications`.

---

## 14. WooCommerce Attributes
- Pack Sizes are also compatible with standard WooCommerce Product Attributes (`pa_pack-size` or custom product attributes) to ensure future filter integration without re-architecting data structures.

---

## 15. Security Approach
- **Direct Execution Guard:** Every PHP file opens with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- **Nonce Verification:** All admin form submissions and settings saves validate WordPress nonces (`wp_verify_nonce`, `settings_fields`).
- **Capability Checks:** All admin interfaces check `current_user_can('manage_options')` or `current_user_can('edit_post')`.
- **Input Sanitization:** Every field is cleaned using `sanitize_text_field()`, `sanitize_textarea_field()`, `sanitize_email()`, or `esc_url_raw()`.
- **Output Escaping:** Strict escaping across templates using `esc_html()`, `esc_attr()`, `esc_url()`, and `wp_kses_post()`.
- **Zero Core Tampering:** WordPress core, `wp-admin`, `wp-includes`, and the `woocommerce` plugin remain 100% pristine.

---

## 16. Development Conventions
- **WordPress Coding Standards (WPCS):** Proper spacing, PHP docblocks, descriptive function prefixes (`spicecraft_`, `_sc_`).
- **CSS Architecture:** Scoped BEM-like naming convention with design tokens (`--sc-color-*`, `--sc-space-*`, `--sc-radius-*`).
- **No Inline Hacks:** Global layout rules are kept in external stylesheets; dynamic attributes use data attributes and semantic markup.

---

## 17. Features Intentionally Disabled (Catalog-Mode Boundaries)
To maintain catalog purity, the following are intentionally disabled in Phase 1:
- Add to Cart buttons
- Shopping Cart page (`/cart/` redirects to `/shop/`)
- Checkout page (`/checkout/` redirects to `/shop/`)
- WooCommerce Cart Fragments AJAX polling
- Payment Gateways & Shipping zones

---

## 18. Phase 1 Completed Features
- [x] Theme Foundation & CSS Design Token Architecture
- [x] WooCommerce Catalog Mode & Non-Transactional Route Interception
- [x] Premium Product Archive & Responsive Discovery Grid
- [x] Single Product Detail Page (55/45 Gallery & FMCG Metadata Showcase)
- [x] FMCG Product Data Architecture (Nutrition, Highlights, Specs, Storage, Pack Sizes)
- [x] SpiceCraft Admin Overview Hub & Centralized Global CMS Settings
- [x] Fully Dynamic Responsive Header & Mobile Drawer (with scroll locking & submenus)
- [x] Fully Dynamic 4-Column Footer (with dynamic copyright & verified regulatory disclosure)
- [x] Accessible Search Form & Categorized Search Results Template
- [x] Branded 404 Page & Generic Page Breadcrumbs
- [x] Verified 5-Star Customer Review Moderation
- [x] 30/30 Comprehensive Foundation QA Test Suite Passed

---

## 19. Known Limitations
- **Favourite Persistence:** The favourite heart icon functions as an interactive UI component; server-side or localStorage persistence is scheduled for Phase 2.
- **Product Filter Widgets:** Advanced facet filtering (by certification, spice form, or origin region) will be added in Phase 2.
- **AJAX Live Search:** Search currently uses standard WordPress keyword matching; instant live search drawer is planned for Phase 2.

---

## 20. Phase 2 Recommendations
1. **Homepage Production:** Build the brand heritage homepage with hero video/slider, featured spice categories, quality assurance pillars, and farm-to-mill timeline.
2. **Advanced Catalog Filters:** Add AJAX sidebar filters for packaging size, certifications, and spice forms.
3. **Favourites / Quote List:** Implement cookie/localStorage quote list allowing buyers to shortlist multiple spices and submit a combined bulk enquiry.
4. **Institutional B2B RFQ Form:** Add a dedicated Request-For-Quote (RFQ) modal with volume and destination specifications.
