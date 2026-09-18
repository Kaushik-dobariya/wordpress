# SpiceCraft — Premium Spices Manufacturer CMS & Product Catalog Website

> **Phase 3 (Step 1: About Us CMS, Step 2: Manufacturing & Quality CMS, Step 3: Certifications CMS) Complete**  
> **Status:** Phase 3 Step 3 Complete · Ready for Verification & Phase 3 Step 4 Planning

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

## 21. Phase 3 — Step 1: About Us CMS & Premium About Page Architecture

### 1. About CMS Architecture
The About Us CMS provides complete administrative governance over the brand storytelling, operational standards, quality benchmarks, and milestone history without requiring modifications to PHP, HTML, CSS, or JavaScript.
- **Admin Location:** `SpiceCraft -> About Us` (`wp-admin/admin.php?page=spicecraft-about`).
- **Tabbed Interface:** 8 organized tabs:
  1. `1. Order & Visibility` — Enable/disable and set 0-150 numeric rendering priority for all 15 sections.
  2. `2. Hero & Intro` — Editorial brand statement, asymmetric media, and company introduction.
  3. `3. Story & Values` — Heritage narrative, origin quote card, and 4-pillar core values repeater.
  4. `4. Philosophy Pillars` — Quality standards, origin sourcing, and low-temperature milling highlights.
  5. `5. Journey & Stats` — Transparent verified metrics and responsive chronological milestones.
  6. `6. Leadership & Team` — Curated selection and display limit of published leadership members.
  7. `7. Trust & Products` — Statutory certifications and WooCommerce product/category showcase.
  8. `8. Business CTAs` — B2B wholesale trade inquiry band and final contact routing.

### 2. Option Schema (`spicecraft_about_settings`)
Stored in a single structured WordPress option `spicecraft_about_settings`:
```php
array(
    'sections_order'    => array( 'hero' => 10, 'introduction' => 20, ... ), // 15 numeric priorities
    'sections_enabled'  => array( 'hero' => 1, 'introduction' => 1, ... ),  // 15 boolean flags (0|1)
    'hero'              => array( 'eyebrow', 'heading', 'heading_highlight', 'intro', 'desktop_image_id', 'mobile_image_id', 'image_alt', 'cta_primary_label', 'cta_primary_url', 'cta_secondary_label', 'cta_secondary_url' ),
    'introduction'      => array( 'eyebrow', 'heading', 'content', 'image_primary_id', 'image_secondary_id', 'cta_label', 'cta_url' ),
    'story'             => array( 'eyebrow', 'heading', 'content', 'image_id', 'image_secondary_id', 'quote_text', 'quote_author' ),
    'vision_mission'    => array( 'eyebrow', 'heading', 'description', 'vision_enabled', 'vision_heading', 'vision_content', 'mission_enabled', 'mission_heading', 'mission_content' ),
    'values'            => array( 'eyebrow', 'heading', 'description', 'items' => array( array( 'icon', 'title', 'description', 'order' ) ) ),
    'quality'           => array( 'eyebrow', 'heading', 'description', 'image_id', 'points' => array( array( 'title', 'text' ) ), 'cta_label', 'cta_url' ),
    'sourcing'          => array( 'eyebrow', 'heading', 'description', 'image_id', 'image_secondary_id', 'points' => array( array( 'title', 'text' ) ), 'cta_label', 'cta_url' ),
    'manufacturing'     => array( 'eyebrow', 'heading', 'description', 'image_id', 'video_url', 'highlights' => array( array( 'title', 'text' ) ), 'cta_label', 'cta_url' ),
    'statistics'        => array( 'eyebrow', 'heading', 'description', 'items' => array( array( 'value', 'suffix', 'label', 'description', 'order' ) ) ),
    'milestones'        => array( 'eyebrow', 'heading', 'description', 'items' => array( array( 'year', 'title', 'description', 'image_id', 'order' ) ) ),
    'leadership'        => array( 'eyebrow', 'heading', 'description', 'selected_ids' => array(), 'limit' => 4 ),
    'certifications'    => array( 'eyebrow', 'heading', 'description', 'selected_ids' => array(), 'limit' => 6, 'cta_label', 'cta_url' ),
    'products'          => array( 'eyebrow', 'heading', 'description', 'source' => 'categories', 'selected_ids' => array(), 'limit' => 4, 'cta_label', 'cta_url' ),
    'b2b_cta'           => array( 'eyebrow', 'heading', 'description', 'bg_image_id', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'show_whatsapp' ),
    'final_cta'         => array( 'heading', 'description', 'cta_label', 'cta_url', 'show_whatsapp', 'show_email' ),
)
```

### 3. Section Ordering & 4. Section Semantic IDs
Default sequence, configuration IDs, and anchor targets:
1. `10` — `#about-hero` (`hero.php`)
2. `20` — `#company-intro` (`introduction.php`)
3. `30` — `#our-story` (`story.php`)
4. `40` — `#vision-mission` (`vision-mission.php`)
5. `50` — `#core-values` (`values.php`)
6. `60` — `#quality-philosophy` (`quality.php`)
7. `70` — `#sourcing-philosophy` (`sourcing.php`)
8. `80` — `#manufacturing-philosophy` (`manufacturing.php`)
9. `90` — `#company-statistics` (`statistics.php`)
10. `100` — `#journey-milestones` (`milestones.php`)
11. `110` — `#leadership-team` (`leadership.php`)
12. `120` — `#certifications-trust` (`certifications.php`)
13. `130` — `#product-connection` (`products.php`)
14. `140` — `#b2b-export` (`b2b-cta.php`)
15. `150` — `#final-contact` (`final-cta.php`)

