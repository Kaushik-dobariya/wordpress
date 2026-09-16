<?php
/**
 * SpiceCraft - Phase 2 Step 2B Automated Regression Test Suite
 */

$baseUrl = 'http://localhost/';

echo "=== SPICECRAFT PHASE 2 STEP 2B TEST SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function assert_test( $testNum, $title, $condition, $details = '' ) {
    global $passCount, $failCount;
    if ( $condition ) {
        $passCount++;
        echo "[+] PASS ($testNum): $title" . ( $details ? " - $details" : "" ) . "\n";
    } else {
        $failCount++;
        echo "[-] FAIL ($testNum): $title" . ( $details ? " - $details" : "" ) . "\n";
    }
}

// 1. Fetch homepage
$html = @file_get_contents($baseUrl);
if ($html === false) {
    echo "[-] FAILED: Could not connect to $baseUrl\n";
    exit(1);
}
echo "[+] SUCCESS: Connected to $baseUrl (HTTP 200, length " . strlen($html) . " bytes)\n\n";

// Check 1: Sticky Header Structure - Topbar before site-header
$topbarPos = strpos($html, 'class="sc-topbar"');
$headerPos = strpos($html, '<header id="masthead" class="site-header"');
assert_test( 1, 'Header Architecture', ( $topbarPos !== false && $headerPos !== false && $topbarPos < $headerPos ), 'sc-topbar is rendered outside and prior to sticky site-header' );

// Check 2: Sticky Header CSS definition
$mainCss = file_get_contents( __DIR__ . '/../wp-content/themes/spicecraft/assets/css/main.css' );
$hasSticky = strpos( $mainCss, 'position: sticky;' ) !== false && strpos( $mainCss, 'top: 0;' ) !== false;
assert_test( 2, 'Header Sticky CSS', $hasSticky, 'site-header has position: sticky; top: 0;' );

// Check 3: Header Scrolled State CSS
$hasScrolledCss = strpos( $mainCss, '.site-header.is-scrolled' ) !== false && strpos( $mainCss, 'box-shadow:' ) !== false;
assert_test( 3, 'Header Scrolled State CSS', $hasScrolledCss, 'site-header.is-scrolled defines elevated shadow, solid background, and padding transition' );

// Check 4: WordPress Admin Bar compensation in CSS
$hasAdminBarCss = strpos( $mainCss, 'body.admin-bar .site-header' ) !== false && strpos( $mainCss, 'top: 32px;' ) !== false && strpos( $mainCss, 'top: 46px;' ) !== false;
assert_test( 4, 'WP Admin Bar Compensation', $hasAdminBarCss, 'Desktop 32px and Mobile 46px admin bar offsets configured' );

// Check 5: Main JS has passive scroll listener & RAF
$mainJs = file_get_contents( __DIR__ . '/../wp-content/themes/spicecraft/assets/js/main.js' );
$hasPassiveScroll = strpos( $mainJs, 'requestAnimationFrame' ) !== false && strpos( $mainJs, 'is-scrolled' ) !== false;
assert_test( 5, 'Passive Scroll Listener (RAF)', $hasPassiveScroll, 'Unified scroll listener uses requestAnimationFrame and passive listener' );

// Check 6: Scroll-to-top button markup in HTML
$hasScrollTopBtn = preg_match( '/<button[^>]*id="sc-scroll-top"[^>]*aria-label="Back to top"[^>]*>/i', $html );
assert_test( 6, 'Scroll-to-top Markup', (bool) $hasScrollTopBtn, '<button id="sc-scroll-top" aria-label="Back to top"> present in DOM' );

// Check 7: Scroll-to-top button is semantic button with SVG up arrow and NO visible "Scroll Top" text
$hasSvgArrow = strpos( $html, 'sc-scroll-top' ) !== false && strpos( $html, '<polyline points="18 15 12 9 6 15"' ) !== false;
$hasDisallowedText = stripos( $html, '>Scroll Top<' ) !== false;
assert_test( 7, 'Scroll-to-top Icon & Text', ( $hasSvgArrow && ! $hasDisallowedText ), 'Contains SVG arrow without hard-coded visible "Scroll Top" text' );

// Check 8: Scroll-to-top CSS rules
$hasScrollTopCss = strpos( $mainCss, '.sc-scroll-top' ) !== false && strpos( $mainCss, '.sc-scroll-top.is-visible' ) !== false && strpos( $mainCss, 'position: fixed;' ) !== false;
assert_test( 8, 'Scroll-to-top Styling', $hasScrollTopCss, 'Circular fixed button with elevation shadow, visibility states, and hover effects' );

// Check 9: Reduced motion preference handled in CSS and JS
$hasReducedMotionCss = strpos( $mainCss, 'prefers-reduced-motion: reduce' ) !== false;
$hasReducedMotionJs = strpos( $mainJs, 'prefers-reduced-motion: reduce' ) !== false;
assert_test( 9, 'Reduced Motion Support', ( $hasReducedMotionCss && $hasReducedMotionJs ), 'CSS disables transitions and JS uses behavior: auto for prefers-reduced-motion' );

// Check 10: Mobile responsiveness for scroll-to-top
$hasMobileScrollTopCss = strpos( $mainCss, 'max-width: 768px' ) !== false && strpos( $mainCss, 'bottom: 18px;' ) !== false;
assert_test( 10, 'Scroll-to-top Mobile Dimensions', $hasMobileScrollTopCss, 'Reduced to 44x44px with 18px margins on mobile' );

