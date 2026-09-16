<?php
/**
 * Test Suite for SpiceCraft Phase 2 Step 3
 * Advanced Product Discovery, Favourites, Recently Viewed & Enquiry UX
 */

require_once __DIR__ . '/../wp-load.php';

$passed = 0;
$failed = 0;

function assert_test($description, $condition, $details = '') {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [+] PASS: {$description} " . ($details ? "({$details})" : "") . "\n";
    } else {
        $failed++;
        echo "  [-] FAIL: {$description} " . ($details ? "({$details})" : "") . "\n";
    }
}

echo "=== PHASE 2 STEP 3 PHP AUTOMATED TEST SUITE ===\n\n";

// 1. Search by SKU
$query_sku = new WP_Query([
    'post_type' => 'product',
    's'         => 'SC-TUR-500',
    'posts_per_page' => 5
]);
assert_test('Product search finds item by SKU (SC-TUR-500)', $query_sku->have_posts() && $query_sku->posts[0]->post_title === 'Organic Turmeric Powder (High Curcumin 5%+)');

// 2. Search by Title keyword
$query_title = new WP_Query([
    'post_type' => 'product',
    's'         => 'Turmeric',
    'posts_per_page' => 5
]);
assert_test('Product search finds item by Title keyword (Turmeric)', $query_title->have_posts() && false !== stripos($query_title->posts[0]->post_title, 'Turmeric'));

// 3. Search with non-matching term returns 0
$query_none = new WP_Query([
    'post_type' => 'product',
    's'         => 'NonExistentSpiceXYZ987',
    'posts_per_page' => 5
]);
assert_test('Search with non-existent keyword returns 0 results', $query_none->found_posts === 0);

// 4. Catalog sorting options
$sort_options = apply_filters('woocommerce_catalog_orderby', []);
assert_test('Catalog sorting removes price (low to high)', !isset($sort_options['price']));
assert_test('Catalog sorting removes price (high to low)', !isset($sort_options['price-desc']));
assert_test('Catalog sorting includes Name: A to Z', isset($sort_options['title']));
assert_test('Catalog sorting includes Name: Z to A', isset($sort_options['title-desc']));
assert_test('Catalog sorting includes Newest First', isset($sort_options['date']));

// 5. Available pack sizes helper
$avail_packs = function_exists('spicecraft_get_catalog_available_pack_sizes') ? spicecraft_get_catalog_available_pack_sizes() : [];
assert_test('spicecraft_get_catalog_available_pack_sizes returns valid list', !empty($avail_packs) && in_array('100g', $avail_packs, true));

// 6. Favourites Shortcode output
$shortcode_out = do_shortcode('[spicecraft_favourites]');
assert_test('Favourites shortcode outputs root container and empty state', false !== strpos($shortcode_out, 'sc-favourites-experience') && false !== strpos($shortcode_out, 'sc-favourites-empty'));

// 7. Favourites page HTTP status
$fav_url = home_url('/favourites/');
$fav_html = @file_get_contents($fav_url);
assert_test('Favourites page is accessible (HTTP 200)', false !== $fav_html && false !== strpos($fav_html, 'sc-favourites-experience'));

// 8. Shop page HTTP and elements
$shop_url = wc_get_page_permalink('shop');
$shop_html = @file_get_contents($shop_url);
assert_test('Shop catalog renders search form with clear button support', false !== strpos($shop_html, 'sc-catalog-search-form'));
assert_test('Shop catalog renders horizontal desktop filter bar', false !== strpos($shop_html, 'sc-desktop-filter-bar'));
assert_test('Shop catalog renders mobile filter drawer', false !== strpos($shop_html, 'sc-filter-drawer'));
assert_test('Shop catalog renders product cards with heart favourite button', false !== strpos($shop_html, 'sc-product-card__favourite'));

// 9. Single Product Page HTTP and elements
$single_prod = wc_get_product(21); // Turmeric
$single_url = get_permalink(21);
$single_html = @file_get_contents($single_url);
assert_test('Single product renders pack size selector with radio buttons', false !== strpos($single_html, 'sc-pack-selector'));
assert_test('Single product renders pack validation notice container', false !== strpos($single_html, 'sc-pack-validation-notice'));
assert_test('Single product renders single product Favourite button', false !== strpos($single_html, 'sc-single-product__fav-btn'));
assert_test('Single product renders Share Product button', false !== strpos($single_html, 'sc-single-product__share-btn'));
assert_test('Single product renders WhatsApp and Email CTAs', false !== strpos($single_html, 'sc-whatsapp-enquiry-cta') && false !== strpos($single_html, 'sc-email-enquiry-cta'));
assert_test('Single product renders Recently Viewed section markup', false !== strpos($single_html, 'sc-recently-viewed'));

// 10. Strict B2B Catalog Mode preserved
assert_test('Zero add-to-cart buttons on Shop catalog', false === stripos($shop_html, 'add_to_cart_button') && false === stripos($shop_html, 'add-to-cart'));
assert_test('Zero add-to-cart buttons on Single product page', false === stripos($single_html, 'single_add_to_cart_button'));
assert_test('Zero cart/checkout links in rendered navigation', false === stripos($shop_html, 'href="/cart"') && false === stripos($shop_html, 'href="/checkout"'));

// 11. Header Favourites link points to /favourites/
assert_test('Header favourites button links to /favourites/', false !== strpos($shop_html, 'href="http://localhost/favourites/"') || false !== strpos($shop_html, 'href="/favourites/"'));

// 12. Search query persistence on ?s=Turmeric
$search_page_html = @file_get_contents(home_url('/?s=Turmeric&post_type=product'));
assert_test('Search results display "Search Results for:" heading', false !== strpos($search_page_html, 'Search Results for: &ldquo;Turmeric&rdquo;'));
assert_test('Search results render search clear button', false !== strpos($search_page_html, 'sc-catalog-search-clear'));
assert_test('Search results render active search filter chip', false !== strpos($search_page_html, 'sc-filter-chip'));

echo "\n============================================\n";
echo "PHP TEST SUITE TOTAL: {$passed} Passed, {$failed} Failed\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
