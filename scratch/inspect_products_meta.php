<?php
require_once __DIR__ . '/../wp-load.php';

$prods = get_posts([
    'post_type' => 'product',
    'posts_per_page' => -1,
    'fields' => 'ids'
]);

echo "Total products: " . count($prods) . "\n";
foreach ($prods as $pid) {
    $wc_prod = wc_get_product($pid);
    $title = get_the_title($pid);
    $form = get_post_meta($pid, '_sc_form', true);
    $packs = function_exists('spicecraft_get_product_pack_sizes') ? spicecraft_get_product_pack_sizes($wc_prod) : [];
    $cats = wp_get_post_terms($pid, 'product_cat', ['fields' => 'names']);
    $tags = wp_get_post_terms($pid, 'product_tag', ['fields' => 'names']);
    $rating = $wc_prod->get_average_rating();
    $rating_count = $wc_prod->get_rating_count();
    echo "Product #{$pid}: {$title}\n";
    echo "  Form: " . ($form ?: 'NONE') . "\n";
    echo "  Packs: " . implode(', ', $packs) . "\n";
    echo "  Cats: " . implode(', ', $cats) . "\n";
    echo "  Tags: " . implode(', ', $tags) . "\n";
    echo "  Rating: {$rating} ({$rating_count})\n";
}
