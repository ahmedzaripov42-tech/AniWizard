<?php
$servername = getenv('DB_HOST') ?: "127.0.0.1";
$username   = getenv('DB_USER') ?: "wizardbot";
$password   = getenv('DB_PASS') ?: "wizardbot123";
$dbname     = getenv('DB_NAME') ?: "wizard_bot";
$connect = mysqli_connect($servername, $username, $password, $dbname);
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
