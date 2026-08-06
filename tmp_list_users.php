<?php
require_once __DIR__ . '/db.php';
$rows = $pdo->query('SELECT id, email, full_name FROM users')->fetchAll();
foreach ($rows as $r) {
    echo $r['id'] . ' ' . $r['email'] . ' ' . $r['full_name'] . PHP_EOL;
}
?>