<?php
require_once __DIR__ . '/config/auth.php';
require_role('teacher');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
if($_SERVER['REQUEST_METHOD']!=='POST') json_response(['error'=>'Методи иҷозат нест'],405);
verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$id=(int)($_POST['session_id']??0);$me=current_user();
$stmt=$pdo->prepare("SELECT id FROM class_sessions WHERE id=? AND teacher_id=? AND status='active'");
$stmt->execute([$id,$me['id']]);
if(!$stmt->fetch()){json_response(['error'=>'Дарс фаъол нест'],404);}
$pdo->prepare('DELETE FROM qr_tokens WHERE class_session_id=?')->execute([$id]);
$raw=bin2hex(random_bytes(32));
$hash=hash('sha256',$raw);
$expires=(new DateTimeImmutable('+30 seconds'))->format('Y-m-d H:i:s');
$pdo->prepare("INSERT INTO qr_tokens(class_session_id,token_hash,expires_at) VALUES(?,?,?)")->execute([$id,$hash,$expires]);
json_response(['token'=>$raw,'expires_at'=>$expires]);
