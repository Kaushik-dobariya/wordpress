<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
$terms = get_terms( array(
	'taxonomy'   => 'spicecraft_certification',
	'hide_empty' => false,
) );
foreach ( $terms as $t ) {
	echo "Term ID: {$t->term_id} | Name: {$t->name} | Slug: {$t->slug}\n";
}
