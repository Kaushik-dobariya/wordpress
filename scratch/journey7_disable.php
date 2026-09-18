<?php
require_once dirname( __DIR__ ) . '/wp-load.php';
$q = spicecraft_get_quality_settings();
$q['sections_enabled']['process'] = 0;
spicecraft_update_quality_settings( $q );
echo "Journey 7 disable applied.\n";
