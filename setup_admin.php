<?php
require_once __DIR__ . '/config/auth.php';

$count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$message = '';
if ($count > 0) {
    $message = 'Администратор аллакай сохта шудааст. Барои амният setup_admin.php-ро баъди истифода нест кунед.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $username === '' || strlen($password) < 8) {
        $message = 'Ҳама майдонҳоро пур кунед. Парол бояд камаш 8 аломат бошад.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users(full_name,username,password_hash,role) VALUES(?,?,?,'admin')");
        $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
        $message = 'Администратор сохта шуд. Акнун ба система ворид шавед.';
    }
}
?>
<!doctype html><html lang="tg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Сохтани администратор</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head>
<body class="auth-bg"><main class="auth-card"><div class="brand-mark">SA</div><h2>Сохтани администратор</h2><p class="small muted">Баҳисобгирии ҳозиршавии донишҷӯён бо рамзи QR</p><p class="muted">Ин амал танҳо як маротиба иҷро мешавад.</p>
<?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
<?php if ($count === 0): ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="form-label">Ному насаб</label><input class="form-control mb-3" name="full_name" required><label class="form-label">Номи корбар</label><input class="form-control mb-3" name="username" required><label class="form-label">Парол (на камтар аз 8 аломат)</label><input class="form-control mb-3" type="password" name="password" minlength="8" required><button class="btn btn-primary w-100">Сохтани администратор</button></form><?php endif; ?>
<a class="d-block mt-3" href="login.php">Ба саҳифаи воридшавӣ</a></main></body></html>
