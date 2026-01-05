<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Google Client Configuration
$google_client = new Google_Client();

// You can set these in .env or hardcode for dev (NOT RECOMMENDED for production)
// For this environment, we'll try to load from env or fallback to empty prompts
$google_client->setClientId('114430708538-7n4ege0pohcpte3b940ljbl4k4g3f2kg.apps.googleusercontent.com');
$google_client->setClientSecret('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');
$google_client->setRedirectUri('https://pawtraitid.azurewebsites.net/google_callback.php');

$google_client->addScope('email');
$google_client->addScope('profile');
