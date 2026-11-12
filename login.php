<?php
require_once __DIR__ . '/config/auth.php';
if (current_user()) redirect_dashboard();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $lim = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE username=? AND ip_address=? AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)");
    $lim->execute([$username, $ip]);
    $blocked = (int)$lim->fetchColumn() >= 10;
    $stmt = $pdo->prepare("SELECT id,full_name,username,password_hash,role,group_id,student_code,is_active FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$blocked && $user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
        $pdo->prepare('DELETE FROM login_attempts WHERE username=?')->execute([$username]);
        $pdo->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        redirect_dashboard();
    }
    $pdo->prepare('INSERT INTO login_attempts(username,ip_address) VALUES(?,?)')->execute([$username, $ip]);
    $error = $blocked ? 'Кӯшишҳои воридшавӣ зиёд шуданд. Пас аз 15 дақиқа кӯшиш кунед.' : 'Номи корбар ё парол нодуруст аст.';
}
?>
<!doctype html><html lang="tg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Воридшавӣ | Баҳисобгирии ҳозиршавии донишҷӯён бо рамзи QR</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head>
<body class="auth-bg"><main class="auth-card"><div class="brand-mark">QR</div><div class="eyebrow">БАҲИСОБГИРИИ ҲОЗИРШАВИИ ДОНИШҶӮЁН</div><h1>Хуш омадед!</h1><h2 class="h6 mt-2">Баҳисобгирии ҳозиршавии донишҷӯён бо рамзи QR</h2><p class="muted">Барои идома ба ҳисоби худ ворид шавед.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="form-label">Номи корбар</label><div class="input-group mb-3"><span class="input-group-text"><i class="bi bi-person"></i></span><input class="form-control" name="username" autocomplete="username" required></div><label class="form-label">Парол</label><div class="input-group mb-4"><span class="input-group-text"><i class="bi bi-lock"></i></span><input class="form-control" type="password" name="password" autocomplete="current-password" required></div><button class="btn btn-primary w-100 py-2">Ворид шудан <i class="bi bi-arrow-right ms-1"></i></button></form>
<div class="small muted mt-4">Системаи баҳисобгирии ҳозиршавӣ бо QR-код</div></main></body></html>
