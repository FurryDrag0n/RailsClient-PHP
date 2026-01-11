<?php
/*
=========================================================================
= Authentication middleware between your application
= and the external authentication server.
=
= Usage:
= 1. Place this file in the same directory as your app script
=    and RailsClient.php.
= 2. Call require_once() at the very top of your app script.
= 3. Any user attempting to bypass authentication will be rejected.
=
= Requirements:
= - RailsClient
= - cURL extension
=========================================================================
*/
session_start();

require_once 'RailsClient.php';
$rails = new RailsClient();

$auth = $rails->auth('Silent58', '29Hjsv04Hvb'); // your account credentials
if (!$auth || $auth['message'] !== 'auth_success') {
	header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
	
	echo json_encode(['error' => 'Incorrect admin credentials'], JSON_UNESCAPED_UNICODE);
    exit;
}

$token = $_GET['token'] ?? ($_SESSION['token'] ?? null);
if (!$token) {
	header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
	
	echo json_encode(['error' => 'Missing token'], JSON_UNESCAPED_UNICODE);
    exit;
}

$uinfo = $rails->userInfo($token);
if (!$uinfo || $uinfo['message'] !== 'user_info_success') {
	header('Content-Type: application/json; charset=utf-8');
	http_response_code(401);
	
	echo json_encode(['error' => 'Invalid token'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['token']) || $_SESSION['token'] !== $token) {
    $_SESSION['token'] = $token;
    $_SESSION['uinfo'] = $uinfo['data'];
}
?>
