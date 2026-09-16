<?php
require_once __DIR__ . '/../wp-load.php';

echo "--- CATEGORIES ---\n";
$cats = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
]);
foreach ($cats as $c) {
    echo $c->term_id . ': ' . $c->name . ' (' . $c->slug . ') - parent: ' . $c->parent . ' - count: ' . $c->count . "\n";
}

echo "\n--- ATTRIBUTES ---\n";
$taxonomies = wc_get_attribute_taxonomies();
foreach ($taxonomies as $tax) {
    echo $tax->attribute_id . ': ' . $tax->attribute_name . ' (' . $tax->attribute_label . ') - type: ' . $tax->attribute_type . "\n";
    $tax_name = wc_attribute_taxonomy_name($tax->attribute_name);
    $terms = get_terms([
        'taxonomy' => $tax_name,
        'hide_empty' => false,
    ]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $t) {
            echo '   - ' . $t->name . ' (' . $t->slug . ') count: ' . $t->count . "\n";
        }
    }
}

echo "\n--- PRODUCT TAGS ---\n";
$tags = get_terms([
    'taxonomy' => 'product_tag',
    'hide_empty' => false,
]);
foreach ($tags as $tg) {
    echo $tg->term_id . ': ' . $tg->name . ' (' . $tg->slug . ') count: ' . $tg->count . "\n";
}

echo "\n--- PRODUCTS SAMPLE ---\n";
$prods = wc_get_products(['limit' => 10]);
foreach ($prods as $p) {
    echo $p->get_id() . ': ' . $p->get_name() . ' | SKU: ' . $p->get_sku() . ' | Rating: ' . $p->get_average_rating() . ' (' . $p->get_rating_count() . ")\n";
    $attrs = $p->get_attributes();
    foreach ($attrs as $aname => $aval) {
        if (is_object($aval)) {
            $opts = $aval->get_options();
            echo '   attr: ' . $aname . ' (is_taxonomy: ' . ($aval->is_taxonomy() ? 'yes' : 'no') . ') => ' . json_encode($opts) . "\n";
        } else {
            echo '   attr: ' . $aname . ' => ' . json_encode($aval) . "\n";
        }
        echo '   permalink: ' . get_permalink($p->get_id()) . "\n";
    }
}
