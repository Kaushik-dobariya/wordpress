<?php
require_once __DIR__ . '/../wp-load.php';

echo "=== Audit Existing Enquiry & Pages State ===\n";

$pages = get_pages();
foreach ( $pages as $p ) {
	echo "Page: {$p->ID} | {$p->post_name} | {$p->post_title} | template: " . get_page_template_slug( $p->ID ) . "\n";
}

echo "\n--- Global Settings Check ---\n";
$global_settings = get_option( 'spicecraft_global_settings', array() );
echo "General Email: " . ( $global_settings['email_general'] ?? 'none' ) . "\n";
echo "Sales Email: " . ( $global_settings['email_sales'] ?? 'none' ) . "\n";
echo "Export Email: " . ( $global_settings['email_export'] ?? 'none' ) . "\n";
echo "WhatsApp Number: " . ( $global_settings['whatsapp_number'] ?? 'none' ) . "\n";
echo "Header CTA Text: " . ( $global_settings['header_cta_text'] ?? 'none' ) . "\n";
echo "Header CTA URL: " . ( $global_settings['header_cta_url'] ?? 'none' ) . "\n";

echo "\n--- Registered Post Types ---\n";
$pts = get_post_types( array(), 'objects' );
foreach ( $pts as $k => $pt ) {
	if ( false !== strpos( $k, 'spicecraft' ) || false !== strpos( $k, 'sc_' ) ) {
		echo "CPT: {$k} | public: " . ( $pt->public ? 'yes' : 'no' ) . " | label: {$pt->label}\n";
	}
}
