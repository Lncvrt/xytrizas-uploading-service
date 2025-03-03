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

$stmt = $conn->prepare("SELECT api_key, role, discord_id FROM users WHERE session = ?");
$stmt->bind_param("s", $session);
$stmt->execute();
$stmt->bind_result($api_key, $role, $discord_id);
$stmt->fetch();
$stmt->close();

checkDiscordLink($discord_id);

ob_start();
?>
<div id="container">
<h1>Upload Tools</h1>
<?php
if (isset($_GET['type'])) {
    if ($_GET['type'] === 'windows') {
        echo '<h3>Windows - Upload Configuration</h3>
        <p>Download ShareX <a href="https://getsharex.com/" style="text-decoration: underline;" target="_blank">here</a></p>
        <p>Download the ShareX upload config <a href="javascript:downloadConfig()" style="text-decoration: underline;">here</a></p>
        <p>Run the ShareX upload config and click "Yes" when prompted</p>';
    } elseif ($_GET['type'] === 'ios') {
        echo '<h3>iOS - Upload Configuration</h3>
        <p>Download the Shortcuts app <a href="https://apps.apple.com/us/app/shortcuts/id915249334" style="text-decoration: underline;" target="_blank">here</a></p>
        <p>Download the iOS shortcut <a href="https://www.icloud.com/shortcuts/335833f37e244a54914fbc5c65dcd6f4" style="text-decoration: underline;">here</a></p>
        <p>Copy your api key from <a href="/dashboard/settings.php" style="text-decoration: underline;">your account settings</a></p>
        <p>Click "Set Up Shortcut"</p>
        <p>Paste your API Key then click "Add Shortcut"
        <p>Optionally add the shortcut to a wiget on your home screen to easily upload files and run the shortcut</p>';
    } else {
        echo '<h3>This platform will be supported soon!</h3>';
    }
} else {
    header('Location: /dashboard/settings.php');
}
echo '<button onclick="window.location.href=\'/dashboard/settings.php\'">Back</button>';
echo '</div>';

$content = ob_get_clean();
$title = 'Upload Tools';
include 'layout.php';
?>