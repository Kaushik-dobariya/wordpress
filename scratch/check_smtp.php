<?php
echo "Checking MX records for cubeontechs.com...\n";
$mx_records = [];
if (getmxrr('cubeontechs.com', $mx_records)) {
    echo "MX records for cubeontechs.com:\n";
    print_r($mx_records);
} else {
    echo "Could not find MX records for cubeontechs.com\n";
}

$host = !empty($mx_records) ? $mx_records[0] : 'cubeontechs.com';
echo "Testing connection to {$host}:25...\n";
$fp = @fsockopen($host, 25, $errno, $errstr, 5);
if ($fp) {
    echo "Connection to {$host}:25 SUCCESS\n";
    fclose($fp);
} else {
    echo "Connection to {$host}:25 FAILED: $errno - $errstr\n";
}
