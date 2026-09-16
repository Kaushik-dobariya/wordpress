<?php
require_once __DIR__ . '/../wp-load.php';

echo "Active sections:\n";
$active = spicecraft_get_homepage_active_sections();
print_r($active);

echo "\nTesting each section rendering:\n";
foreach ($active as $sec) {
    $map = [
        'hero'              => 'hero',
        'categories'        => 'categories',
        'featured_products' => 'featured-products',
        'brand_story'       => 'brand-story',
        'why_choose_us'     => 'why-choose-us',
        'quality_sourcing'  => 'quality-sourcing',
        'manufacturing'     => 'manufacturing',
        'certifications'    => 'certifications',
        'product_discovery' => 'product-discovery',
        'recipes'           => 'recipes',
        'testimonials'      => 'testimonials',
        'blog'              => 'blog',
        'b2b_cta'           => 'b2b-cta',
        'final_cta'         => 'final-cta',
    ];
    $slug = isset($map[$sec]) ? $map[$sec] : $sec;
    ob_start();
    get_template_part('template-parts/home/' . $slug);
    $output = ob_get_clean();
    echo sprintf("%-20s (slug: %-20s): %d bytes\n", $sec, $slug, strlen($output));
    if (strlen($output) < 200) {
        echo "   Snippet: " . trim($output) . "\n";
    }
}
