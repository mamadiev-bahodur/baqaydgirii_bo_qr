<?php
require_once __DIR__ . '/config/auth.php';
require_role('admin');
$notice=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $action=$_POST['action']??'';
    try {
        if ($action==='group') {
            $name=trim($_POST['group_name']??'');
            if ($name==='') throw new RuntimeException('Номи гурӯҳро ворид кунед.');
            $pdo->prepare("INSERT INTO student_groups(group_name) VALUES(?)")->execute([$name]);
            $notice='Гурӯҳ илова карда шуд.';
        } elseif ($action==='user') {
            $name=trim($_POST['full_name']??''); $username=trim($_POST['username']??'');
            $password=$_POST['password']??''; $role=$_POST['role']??'student';
            $group=($_POST['group_id']??'')!==''?(int)$_POST['group_id']:null;
            $code=trim($_POST['student_code']??'');
            if ($name===''||$username===''||strlen($password)<8) throw new RuntimeException('Маълумотро пур кунед. Парол бояд камаш 8 аломат бошад.');
            if (!in_array($role,['teacher','student'],true)) throw new RuntimeException('Нақш нодуруст аст.');
            if ($role==='student' && ($code==='' || !$group)) throw new RuntimeException('Рамзи донишҷӯӣ ва гурӯҳро интихоб кунед.');
            $stmt=$pdo->prepare("INSERT INTO users(full_name,username,password_hash,role,student_code,group_id) VALUES(?,?,?,?,?,?)");
            $stmt->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT),$role,$role==='student'?$code:null,$role==='student'?$group:null]);
            $notice='Корбар илова карда шуд.';
        } elseif ($action==='toggle_user') {
            $id=(int)($_POST['id']??0);
            $pdo->prepare("UPDATE users SET is_active=1-is_active WHERE id=? AND role IN ('teacher','student')")->execute([$id]);
            audit_log($pdo,'user_status','Тағйири ҳолати корбар '.$id); $notice='Ҳолати корбар тағйир дода шуд.';
        } elseif ($action==='delete_user') {
            $id=(int)($_POST['id']??0);
            $pdo->prepare("DELETE FROM users WHERE id=? AND role IN ('teacher','student')")->execute([$id]);
            $notice='Корбар нест карда шуд.';
        } elseif ($action==='delete_group') {
            $id=(int)($_POST['id']??0);
            $pdo->prepare("DELETE FROM student_groups WHERE id=?")->execute([$id]);
            $notice='Гурӯҳ нест карда шуд.';
        }
    } catch (Throwable $ex) { $error=$ex instanceof PDOException?'Ин маълумот такрорӣ аст ё бо сабтҳои дигар алоқаманд мебошад.':$ex->getMessage(); }
}
$groups=$pdo->query("SELECT * FROM student_groups ORDER BY group_name")->fetchAll();
$users=$pdo->query("SELECT u.*,g.group_name FROM users u LEFT JOIN student_groups g ON g.id=u.group_id WHERE u.role IN ('teacher','student') ORDER BY u.id DESC")->fetchAll();
$pageTitle='Идоракунии корбарон';
require __DIR__.'/partials/header.php';
?>
<?php if($notice): ?><div class="alert alert-success"><?=e($notice)?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?>
<div class="two-col">
<div class="content-card"><div class="card-heading"><div><h3>Иловаи корбар</h3><p>Донишҷӯ ё омӯзгорро сабт кунед.</p></div><i class="bi bi-person-plus"></i></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="user">
<label class="form-label">Ному насаб</label><input class="form-control mb-3" name="full_name" required>
<label class="form-label">Номи корбар</label><input class="form-control mb-3" name="username" required>
<label class="form-label">Парол (на камтар аз 8 аломат)</label><input class="form-control mb-3" type="password" name="password" minlength="8" required>
<label class="form-label">Нақш</label><select class="form-select mb-3" name="role" id="roleSelect"><option value="student">Донишҷӯ</option><option value="teacher">Омӯзгор</option></select>
<div id="studentFields"><label class="form-label">Рамзи донишҷӯӣ</label><input class="form-control mb-3" name="student_code"><label class="form-label">Гурӯҳ</label><select class="form-select mb-3" name="group_id"><option value="">Интихоб кунед</option><?php foreach($groups as $g):?><option value="<?=$g['id']?>"><?=e($g['group_name'])?></option><?php endforeach;?></select></div>
<button class="btn btn-primary w-100">Сабт кардан</button></form></div>
<div class="content-card"><div class="card-heading"><div><h3>Иловаи гурӯҳ</h3><p>Гурӯҳи нави таълимӣ созед.</p></div><i class="bi bi-diagram-3"></i></div><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="group"><label class="form-label">Номи гурӯҳ</label><input class="form-control mb-3" name="group_name" placeholder="Масалан: 1-400101" required><button class="btn btn-outline-primary w-100">Илова кардани гурӯҳ</button></form><hr><h4 class="h6">Гурӯҳҳои мавҷуда</h4><?php foreach($groups as $g):?><div class="list-line"><span><?=e($g['group_name'])?></span><div class="d-flex gap-2"><a class="icon-btn" href="edit_group.php?id=<?=$g['id']?>" title="Таҳрир"><i class="bi bi-pencil"></i></a><form method="post" onsubmit="return confirm('Гурӯҳ нест карда шавад?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_group"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="icon-btn text-danger"><i class="bi bi-trash"></i></button></form></div></div><?php endforeach;?></div></div>
<div class="content-card mt-4"><div class="section-heading"><div><h3>Рӯйхати корбарон</h3><p>Донишҷӯён ва омӯзгорони сабтшуда</p></div><span class="count-pill"><?=count($users)?> корбар</span></div><div class="table-responsive"><table class="table modern-table"><thead><tr><th>Ному насаб</th><th>Номи корбар</th><th>Нақш</th><th>Рамз / гурӯҳ</th><th>Ҳолат</th><th>Амал</th></tr></thead><tbody><?php foreach($users as $u):?><tr><td><strong><?=e($u['full_name'])?></strong></td><td><?=e($u['username'])?></td><td><span class="role-pill"><?= $u['role']==='teacher'?'Омӯзгор':'Донишҷӯ' ?></span></td><td><?=e($u['student_code']??'—')?> <?= $u['group_name']?' / '.e($u['group_name']):'' ?></td><td><span class="status-pill <?=$u['is_active']?'active':'closed'?>"><?=$u['is_active']?'Фаъол':'Ғайрифаъол'?></span></td><td><div class="d-flex gap-1"><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="btn btn-sm btn-outline-secondary" title="Фаъол/ғайрифаъол"><i class="bi bi-toggle-on"></i></button></form><a class="btn btn-sm btn-outline-primary" href="edit_user.php?id=<?=$u['id']?>" title="Таҳрир"><i class="bi bi-pencil"></i></a><form method="post" onsubmit="return confirm('Корбар нест карда шавад?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="icon-btn text-danger"><i class="bi bi-trash"></i></button></form></div></td></tr><?php endforeach;?></tbody></table></div></div>
<script>document.getElementById('roleSelect').addEventListener('change',e=>{document.getElementById('studentFields').style.display=e.target.value==='student'?'block':'none'});</script>
<?php require __DIR__.'/partials/footer.php'; ?>
