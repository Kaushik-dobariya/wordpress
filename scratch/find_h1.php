<?php
$html = file_get_contents('http://localhost/');
preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches);
foreach ($matches[0] as $i => $h1) {
    echo "H1 #$i:\n" . $h1 . "\n\n";
}
