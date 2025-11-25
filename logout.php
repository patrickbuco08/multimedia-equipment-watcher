<?php
require_once 'config/database.php';
startSession();

// Destroy session and redirect to login
session_destroy();
header('Location: index.php');
exit;
