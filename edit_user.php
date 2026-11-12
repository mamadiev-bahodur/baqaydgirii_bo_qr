<?php
require_once __DIR__.'/config/auth.php'; require_role('admin');
$id=(int)($_GET['id']??$_POST['id']??0);$st=$pdo->prepare("SELECT * FROM users WHERE id=? AND role IN ('teacher','student')");$st->execute([$id]);$user=$st->fetch();if(!$user){http_response_code(404);exit('Корбар ёфт нашуд.');}
$groups=$pdo->query('SELECT * FROM student_groups ORDER BY group_name')->fetchAll();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$name=trim($_POST['full_name']??'');$username=trim($_POST['username']??'');$code=trim($_POST['student_code']??'');$group=($_POST['group_id']??'')!==''?(int)$_POST['group_id']:null;$password=$_POST['password']??'';
 if($name===''||$username==='')$error='Ному насаб ва номи корбар ҳатмист.';
 elseif($user['role']==='student'&&($code===''||!$group))$error='Барои донишҷӯ рамз ва гурӯҳ ҳатмист.';
 elseif($password!==''&&strlen($password)<10)$error='Пароли нав бояд камаш 10 аломат дошта бошад.';
 else{try{$sql='UPDATE users SET full_name=?,username=?,student_code=?,group_id=?';$args=[$name,$username,$user['role']==='student'?$code:null,$user['role']==='student'?$group:null];if($password!==''){$sql.=',password_hash=?';$args[]=password_hash($password,PASSWORD_DEFAULT);}$sql.=' WHERE id=?';$args[]=$id;$pdo->prepare($sql)->execute($args);audit_log($pdo,'user_update','Маълумоти корбар тағйир ёфт: '.$id);header('Location: admin.php');exit;}catch(PDOException $e){$error='Номи корбар ё рамзи донишҷӯӣ аллакай истифода мешавад.';}}
}
$pageTitle='Таҳрири корбар';require __DIR__.'/partials/header.php';
?>
<div class="content-card" style="max-width:700px"><h3>Таҳрири маълумоти корбар</h3><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><label class="form-label">Ному насаб</label><input class="form-control mb-3" name="full_name" value="<?=e($user['full_name'])?>" required><label class="form-label">Номи корбар</label><input class="form-control mb-3" name="username" value="<?=e($user['username'])?>" required><?php if($user['role']==='student'):?><label class="form-label">Рамзи донишҷӯӣ</label><input class="form-control mb-3" name="student_code" value="<?=e($user['student_code'])?>" required><label class="form-label">Гурӯҳ</label><select class="form-select mb-3" name="group_id" required><?php foreach($groups as $g):?><option value="<?=$g['id']?>" <?=$user['group_id']==$g['id']?'selected':''?>><?=e($g['group_name'])?></option><?php endforeach;?></select><?php endif;?><label class="form-label">Пароли нав (ихтиёрӣ)</label><input class="form-control mb-3" type="password" name="password" minlength="10" placeholder="Агар иваз кардан нахоҳед, холӣ гузоред"><button class="btn btn-primary">Захира кардан</button> <a class="btn btn-light" href="admin.php">Бекор кардан</a></form></div>
<?php require __DIR__.'/partials/footer.php'; ?>
