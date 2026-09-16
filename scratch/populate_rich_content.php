<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$settings = get_option( 'spicecraft_homepage_settings', array() );

// Hero
$settings['hero']['eyebrow'] = 'Artisanal Spice Manufacturer';
$settings['hero']['heading'] = 'Master Crafted Spices for Culinary Excellence';
$settings['hero']['highlight_text'] = 'Master Crafted';
$settings['hero']['description'] = 'Origin-verified Indian whole spices and cold-milled powders for commercial kitchens, gourmet retailers, and export markets.';
$settings['hero']['badge_text'] = '100% Origin Guaranteed';
$settings['hero']['primary_cta_label'] = 'Explore Spice Catalog';
$settings['hero']['primary_cta_url'] = wc_get_page_permalink( 'shop' );
$settings['hero']['secondary_cta_label'] = 'Institutional Enquiries';
$settings['hero']['secondary_cta_url'] = home_url( '/#business-enquiry' );
$settings['hero']['image_alt'] = 'Master Crafted Indian Spices';

// Categories
$settings['categories']['eyebrow'] = 'Curated Portfolio';
$settings['categories']['heading'] = 'Our Spice Classifications';
$settings['categories']['description'] = 'From fiery Kashmiri red chillies to handpicked cardamom and fragrant whole seeds.';
$settings['categories']['display_mode'] = 'all';
$settings['categories']['limit'] = 4;

// Featured Products
$settings['featured_products']['eyebrow'] = 'Master Selection';
$settings['featured_products']['heading'] = 'Signature Single-Origin Spices';
$settings['featured_products']['description'] = 'Harvested at peak potency, laboratory tested for essential oil richness and natural color.';
$settings['featured_products']['source'] = 'latest';
$settings['featured_products']['limit'] = 4;
$settings['featured_products']['cta_label'] = 'Explore Full Range';
$settings['featured_products']['cta_url'] = wc_get_page_permalink( 'shop' );

// Brand Story
$settings['brand_story']['eyebrow'] = 'Our Heritage';
$settings['brand_story']['heading'] = 'Rooted in Purity, Crafted by Generations';
$settings['brand_story']['description'] = "Founded on the fertile spice tracts of India, SpiceCraft bridges centuries-old spice grading traditions with modern hygienic processing.\n\nWe partner directly with multigenerational spice growers across Kerala, Gujarat, and Kashmir to guarantee unadulterated crops, locking in vibrant natural aromas and rich volatile oils.";
$settings['brand_story']['stat_value'] = '35+';
$settings['brand_story']['stat_label'] = 'Years of Heritage Sourcing';
$settings['brand_story']['cta_label'] = 'Discover Our Story';
$settings['brand_story']['cta_url'] = home_url( '/about/' );
$settings['brand_story']['primary_image_id'] = 33; // Sourcing image

// Why Choose Us
$settings['why_choose_us']['eyebrow'] = 'The SpiceCraft Standard';
$settings['why_choose_us']['heading'] = 'Uncompromising Quality at Every Stage';
$settings['why_choose_us']['description'] = 'Why leading chefs, institutional buyers, and food manufacturers trust our spice craft.';
$settings['why_choose_us']['items'] = array(
    array(
        'icon' => 'leaf',
        'title' => 'Single-Origin Sourced',
        'description' => 'Direct plantation partnerships ensuring unadulterated crop integrity and fair farmer pricing.',
        'order' => 10,
    ),
    array(
        'icon' => 'shield',
        'title' => 'Cryogenic Cold Milled',
        'description' => 'Precision pulverization below 40°C to lock in natural volatile oils, intense flavor, and aroma.',
        'order' => 20,
    ),
    array(
        'icon' => 'awards',
        'title' => 'Export Grade Purity',
        'description' => 'Zero artificial food colors, added starches, preservatives, or chemical fillers — ever.',
        'order' => 30,
    ),
    array(
        'icon' => 'clock',
        'title' => 'Complete Batch Traceability',
        'description' => 'Every production lot is lab tested for moisture, curcumin content, and microbiological purity.',
        'order' => 40,
    ),
);

