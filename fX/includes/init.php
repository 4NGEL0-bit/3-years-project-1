<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configurations
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Load models
require_once __DIR__ . '/../models/User.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Set default timezone
date_default_timezone_set('UTC');

// Constants
define('SITE_URL', 'http://localhost/fX');
define('TEMPLATES_PATH', __DIR__ . '/../templates');
