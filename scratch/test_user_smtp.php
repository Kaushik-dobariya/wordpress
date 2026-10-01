<?php
require_once __DIR__ . '/../wp-load.php';

$opt = get_option('spicecraft_careers_settings', array());
$password = $opt['smtp_pass'] ?? '';

echo "Testing Outgoing Server: smtp.cubeontechs.com\n";
echo "Username: career@cubeontechs.com\n";
echo "Password length: " . strlen($password) . "\n\n";

// Test 1: Port 465 with SSL
echo "--- Testing Port 465 (SSL) ---\n";
test_smtp('smtp.cubeontechs.com', 465, 'ssl', 'career@cubeontechs.com', $password);

// Test 2: Port 587 with TLS
echo "\n--- Testing Port 587 (TLS) ---\n";
test_smtp('smtp.cubeontechs.com', 587, 'tls', 'career@cubeontechs.com', $password);

function test_smtp($host, $port, $encryption, $user, $pass) {
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->Port       = $port;
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = $encryption;
        $mail->Timeout    = 10;
        $mail->SMTPDebug  = 2; // Output debug info

        // Set from and to
        $mail->setFrom($user, 'SpiceCraft Recruitment');
        $mail->addAddress($user);
        $mail->Subject = 'SpiceCraft SMTP Test (' . $encryption . ':' . $port . ')';
        $mail->Body    = 'Test body';

        ob_start();
        $sent = $mail->send();
        $debug = ob_get_clean();
        echo "Debug output:\n" . $debug . "\n";
        echo "Result: SUCCESS!\n";
    } catch (\Exception $e) {
        if (ob_get_level() > 0) {
            $debug = ob_get_clean();
            echo "Debug output:\n" . $debug . "\n";
        }
        echo "Result: FAILED -> " . $e->getMessage() . "\n";
    }
}
