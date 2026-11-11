<?php
require_once __DIR__ . '/config/auth.php';
require_role('teacher');
$id=(int)($_GET['id']??0);$me=current_user();
$stmt=$pdo->prepare("SELECT s.*,g.group_name FROM class_sessions s JOIN student_groups g ON g.id=s.group_id WHERE s.id=? AND s.teacher_id=? AND s.status='active'");
$stmt->execute([$id,$me['id']]);$lesson=$stmt->fetch();
if(!$lesson){http_response_code(404);exit('Дарс ёфт нашуд ё баста шудааст.');}
$pageTitle='QR-коди дарс';require __DIR__.'/partials/header.php';
?>
<div class="qr-layout"><div class="content-card qr-main"><div class="section-heading"><div><span class="status-pill active">ДАРС ФАЪОЛ АСТ</span><h3 class="mt-2"><?=e($lesson['lesson_title'])?></h3><p><?=e($lesson['group_name'])?></p></div><a href="session_report.php?id=<?=$id?>" class="btn btn-outline-primary">Ҳисоботи пурра</a></div><div class="qr-stage"><div id="qrcode" class="qr-code-box"><div class="qr-loading"><div class="spinner-border text-primary"></div><span>QR сохта мешавад...</span></div></div><div class="qr-timer"><span>Рамз баъди</span><strong id="countdown">30</strong><span>сония нав мешавад</span></div><div class="qr-progress"><div id="timerBar"></div></div><p class="muted small mt-3">Донишҷӯён ин рамзро бо телефони худ скан кунанд.</p></div></div><div class="content-card qr-side"><div class="stat-icon green"><i class="bi bi-people"></i></div><span class="muted">Ҳозиршудагон</span><strong class="big-number" id="presentCount">0</strong><p class="muted small">Шумораи донишҷӯёни сабтшуда дар ин дарс</p><hr><div class="d-flex justify-content-between"><span class="muted">Навсозии QR</span><strong>30 сония</strong></div><div class="d-flex justify-content-between mt-2"><span class="muted">Муҳофизат</span><strong class="text-success">Фаъол</strong></div><form method="post" action="close_session.php" class="mt-4" onsubmit="return confirm('Дарсро анҷом медиҳед?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="session_id" value="<?=$id?>"><button class="btn btn-outline-danger w-100">Анҷом додани дарс</button></form></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const sessionId=<?=json_encode($id)?>;const csrf=<?=json_encode(csrf_token())?>;let seconds=30;let qr;
const box=document.getElementById('qrcode');
async function refreshQR(){
  try{
    const response=await fetch('api_qr.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token':csrf},body:'session_id='+encodeURIComponent(sessionId),cache:'no-store'});
    const data=await response.json(); if(!response.ok) throw new Error(data.error||'Хато');
    box.innerHTML=''; qr=new QRCode(box,{text:data.token,width:260,height:260,colorDark:'#101828',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.H});
    seconds=30; document.getElementById('countdown').textContent=seconds;
  }catch(err){box.innerHTML='<div class="alert alert-danger">QR сохта нашуд. Саҳифаро нав кунед.</div>';}
}
async function countPresent(){try{const r=await fetch('session_count.php?id='+sessionId,{cache:'no-store'});const d=await r.json();document.getElementById('presentCount').textContent=d.count??'—';}catch(e){}}
refreshQR();countPresent();
setInterval(()=>{seconds--;document.getElementById('countdown').textContent=Math.max(0,seconds);document.getElementById('timerBar').style.width=(seconds/30*100)+'%';if(seconds<=0)refreshQR();},1000);
setInterval(countPresent,5000);
</script>
<?php require __DIR__.'/partials/footer.php'; ?>
