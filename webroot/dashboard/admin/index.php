<?php
include '../../config/config.php';
include '../../incl/main.php';

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

$stmt = $conn->prepare("SELECT uid, username, email, api_key, role, discord_id FROM users WHERE session = ?");
$stmt->bind_param("s", $session);
$stmt->execute();
$stmt->bind_result($uid, $username, $email, $api_key, $role, $discord_id);
$stmt->fetch();
$stmt->close();

if ($role !== 1 && $role !== 2) {
    header('Location: /dashboard/');
    die();
}

checkDiscordLink($discord_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['news'])) {
        $news = base64_encode(htmlspecialchars($_POST['news']));
        $sql = "UPDATE settings SET value = ? WHERE id = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $news);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['motd'])) {
        $motd = base64_encode(htmlspecialchars($_POST['motd']));
        $sql = "UPDATE settings SET value = ? WHERE id = 2";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $motd);
        $stmt->execute();
        $stmt->close();
    }
}

$sql = "SELECT value FROM settings WHERE id = 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$news = base64_decode($row['value']);

$sql = "SELECT value FROM settings WHERE id = 2";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$motd = base64_decode($row['value']);

$totalSize = 0;
$uploadCount = 0;

$sql = "SELECT size FROM uploads";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $totalSize += $row['size'];
    $uploadCount++;
}

$sql = "SELECT COUNT(*) FROM users";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$users = $row['COUNT(*)'];

$sql = "SELECT COUNT(*) FROM logins";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$logins = $row['COUNT(*)'];

$sql = "SELECT COUNT(*) FROM uploads";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$uploads = $row['COUNT(*)'];

$size = formatUnitSize($totalSize);

ob_start();
?>
<div id="container">
    <div class="info-group">
        <div class="info-box">
            <p class="big"><strong>Total Storage Used</strong></p>
            <p><?php echo $size; ?></p>
        </div>
        <div class="info-box">
            <p class="big"><strong>Total Uploads</strong></p>
            <p><?php echo $uploads; ?></p>
        </div>
        <div class="info-box">
            <p class="big"><strong>Total Users</strong></p>
            <p><?php echo $users; ?></p>
        </div>
        <div class="info-box">
            <p class="big"><strong>Total Logins</strong></p>
            <p><?php echo $logins; ?></p>
        </div>
    </div>
    <p>Dynamic placeholders like %discord% (Discord link), %username% (user's name), %uploads% (user's upload count),
        %role% (user's role), %uid% (user ID), %storage% (user's storage usage), and date details (%year%, %month%,
        %monthformat%, %day%, %dayformat%, %date%) are automatically replaced with specific user or system information
        for personalized messages.</p>
    <form method="POST">
        <p>News</p>
        <textarea name="news"><?php echo $news; ?></textarea>
        <p>MOTD</p>
        <textarea name="motd"><?php echo $motd; ?></textarea>
        <button type="submit">Update</button>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = 'Admin Settings';
include __DIR__ . '/../layout.php'; //this is the only way it wanted to work?
?>