<?php
echo "Checking smtp.stackmail.com on port 587 (STARTTLS) and 465 (SSL)...\n";

$fp587 = @fsockopen('smtp.stackmail.com', 587, $errno, $errstr, 5);
echo "Port 587: " . ($fp587 ? "CONNECTED" : "FAILED ($errstr)") . "\n";
if ($fp587) fclose($fp587);

$fp465 = @fsockopen('ssl://smtp.stackmail.com', 465, $errno, $errstr, 5);
echo "Port 465: " . ($fp465 ? "CONNECTED" : "FAILED ($errstr)") . "\n";
if ($fp465) fclose($fp465);

$fpMail = @fsockopen('mail.cubeontechs.com', 587, $errno, $errstr, 5);
echo "mail.cubeontechs.com:587: " . ($fpMail ? "CONNECTED" : "FAILED ($errstr)") . "\n";
if ($fpMail) fclose($fpMail);
