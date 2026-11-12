<?php
require_once __DIR__ . '/../config/auth.php';
require_login();
$me = current_user();
$pageTitle = $pageTitle ?? 'Панели идоракунӣ';
?>
<!doctype html><html lang="tg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($pageTitle) ?> | Баҳисобгирии ҳозиршавии донишҷӯён бо рамзи QR</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head><body>
<div class="app-shell"><aside class="sidebar"><a class="sidebar-brand" href="dashboard.php"><span class="brand-mark small-mark">QR</span><span>Ҳозиршавӣ<span class="brand-light"> QR</span></span></a><div class="side-caption">МЕНЮИ АСОСӢ</div><nav class="side-nav">
<a href="dashboard.php"><i class="bi bi-grid-1x2"></i> Панели асосӣ</a>
<?php if ($me['role']==='admin'): ?>
<a href="admin.php"><i class="bi bi-people"></i> Идоракунии корбарон</a>
<a href="reports.php"><i class="bi bi-bar-chart"></i> Ҳисоботҳо</a>
<a href="manual_attendance.php"><i class="bi bi-pencil-square"></i> Ислоҳи ҳозиршавӣ</a>
<?php elseif ($me['role']==='teacher'): ?>
<a href="teacher.php"><i class="bi bi-qr-code"></i> Дарс ва QR-код</a>
<a href="reports.php"><i class="bi bi-clipboard-data"></i> Ҳозиршавӣ</a>
<a href="manual_attendance.php"><i class="bi bi-pencil-square"></i> Ислоҳи ҳозиршавӣ</a>
<?php else: ?>
<a href="scan.php"><i class="bi bi-camera"></i> Скан кардани QR</a>
<a href="my_attendance.php"><i class="bi bi-calendar-check"></i> Таърихи ман</a>
<?php endif; ?>
<a href="profile.php"><i class="bi bi-person-gear"></i> Профили ман</a>
</nav><div class="sidebar-bottom"><div class="user-mini"><div class="avatar"><?= e(mb_strtoupper(mb_substr($me['full_name'],0,1))) ?></div><div><strong><?= e($me['full_name']) ?></strong><small><?= e($me['role']==='admin'?'Администратор':($me['role']==='teacher'?'Омӯзгор':'Донишҷӯ')) ?></small></div></div><form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="logout-link border-0 bg-transparent" type="submit"><i class="bi bi-box-arrow-right"></i> Баромадан</button></form></div></aside>
<main class="main-area"><header class="topbar"><div><div class="eyebrow">БАҲИСОБГИРИИ ҲОЗИРШАВИИ ДОНИШҶӮЁН БО РАМЗИ QR</div><h1><?= e($pageTitle) ?></h1></div><div class="topbar-right"><span class="online-dot"></span><span class="muted small">Система фаъол аст</span><div class="avatar top-avatar"><?= e(mb_strtoupper(mb_substr($me['full_name'],0,1))) ?></div></div></header><section class="page-content">
