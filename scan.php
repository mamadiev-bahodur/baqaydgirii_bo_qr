<?php
require_once __DIR__ . '/config/auth.php';
require_role('student');
$pageTitle='Скан кардани QR';require __DIR__.'/partials/header.php';
?>
<div class="two-col"><div class="content-card"><div class="card-heading"><div><h3>Сканери QR-код</h3><p>Камераро ба QR-коди дар экран буда равона кунед.</p></div><i class="bi bi-camera"></i></div><div id="reader" class="scanner-box"></div><div id="scanResult" class="mt-3"></div><div class="small muted mt-3"><i class="bi bi-info-circle me-1"></i> Барои истифодаи камера сайт бояд дар localhost ё HTTPS кушода шавад.</div></div><div class="content-card"><div class="feature-icon"><i class="bi bi-lightbulb"></i></div><h3>Чӣ тавр скан кардан?</h3><ol class="steps-list"><li>Ба дарси омӯзгор ҳозир шавед.</li><li>QR-кодро дар экрани омӯзгор пайдо кунед.</li><li>Камераро ба рамз равона кунед.</li><li>Натиҷаи сабти ҳозиршавиро интизор шавед.</li></ol><div class="alert alert-info small mb-0">QR-код ҳар 30 сония нав мешавад. Агар рамз муҳлаташ гузашта бошад, рамзи навро скан кунед.</div></div></div>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const resultBox=document.getElementById('scanResult');let submitted=false;
function onScanSuccess(decodedText){
 if(submitted)return;
 submitted=true;
 fetch('record_attendance.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':<?=json_encode(csrf_token())?>},body:JSON.stringify({token:decodedText})})
 .then(r=>r.json()).then(d=>{
   resultBox.innerHTML='<div class="alert '+(d.success?'alert-success':'alert-warning')+'">'+d.message+'</div>';
   if(d.success){try{scanner.pause(true)}catch(e){}}
   else submitted=false;
 }).catch(()=>{resultBox.innerHTML='<div class="alert alert-danger">Хатои пайвастшавӣ. Аз нав кӯшиш кунед.</div>';submitted=false;});
}
const scanner=new Html5QrcodeScanner('reader',{fps:10,qrbox:{width:230,height:230},rememberLastUsedCamera:true},false);
scanner.render(onScanSuccess,()=>{});
</script>
<?php require __DIR__.'/partials/footer.php'; ?>
