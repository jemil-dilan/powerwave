<?php
session_start();
require_once 'includes/functions.php';

$_SESSION['user_id'] = 1; // Simulate a logged-in user
$token = generateCSRFToken();

echo $token;
?>