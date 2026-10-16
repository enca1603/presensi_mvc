<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$config = require dirname(__DIR__) . '/config/database.php';

$dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $config['driver'], $config['host'], $config['port'], $config['database'], $config['charset']);
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
]);

$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    echo "Running migration: " . basename($file) . "\n";
    $sql = file_get_contents($file);

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    while ($stmt->nextRowset()) {
        // drain multi-statement result sets
    }
}

echo "Migration completed successfully.\n";
