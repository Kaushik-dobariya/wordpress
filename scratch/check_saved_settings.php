<?php
require_once __DIR__ . '/../wp-load.php';
$opt = get_option('spicecraft_careers_settings', array());
$display = $opt;
if (isset($display['smtp_pass'])) {
    $display['smtp_pass_len'] = strlen($display['smtp_pass']);
    $display['smtp_pass'] = '***REDACTED***';
}
print_r($display);
