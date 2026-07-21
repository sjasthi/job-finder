<?php

require_once __DIR__ . '/env.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'role_genie');
define('DB_USER', 'root');
define('DB_PASS', 'ODr@969710'); // Pass123
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
  error_log('Database connection failed: ' . $e->getMessage());
  die('Database connection failed. Please try again later.');
}