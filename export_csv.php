<?php
require_once __DIR__ . '/config/auth.php';
require_role('admin','teacher');
$me=current_user(); $date=$_GET['date']??date('Y-m-d');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $date=date('Y-m-d');
$group=(int)($_GET['group_id']??0);
$sql="SELECT a.scanned_at,u.full_name,u.student_code,g.group_name,s.lesson_title,t.full_name teacher_name,a.status,a.source FROM attendance a JOIN users u ON u.id=a.student_id JOIN class_sessions s ON s.id=a.class_session_id JOIN student_groups g ON g.id=s.group_id JOIN users t ON t.id=s.teacher_id WHERE DATE(a.scanned_at)=?";
$args=[$date]; if($group){$sql.=' AND s.group_id=?';$args[]=$group;} if($me['role']==='teacher'){$sql.=' AND s.teacher_id=?';$args[]=$me['id'];}
$sql.=' ORDER BY a.scanned_at';$st=$pdo->prepare($sql);$st->execute($args);
header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="attendance_'.$date.'.csv"');
echo "\xEF\xBB\xBF"; $out=fopen('php://output','w');
$csvSafe=static function($v){$v=(string)($v??'');return preg_match('/^[=+@\-\t\r]/u',$v)?"'".$v:$v;};
fputcsv($out,['Вақт','Ному насаб','Рамзи донишҷӯ','Гурӯҳ','Дарс','Омӯзгор','Ҳолат','Манбаъ'],';');
while($r=$st->fetch()) fputcsv($out,array_map($csvSafe,[$r['scanned_at'],$r['full_name'],$r['student_code'],$r['group_name'],$r['lesson_title'],$r['teacher_name'],$r['status']==='present'?'Ҳозир':($r['status']==='late'?'Дер омад':'Узрнок'),$r['source']==='qr'?'QR':'Дастӣ']),';');
fclose($out); exit;
