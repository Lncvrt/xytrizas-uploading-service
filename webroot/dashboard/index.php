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

$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

checkDiscordLink($discord_id);

$sql = "SELECT value FROM settings WHERE id = 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$news = base64_decode($row['value'] ?? '');

$sql = "SELECT value FROM settings WHERE id = 2";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$motd = base64_decode($row['value'] ?? '');

$totalSize = 0;
$uploadCount = 0;

$sql = "SELECT size FROM uploads WHERE uid = '$uid'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalSize += $row['size'];
        $uploadCount++;
    }
}

$uploadsByDay = $overallUploadsByDay = $overallLoginsByDay = array();

$currentDate = new DateTime('now', new DateTimeZone("America/Los_Angeles"));
for ($i = 0; $i < 7; $i++) {
    $day = $currentDate->format('m-d-Y');
    $uploadsByDay[$day] = 0;
    $overallUploadsByDay[$day] = 0;
    $overallLoginsByDay[$day] = 0;
    $currentDate->sub(new DateInterval('P1D'));
}

$sql = "SELECT uploaded FROM uploads WHERE uid = '$uid' AND uploaded >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY))";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timestamp = $row['uploaded'];
        $dateTime = new DateTime("@$timestamp");
        $dateTime->setTimezone(new DateTimeZone("America/Los_Angeles"));
        $day = $dateTime->format('m-d-Y');
        if (isset($uploadsByDay[$day])) {
            $uploadsByDay[$day]++;
        }
    }
}

$sql = "SELECT uploaded FROM uploads WHERE uploaded >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY))";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timestamp = $row['uploaded'];
        $dateTime = new DateTime("@$timestamp");
        $dateTime->setTimezone(new DateTimeZone("America/Los_Angeles"));
        $day = $dateTime->format('m-d-Y');
        if (isset($overallUploadsByDay[$day])) {
            $overallUploadsByDay[$day]++;
        }
    }
}

$sql = "SELECT login_time FROM logins WHERE login_time >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY))";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timestamp = $row['login_time'];
        $dateTime = new DateTime("@$timestamp");
        $dateTime->setTimezone(new DateTimeZone("America/Los_Angeles"));
        $day = $dateTime->format('m-d-Y');
        if (isset($overallLoginsByDay[$day])) {
            $overallLoginsByDay[$day]++;
        }
    }
}

$size = formatUnitSize($totalSize);

$uploadStatsData = json_encode(array_reverse(array_values($uploadsByDay)));
$overallUploadStatsData = json_encode(array_reverse(array_values($overallUploadsByDay)));
$loginStatsData = json_encode(array_reverse(array_values($overallLoginsByDay)));
$uploadStatsLabels = json_encode(array_reverse(array_keys($uploadsByDay)));

$role_format = $role == 1 ? "Owner" : ($role == 2 ? "Manager" : ($role == 0 ? "User" : "Unknown"));

$motd = str_ireplace('%discord%', '<a href="/discord" style="text-decoration: underline;">Discord</a>', $motd);
$news = str_ireplace('%discord%', '<a href="/discord" style="text-decoration: underline;">Discord</a>', $news);

$motd = str_ireplace('%username%', $username, $motd);
$news = str_ireplace('%username%', $username, $news);

$motd = str_ireplace('%uploads%', $uploadCount, $motd);
$news = str_ireplace('%uploads%', $uploadCount, $news);

$motd = str_ireplace('%role%', $role_format, $motd);
$news = str_ireplace('%role%', $role_format, $news);

$motd = str_ireplace('%uid%', $uid, $motd);
$news = str_ireplace('%uid%', $uid, $news);

$motd = str_ireplace('%storage%', $size, $motd);
$news = str_ireplace('%storage%', $size, $news);

