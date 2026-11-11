<?php
require_once __DIR__.'/config/auth.php'; require_role('admin','teacher');
$id=(int)($_GET['session_id']??0);$me=current_user();
$sql="SELECT s.group_id FROM class_sessions s WHERE s.id=?";$args=[$id];if($me['role']==='teacher'){$sql.=' AND s.teacher_id=?';$args[]=$me['id'];}
$st=$pdo->prepare($sql);$st->execute($args);$session=$st->fetch();if(!$session)json_response(['students'=>[]],404);
$st=$pdo->prepare("SELECT id,full_name,student_code FROM users WHERE role='student' AND group_id=? ORDER BY full_name");$st->execute([$session['group_id']]);json_response(['students'=>$st->fetchAll()]);
