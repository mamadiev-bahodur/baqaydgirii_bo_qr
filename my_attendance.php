<?php
require_once __DIR__ . '/config/auth.php';
require_role('student');
$stmt=$pdo->prepare("SELECT a.scanned_at,s.lesson_title,g.group_name FROM attendance a JOIN class_sessions s ON s.id=a.class_session_id JOIN student_groups g ON g.id=s.group_id WHERE a.student_id=? ORDER BY a.scanned_at DESC");
$stmt->execute([current_user()['id']]);$rows=$stmt->fetchAll();
$pageTitle='Таърихи ҳозиршавии ман';require __DIR__.'/partials/header.php';
?>
<div class="content-card"><div class="section-heading"><div><h3>Таърихи ҳозиршавӣ</h3><p>Рӯйхати ҳамаи дарсҳое, ки шумо ҳозир шудаед.</p></div><span class="count-pill"><?=count($rows)?> дарс</span></div><div class="table-responsive"><table class="table modern-table"><thead><tr><th>Номи дарс</th><th>Гурӯҳ</th><th>Сана ва вақт</th><th>Ҳолат</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><strong><?=e($r['lesson_title'])?></strong></td><td><?=e($r['group_name'])?></td><td><?=e($r['scanned_at'])?></td><td><span class="status-pill active">Ҳозир</span></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="4" class="text-center muted py-4">Ҳоло сабти ҳозиршавӣ нест.</td></tr><?php endif;?></tbody></table></div></div>
<?php require __DIR__.'/partials/footer.php'; ?>
