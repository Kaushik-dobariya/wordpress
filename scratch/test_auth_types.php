<?php
require_once __DIR__ . '/../wp-load.php';

$opt = get_option('spicecraft_careers_settings', array());
$password = $opt['smtp_pass'] ?? '';

echo "Testing AuthType LOGIN and PLAIN on smtp.cubeontechs.com:465 (SSL)\n";

test_auth_type('LOGIN', $password);
test_auth_type('PLAIN', $password);

function test_auth_type($type, $pass) {
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
    require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
    require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.cubeontechs.com';
        $mail->Port       = 465;
        $mail->SMTPAuth   = true;
        $mail->AuthType   = $type; // explicitly set LOGIN or PLAIN
        $mail->Username   = 'career@cubeontechs.com';
        $mail->Password   = $pass;
        $mail->SMTPSecure = 'ssl';
        $mail->Timeout    = 10;
        $mail->SMTPDebug  = 2;

        $mail->setFrom('career@cubeontechs.com', 'SpiceCraft Recruitment');
        $mail->addAddress('career@cubeontechs.com');
        $mail->Subject = 'Test AuthType ' . $type;
        $mail->Body    = 'Test body';

        ob_start();
        $sent = $mail->send();
        $debug = ob_get_clean();
        echo "=== Result for $type: SUCCESS! ===\n";
        echo $debug . "\n";
    } catch (\Exception $e) {
        if (ob_get_level() > 0) {
            $debug = ob_get_clean();
        }
        echo "=== Result for $type: FAILED -> " . $e->getMessage() . " ===\n";
        if (!empty($debug)) {
            echo $debug . "\n";
        }
    }
}
