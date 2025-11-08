<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
use Google\Client;

session_start();

$client = new Client();
$client->setApplicationName('ROTC Enrollment System');
$client->setScopes(['https://www.googleapis.com/auth/gmail.send']);
$client->setAuthConfig(__DIR__ . '/credential.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$tokenPath = __DIR__ . '/token.json';

// Step 1: Redirect to Google if no code
if (!isset($_GET['code']) && !file_exists($tokenPath)) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// Step 2: Handle Google redirect with code
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    try {
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            echo "⚠️ Error fetching access token: " . htmlspecialchars($token['error_description'] ?? $token['error']);
            exit;
        }

        file_put_contents($tokenPath, json_encode($token));
        echo "✅ token.json created successfully! You can now use send_gmail.php.";
        echo "<br><a href='send_gmail.php'>Test sending email</a>";
        exit;

    } catch (Exception $e) {
        echo "⚠️ Exception: " . $e->getMessage();
        exit;
    }
}

// Step 3: Already authorized
if (file_exists($tokenPath)) {
    echo "✅ Gmail already authorized! token.json exists.<br>";
    echo "<a href='send_gmail.php'>Test sending email</a>";
    exit;
}
