<?php
/**
 * Restore clean unpopulated schema in DB (Zero-Fabricated-Content Rule)
 */
require_once dirname( __DIR__ ) . '/wp-load.php';

// Reset to clean default empty schemas
$clean_mfg = spicecraft_get_manufacturing_default_settings();
$clean_q   = spicecraft_get_quality_default_settings();

update_option( 'spicecraft_manufacturing_settings', $clean_mfg );
update_option( 'spicecraft_quality_settings', $clean_q );

echo "Clean default schemas restored for Manufacturing & Quality (Zero-Fabricated-Content Rule).\n";
