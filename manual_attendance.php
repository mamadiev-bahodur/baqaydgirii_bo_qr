<?php
require_once __DIR__.'/config/auth.php'; require_role('admin','teacher');
$me=current_user();$error='';$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$sid=(int)($_POST['session_id']??0);$uid=(int)($_POST['student_id']??0);$status=$_POST['status']??'present';$note=trim($_POST['note']??'');
 if(!in_array($status,['present','late','excused'],true))$error='Ҳолат нодуруст аст.';
 else{
  $q="SELECT s.id,s.group_id FROM class_sessions s WHERE s.id=?";$a=[$sid];if($me['role']==='teacher'){$q.=' AND s.teacher_id=?';$a[]=$me['id'];}$st=$pdo->prepare($q);$st->execute($a);$session=$st->fetch();
  $st=$pdo->prepare("SELECT id FROM users WHERE id=? AND role='student' AND group_id=?");$st->execute([$uid,$session['group_id']??0]);
  if(!$session||!$st->fetch())$error='Дарс ё донишҷӯ нодуруст интихоб шудааст.';
  else{$st=$pdo->prepare("INSERT INTO attendance(class_session_id,student_id,status,source,note) VALUES(?,?,?,'manual',?) ON DUPLICATE KEY UPDATE status=VALUES(status),source='manual',note=VALUES(note)");$st->execute([$sid,$uid,$status,$note]);audit_log($pdo,'attendance_manual','Ислоҳи дастии ҳозиршавӣ');$success='Ҳозиршавӣ сабт ё нав карда шуд.';}
 }
}
if($me['role']==='teacher'){$st=$pdo->prepare('SELECT s.id,s.lesson_title,g.group_name FROM class_sessions s JOIN student_groups g ON g.id=s.group_id WHERE s.teacher_id=? ORDER BY s.id DESC LIMIT 100');$st->execute([$me['id']]);$sessions=$st->fetchAll();}else{$sessions=$pdo->query('SELECT s.id,s.lesson_title,g.group_name FROM class_sessions s JOIN student_groups g ON g.id=s.group_id ORDER BY s.id DESC LIMIT 100')->fetchAll();}
$pageTitle='Идоракунии ҳозиршавӣ';require __DIR__.'/partials/header.php';
?>
<div class="content-card" style="max-width:760px"><h3>Сабт ё ислоҳи дастӣ</h3><p class="muted">Барои ҳолатҳои истисноӣ, масалан кор накардани камера.</p><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><?php if($success):?><div class="alert alert-success"><?=e($success)?></div><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label class="form-label">Дарс</label><select class="form-select mb-3" name="session_id" id="sessionSelect" required><option value="">Интихоб кунед</option><?php foreach($sessions as $s):?><option value="<?=$s['id']?>"><?=e($s['lesson_title'].' — '.$s['group_name'])?></option><?php endforeach;?></select><label class="form-label">Донишҷӯ</label><select class="form-select mb-3" name="student_id" id="studentSelect" required><option value="">Аввал дарсро интихоб кунед</option></select><label class="form-label">Ҳолат</label><select class="form-select mb-3" name="status"><option value="present">Ҳозир</option><option value="late">Дер омад</option><option value="excused">Узрнок</option></select><label class="form-label">Шарҳ (ихтиёрӣ)</label><input class="form-control mb-3" name="note" maxlength="255"><button class="btn btn-primary">Сабт кардан</button></form><script>document.getElementById('sessionSelect').addEventListener('change',async e=>{const box=document.getElementById('studentSelect');box.innerHTML='<option value="">Бор шуда истодааст...</option>';if(!e.target.value){box.innerHTML='<option value="">Аввал дарсро интихоб кунед</option>';return;}try{const r=await fetch('session_students.php?session_id='+encodeURIComponent(e.target.value));const d=await r.json();box.innerHTML='<option value="">Донишҷӯро интихоб кунед</option>';d.students.forEach(s=>{const o=document.createElement('option');o.value=s.id;o.textContent=s.full_name+' ('+s.student_code+')';box.appendChild(o);});}catch(err){box.innerHTML='<option value="">Хато ҳангоми боркунӣ</option>';}});</script></div>
<?php require __DIR__.'/partials/footer.php'; ?>
