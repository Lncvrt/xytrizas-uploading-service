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

$sql = "SELECT id, delete_key, size, original_name, filetype, password FROM uploads WHERE uid = '$uid' ORDER BY uploaded DESC";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

ob_start();
?>
<div id="main-content">
    <div id="gallery">
        <?php
        if ($result->num_rows > 0) {
            do {
                $fileId = basename($row['id']);
                $filename = basename(base64_decode($row['original_name']));
                $filePassword = $row['password'];
                $fileType = $row['filetype'];
                $fileSize = $row['size'];
                $deletionKey = $row['delete_key'];

                echo '<div class="gallery-item" data-id="' . $fileId . '">';
                if (in_array($fileType, ["image/png", "image/jpeg", "image/gif", "image/webp", "image/svg+xml"])) {
                    echo '<img src="https://files.upload.xytriza.com/' . $fileId . '" alt="' . $filename . '"></a>';
                } elseif (in_array($fileType, ["video/mp4", "video/webm", "video/ogg"])) {
                    echo '<video width="400px" height="200px" controls><source src="' . htmlspecialchars('https://files.upload.xytriza.com/' . $fileId, ENT_QUOTES, 'UTF-8') . '" type="' . htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8') . '">Your browser does not support the video tag.</video>';
                } elseif (in_array($fileType, ["audio/mpeg", "audio/wav", "audio/ogg"])) {
                    echo '<audio controls><source src="' . htmlspecialchars('https://files.upload.xytriza.com/' . $fileId, ENT_QUOTES, 'UTF-8') . '" type="' . htmlspecialchars($fileType, ENT_QUOTES, 'UTF-8') . '">Your browser does not support the audio element.</audio>';
                } elseif ($fileType == "text/plain") {
                    $fileContent = htmlspecialchars(file_get_contents('https://files.upload.xytriza.com/' . $fileId), ENT_QUOTES, 'UTF-8');
                    if (strlen($fileContent) > 10000) {
                        $fileContent = substr($fileContent, 0, 10000) . "... Preview too long. Download the file to view the full content.";
                    }
                    echo '<textarea readonly>' . $fileContent . '</textarea>';
                } else {
                    echo '<p>Unable to display file "' . $fileType . '". Suggest support for this type in our Discord.</p>';
                }
                echo '<div class="file-info">';
                echo '<a href="/files/' . $fileId . '" style="text-decoration: underline;"><p><strong>' . $filename . '</strong></p></a>';
                echo '<p>' . formatUnitSize($fileSize) . '</p>';
                echo '<div class="icon-container">';

                echo "<div class='fas fa-trash gallery-btn' onclick='deleteFile(\"{$deletionKey}\", \"{$fileId}\")'></div>";
                echo "<div class='fas fa-clipboard gallery-btn' onclick='copyToClipboard(\"/files/{$fileId}\", \"File URL copied to Clipboard\", \"0\")'></div>";
                echo "<div class='fas fa-download gallery-btn' onclick='downloadFile(\"{$fileId}\", \"{$filename}\", \"{$fileType}\")'></div>";
                echo "<div class='fas fa-cog gallery-btn' onclick='openFileSettings(\"{$fileId}\", \"{$filename}\", \"{$filePassword}\")'></div>";

                echo '</div>';
                echo '</div>';
                echo '</div>';
                $row = $result->fetch_assoc();
            } while ($row);
        } else {
            echo '<h1 style="text-align: center; margin-top: 10%;">No files uploaded!</h1>';
        }
        ?>
    </div>
</div>

<h1 id="loading">Loading files...</h1>
<div id="file-settings"
    style="position: fixed; z-index: 99999; display: none; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div id="file-settings-container">
        <div id="file-settings-content">
            <h1 style="text-align: center; margin-bottom: 5px;">File Settings</h1>
            <div id="file-settings-close" onclick="closeFileSettings()"><i class="fas fa-times"></i></div>
            <div id="file-settings-form">
                <input type="text" id="file-settings-password" placeholder="File Password">
                <input type="text" id="file-settings-filename" placeholder="Filename">
                <input type="hidden" id="file-settings-id" placeholder="File ID">
                <!--<p style="text-align: center">Do not use a personal password.</p>-->
                <button style="margin: auto; display:block;" onclick="saveFileSettings()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function () {
        document.getElementById("loading").style.display = "none";
        document.getElementById("gallery").style.display = "flex";
    };
</script>
<?php
$content = ob_get_clean();
$title = 'Gallery';
include 'layout.php';
?>