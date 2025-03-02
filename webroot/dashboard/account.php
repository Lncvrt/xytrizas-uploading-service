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

$stmt = $conn->prepare("SELECT uid, username, email, api_key, discord_id, role, discord_avatar FROM users WHERE session = ?");
$stmt->bind_param("s", $session);
$stmt->execute();
$stmt->bind_result($uid, $username, $email, $api_key, $discord_id, $role, $avatar);
$stmt->fetch();
$stmt->close();

checkDiscordLink($discord_id);

$role_format = $role == 1 ? "Owner" : ($role == 2 ? "Admin" : ($role == 0 ? "User" : "Unknown"));

ob_start();
?>
<div id="container" style="max-width: 48%;">
    <div id="account-information" class="section">
        <h2>Account Information</h2>
        <p id="username"><strong>Username:</strong> <?php echo $username; ?></p>
        <p><strong>UID:</strong> <?php echo $uid; ?></p>
        <p><strong>Discord ID:</strong> <?php echo $discord_id; ?></p>
        <p><strong>Email:</strong> <em class="blur-on-hover"><?php echo $email; ?></em></p>
        <p><strong>Role:</strong> <?php echo $role_format; ?></p>
    </div>

    <div class="row">
        <div id="file-settings" class="section half-width">
            <h2>File Settings</h2>
            <?php 'PGRpdiBjbGFzcz0ic2xpZGVyLWNvbnRhaW5lciI+CiAgICAgICAgICAgICAgICAgICAgPHAgc3R5bGU9ImRpc3BsYXk6IG5vbmU7IiBpZD0ic2V0dGluZ05hbWUiPmxvY2FsaXplZC10aW1lem9uZTwvcD4KICAgICAgICAgICAgICAgICAgICA8bGFiZWwgZm9yPSJib29sZWFuU2V0dGluZyIgY2xhc3M9InNsaWRlci1sYWJlbCI+VXNlIGxvY2FsaXplZCB0aW1lem9uZSBmb3IgZmlsZSBwYWdlPC9sYWJlbD4KICAgICAgICAgICAgICAgICAgICA8cD5EaXNhYmxlIHRvIHVzZSB5b3VyIHVzZXIgc2V0dGluZyB0aW1lem9uZSBmb3Igd2hlbiBhIHVzZXIgdmlld3MgYSBmaWxlIHlvdSBwb3N0ZWQ8cD4KICAgICAgICAgICAgICAgICAgICA8cD5FbmFibGUgdG8gdXNlIHRoZSB1c2VyJ3MgdGltZXpvbmUgZm9yIHdoZW4gYSB1c2VyIHZpZXdzIGEgZmlsZSB5b3UgcG9zdGVkPHA+CiAgICAgICAgICAgICAgICAgICAgPGxhYmVsIGNsYXNzPSJzd2l0Y2giPgogICAgICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT0iY2hlY2tib3giIGlkPSJib29sZWFuU2V0dGluZyI8P3BocCBlY2hvICRsb2NhbGl6c2VkVGltZXpvbmUgPT09ICd0cnVlJyA/ICIgY2hlY2tlZCIgOiAiIjs/Pj4KICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9InNsaWRlciByb3VuZCI+PC9zcGFuPgogICAgICAgICAgICAgICAgICAgIDwvbGFiZWw+CiAgICAgICAgICAgICAgICA8L2Rpdj4=';
            ?>
            <button onclick="deleteAllFiles();">Delete all files</button>
        </div>

        <div id="change-password" class="section half-width">
            <h2>Change Password</h2>
            <form id="passwordChangeForm">
                <input type="password" id="oldPassword" placeholder="Old Password">
                <input type="password" id="newPassword" placeholder="New Password">
                <input type="password" id="confirmPassword" placeholder="Confirm New Password">
                <input type="hidden" id="username" value="<?php echo $username; ?>">
                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div id="change-username" class="section half-width">
            <h2>Change Username</h2>
            <form id="usernameChangeForm">
                <input type="text" id="newUsername" placeholder="New Username">
                <input type="hidden" id="oldUsername" value="<?= $username ?>">
                <input type="password" id="usernamepassword" placeholder="Password">
                <button type="submit">Change Username</button>
            </form>
        </div>

        <div id="user-settings" class="section half-width">
            <h2>User Settings</h2>
            <button onclick="setCookieAndRedirect('<?= $discordClientId ?>', '<?= urlencode($discordRedirectUri) ?>')">Re-Link Discord</button>
            <button onclick="copyAPIKey();">Copy API Key</button>
            <button onclick="generateAPIKey();">Generate new API Key</button>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Account Management';
include 'layout.php';
?>