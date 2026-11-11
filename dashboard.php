<?php
require_once __DIR__ . '/config/auth.php';
require_login();
$me = current_user();
$pageTitle = 'Панели асосӣ';
if ($me['role']==='admin') {
    $students=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $teachers=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
    $groups=(int)$pdo->query("SELECT COUNT(*) FROM student_groups")->fetchColumn();
    $sessions=(int)$pdo->query("SELECT COUNT(*) FROM class_sessions")->fetchColumn();
} elseif ($me['role']==='teacher') {
    $st=$pdo->prepare("SELECT COUNT(*) FROM class_sessions WHERE teacher_id=?"); $st->execute([$me['id']]); $sessions=(int)$st->fetchColumn();
    $st=$pdo->prepare("SELECT COUNT(*) FROM class_sessions WHERE teacher_id=? AND status='active'"); $st->execute([$me['id']]); $active=(int)$st->fetchColumn();
} else {
    $st=$pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id=?"); $st->execute([$me['id']]); $mine=(int)$st->fetchColumn();
}
require __DIR__ . '/partials/header.php';
?>
<div class="welcome-panel"><div><div class="eyebrow text-white-50">ХУШ ОМАДЕД</div><h2>Салом, <?= e($me['full_name']) ?>!</h2><p>Ҳамаи маълумоти зарурӣ дар як ҷой. Кори худро аз панели идоракунӣ оғоз кунед.</p></div><div class="welcome-icon"><i class="bi bi-mortarboard"></i></div></div>
<?php if ($me['role']==='admin'): ?>
<div class="section-heading"><div><h3>Омори умумӣ</h3><p>Маълумоти умумии система</p></div></div><div class="stats-grid">
<div class="stat-card"><div class="stat-icon purple"><i class="bi bi-people"></i></div><span>Донишҷӯён</span><strong><?= $students ?></strong><small>Ҳамаи донишҷӯён</small></div>
<div class="stat-card"><div class="stat-icon blue"><i class="bi bi-person-video3"></i></div><span>Омӯзгорон</span><strong><?= $teachers ?></strong><small>Ҳамаи омӯзгорон</small></div>
<div class="stat-card"><div class="stat-icon orange"><i class="bi bi-diagram-3"></i></div><span>Гурӯҳҳо</span><strong><?= $groups ?></strong><small>Гурӯҳҳои таълимӣ</small></div>
<div class="stat-card"><div class="stat-icon green"><i class="bi bi-journal-check"></i></div><span>Дарсҳо</span><strong><?= $sessions ?></strong><small>Дарсҳои сабтшуда</small></div></div>
<div class="content-card mt-4"><h3>Идоракунии система</h3><p class="muted">Аз ин ҷо донишҷӯён, омӯзгорон ва гурӯҳҳоро илова кунед.</p><a class="btn btn-primary" href="admin.php"><i class="bi bi-people me-1"></i> Идоракунии корбарон</a><a class="btn btn-outline-primary ms-2" href="reports.php">Дидани ҳисобот</a></div>
<?php elseif ($me['role']==='teacher'): ?>
<div class="stats-grid"><div class="stat-card"><div class="stat-icon blue"><i class="bi bi-journal-bookmark"></i></div><span>Ҳамаи дарсҳо</span><strong><?= $sessions ?></strong><small>Дарсҳои сохташуда</small></div><div class="stat-card"><div class="stat-icon green"><i class="bi bi-broadcast"></i></div><span>Дарсҳои фаъол</span><strong><?= $active ?></strong><small>Ҳоло идома доранд</small></div></div>
<div class="content-card mt-4"><h3>Оғози бақайдгирии ҳозиршавӣ</h3><p class="muted">Дарси нав кушоед ва QR-коди тағйирёбандаро ба донишҷӯён нишон диҳед.</p><a class="btn btn-primary" href="teacher.php"><i class="bi bi-qr-code me-1"></i> Сохтани дарс ва QR</a></div>
<?php else: ?>
<div class="stats-grid"><div class="stat-card"><div class="stat-icon green"><i class="bi bi-calendar-check"></i></div><span>Дарсҳои ҳозиршуда</span><strong><?= $mine ?></strong><small>Ҳамаи сабтҳои шумо</small></div></div>
<div class="content-card mt-4"><h3>Бақайдгирии ҳозиршавӣ</h3><p class="muted">QR-коди дар экран нишон додашударо бо камераи телефон скан кунед.</p><a class="btn btn-primary" href="scan.php"><i class="bi bi-camera me-1"></i> Скан кардани QR</a><a class="btn btn-outline-primary ms-2" href="my_attendance.php">Таърихи ҳозиршавӣ</a></div>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
