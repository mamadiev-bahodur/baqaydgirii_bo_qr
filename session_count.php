<?php
require_once __DIR__ . '/config/auth.php';
require_role('teacher');
header('Content-Type: application/json; charset=utf-8');
$id=(int)($_GET['id']??0);$me=current_user();
$stmt=$pdo->prepare("SELECT COUNT(a.id) FROM attendance a JOIN class_sessions s ON s.id=a.class_session_id WHERE s.id=? AND s.teacher_id=?");
$stmt->execute([$id,$me['id']]);
echo json_encode(['count'=>(int)$stmt->fetchColumn()]);
