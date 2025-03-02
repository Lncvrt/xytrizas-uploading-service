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
<div id="upload">
    <div id="dropZone">
        <p>Drag and drop a file here or click to upload</p>
    </div>
    <input type="file" id="fileInput" style="display: none;" />
    <p>Or upload a file from a URL</p>
    <div id="urlUpload">
        <input type="text" id="urlInput" placeholder="Enter Direct File URL" />
        <input type="text" id="fileName" placeholder="File URL Filename" />
        <button onclick="handleUrlUpload()" style="display: block; margin-left: auto; margin-right: auto;">Upload</button>
    </div>
    <div id="progressText" class="progressText" style="display: none; font-weight: bold;">0%</div>
</div>
<?php
$content = ob_get_clean();
$title = 'Upload a file';
include 'layout.php';
?>