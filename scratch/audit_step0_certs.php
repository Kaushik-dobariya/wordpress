<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

echo "=== 1. AUDIT TAXONOMY TERMS IN DB ===\n";
$terms = get_terms( array(
	'taxonomy'   => 'spicecraft_certification',
	'hide_empty' => false,
) );
foreach ( $terms as $t ) {
	echo "Term ID: {$t->term_id} | Name: {$t->name} | Slug: {$t->slug} | Count: {$t->count}\n";
}

echo "\n=== 2. AUDIT GLOBAL SETTINGS FOR CERT CLAIMS ===\n";
$global = get_option( 'spicecraft_global_settings', array() );
foreach ( $global as $k => $v ) {
	if ( is_string( $v ) && preg_match( '/(FSSAI|ISO|HACCP|HALAL|Organic|FDA|BRC|GMP|APEDA|Spices Board|AGMARK|Kosher)/i', $v ) ) {
		echo "Global key '{$k}': {$v}\n";
	}
}

echo "\n=== 3. AUDIT HOMEPAGE SETTINGS FOR CERTS ===\n";
$home = get_option( 'spicecraft_homepage_settings', array() );
if ( isset( $home['sections']['certifications'] ) ) {
	echo "Homepage certs section: " . json_encode( $home['sections']['certifications'] ) . "\n";
}

echo "\n=== 4. AUDIT ABOUT SETTINGS FOR CERTS ===\n";
$about = get_option( 'spicecraft_about_settings', array() );
if ( isset( $about['certifications'] ) ) {
	echo "About certs section: " . json_encode( $about['certifications'] ) . "\n";
}

echo "\n=== 5. AUDIT MANUFACTURING SETTINGS FOR CERTS ===\n";
$mfg = get_option( 'spicecraft_manufacturing_settings', array() );
if ( isset( $mfg['certifications'] ) ) {
	echo "Mfg certs section: " . json_encode( $mfg['certifications'] ) . "\n";
}

echo "\n=== 6. AUDIT QUALITY SETTINGS FOR CERTS ===\n";
$q = get_option( 'spicecraft_quality_settings', array() );
if ( isset( $q['certifications'] ) ) {
	echo "Quality certs section: " . json_encode( $q['certifications'] ) . "\n";
}

echo "\n=== 7. AUDIT PRODUCTS FOR LINKED CERTS ===\n";
$products = get_posts( array(
	'post_type'   => 'product',
	'numberposts' => -1,
	'post_status' => 'any',
) );
foreach ( $products as $p ) {
	$product_terms = wp_get_post_terms( $p->ID, 'spicecraft_certification' );
	if ( ! empty( $product_terms ) ) {
		$names = wp_list_pluck( $product_terms, 'name' );
		echo "Product ID {$p->ID} ({$p->post_title}): " . implode( ', ', $names ) . "\n";
	}
}
