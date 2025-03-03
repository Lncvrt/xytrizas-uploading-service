<?php
include '../config/config.php';
include '../incl/main.php';

$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die('Unable to access the database, please try again later');
}

if (!checkUserSession($conn)) {
    setcookie('session', '', time(), "/", "", true, true);
    header('Location: /dashboard/login.php');
    die();
}
$checkedSession = true;

$session = htmlspecialchars($_COOKIE['session']);

$stmt = $conn->prepare("SELECT uid, username, email, api_key, role, discord_id FROM users WHERE session = ?");
$stmt->bind_param("s", $session);
$stmt->execute();
$stmt->bind_result($uid, $username, $email, $api_key, $role, $discord_id);
$stmt->fetch();
$stmt->close();

checkDiscordLink($discord_id);

ob_start();
?>
<div id="container">
    <h1>Upload Tools</h1>
    <div class="upload-tools">
        <div class="tool-box">
            <a href="/dashboard/upload-tools.php?type=windows">
                <div class="tool-title">Windows</div>
                <div class="tool-icon"><i class="fab fa-windows"></i></div>
            </a>
        </div>
        <div class="tool-box">
            <a href="/dashboard/upload-tools.php?type=macos">
                <div class="tool-title">MacOS</div>
                <div class="tool-icon"><i class="fab fa-apple"></i></div>
            </a>
        </div>
        <div class="tool-box">
            <a href="/dashboard/upload-tools.php?type=ios">
                <div class="tool-title">iOS</div>
                <div class="tool-icon"><i class="fab fa-app-store-ios"></i></div>
            </a>
        </div>
        <div class="tool-box">
            <a href="/dashboard/upload-tools.php?type=linux">
                <div class="tool-title">Linux</div>
                <div class="tool-icon"><i class="fab fa-linux"></i></div>
            </a>
        </div>
        <div class="tool-box">
            <a href="/dashboard/upload-tools.php?type=android">
                <div class="tool-title">Android</div>
                <div class="tool-icon"><i class="fab fa-android"></i></div>
            </a>
        </div>
    </div>
    <button onclick="downloadConfig()" style="margin-top: 8px;">Download ShareX Config</button>
</div>
<?php
$content = ob_get_clean();
$title = 'Settings';
include 'layout.php';
?>