### 5. Team Custom Post Type Architecture
- **Post Type Key:** `spicecraft_team` (strictly complies with WordPress core's 20-character post type length limit; `strlen('spicecraft_team') === 15`).
- **Menu Location:** Submenu under `SpiceCraft -> Leadership & Team`.
- **Supports:** `title` (Full Name), `editor` (Biographical narrative), `thumbnail` (High-resolution portrait photo).

### 6. Team Fields
- `_sc_team_role`: Designation / Role (e.g. *Head of Sourcing & Master Blender*).
- `_sc_team_linkedin`: Complete profile URL (e.g. `https://linkedin.com/in/...`).
- `_sc_team_order`: Integer sorting index for display prioritization.

### 7. Statistics Structure
- Repeatable rows: `value` (numeric string, e.g. `100`), `suffix` (e.g. `%`, `+`), `label` (e.g. *Purity Tested*), `description` (optional subtitle), `order`.
- **Zero-Fabricated-Data Enforcement:** Empty items cleanly hide the section from frontend rendering.

### 8. Milestone Structure
- Repeatable rows: `year` (date or period label, e.g. *Foundations*, *Phase 1*, *2024*), `title`, `description`, optional `image_id`, `order`.
- CSS-driven responsive timeline (alternating on desktop, clean vertical left-aligned on mobile).

### 9. Certification Integration
- Reuses existing `spicecraft_certification` taxonomy terms.
- Displays certified seals, regulatory license numbers, and authority badges without duplicating tax terms.

### 10. WooCommerce Integration
- Section 13 (`products`) bridges visitors back to active catalog items.
- Supports querying by either product categories or specific featured products using standard `WC_Product_Query` / `get_terms()`.

### 11. Global Settings Integration
- Phone, email, registered physical address, GSTIN, FSSAI license, and WhatsApp numbers are retrieved strictly from `spicecraft_global_settings`.
- No duplication of global contact information within About CMS.

### 12. Media Architecture
- Integrated with WordPress Media Library via `wp.media`.
- Stores integer Attachment IDs.
- Renders responsive markup using `wp_get_attachment_image()` with `srcset`, `sizes`, and native `loading="lazy"` below the fold.

### 13. Template Architecture
- Dedicated template: `wp-content/themes/spicecraft/page-about.php`.
- Modular section template parts in `wp-content/themes/spicecraft/template-parts/about/*.php`.
- Lightweight orchestrator pattern reading enabled sections via `spicecraft_get_about_active_sections()`.

### 14. CSS Architecture
- Dedicated stylesheet: `wp-content/themes/spicecraft/assets/css/about.css`.
- Enqueued exclusively on `is_page_template( 'page-about.php' )` or `is_page( 'about' )`.
- Reuses core CSS custom properties (`--sc-color-*`, `--sc-font-*`, `--sc-space-*`).
- Includes full `@media (prefers-reduced-motion: reduce)` accessibility support.

### 15. Security & Sanitization
- Nonce verification via `check_admin_referer('spicecraft_about_group-options')`.
- User capability check `current_user_can('manage_options')`.
- Strict sanitization: `sanitize_text_field()`, `wp_kses_post()`, `absint()`, `esc_url_raw()`.
- Template output escaping via `esc_html()`, `esc_attr()`, `esc_url()`, and `wp_kses_post()`.

### 16. Responsive Behavior
- Verified across 12 standard viewports: 1920px, 1440px, 1280px, 1024px, 820px, 768px, 480px, 430px, 390px, 375px, 360px, 320px.
- Zero horizontal overflow (`document.documentElement.scrollWidth === window.innerWidth`).
- Asymmetric grid layouts stack gracefully into single-column editorial blocks on mobile.

### 17. Accessibility
- Exactly one `<h1>` per page located in the Hero section.
- Proper heading levels (`<h2>` for section titles, `<h3>` for cards/points).
- Semantic `<section>` wrappers with descriptive `aria-label` attributes.
- Keyboard-accessible focus outlines (`:focus-visible`).
- Contrast ratios exceed WCAG AA standards across light and dark brand sections.

### 18. SEO Foundation
- Crawlable semantic HTML structure.
- Compatible with WordPress title-tag hooks.
- All media elements feature descriptive `alt` tags.
- Contextual internal linking to `/shop/`, category archives, and contact anchors.

### 19. Admin Usage
- To manage the About Us experience, navigate to `wp-admin -> SpiceCraft -> About Us`.
- Select any of the 8 tabs, make changes, and click **Save Changes**.
- To add or manage leadership profiles, navigate to `wp-admin -> SpiceCraft -> Leadership & Team`.

### 20. Manual Setup Actions
1. Ensure a WordPress page with slug `about` is published.
2. In the page settings, assign the template **About Us** (`page-about.php`).
3. If new team members are added, publish them under `SpiceCraft -> Leadership & Team`.

### 21. Deferred Functionality
The following items belong to future steps/phases and are intentionally deferred:
- Full standalone Certification archive / single certification pages (Phase 3 Step 3)
- Recipe CMS & Culinary Innovation blog
- Career application portal
- Interactive customer distributor portal / CRM

---

## 11. Phase 3 — Step 2: Manufacturing + Quality & Sourcing CMS Architecture & Frontend Pages

### 1. Manufacturing CMS Architecture
The Manufacturing CMS is an independent, structured content management module implemented in `wp-content/plugins/spicecraft-core/includes/settings/class-manufacturing-settings.php` and supported by `includes/helpers/manufacturing-helpers.php`.
- **Menu Location:** `wp-admin -> SpiceCraft -> Manufacturing` (`admin.php?page=spicecraft-manufacturing`).
- **Storage Strategy:** Stores all manufacturing configuration as a single structured option array under `spicecraft_manufacturing_settings` in `wp_options`, avoiding option table pollution.
- **Admin Layout:** Native WordPress admin interface organized into 8 logical tabs:
  1. *Order & Visibility* (drag/priority table and enable/disable toggles for all 15 sections)
  2. *Hero & Introduction* (single hero, title highlighting, rich intro narrative, media)
  3. *Facility & Process* (facility layout highlights, visual process step repeater)
  4. *Capabilities & Equipment* (core processing capabilities, machinery specs repeater)
  5. *Hygiene & Packaging* (sanitation operating procedures, packaging formats)
  6. *Stats & Gallery* (operational statistics repeater, multi-image gallery manager)
  7. *Trust & Products* (certification selection, WooCommerce catalog integration)
  8. *Business CTA* (technical inquiry & dual-action contact block)

### 2. Manufacturing Option Schema
The `spicecraft_manufacturing_settings` option adheres to the following structured PHP array schema:
```php
array(
    'sections_order'   => array( 'hero' => 10, 'introduction' => 20, ... ), // integer priority
    'sections_enabled' => array( 'hero' => 1, 'introduction' => 1, ... ),  // 1 or 0
    'hero' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'heading_highlight'   => string,
        'intro'               => string,
        'desktop_image_id'    => int,
        'mobile_image_id'     => int,
        'image_alt'           => string,
        'cta_primary_label'   => string,
        'cta_primary_url'     => string,
        'cta_secondary_label' => string,
        'cta_secondary_url'   => string,
    ),
    'introduction' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'content'             => string (rich HTML),
        'image_primary_id'    => int,
        'image_secondary_id'  => int,
        'cta_label'           => string,
        'cta_url'             => string,
    ),
    'facility' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'image_id'            => int,
        'highlights'          => array( array( 'title' => string, 'description' => string ), ... ),
    ),
    'process' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'items'               => array( array( 'step_number' => string, 'title' => string, 'description' => string, 'image_id' => int, 'icon' => string, 'order' => int ), ... ),
    ),
    'capabilities' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'items'               => array( array( 'title' => string, 'description' => string, 'icon' => string, 'order' => int ), ... ),
        'cta_label'           => string,
        'cta_url'             => string,
    ),
    'equipment' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'items'               => array( array( 'name' => string, 'description' => string, 'image_id' => int, 'order' => int, 'specs' => array( array( 'label' => string, 'value' => string ), ... ) ), ... ),
    ),
    'hygiene' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'image_id'            => int,
        'practices'           => array( array( 'title' => string, 'description' => string ), ... ),
        'cta_label'           => string,
        'cta_url'             => string,
    ),
    'packaging' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'image_id'            => int,
        'capabilities'        => array( array( 'title' => string, 'description' => string ), ... ),
        'cta_label'           => string,
        'cta_url'             => string,
    ),
    'warehousing' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'image_id'            => int,
        'highlights'          => array( array( 'title' => string, 'description' => string ), ... ),
    ),
    'statistics' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'items'               => array( array( 'value' => string, 'suffix' => string, 'label' => string, 'description' => string, 'order' => int ), ... ),
    ),
    'gallery' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'attachment_ids'      => array( int, ... ),
    ),
    'certifications' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'selected_ids'        => array( int, ... ),
    ),
    'products' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'source'              => 'categories' | 'products',
        'selected_ids'        => array( int, ... ),
        'display_count'       => int,
    ),
    'b2b_cta' => array(
        'eyebrow'             => string,
        'heading'             => string,
        'description'         => string,
        'primary_cta_label'   => string,
        'primary_cta_url'     => string,
        'secondary_cta_label' => string,
        'secondary_cta_url'   => string,
    ),
    'final_cta' => array(
        'heading'             => string,
        'description'         => string,
        'cta_label'           => string,
        'cta_url'             => string,
        'show_whatsapp'       => int (0|1),
        'show_email'          => int (0|1),
    ),
);
```

### 3. Manufacturing Sections
1. **Hero:** High-impact editorial masthead with title highlighting, introduction text, single responsive imagery, and dual CTAs.
2. **Introduction:** Deep philosophical narrative explaining manufacturing ethics, raw handling, and segregation.
3. **Facility Overview:** Architectural zoning details with side-by-side technical highlights and photography.
4. **Manufacturing Process:** Premium chronological flow from lot intake to finished goods, featuring numbered badges and alternating card hierarchy.
5. **Capabilities:** Grid of verified processing abilities (e.g. pulverization mesh sizing, grading) with subtle hover elevations.
6. **Technology & Equipment:** Industrial machinery catalog featuring nested technical specification tables (contact parts, motor ratings, capacity).
7. **Hygiene & Food Safety:** Sanitation SOPs and personnel entry controls with warm spice-brand accents.
8. **Packaging:** Commercial & retail packing formats with protective nitrogen flush specifications.
9. **Warehousing & Handling:** Finished lot quarantine, racked FIFO inventory controls, and ambient climate storage.
10. **Manufacturing Statistics:** Key verified operational metrics with big typographic numerals, custom suffixes (MT, %, h), and descriptive labels.
11. **Facility Gallery:** Responsive visual inspection gallery powered by WordPress attachment IDs.
12. **Certifications & Standards:** Direct integration with `spicecraft_certification` taxonomy displaying official accredited badges.
13. **Related Products:** WooCommerce category/product bridge linking industrial output to catalog discovery.
14. **B2B / Contract Manufacturing CTA:** Commercial contract milling inquiry card.
15. **Final Contact CTA:** Global contact integration with direct WhatsApp and Trade Desk inquiry actions.

### 4. Quality CMS Architecture
The Quality & Sourcing CMS is an independent, dedicated content management module implemented in `wp-content/plugins/spicecraft-core/includes/settings/class-quality-settings.php` and supported by `includes/helpers/quality-helpers.php`.
- **Menu Location:** `wp-admin -> SpiceCraft -> Quality & Sourcing` (`admin.php?page=spicecraft-quality`).
- **Storage Strategy:** Stores all quality & sourcing configuration under `spicecraft_quality_settings` in `wp_options`.
- **Admin Layout:** Native WordPress admin interface organized into 8 logical tabs:
  1. *Order & Visibility* (drag/priority table and enable/disable toggles for all 16 sections)
  2. *Hero & Introduction* (single hero, title highlighting, rich intro narrative, media)
  3. *Principles & Process* (quality pillars repeater, multi-stage inspection flow)
  4. *Testing & Laboratory* (testing protocols, method specifications, testing context selector)
  5. *Sourcing & Origins* (sourcing philosophy, structured agricultural regions repeater)
  6. *Traceability & Food Safety* (chain of custody steps, raw material acceptance criteria)
  7. *Trust & Products* (certification selection, WooCommerce catalog integration)
  8. *Business CTA* (COA / technical inquiry & dual-action contact block)

### 5. Quality Option Schema
The `spicecraft_quality_settings` option adheres to a structured PHP array schema with 16 sections, matching `spicecraft_manufacturing_settings` conventions while incorporating quality-specific fields:
- `testing_context`: Strict enum `['not_specified', 'in_house', 'external', 'combination']`.
- `regions`: Structured agricultural origin locations with `region_name`, `state`, `country`, `ingredient`, `description`, and `image_id`.
- `testing`: Analytical parameter rows with `name`, `description`, `method`, and `standard`.
- `traceability`: Gate-check steps tracking lot custody from weighbridge to dispatch.

### 6. Quality Sections
1. **Hero:** Visually distinct editorial masthead emphasizing purity, verification, and ethical sourcing.
2. **Quality Philosophy:** High-level narrative on empirical quality benchmarks.
3. **Quality Principles:** Core operational pillars (e.g. Zero Filler Policy, Essential Oil Retention).
4. **Quality Control Process:** Gate-check sequence detailing lot sampling and inspection.
5. **Testing / Laboratory:** Analytical testing parameters with explicit testing context indicators.
6. **Sourcing Philosophy:** Regional procurement strategy timed with seasonal harvest cycles.
7. **Sourcing Regions:** Structured geographic origins celebrating native growing belts and terroirs.
8. **Supplier / Raw Material Standards:** Inward acceptance criteria for agricultural lots.
9. **Traceability:** Documented batch traceability protocols.
10. **Food Safety:** Preventive sanitation and cross-contact controls.
11. **Certifications & Accreditations:** Reusable accreditation grid reading from taxonomy.
12. **Quality Statistics:** Numerical proof points (e.g. 100% batch traceability).
13. **Quality / Sourcing Gallery:** Visual tour of testing and sampling procedures.
14. **Product Connection:** Featured lab-tested catalog products.
15. **Quality / COA CTA:** Technical documentation and Certificate of Analysis inquiry block.
16. **Final Contact CTA:** Direct WhatsApp and Trade Desk inquiry actions.

### 7. Reusable Components & Primitives
To prevent duplicate code while maintaining strict architectural boundaries, all common CMS admin and rendering tasks utilize centralized primitives in `wp-content/plugins/spicecraft-core/includes/helpers/shared-cms-helpers.php`:
- `spicecraft_render_media_uploader()`: Single attachment ID media picker with image preview, thumbnail generation, and remove triggers.
- `spicecraft_render_gallery_manager()`: Multi-attachment gallery organizer with thumbnail grid, add button, remove triggers, and drag-and-drop sortable items.
- `spicecraft_render_section_order_table()`: Drag-and-drop / numeric priority table with active section toggles.
- `spicecraft_render_multiselect()`: Multi-selection interface for taxonomies, products, and categories.
- `spicecraft_render_repeatable_table()`: Flexible tabular row repeaters with add/remove actions.
- Shared sanitizers: `spicecraft_sanitize_numeric_order()`, `spicecraft_sanitize_enabled_flags()`, `spicecraft_sanitize_attachment_id_list()`.

### 8. Gallery Architecture
- **Storage:** Stores ordered arrays of WordPress Attachment IDs (integers).
- **Admin:** Interactive JavaScript manager via `wp.media` with multiple selection enabled, visual thumbnails, and instantaneous item removal.
- **Frontend:** Responsive CSS grid with natural aspect ratios, subtle hover zooms, image captions from attachment metadata, and full `srcset`/`sizes` generation.
- **Dependencies:** Zero external gallery plugins or heavy JavaScript libraries.

### 9. Process Architecture
- **Manufacturing Process:** Alternating step cards with numbered badges (`01`, `02`, `03`), bold editorial titles, and descriptive technical parameters.
- **Quality Control Process:** Multi-stage inspection flow displaying sequential gate checks.
- **Responsive Handling:** Desktop displays alternating/horizontal visual progressions; mobile automatically restructures into an intuitive, linear vertical sequence with connecting lines.

### 10. Equipment Architecture
- **Structure:** Repeatable machinery records supporting nested key-value specification pairs.
- **Admin:** Dynamic JavaScript row repeater allowing arbitrary technical specifications (e.g., *Contact Parts: Stainless Steel*, *Drive: Inverter VFD*, *Aspiration: Pneumatic*).
- **Frontend:** Clean technical spec tables integrated directly into equipment cards, styled with industrial precision.

### 11. Sourcing Region Architecture
- **Structure:** Repeatable geographic hub entries with structured fields: `region_name`, `state`, `country`, `ingredient`, `description`, `image_id`, and `order`.
- **Frontend:** Editorial visual geography cards highlighting native growing belts, terroir characteristics, and botanical varieties without requiring heavy external map APIs (Google Maps/Leaflet).

### 12. Testing-Context Architecture
- **Requirement:** Never falsely imply an on-site laboratory if testing is contracted to external NABL-accredited labs.
- **Implementation:** Explicit `testing_context` selector with 4 standardized states:
  - `not_specified`: Context badge cleanly suppressed.
  - `in_house`: Displayed as *In-House Facility Testing*.
  - `external`: Displayed as *Independent Accredited Laboratory Testing*.
  - `combination`: Displayed as *Combined In-House & Independent Laboratory Testing*.
- **Frontend:** Renders an elegant contextual badge and explanatory notice at the top of the testing grid.

### 13. Certification Integration
- **Source of Truth:** Reuses the existing `spicecraft_certification` custom taxonomy created in Phase 1.
- **Selection:** Admin chooses specific certifications to display on the Manufacturing and Quality pages via multi-select checkboxes.
- **Rendering:** Automatically pulls certification title, description, official accreditation logo, and issuing body from term metadata. Zero duplicate certification tables or redundant data entry.

### 14. WooCommerce Integration
- **Catalog Linking:** Both pages connect directly to the WooCommerce product catalog via category cards or specific product cards.
- **Component Reuse:** Uses the shared, battle-tested `content-product.php` card component.
- **Catalog Mode Safety:** Guaranteed zero purchase actions — all product links navigate to single product catalog pages or trigger the B2B Trade Enquiry modal.

### 15. Global Settings Integration
- **Contact Details:** Phone numbers, email addresses, WhatsApp numbers, physical addresses, and corporate identifiers are read dynamically from `spicecraft_global_settings`.
- **WhatsApp Formatter:** CTA links utilize `spicecraft_get_whatsapp_url()` to generate pre-filled, context-aware WhatsApp chat sessions for manufacturing inquiries.

### 16. Template Architecture
- **Manufacturing Orchestrator:** `wp-content/themes/spicecraft/page-manufacturing.php` (Template Name: *Manufacturing*).
- **Manufacturing Parts (15 files):** `template-parts/manufacturing/hero.php`, `introduction.php`, `facility.php`, `process.php`, `capabilities.php`, `equipment.php`, `hygiene.php`, `packaging.php`, `warehousing.php`, `statistics.php`, `gallery.php`, `certifications.php`, `products.php`, `b2b-cta.php`, `final-cta.php`.
- **Quality Orchestrator:** `wp-content/themes/spicecraft/page-quality.php` (Template Name: *Quality & Sourcing*).
- **Quality Parts (16 files):** `template-parts/quality/hero.php`, `introduction.php`, `principles.php`, `process.php`, `testing.php`, `sourcing.php`, `regions.php`, `raw-materials.php`, `traceability.php`, `food-safety.php`, `certifications.php`, `statistics.php`, `gallery.php`, `products.php`, `b2b-cta.php`, `final-cta.php`.
- **Orchestrator Pattern:** Main templates iterate through active sections returned by helper APIs, delegating rendering to individual template parts.

### 17. CSS Architecture
- **Dedicated Stylesheets:**
  - `assets/css/manufacturing.css` (Precision, scale, industrial contrast, process sequences)
  - `assets/css/quality.css` (Clean, natural, editorial whitespace, testing grids, origin cards)
- **Conditional Loading:** Enqueued in `inc/enqueue.php` strictly on their respective page templates. Zero CSS bloat on shop or product pages.
- **Design Tokens:** Strictly utilizes root tokens (`--sc-color-brand-primary`, `--sc-color-surface`, `--sc-space-*`, `--sc-radius-*`, `--sc-font-*`).

### 18. Security & Sanitization
- **Admin Verification:** Every save action verifies WordPress nonces (`check_admin_referer()`) and requires `current_user_can('manage_options')`.
- **Sanitization:** All text inputs sanitized via `sanitize_text_field()`, descriptions via `wp_kses_post()`, URLs via `esc_url_raw()`, attachment and term IDs via `absint()`.
- **Output Escaping:** Universal output escaping via `esc_html()`, `esc_attr()`, `esc_url()`, and `wp_kses_post()`. Zero raw `echo` statements.

### 19. Accessibility (a11y)
- **Heading Hierarchy:** Strictly one `<h1>` per page (in Hero or Empty State). All section headings use `<h2>`, and component titles use `<h3>`.
- **Semantics:** Landmark `<section>` wrappers with descriptive `aria-label` attributes.
- **Keyboard Navigation:** Full focus visibility with `--sc-color-brand-primary` focus rings.
- **Touch Targets:** All buttons and interactive triggers measure at least 44px on mobile viewports.
- **Reduced Motion:** Comprehensive `@media (prefers-reduced-motion: reduce)` rules disable transitions and animations.

### 20. Performance
- **Image Optimization:** Uses WordPress `wp_get_attachment_image()` with responsive `srcset` and `sizes`.
- **LCP Protection:** Hero images are rendered eagerly without lazy loading; all below-the-fold imagery includes native `loading="lazy"`.
- **Query Efficiency:** Product and certification lookups execute single batch queries using `include` / `post__in`. Zero N+1 query loops.
- **Asset Overhead:** Zero external dependencies, fonts, or third-party JS libraries added.

### 21. Responsive Behavior
- **Viewport Scale Tested:** Tested and verified across all 14 standard viewports: 1920px, 1600px, 1440px, 1366px, 1280px, 1024px, 820px, 768px, 480px, 430px, 390px, 375px, 360px, 320px.
- **Zero Horizontal Overflow:** Verified `document.documentElement.scrollWidth === window.innerWidth` across all 14 viewports for both pages in both empty and populated states.
- **Mobile Recomposition:** Multi-column grids smoothly transition to 2-column or 1-column layouts; process steps stack vertically with preserved visual sequence.

### 22. Admin Instructions
1. Navigate to `wp-admin -> SpiceCraft -> Manufacturing` or `SpiceCraft -> Quality & Sourcing`.
2. Use the **Order & Visibility** tab to enable/disable specific sections and adjust display sequence.
3. Configure genuine, verified business details in the respective tabs.
4. Click **Save Changes**. The frontend immediately updates to reflect published data.

### 23. Menu Instructions
To add Manufacturing and Quality & Sourcing pages to navigation:
1. Go to `wp-admin -> Appearance -> Menus`.
2. Select the Primary Menu.
3. Under **Pages**, check **Manufacturing** and **Quality & Sourcing**.
4. Click **Add to Menu**, position them under the desired hierarchy, and click **Save Menu**.
5. *Note:* Both pages are also included in the theme's built-in header and mobile drawer fallback menus.

### 24. Content Ownership Strategy
- **Global Settings:** Owns corporate identity, phone, email, WhatsApp, physical address, and global legal identifiers.
- **Certification System:** Owns formal accreditation records (`spicecraft_certification` taxonomy).
- **WooCommerce:** Owns catalog items, categories, attributes, and product specifications.
- **About Us CMS:** Retains concise, brand-level executive summaries and heritage narratives.
- **Manufacturing CMS:** Owns plant-specific engineering, equipment, layout, and processing content.
- **Quality CMS:** Owns laboratory testing, origin terroir, sourcing criteria, and food safety protocols.

### 25. Sample & Fabricated-Data Audit
- **Zero-Fabricated-Content Rule:** Strictly enforced. No demo business claims (cryogenic milling, cleanrooms, farm networks, factory capacity, 100%/4+/0 statistics, etc.) are pre-seeded in the database or hard-coded into frontend templates.
- **Audit Verification:** All sample data from Step 1 was purged during Step 0. Default option schemas for Manufacturing and Quality contain empty strings and empty arrays.
- **Empty State Behavior:** Unpopulated or disabled sections are cleanly suppressed with zero empty containers or layout gaps. If all sections are unpopulated, a clean setup guide is displayed.

### 26. Deferred Functionality (Step 2)
The following items belonged to future steps/phases and are appropriately sequenced:
- Full standalone Certification archive / single certification pages (Completed in Phase 3 Step 3)
- Recipe CMS & Culinary Innovation blog (Phase 3 Step 4)
- Career application portal
- Interactive customer distributor portal / CRM
- Google Maps interactive sourcing GIS system
- ERP / Inventory management integration

---

## 9. Phase 3 Step 3: Certifications CMS & Premium Certification Experience

### 1. Existing Certification Architecture Audit
- **Taxonomy Retention:** Audited existing `spicecraft_certification` taxonomy registered against post type `product`. Retained and extended the existing taxonomy architecture rather than creating a competing CPT or duplicate data store.
- **Data Cleanup:** Searched the development environment and database for demo/seeded terms (`FSSAI`, `ISO 22000`, `HACCP`, `BRCGS`, `HALAL`, `Organic`, `AGMARK`). All 7 demo terms were completely purged from the taxonomy, and product term relationships were cleared. Zero fabricated terms remain.
- **Frontend Audit:** Confirmed that all frontend templates (Homepage, About, Manufacturing, Quality, Single Product) consume only genuine public taxonomy terms, hiding gracefully when unpopulated.

### 2. Certification Data Model
Each certification supports comprehensive, statutory compliance metadata:
- **Identity:** Certification Name, Short Name / Acronym, Description, Official Logo / Mark (WP Media attachment ID), Featured toggle.
- **Authority:** Certificate / Registration Number, Issuing Authority / Registrar, Accreditation Body.
- **Validity:** Issue Date, Valid From Date, Expiry Date, Controlled Status, Automatic Expiry Detection toggle.
- **Scope:** Scope Description (audit parameters, standards covered), Facility / Location Scope (plants/units covered).
- **Media & Documents:** Certificate Scan Image (attachment ID), Certificate Document / PDF (attachment ID).
- **Verification:** Official Issuing Authority Verification URL (sanitized external link).
- **Relationships:** Related WooCommerce Products (array of product IDs), Related Product Categories (array of product_cat IDs).
- **Visibility:** Public Visibility (`public` vs `internal`), Document Visibility (`public` vs `private`), Public Detail Page toggle (`1` vs `0`), Display Order (integer priority), Internal Notes.

### 3. Metadata Keys
All term metadata keys follow the canonical `_sc_cert_*` naming convention:
- `_sc_cert_short_name` (string)
- `_sc_cert_number` (string)
- `_sc_cert_issuing_authority` (string)
- `_sc_cert_accreditation_body` (string)
- `_sc_cert_issue_date` (YYYY-MM-DD)
- `_sc_cert_valid_from` (YYYY-MM-DD)
- `_sc_cert_expiry_date` (YYYY-MM-DD)
- `_sc_cert_status` (`active`, `expired`, `pending_renewal`, `suspended`, `archived`, `not_disclosed`)
- `_sc_cert_auto_expiry` (`1` or `0`)
- `_sc_cert_scope` (textarea)
- `_sc_cert_facility_scope` (string)
- `_sc_cert_logo_id` (int attachment ID)
- `_sc_cert_image_id` (int attachment ID)
- `_sc_cert_doc_id` (int attachment ID)
- `_sc_cert_doc_visibility` (`public` or `private`)
- `_sc_cert_verification_url` (safe URL)
- `_sc_cert_visibility` (`public` or `internal`)
- `_sc_cert_public_detail` (`1` or `0`)
- `_sc_cert_featured` (`1` or `0`)
- `_sc_cert_order` (int)
- `_sc_cert_related_products` (int[])
- `_sc_cert_related_categories` (int[])
- `_sc_cert_notes` (textarea, internal only)

### 4. Admin Workflow
- **Admin Screens:** Integrated into standard WordPress taxonomy UI under `Products -> Certifications` and linked directly from `SpiceCraft -> Certifications Display`.
- **Field Organization:** Form fields are grouped into 8 clean, WordPress-native fieldsets:
  1. Identity & Acronym
  2. Status & Automatic Expiry
  3. Authority & Registration
  4. Validity & Lifecycle
  5. Audit Scope & Facility Coverage
  6. Official Media & PDF Documents
  7. Verification Portal
  8. Visibility, Detail Toggles & Commercial Relationships
- **List Table Columns:** Custom columns display Logo thumbnail, Short Name, Controlled Status Badge, Certificate Number, Issuing Authority, Expiry Date with warning badges, Visibility status, and Numeric Order.
- **Enqueued Media:** Native WordPress media modal handles logo, image, and PDF attachment selection with live image and file previews.

### 5. Status Model
- **Controlled Statuses:** `active`, `expired`, `pending_renewal`, `suspended`, `archived`, `not_disclosed`.
- **Textual Badges:** Status presentation strictly pairs a colored status dot with human-readable, accessible text labels (`Active / Valid`, `Expired`, `Pending Renewal`, `Suspended`, `Archived`, `Status on Request`). Never relies on color alone.
- **Strict Active Rule:** The system **never** automatically infers `active` simply because an expiry date is in the future. Active status must be explicitly selected by the administrator.

### 6. Automatic Expiry Behavior
- **Setting:** `Automatic Expiry Detection` (`auto_expiry_detection` under Global Display Settings or per-term `auto_expiry`). Default: **No (Disabled)**.
- **Behavior:** When enabled AND an expiry date exists, the helper `spicecraft_get_certification_effective_status()` automatically computes `expired` if `current_date > expiry_date`. If disabled, administrator manual status selection is strictly respected.

### 7. Visibility Model
- **Values:** `public` vs `internal`.
- **Public Guard:** Only `public` records are returned by `spicecraft_get_public_certifications()`.
- **Internal Protection:** Internal certifications are completely suppressed from:
  - Public archive `/certifications/`
  - Single product trust badges
  - Homepage certifications section
  - About Us certifications section
  - Manufacturing page certifications
  - Quality & Sourcing page certifications
  - If a user navigates directly to the detail URL of an internal certification, the system issues an immediate HTTP 302 redirect to `/certifications/`.

### 8. Document Visibility Limitations
- **Values:** `public` vs `private`.
- **Frontend Suppression:** When marked `private`, `spicecraft_get_certification_public_document_url()` returns an empty string. The template completely suppresses all document download buttons and anchors.
- **Security Limitation Notice:** WordPress Media Library uploads reside at public URLs on the web server. Marking a document `private` suppresses its exposure on frontend templates but does **not** provide encrypted or access-controlled server-side file serving. If true DRM or authenticated file streaming is required, it must be implemented at the web-server or cloud-storage level.

### 9. Product Relationships
- **Direct & Inverse Mapping:** Certifications can link to products either via standard taxonomy assignment (`wp_set_object_terms`) or via the `_sc_cert_related_products` term meta array.
- **Detail Integration:** The certification detail page renders related products using the canonical `content-product.php` card component.
- **Catalog Mode Safety:** All related product cards operate strictly under catalog mode with purchasing, cart, and pricing buy-buttons removed.

### 10. Category Relationships
- **Taxonomy Term Links:** Certifications link to product categories via `_sc_cert_related_categories`.
- **Detail Rendering:** The detail page displays verified product category cards linking back to the relevant shop category archive.

### 11. Homepage Integration
- Consumes centralized public certifications via `spicecraft_get_public_certifications()`.
- Supports filtering by `featured` flag or administrator-selected IDs in Homepage CMS settings.
- Gracefully suppresses the entire section when no genuine certifications exist.

### 12. About Integration
- Consumes centralized public certifications via `template-parts/about/certifications.php`.
- Renders verified accreditations without duplicating certification fields in About settings.

### 13. Manufacturing Integration
- Integrates with `template-parts/manufacturing/certifications.php`.
- Respects administrator section enablement and selected certification IDs.

### 14. Quality Integration
- Integrates with `template-parts/quality/certifications.php`.
- Displays formal laboratory, safety, and testing accreditations with badges and detail links.

### 15. Product-Page Integration
- **Strict Scope Rule:** Product pages (`content-single-product.php`) display certification badges **only** for certifications explicitly assigned to that product or referencing that product ID.
- **No Global Assumption:** The system never assumes a company-wide certification applies to all products.
- **Compact UI:** Renders subtle trust pills (`sc-product-certs`) with official logo, short name, and link to the certification detail specification.

### 16. Archive Architecture
- **URL:** `/certifications/` (via `page-certifications.php`).
- **Template Parts (10 modular files):**
  - `template-parts/certifications/archive-hero.php`
  - `template-parts/certifications/archive-filters.php`
  - `template-parts/certifications/archive-grid.php`
  - `template-parts/certifications/detail-hero.php`
  - `template-parts/certifications/detail-overview.php`
  - `template-parts/certifications/detail-scope.php`
  - `template-parts/certifications/detail-documents.php`
  - `template-parts/certifications/detail-verification.php`
  - `template-parts/certifications/detail-products.php`
  - `template-parts/certifications/final-cta.php`
- **Empty State:** Displays a neutral, non-misleading notice: *"Certification Information Being Updated"* with contact options. Zero fake cards.

### 17. Detail Architecture
- **Template:** `taxonomy-spicecraft_certification.php`.
- **Breadcrumbs:** Structured breadcrumb navigation (`Home / Certifications / {Certification Name}`).
- **Layout:** 2-column editorial grid (Scope, Facility Coverage, Related Products on the left; Certificate Specifications, Document Viewer, Official Verification on the right).
- **Commercial CTA:** Integrated final CTA for requesting verified certification dossiers via WhatsApp and Email.

### 18. Filtering
- Lightweight URL query state filters:
  - `cert_status`: Active, Pending Renewal, etc.
  - `cert_cat`: Filter by product category.
  - `cert_featured`: Filter by featured flag.
- Reset action automatically returns to the full archive.

### 19. Sorting
- Supported sort options:
  - `order`: Administrator display order (default).
  - `title_asc`: Alphabetical A-Z.
  - `issue_date_desc`: Newest issued.
  - `expiry_date_asc`: Expiry date order.

### 20. Responsive Design
- **14 Viewports Verified:** 1920, 1600, 1440, 1366, 1280, 1024, 820, 768, 480, 430, 390, 375, 360, 320.
- **Zero Horizontal Overflow:** `scrollWidth <= winWidth` verified across all viewports on both Archive and Detail.
- **Mobile Stack:** Desktop 2-column detail grid seamlessly stacks to a single column on tablet/mobile with 44px touch targets.

### 21. Accessibility (a11y)
- **Single H1:** Exactly one `<h1>` per page.
- **External Links:** Verification links explicitly declare `target="_blank"` with `rel="noopener noreferrer"` and accessible screen-reader notices.
- **Color Independence:** Status badges combine high-contrast text labels with iconography.
- **Keyboard & Focus:** Visible gold/amber focus outlines on interactive links and buttons.
- **Reduced Motion:** Fully compatible with `prefers-reduced-motion: reduce`.

### 22. Performance
- **Image Variants:** Uses WordPress `wp_get_attachment_image()` with appropriate size variants (`thumbnail` for badges, `medium` for cards, `large` for certificate scans).
- **Lazy Loading:** All card and detail images below the fold include native `loading="lazy"`.
- **Efficient Queries:** Avoids N+1 query loops by querying terms with single batch retrieval.

### 23. SEO
- **Title Tags:** Native WordPress document title support.
- **Canonical URLs:** Uses canonical WordPress taxonomy permalinks.
- **Internal Links:** Structured cross-linking between Certifications, Quality, Manufacturing, and Products.

### 24. Security
- **Capability Checks:** All admin operations strictly require `current_user_can('manage_options')`.
- **Nonce Verification:** Every term save and settings update verifies cryptographic nonces (`check_admin_referer()`, `wp_verify_nonce()`).
- **Sanitization & Escaping:** Rigorous sanitization on save and context-specific escaping on render.

### 25. Admin Expiry Warnings
- **90-Day Warning Threshold:** Admin list table visually flags certificates expiring within 90 days with amber alert badges.
- **Dashboard Notice:** Non-intrusive warning notice appears on the taxonomy screen when certificates are expired or expiring soon.

### 26. Fabricated-Data Audit
- **Zero-Fabricated-Content Rule:** Strictly enforced. No fake certificate numbers, authorities, dates, or fake seal graphics exist in the database or code.
- **Clean Slate:** Database currently contains 0 terms. Frontend empty states display clean, neutral notices.

### 27. Manual Configuration
1. Go to `wp-admin -> Products -> Certifications`.
2. Enter genuine certification records (Name, Short Name, Authority, Number, Dates, Scope, Logo, PDF).
3. Set **Visibility** to *Public* when ready for frontend display.
4. Set **Public Detail Page** to *Yes* for comprehensive records or *No* for thin records (name + logo only).
5. Configure archive hero and display toggles under `SpiceCraft -> Certifications Display`.

### 28. Deferred Functionality
The following items belong to future steps/phases:
- Recipe CMS & Culinary Innovation blog (Phase 3 Step 4)
- Career application portal
- Interactive customer distributor portal / CRM
- Automated registrar API verification integrations
- Certificate OCR / automated scanning
- Protected private document server


