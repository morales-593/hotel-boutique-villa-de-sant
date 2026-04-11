<?php
// Define physical path
define('BASE_PATH', dirname(__DIR__));

// Define URL path (assuming it's in a subdirectory)
// This helps with assets paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_dir = str_replace(basename($script_name), '', $script_name);
$base_dir = str_replace(['views/usuario/', 'views/admin/', 'api/'], '', $base_dir);

define('BASE_URL', $protocol . "://" . $host . rtrim($base_dir, '/') . '/');
?>
