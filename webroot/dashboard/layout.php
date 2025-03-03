<?php
if (!isset($dbservername)) include '../config/config.php';
if (!function_exists("checkUserSession")) include '../incl/main.php';

if (!isset($conn)) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die('Unable to access the database, please try again later');
    }
}

if (!isset($checkedSession)) {
    if (!checkUserSession($conn)) {
        setcookie('session', '', time(), "/", "", true, true);
        header('Location: /dashboard/login.php');
        die();
    }
}

if (!isset($session)) $session = htmlspecialchars($_COOKIE['session']);

if (isset($role)) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE session = ?");
    $stmt->bind_param("s", $session);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Xytriza's Uploading Service - <?= $title ?></title>
        <link rel="icon" href="/assets/logo.png" type="image/png">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <link rel="stylesheet" href="/dashboard/assets/main.css?v=<?php echo filemtime(__DIR__ . '/assets/main.css'); ?>">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="/dashboard/assets/main.js?v=<?php echo filemtime(__DIR__ . '/assets/main.js'); ?>"></script>
    </head>
    <body>
        <div id="sidebar">
            <a href="/" class="logo"><img class="sidebar-item" src="/assets/logo.png" alt="Xytriza's Uploading Service"
                    height="40vw" width="40vw"></a>
            <a href="/dashboard/"><i class="fas fa-home sidebar-item"></i></a>
            <a href="/dashboard/gallery.php"><i class="fas fa-file-alt sidebar-item" style="margin-left: 20%;"></i></a>
            <a href="/dashboard/upload.php"><i class="fas fa-upload sidebar-item"></i></a>
            <a href="/dashboard/settings.php"><i class="fas fa-cog sidebar-item"></i></a>
            <a href="/dashboard/account.php" style="margin-top: auto;"><i class="fas fa-user-cog sidebar-item"></i></a>
            <?php
            if ($role === 1 || $role === 2) {
                echo '<a href="/dashboard/admin" class="sidebar-item"><i class="fas fa-user-shield"></i></a>';
            }
            ?>
            <i onclick="logout()" class="fas fa-sign-out-alt sidebar-item"></i>
        </div>
        <div id="notification-container"></div>
        <?= $content ?>
    </body>
</html>