// Quality & Sourcing
$settings['quality_sourcing']['eyebrow'] = 'Uncompromising Integrity';
$settings['quality_sourcing']['heading'] = 'Farm to Commercial Kitchen Traceability';
$settings['quality_sourcing']['description'] = 'Our quality assurance begins at the soil level with contract farmers and extends through hygienic cleanroom processing and airtight barrier packaging.';
$settings['quality_sourcing']['main_image_id'] = 33;
$settings['quality_sourcing']['points'] = array(
    array(
        'title' => 'Direct Plantation Grading',
        'text' => 'Meticulous manual visual inspection and moisture analysis at origin collection centers.',
    ),
    array(
        'title' => 'Multi-Stage Magnetic Purification',
        'text' => 'Automated destoning, gravity separation, and high-intensity rare-earth magnetic filtration.',
    ),
    array(
        'title' => 'Volatile Oil Laboratory Testing',
        'text' => 'Gas chromatography analysis confirming authentic essential oil levels prior to dispatch.',
    ),
);
$settings['quality_sourcing']['cta_label'] = 'Our Quality Standards';
$settings['quality_sourcing']['cta_url'] = home_url( '/quality/' );

// Manufacturing
$settings['manufacturing']['eyebrow'] = 'Infrastructure';
$settings['manufacturing']['heading'] = 'Pharmaceutical-Grade Spice Processing';
$settings['manufacturing']['description'] = 'Engineered for international food export specifications with ISO and HACCP certified cleanroom operations, automated bulk bagging, and nitrogen-flushed packaging lines.';
$settings['manufacturing']['main_image_id'] = 32;
$settings['manufacturing']['stats'] = array(
    array(
        'value' => '25,000 MT',
        'label' => 'Annual Capacity',
    ),
    array(
        'value' => 'SS 316',
        'label' => 'Food Grade Contact',
    ),
    array(
        'value' => '< 40°C',
        'label' => 'Cryo-Mill Temp',
    ),
    array(
        'value' => '100%',
        'label' => 'Batch COA Tested',
    ),
);
$settings['manufacturing']['cta_label'] = 'Explore Infrastructure';
$settings['manufacturing']['cta_url'] = home_url( '/infrastructure/' );

// Certifications
$settings['certifications']['eyebrow'] = 'Compliance & Trust';
$settings['certifications']['heading'] = 'Statutory & International Accreditations';
$settings['certifications']['description'] = 'Independently audited and certified by leading global food safety benchmarks and statutory boards.';
$settings['certifications']['limit'] = 6;

// Product Discovery
$settings['product_discovery']['eyebrow'] = 'Explore the Collection';
$settings['product_discovery']['heading'] = 'Discover the Spectrum of Indian Spices';
$settings['product_discovery']['description'] = 'From fiery Kashmiri chillies to fragrant Malabar cardamom, navigate our culinary classifications.';
$settings['product_discovery']['cta_label'] = 'Explore All Spice Ranges';
$settings['product_discovery']['cta_url'] = wc_get_page_permalink( 'shop' );

// B2B CTA
$settings['b2b_cta']['eyebrow'] = 'Institutional & Export Supply';
$settings['b2b_cta']['heading'] = 'Wholesale, Bulk Supply & Export Trade Desk';
$settings['b2b_cta']['description'] = 'Serving commercial food chains, gourmet retail distributors, and overseas importers with custom granulation specs, certificate of analysis, and private-label packaging.';
$settings['b2b_cta']['primary_cta_label'] = 'Request B2B Trade Catalog';
$settings['b2b_cta']['primary_cta_url'] = home_url( '/#business-enquiry' );
$settings['b2b_cta']['secondary_cta_label'] = 'Institutional Specifications';
$settings['b2b_cta']['secondary_cta_url'] = home_url( '/infrastructure/' );
$settings['b2b_cta']['enable_whatsapp'] = 1;

// Final CTA
$settings['final_cta']['heading'] = 'Bring Authentic Spice Purity to Your Portfolio';
$settings['final_cta']['description'] = 'Connect directly with our culinary specialists or trade desk for custom sampling, harvest reports, and institutional price quotations.';
$settings['final_cta']['primary_cta_label'] = 'Send Direct Enquiry';
$settings['final_cta']['primary_cta_url'] = home_url( '/#contact' );
$settings['final_cta']['enable_whatsapp'] = 1;
$settings['final_cta']['enable_email'] = 1;

update_option( 'spicecraft_homepage_settings', $settings );
echo "Rich content populated in spicecraft_homepage_settings successfully!\n";