ob_start();
?>
<div id="container">
    <div class="info-group">
        <h1 style="font-weight: bold;">Hello, <?php echo $username; ?> 👋</h1>

        <div class="info-box">
            <p class="big"><strong>Storage Used</strong></p>
            <p><?php echo $size; ?>/2.5 GB</p>
        </div>
        <div class="info-box">
            <p class="big"><strong>Uploads</strong></p>
            <p><?php echo $uploadCount; ?></p>
        </div>
        <div class="info-box">
            <p class="big"><strong>User ID</strong></p>
            <p><?php echo $uid; ?></p>
        </div>
    </div>
    <div>
        <div class="info-box" style="display: inline-block; width: 100%; min-height: 20vh;">
            <p class="big"><strong>Recent News</strong></p>
            <p><?php echo $news; ?></p>
        </div>
        <div class="info-box" style="display: inline-block; width: 100%; min-height: 20vh;">
            <p class="big"><strong>MOTD</strong></p>
            <p><?php echo $motd; ?></p>
        </div>
    </div>

    <div id="charts-container">
        <div id="canvas-group">
            <p><strong>Daily Uploads</strong></p>
            <canvas id="uploadStats"></canvas>
        </div>
        <div id="canvas-group">
            <p><strong>Daily Unique Logins</strong></p>
            <canvas id="loginStats"></canvas>
        </div>
    </div>
</div>

<script>
    const formattedLabels = formatAllDates(<?php echo $uploadStatsLabels; ?>);

    const uploadStatsData = {
        labels: formattedLabels,
        datasets: [{
            label: 'Overall',
            data: <?php echo $overallUploadStatsData; ?>,
            borderColor: 'rgba(64, 82, 255, 1)',
            borderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 10,
            pointHitRadius: 20,
            fill: true,
            backgroundColor: 'rgba(64, 82, 255, 0.15)',
        },
        {
            label: 'You',
            data: <?php echo $uploadStatsData; ?>,
            borderColor: 'rgba(255, 64, 82, 1)',
            borderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 10,
            pointHitRadius: 20,
            fill: true,
            backgroundColor: 'rgba(255, 64, 82, 0.15)',
        }]
    };

    const uploadStatsConfig = {
        type: 'line',
        data: uploadStatsData,
        options: {
            responsive: true,
            scales: {
                x: {
                    display: false,
                    grid: {
                        display: false,
                    }
                },
                y: {
                    display: false,
                    beginAtZero: true,
                    grid: {
                        display: false,
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function (context) {
                            return context[0].label;
                        },
                        label: function (context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            },
            elements: {
                line: {
                    cubicInterpolationMode: 'monotone',
                }
            },
            animation: {
                duration: 500
            }
        }
    };

    const loginStatsData = {
        labels: formattedLabels,
        datasets: [{
            label: 'Logins',
            data: <?php echo $loginStatsData; ?>,
            borderColor: 'rgba(64, 255, 82, 1)',
            borderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 10,
            pointHitRadius: 20,
            fill: true,
            backgroundColor: 'rgba(64, 255, 82, 0.15)',
        }]
    };

    const loginStatsConfig = {
        type: 'line',
        data: loginStatsData,
        options: {
            responsive: true,
            scales: {
                x: {
                    display: false,
                    grid: {
                        display: false,
                    }
                },
                y: {
                    display: false,
                    beginAtZero: true,
                    grid: {
                        display: false,
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function (context) {
                            return context[0].label;
                        },
                        label: function (context) {
                            return `${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            },
            elements: {
                line: {
                    cubicInterpolationMode: 'monotone',
                }
            },
            animation: {
                duration: 500
            }
        }
    };

    const uploadStatsCanvas = document.getElementById('uploadStats').getContext('2d');
    new Chart(uploadStatsCanvas, uploadStatsConfig);
    const loginStatsCanvas = document.getElementById('loginStats').getContext('2d');
    new Chart(loginStatsCanvas, loginStatsConfig);
</script>
<?php
$content = ob_get_clean();
$title = 'Dashboard';
include 'layout.php';
?>