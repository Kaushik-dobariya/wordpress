<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
$s = spicecraft_get_manufacturing_settings();
$s['hero']['heading'] = 'Custom Admin Milling Benchmark';
spicecraft_update_manufacturing_settings( $s );
echo "Journey 6 update applied.\n";
