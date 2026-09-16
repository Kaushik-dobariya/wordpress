<?php
require_once __DIR__ . '/../wp-load.php';
$settings = get_option('spicecraft_homepage_settings');
echo "Heading: [" . $settings['hero']['heading'] . "]\n";
echo "Highlight: [" . $settings['hero']['highlight_text'] . "]\n";
