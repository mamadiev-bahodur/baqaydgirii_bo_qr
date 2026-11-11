<?php
require_once __DIR__ . '/config/auth.php';
require_role('teacher');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
verify_csrf();$id=(int)($_POST['session_id']??0);
$pdo->prepare("UPDATE class_sessions SET status='closed',closed_at=NOW() WHERE id=? AND teacher_id=?")->execute([$id,current_user()['id']]);
$pdo->prepare('DELETE FROM qr_tokens WHERE class_session_id=?')->execute([$id]);
audit_log($pdo,'session_close','Дарс баста шуд: '.$id);
header('Location: teacher.php');exit;
