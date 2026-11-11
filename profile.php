<?php
require_once __DIR__.'/config/auth.php'; require_login();
$me=current_user();$ok='';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$old=$_POST['old_password']??'';$new=$_POST['new_password']??'';$confirm=$_POST['confirm_password']??'';
 $st=$pdo->prepare('SELECT password_hash FROM users WHERE id=?');$st->execute([$me['id']]);$hash=$st->fetchColumn();
 if(!$hash||!password_verify($old,$hash))$err='Пароли ҷорӣ нодуруст аст.';
 elseif(strlen($new)<10||!preg_match('/[A-ZА-Я]/u',$new)||!preg_match('/[a-zа-я]/u',$new)||!preg_match('/\d/',$new))$err='Пароли нав бояд камаш 10 аломат, ҳарфи калон, хурд ва рақам дошта бошад.';
 elseif($new!==$confirm)$err='Паролҳо мувофиқат намекунанд.';
 else{$pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$me['id']]);audit_log($pdo,'password_change','Парол иваз карда шуд');$ok='Парол бомуваффақият иваз карда шуд.';}
}
$pageTitle='Профили ман';require __DIR__.'/partials/header.php';
?>
<div class="content-card" style="max-width:650px"><h3>Маълумоти ҳисоб</h3><p class="muted">Ному насаб: <strong><?=e($me['full_name'])?></strong></p><p class="muted">Номи корбар: <strong><?=e($me['username'])?></strong></p><p class="muted">Нақш: <strong><?=e($me['role']==='admin'?'Администратор':($me['role']==='teacher'?'Омӯзгор':'Донишҷӯ'))?></strong></p>
<?php if($ok):?><div class="alert alert-success"><?=e($ok)?></div><?php endif;?><?php if($err):?><div class="alert alert-danger"><?=e($err)?></div><?php endif;?>
<hr><h4 class="h5">Иваз кардани парол</h4><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label class="form-label">Пароли ҷорӣ</label><input class="form-control mb-3" type="password" name="old_password" required><label class="form-label">Пароли нав</label><input class="form-control mb-3" type="password" name="new_password" minlength="10" required><label class="form-label">Такрори пароли нав</label><input class="form-control mb-3" type="password" name="confirm_password" minlength="10" required><button class="btn btn-primary">Нав кардани парол</button></form></div>
<?php require __DIR__.'/partials/footer.php'; ?>