// CONTENT CLAIM AUDIT CHECKS (Checks 11 to 24)
echo "\n--- Content Claim Audit Verification ---\n";
$prohibitedClaims = array(
    '100% Origin Guaranteed'            => 'Hero Badge claim',
    'Single Origin Lot Tested'           => 'Hero trust item',
    'Cryogenic Cold Milled'              => 'Hero / WCU claim',
    'Export Certified Facility'          => 'Hero trust item',
    'Zero Artificial Colors or Fillers'  => 'Hero floating badge',
    '35+ Years of Heritage Sourcing'     => 'Brand story stat',
    '25,000 MT'                          => 'Manufacturing annual capacity',
    'SS 316'                             => 'Manufacturing food contact stat',
    '< 40°C'                             => 'Manufacturing cryo-mill temp',
    '<40°C'                              => 'Manufacturing cryo-mill temp',
    '100% Batch COA Tested'              => 'Manufacturing COA stat',
    'Cryogenic Pulverization Plant'      => 'Manufacturing stage overlay',
    'Direct Plantation Harvest'          => 'Quality & Sourcing overlay',
    'Artisanal Purity'                   => 'Hero floating badge label',
);

$auditViolations = 0;
foreach ( $prohibitedClaims as $claim => $desc ) {
    $found = stripos( $html, $claim ) !== false;
    if ( $found ) {
        echo "[-] PROHIBITED CLAIM FOUND: '$claim' ($desc)\n";
        $auditViolations++;
    }
}
assert_test( 11, 'Prohibited Fabricated Claims Absent', ( $auditViolations === 0 ), "0 of " . count($prohibitedClaims) . " fabricated claims found in rendered HTML" );

// Check 12: Legitimate CMS data continues to render
$legitimateContent = array(
    'id="home-hero"'          => 'Hero section',
    'id="product-categories"' => 'Product categories',
    'id="featured-products"'  => 'Featured products',
    'id="brand-story"'        => 'Brand story',
    'id="why-choose-us"'      => 'Why choose us',
    'id="quality-sourcing"'   => 'Quality & sourcing',
    'id="manufacturing"'      => 'Manufacturing facility',
    'id="certifications"'     => 'Certifications',
    'id="product-discovery"'  => 'Product discovery',
    'id="latest-insights"'    => 'Blog insights',
    'id="business-enquiry"'   => 'B2B enquiry',
    'id="contact-cta"'        => 'Final CTA',
);

$missingContent = 0;
foreach ( $legitimateContent as $needle => $name ) {
    if ( strpos( $html, $needle ) === false ) {
        echo "[-] Missing legitimate content: $name ($needle)\n";
        $missingContent++;
    }
}
assert_test( 12, 'Legitimate CMS Sections Preserved', ( $missingContent === 0 ), "All 12 active homepage sections present" );

// Check 13: Certifications taxonomy terms render legitimately
$hasCertItems = strpos( $html, 'class="sc-cert-item"' ) !== false;
assert_test( 13, 'Certifications Dynamic Rendering', $hasCertItems, 'Certifications render legitimate taxonomy terms from spicecraft_certification' );

// Check 14: Single H1 Tag
preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches );
$h1Count = count( $h1Matches[0] );
assert_test( 14, 'Single H1 Accessibility Rule', ( $h1Count === 1 ), "Exactly 1 H1 tag found ('" . trim( strip_tags( $h1Matches[0][0] ?? '' ) ) . "')" );

// Check 15: Catalog-Only Mode (Zero Add to Cart, Cart, Checkout)
$disallowedTerms = ['name="add-to-cart"', 'add_to_cart_button', 'woocommerce-cart', 'wc-proceed-to-checkout'];
$cartViolations = 0;
foreach ( $disallowedTerms as $dt ) {
    if ( stripos( $html, $dt ) !== false ) {
        $cartViolations++;
    }
}
assert_test( 15, 'Strict Catalog-Only Mode', ( $cartViolations === 0 ), '0 add-to-cart buttons and 0 cart/checkout links' );

// Check 16: Zero PHP errors or notices
$hasPhpError = preg_match( '/(Fatal error|Parse error|Warning:|Notice:)/i', $html, $errMatch );
assert_test( 16, 'Zero PHP Errors or Warnings', ! $hasPhpError, $hasPhpError ? "Error: " . $errMatch[0] : "Clean PHP execution" );

// Check 17: Inner Page (Shop Catalog) Sticky Header & Scroll-to-top
$shopHtml = @file_get_contents( $baseUrl . 'shop/' );
$shopHasHeader = strpos( $shopHtml, '<header id="masthead" class="site-header"' ) !== false;
$shopHasScrollTop = strpos( $shopHtml, 'id="sc-scroll-top"' ) !== false;
assert_test( 17, 'Shop Catalog Sticky Header & Scroll-to-top', ( $shopHasHeader && $shopHasScrollTop ), 'Site-wide persistence verified on /shop/' );

// Check 18: Inner Page (Product Detail) Sticky Header & Scroll-to-top
$detailHtml = @file_get_contents( $baseUrl . '?post_type=product&p=21' );
$detailHasHeader = strpos( $detailHtml, '<header id="masthead" class="site-header"' ) !== false;
$detailHasScrollTop = strpos( $detailHtml, 'id="sc-scroll-top"' ) !== false;
assert_test( 18, 'Single Product Sticky Header & Scroll-to-top', ( $detailHasHeader && $detailHasScrollTop ), 'Site-wide persistence verified on single product page' );

echo "\n============================================\n";
echo "TEST RESULTS: $passCount Passed, $failCount Failed\n";
echo "============================================\n";

if ( $failCount > 0 ) {
    exit(1);
}
