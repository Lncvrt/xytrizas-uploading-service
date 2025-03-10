<?php
require '../config/config.php';
include '../incl/main.php';

$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    $response = [
        'success' => 'false',
        'response' => 'Unable to access database',
    ];

    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode($response));
}

$apikey = $_SERVER['HTTP_KEY'] ?? '';
$uid = $_SERVER['HTTP_UID'] ?? '';

if (empty($apikey)) {
    $response = [
        'success' => 'false',
        'response' => 'Invalid request',
    ];
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode($response));
}

$stmt = $conn->prepare("SELECT * FROM users WHERE api_key = ?");
$stmt->bind_param("s", $apikey);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $stmt = $conn->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $response = [
            'success' => 'false',
            'response' => 'User not found',
        ];
        http_response_code(404);
        header('Content-Type: application/json');
        die(json_encode($response));
    }

    $userInfo = $result->fetch_assoc();
    $role = $userInfo['role'] == 1 ? "Owner" : ($userInfo['role'] == 2 ? "Manager" : ($userInfo['role'] == 0 ? "User" : "Unknown"));
    if ($userInfo['discord_id'] == null || $userInfo['discord_id'] == "") {
        $userInfo['discord_id'] = "N/A";
    }
    if ($userInfo['discord_avatar'] == null || $userInfo['discord_avatar'] == "") {
        $userInfo['discord_avatar'] = "N/A";
    }

    $info = [
        'uid' => $userInfo['uid'],
        'username' => $userInfo['username'],
        'discordId' => $userInfo['discord_id'],
        'role' => $role,
        'registerTime' => $userInfo['register_time'],
        'avatar' => $userInfo['discord_avatar'],
    ];

    $response = [
        'success' => true,
        'response' => 'Information for user "' . $userInfo['username'] . '"',
        'userinfo' => $info,
    ];

    http_response_code(200);
    header('Content-Type: application/json');
    die(json_encode($response));
} else {
    $response = [
        'success' => 'false',
        'response' => 'Invalid API key',
    ];
    http_response_code(401);
    header('Content-Type: application/json');
    die(json_encode($response));
}
?>