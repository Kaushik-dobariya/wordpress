<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

echo "--- ATTACHMENTS ---\n";
$attachments = get_posts( array( 'post_type' => 'attachment', 'posts_per_page' => 20 ) );
foreach ( $attachments as $a ) {
    echo "ID: " . $a->ID . " | Title: " . $a->post_title . " | URL: " . wp_get_attachment_url( $a->ID ) . "\n";
}

echo "\n--- PRODUCTS ---\n";
$products = wc_get_products( array( 'limit' => 20 ) );
foreach ( $products as $p ) {
    echo "ID: " . $p->get_id() . " | Name: " . $p->get_name() . " | Featured: " . ( $p->is_featured() ? 'YES' : 'NO' ) . "\n";
}

echo "\n--- PRODUCT CATEGORIES ---\n";
$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
foreach ( $cats as $c ) {
    echo "ID: " . $c->term_id . " | Name: " . $c->name . " | Count: " . $c->count . " | Thumb ID: " . get_term_meta( $c->term_id, 'thumbnail_id', true ) . "\n";
}

echo "\n--- POSTS ---\n";
$posts = get_posts( array( 'posts_per_page' => 10 ) );
foreach ( $posts as $po ) {
    echo "ID: " . $po->ID . " | Title: " . $po->post_title . "\n";
}

echo "\n--- CERTIFICATIONS ---\n";
$certs = get_terms( array( 'taxonomy' => 'spicecraft_certification', 'hide_empty' => false ) );
if ( ! empty( $certs ) && ! is_wp_error( $certs ) ) {
    foreach ( $certs as $ce ) {
        echo "ID: " . $ce->term_id . " | Name: " . $ce->name . " | Number: " . get_term_meta( $ce->term_id, '_sc_cert_number', true ) . "\n";
    }
} else {
    echo "None.\n";
}

echo "\n--- TESTIMONIALS ---\n";
$tsts = get_posts( array( 'post_type' => 'spicecraft_testimonial', 'posts_per_page' => 10 ) );
if ( ! empty( $tsts ) ) {
    foreach ( $tsts as $t ) {
        echo "ID: " . $t->ID . " | Title: " . $t->post_title . "\n";
    }
} else {
    echo "None.\n";
}
