<?php
require_once __DIR__ . '/config/auth.php';
require_role('admin','teacher');
$me=current_user();$groups=$pdo->query("SELECT * FROM student_groups ORDER BY group_name")->fetchAll();
$groupId=(int)($_GET['group_id']??0);$date=$_GET['date']??date('Y-m-d');$sessionId=(int)($_GET['session_id']??0);
$sql="SELECT a.scanned_at,u.full_name,u.student_code,g.group_name,s.lesson_title,t.full_name AS teacher_name FROM attendance a JOIN users u ON u.id=a.student_id JOIN class_sessions s ON s.id=a.class_session_id JOIN student_groups g ON g.id=s.group_id JOIN users t ON t.id=s.teacher_id WHERE DATE(a.scanned_at)=?";
$params=[$date];
if($groupId){$sql.=" AND s.group_id=?";$params[]=$groupId;}
if($sessionId){$sql.=" AND s.id=?";$params[]=$sessionId;}
if($me['role']==='teacher'){$sql.=" AND s.teacher_id=?";$params[]=$me['id'];}
$sql.=" ORDER BY a.scanned_at DESC";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$pageTitle='Ҳисоботҳои ҳозиршавӣ';require __DIR__.'/partials/header.php';
?>
<div class="content-card"><div class="section-heading"><div><h3>Филтри ҳисобот</h3><p>Маълумотро аз рӯйи сана ва гурӯҳ ҷустуҷӯ кунед.</p></div><span class="count-pill"><?=count($rows)?> сабт</span></div><form class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label">Сана</label><input type="date" class="form-control" name="date" value="<?=e($date)?>"></div><div class="col-md-4"><label class="form-label">Гурӯҳ</label><select class="form-select" name="group_id"><option value="0">Ҳамаи гурӯҳҳо</option><?php foreach($groups as $g):?><option value="<?=$g['id']?>" <?=$groupId===$g['id']?'selected':''?>><?=e($g['group_name'])?></option><?php endforeach;?></select></div><div class="col-md-4"><button class="btn btn-primary w-100">Намоиши ҳисобот</button></div></form><div class="mt-3"><a class="btn btn-outline-success" href="export_csv.php?date=<?=e($date)?>&group_id=<?=$groupId?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Боргирии CSV / Excel</a></div></div>
<div class="content-card mt-4"><div class="table-responsive"><table class="table modern-table"><thead><tr><th>Донишҷӯ</th><th>Рамз</th><th>Гурӯҳ</th><th>Дарс</th><th>Омӯзгор</th><th>Вақт</th><th>Ҳолат</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><strong><?=e($r['full_name'])?></strong></td><td><?=e($r['student_code'])?></td><td><?=e($r['group_name'])?></td><td><?=e($r['lesson_title'])?></td><td><?=e($r['teacher_name'])?></td><td><?=e($r['scanned_at'])?></td><td><span class="status-pill active">Ҳозир</span></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="7" class="text-center muted py-4">Барои ин филтр маълумот ёфт нашуд.</td></tr><?php endif;?></tbody></table></div></div>
<?php require __DIR__.'/partials/footer.php'; ?>
