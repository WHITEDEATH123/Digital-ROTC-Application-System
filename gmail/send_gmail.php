<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

function sendGmail($to, $subject, $bodyHtml) {
    $client = new Client();
    $client->setApplicationName('ROTC Enrollment System');
    $client->setScopes(Gmail::GMAIL_SEND);
    $client->setAuthConfig(__DIR__ . '/credential.json');
    $client->setAccessType('offline');

    $tokenPath = __DIR__ . '/token.json';
    if (!file_exists($tokenPath)) {

        exit("Token not found. Run authorize_gmail.php first.");
    }

    $accessToken = json_decode(file_get_contents($tokenPath), true);
    $client->setAccessToken($accessToken);


    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        } else {
            exit("Token expired. Re-authorize via authorize_gmail.php.");
        }
    }

    $service = new Gmail($client);

    $rawMessage = "From: ROTC Enrollment System <lexerbaridji1@gmail.com>\r\n";
    $rawMessage .= "To: <$to>\r\n";
    $rawMessage .= "Subject: $subject\r\n";
    $rawMessage .= "MIME-Version: 1.0\r\n";
    $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $rawMessage .= $bodyHtml;

    $mime = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');
    $message = new Message();
    $message->setRaw($mime);

    try {
        $service->users_messages->send("me", $message);
        return true;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}
?>
