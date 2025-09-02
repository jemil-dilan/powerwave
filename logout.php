<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Clear all session data
session_destroy();

// Start a new session for messages
session_start();
showMessage('You have been logged out successfully', 'success');

redirect('index.php');
?>
