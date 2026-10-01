<?php
require_once __DIR__ . '/../wp-load.php';

$archive_html = file_get_contents( home_url( '/recipes/' ) );
echo "LENGTH: " . strlen( $archive_html ) . "\n";
echo "CONTAINS sc-recipe-empty-state: " . ( strpos( $archive_html, 'sc-recipe-empty-state' ) !== false ? 'YES' : 'NO' ) . "\n";
echo "CONTAINS No Recipes Found: " . ( strpos( $archive_html, 'No Recipes Found' ) !== false ? 'YES' : 'NO' ) . "\n";
echo "CONTAINS sc-recipe-hero: " . ( strpos( $archive_html, 'sc-recipe-hero' ) !== false ? 'YES' : 'NO' ) . "\n";

// Let's find where empty state is or what is rendered in the body
if ( preg_match( '/<div class="sc-recipe-results-section"[^>]*>(.*?)<\/div>/is', $archive_html, $m ) ) {
    echo "RESULTS SECTION:\n" . substr( $m[1], 0, 500 ) . "\n";
} else {
    echo "NO RESULTS SECTION FOUND!\n";
    // Check if 404 or something
    if ( preg_match( '/<title>(.*?)<\/title>/is', $archive_html, $tm ) ) {
        echo "PAGE TITLE: " . $tm[1] . "\n";
    }
}
