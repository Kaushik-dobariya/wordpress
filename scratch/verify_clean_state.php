<?php
require_once dirname( __DIR__ ) . '/wp-load.php';

$mfg = get_option( 'spicecraft_manufacturing_settings' );
$q   = get_option( 'spicecraft_quality_settings' );

echo "Mfg hero heading: " . json_encode( $mfg['hero']['heading'] ?? null ) . "\n";
echo "Mfg active sections: " . json_encode( spicecraft_get_manufacturing_active_sections() ) . "\n";
echo "Quality hero heading: " . json_encode( $q['hero']['heading'] ?? null ) . "\n";
echo "Quality active sections: " . json_encode( spicecraft_get_quality_active_sections() ) . "\n";
