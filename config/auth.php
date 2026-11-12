<?php
declare(strict_types=1);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $isHttps,
        'httponly' => true, 'samesite' => 'Lax'
    ]);
    session_start();
}
require_once __DIR__ . '/db.php';
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(self)');

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
    global $pdo;
    $st = $pdo->prepare('SELECT is_active FROM users WHERE id=?');
    $st->execute([current_user()['id']]);
    if ((int)$st->fetchColumn() !== 1) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }
}
function require_role(string ...$roles): void {
    require_login();
    if (!in_array(current_user()['role'] ?? '', $roles, true)) { http_response_code(403); exit('Шумо барои иҷрои ин амал иҷозат надоред.'); }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(?string $sent = null): void {
    $sent = $sent ?? ($_POST['csrf'] ?? '');
    if (!is_string($sent) || $sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419); exit('Муҳлати амнияти дархост гузаштааст. Саҳифаро нав кунед.');
    }
}
function redirect_dashboard(): void { header('Location: dashboard.php'); exit; }
function audit_log(PDO $pdo, string $action, string $details = ''): void {
    try {
        $u = current_user();
        $stmt = $pdo->prepare('INSERT INTO audit_logs(user_id, action, details, ip_address) VALUES(?,?,?,?)');
        $stmt->execute([$u['id'] ?? null, $action, mb_substr($details, 0, 500), $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) { /* logging must not break the main action */ }
}
function json_response(array $data, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit;
}
