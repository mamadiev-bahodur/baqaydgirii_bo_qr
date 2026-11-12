<?php
require_once __DIR__ . '/config/auth.php';
require_role('student');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['success'=>false,'message'=>'Методи иҷозат нест.'],405);
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
verify_csrf($csrf);
$body=json_decode(file_get_contents('php://input'),true);
$token=(string)($body['token']??'');
if(!preg_match('/^[a-f0-9]{64}$/',$token)) json_response(['success'=>false,'message'=>'QR-код нодуруст аст.'],400);
$hash=hash('sha256',$token); $student=current_user();
try {
  $pdo->beginTransaction();
  $stmt=$pdo->prepare("SELECT q.id,q.class_session_id,s.group_id,s.status,s.lesson_title FROM qr_tokens q JOIN class_sessions s ON s.id=q.class_session_id WHERE q.token_hash=? AND q.expires_at>NOW() AND s.status='active' LIMIT 1 FOR UPDATE");
  $stmt->execute([$hash]); $valid=$stmt->fetch();
  if(!$valid){$pdo->rollBack();json_response(['success'=>false,'message'=>'Муҳлати QR гузаштааст ё дарс баста шудааст. Рамзи навро скан кунед.'],410);}
  if((int)$student['group_id']!==(int)$valid['group_id']){$pdo->rollBack();json_response(['success'=>false,'message'=>'Ин QR-код барои гурӯҳи шумо нест.'],403);}
  $check=$pdo->prepare('SELECT id FROM attendance WHERE class_session_id=? AND student_id=?');
  $check->execute([$valid['class_session_id'],$student['id']]);
  if($check->fetch()){$pdo->commit();json_response(['success'=>true,'already'=>true,'message'=>'Шумо аллакай дар ин дарс ҳозир ҳисоб шудаед.']);}
  $ins=$pdo->prepare("INSERT INTO attendance(class_session_id,student_id,status,source) VALUES(?,?,'present','qr')");
  $ins->execute([$valid['class_session_id'],$student['id']]);
  audit_log($pdo,'attendance_qr','Ҳозиршавӣ дар дарси '.$valid['lesson_title']);
  $pdo->commit();
  json_response(['success'=>true,'message'=>'Ҳозиршавии шумо бомуваффақият сабт шуд.']);
} catch(Throwable $e) {
  if($pdo->inTransaction()) $pdo->rollBack();
  if($e instanceof PDOException && $e->getCode()==='23000') json_response(['success'=>true,'already'=>true,'message'=>'Шумо аллакай дар ин дарс ҳозир ҳисоб шудаед.']);
  json_response(['success'=>false,'message'=>'Хатои дохилии сервер. Баъдтар такрор кунед.'],500);
}
