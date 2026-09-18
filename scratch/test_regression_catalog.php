<?php
require_once __DIR__ . '/../wp-load.php';

echo "==================================================" . PHP_EOL;
echo "REGRESSION: CATALOG MODE & GLOBAL INTEGRITY AUDIT" . PHP_EOL;
echo "==================================================" . PHP_EOL;

// 1. Check Product page has no Add to Cart
$context = stream_context_create(array(
    'http' => array('timeout' => 10, 'ignore_errors' => true)
));

$product_html = file_get_contents('http://localhost/product/organic-turmeric-powder/', false, $context);

$has_add_to_cart = strpos($product_html, 'single_add_to_cart_button') !== false;
$has_trade_enquiry = strpos($product_html, 'Trade Enquiry') !== false || strpos($product_html, 'enquiry-modal') !== false || strpos($product_html, 'sc-product-actions') !== false;

if (!$has_add_to_cart) {
    echo "✅ PASS: Add to Cart button is NOT present (Catalog mode enforced)" . PHP_EOL;
} else {
    echo "❌ FAIL: Add to Cart button was found on product page" . PHP_EOL;
}

if ($has_trade_enquiry) {
    echo "✅ PASS: Trade Enquiry CTA / action exists on product page" . PHP_EOL;
} else {
    echo "❌ FAIL: Trade Enquiry button missing from product page" . PHP_EOL;
}

// 2. Check Cart and Checkout accessibility
$cart_headers = get_headers('http://localhost/cart/');
$cart_status = $cart_headers[0] ?? '';
echo "Cart Page status: " . $cart_status . PHP_EOL;

$checkout_headers = get_headers('http://localhost/checkout/');
$checkout_status = $checkout_headers[0] ?? '';
echo "Checkout Page status: " . $checkout_status . PHP_EOL;

// 3. Check Homepage loads with HTTP 200
$home_headers = get_headers('http://localhost/');
$home_status = $home_headers[0] ?? '';
if (strpos($home_status, '200') !== false) {
    echo "✅ PASS: Homepage HTTP 200 OK" . PHP_EOL;
} else {
    echo "❌ FAIL: Homepage status: " . $home_status . PHP_EOL;
}

// 4. Check Shop loads with HTTP 200
$shop_headers = get_headers('http://localhost/shop/');
$shop_status = $shop_headers[0] ?? '';
if (strpos($shop_status, '200') !== false) {
    echo "✅ PASS: Shop archive HTTP 200 OK" . PHP_EOL;
} else {
    echo "❌ FAIL: Shop status: " . $shop_status . PHP_EOL;
}

// 5. Check Sticky Header & Scroll-top in About page
$about_html = file_get_contents('http://localhost/about/', false, $context);
if (strpos($about_html, 'id="masthead"') !== false && strpos($about_html, 'site-header') !== false) {
    echo "✅ PASS: Header markup present on About page" . PHP_EOL;
} else {
    echo "❌ FAIL: Header missing on About page" . PHP_EOL;
}

if (strpos($about_html, 'id="sc-scroll-top"') !== false) {
    echo "✅ PASS: Scroll-to-top button present on About page" . PHP_EOL;
} else {
    echo "❌ FAIL: Scroll-to-top missing on About page" . PHP_EOL;
}

