<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Google Client Configuration
$google_client = new Google_Client();

$google_client->setClientId(getenv('GOOGLE_CLIENT_ID'));
$google_client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
$google_client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));

$google_client->addScope('email');
$google_client->addScope('profile');
