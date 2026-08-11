<?php

require_once __DIR__ . '/env.php';

function bootstrapDatabase(): PDO
{
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'role_genie');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: getenv('DB_PASS') ?: '');
    define('DB_CHARSET', 'utf8mb4');

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $rootDsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;

    try {
        $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        die('Database connection failed. Please ensure MySQL is running and the database settings in .env are correct.');
    }

    $rootPdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $rootPdo = null;

    $schemaPath = __DIR__ . '/../SQL/RoleGenie schema.sql';
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        die('Database connection failed. Please ensure MySQL is running and the database settings in .env are correct.');
    }

    if (is_readable($schemaPath)) {
        $schemaSql = file_get_contents($schemaPath);
        if ($schemaSql !== false) {
            $schemaSql = preg_replace('/--.*$/m', '', $schemaSql);
            $schemaSql = preg_replace('/\/\*.*?\*\//s', '', $schemaSql);
            $statements = array_filter(
                array_map('trim', preg_split('/;(?=(?:[^\'\"]*[\'\"][^\'\"]*[\'\"])*[^\'\"]*$)/', $schemaSql)),
                static function ($statement): bool {
                    return $statement !== '';
                }
            );

            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }
        }
    }

    return $pdo;
}

$pdo = bootstrapDatabase();

/* old
<?php

require_once __DIR__ . '/env.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'role_genie');
define('DB_USER', 'root');
define('DB_PASS', getenv('DB_PASS'));
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
  */
