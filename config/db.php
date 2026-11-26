<?php
declare(strict_types=1);

$host = 'sql211.infinityfree.com';
$dbname = 'if0_43479744_qr';
$dbuser = 'if0_43479744';
$dbpass = 'dferhtDbgfn '; // XAMPP default; change if your MySQL root has a password.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Пайвастшавӣ ба пойгоҳи додаҳо иҷро нашуд. Файли config/db.php-ро санҷед.');
}
