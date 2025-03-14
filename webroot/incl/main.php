<?php

chdir(dirname(__FILE__));

use PHPMailer\PHPMailer\PHPMailer;

function checkUserSession($conn) {
    if (isset($_COOKIE['session'])) {
        $session = $_COOKIE['session'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE session = ?");
        $stmt->bind_param("s", $session);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function formatUnitSize($unformattedsize) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
    $i = 0;
    while ($unformattedsize >= 1024 && $i < count($units) - 1) {
        $unformattedsize /= 1024;
        $i++;
    }

    $size = round($unformattedsize, 1) . ' ' . $units[$i];
    return $size;
}
function sendEmail($email, $password, $sender, $target, $target_user, $subject, $body, $ishtml, $mailhost) {
    require '../../libs/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require '../../libs/vendor/vendor/phpmailer/phpmailer/src/Exception.php';
    require '../../libs/vendor/vendor/phpmailer/phpmailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();

        $mail->Host = $mailhost;

        $mail->SMTPAuth = true;

        $mail->Username = $email;

        $mail->Password = $password;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        $mail->setFrom($email, $sender);
        $mail->addAddress($target, $target_user);

        $mail->Subject = $subject;

        $mail->isHTML($ishtml);
        $mail->Body = $body;

        $mail->send();

        return true;
    } catch (Exception $e) {
        return false;
    }
}
function checkDiscordLink($id) {
    include '../config/config.php';
    if ($id == null || $id == "") {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xytriza's Uploading Service - Discord link required</title>
    <link rel="icon" href="/assets/logo.png" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/dashboard/assets/main.css?v=<?php echo filemtime('/var/www/xus/webroot/dashboard/assets/main.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/dashboard/assets/main.js?v=<?php echo filemtime('/var/www/xus/webroot/dashboard/assets/main.js'); ?>"></script>
</head>
<body>
    <div id="notification-container"></div>

    <div id="container">
        <div id="discord-link">
            <h1>Discord link required</h1>
            <p>You need to link your Discord account to use this service.</p>
            <button onclick="setCookieAndRedirect('<?= $discordClientId ?>', '<?= urlencode($discordRedirectUri) ?>')">Link Discord</button>
        </div>
    </div>
</body>
</html>
<?php
        exit();
    }
}