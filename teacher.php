<?php
require_once __DIR__ . '/config/auth.php';
require_role('teacher');
$me=current_user(); $notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $group=(int)($_POST['group_id']??0); $title=trim($_POST['lesson_title']??'');
    $check=$pdo->prepare("SELECT id FROM student_groups WHERE id=?");$check->execute([$group]);
    if(!$check->fetch()||$title==='') $notice='Гурӯҳ ва номи дарсро дуруст интихоб кунед.';
    else {
        $pdo->beginTransaction();
        try {
          $pdo->prepare("UPDATE class_sessions SET status='closed',closed_at=NOW() WHERE teacher_id=? AND status='active'")->execute([$me['id']]);
          $stmt=$pdo->prepare("INSERT INTO class_sessions(teacher_id,group_id,lesson_title) VALUES(?,?,?)");
          $stmt->execute([$me['id'],$group,$title]);
          $newId=(int)$pdo->lastInsertId(); audit_log($pdo,'session_start','Дарси нав: '.$title); $pdo->commit();
        } catch(Throwable $ex) { $pdo->rollBack(); throw $ex; }
        header('Location: qr_display.php?id='.$newId);exit;
    }
}
$groups=$pdo->query("SELECT * FROM student_groups ORDER BY group_name")->fetchAll();
$stmt=$pdo->prepare("SELECT s.*,g.group_name,(SELECT COUNT(*) FROM attendance a WHERE a.class_session_id=s.id) AS present_count FROM class_sessions s JOIN student_groups g ON g.id=s.group_id WHERE s.teacher_id=? ORDER BY s.id DESC LIMIT 20");
$stmt->execute([$me['id']]);$sessions=$stmt->fetchAll();
$pageTitle='Дарс ва QR-код';require __DIR__.'/partials/header.php';
?>
<?php if($notice):?><div class="alert alert-warning"><?=e($notice)?></div><?php endif;?>
<div class="two-col"><div class="content-card"><div class="card-heading"><div><h3>Оғози дарси нав</h3><p>Барои гурӯҳ QR-коди динамикӣ фаъол кунед.</p></div><i class="bi bi-qr-code"></i></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label class="form-label">Номи дарс</label><input class="form-control mb-3" name="lesson_title" placeholder="Масалан: Забони PHP" required><label class="form-label">Гурӯҳ</label><select class="form-select mb-4" name="group_id" required><option value="">Гурӯҳро интихоб кунед</option><?php foreach($groups as $g):?><option value="<?=$g['id']?>"><?=e($g['group_name'])?></option><?php endforeach;?></select><button class="btn btn-primary w-100"><i class="bi bi-play-circle me-1"></i> Оғози дарс ва нишон додани QR</button></form></div>
<div class="content-card feature-card"><div class="feature-icon"><i class="bi bi-shield-lock"></i></div><h3>QR-и бехатар</h3><p>Рамз ҳар 30 сония нав мешавад. Сервер муҳлати эътиборро месанҷад ва сабти такрории донишҷӯро манъ мекунад.</p><div class="feature-stat"><i class="bi bi-arrow-repeat"></i> Навсозии автоматӣ: 30 сония</div></div></div>
<div class="content-card mt-4"><h3>Дарсҳои охирин</h3><div class="table-responsive"><table class="table modern-table"><thead><tr><th>Дарс</th><th>Гурӯҳ</th><th>Оғоз</th><th>Ҳозир</th><th>Ҳолат</th><th></th></tr></thead><tbody><?php foreach($sessions as $s):?><tr><td><strong><?=e($s['lesson_title'])?></strong></td><td><?=e($s['group_name'])?></td><td><?=e($s['started_at'])?></td><td><?=$s['present_count']?></td><td><span class="status-pill <?=$s['status']==='active'?'active':'closed'?>"><?=$s['status']==='active'?'Фаъол':'Анҷом ёфт'?></span></td><td><a href="session_report.php?id=<?=$s['id']?>" class="btn btn-sm btn-outline-secondary">Ҳисобот</a> <?php if($s['status']==='active'):?><a href="qr_display.php?id=<?=$s['id']?>" class="btn btn-sm btn-outline-primary">QR</a><?php endif;?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require __DIR__.'/partials/footer.php'; ?>
