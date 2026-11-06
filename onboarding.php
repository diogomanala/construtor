<?php
/** @var array|null $c */
h('Vamos configurar seu consultório');
?>
<p class="lead">São três etapas rápidas para preparar seu painel. Você poderá corrigir essas informações depois.</p>
<div class="onboard" aria-label="Progresso do cadastro"><span class="active"></span><span></span><span></span></div>
<section class="card" style="max-width:810px; margin:0 auto">
<form id="onboarding" method="post" novalidate>
<?= token() ?><input type="hidden" name="act" value="clinic">
<div class="onboard-step active" data-step="0">
<div class="eyebrow">ETAPA 1 DE 3 · IDENTIFICAÇÃO</div>
<h2>Primeiro, vamos conhecer sua clínica.</h2><p class="muted">Usaremos esses dados para identificar o estabelecimento nos modelos documentais.</p>
<div class="two">
<label>Nome do estabelecimento *<input name="name" required maxlength="180" placeholder="Ex.: Clínica Sorriso" value="<?=esc($c['name']??'')?>"></label>
<label>CNPJ<input name="cnpj" maxlength="18" placeholder="00.000.000/0000-00" value="<?=esc($c['cnpj']??'')?>"></label>
<label>Cidade<input name="city" maxlength="120" placeholder="Ex.: Rio de Janeiro" value="<?=esc($c['city']??'')?>"></label>
<label>UF<input name="state" maxlength="2" placeholder="RJ" value="<?=esc($c['state']??'')?>"></label>
</div>
</div>
<div class="onboard-step" data-step="1">
<div class="eyebrow">ETAPA 2 DE 3 · PERFIL OPERACIONAL</div>
<h2>Como funciona seu atendimento?</h2><p class="muted">Essas informações servem de contexto. Elas não determinam, por si só, o enquadramento sanitário.</p>
<label>Quantidade de consultórios/cadeiras<input type="number" name="chairs" min="1" max="200" value="<?=esc($c['chairs']??1)?>" required></label>
<label>Processamento e esterilização<select name="sterilization">
<?php foreach(['interna'=>'Realizado na clínica','terceirizada'=>'Serviço terceirizado','mista'=>'Ambos'] as $k=>$v): ?>
<option value="<?=esc($k)?>" <?=($c['sterilization']??'interna')===$k?'selected':''?>><?=esc($v)?></option>
<?php endforeach; ?>
</select></label>
<div class="checkline"><label><input type="checkbox" name="has_xray" <?=!empty($c['has_xray'])?'checked':''?>> Possui serviço de radiologia odontológica</label></div>
<div class="checkline"><label><input type="checkbox" name="has_sedation" <?=!empty($c['has_sedation'])?'checked':''?>> Realiza procedimentos com sedação</label></div>
<div class="warn">Se houver serviços especiais, o responsável técnico deverá conferir as exigências específicas.</div>
</div>
<div class="onboard-step" data-step="2">
<div class="eyebrow">ETAPA 3 DE 3 · RESPONSÁVEL TÉCNICO</div>
<h2>Quem fará a revisão da documentação?</h2><p class="muted">As minutas precisam ser adaptadas, aprovadas e implementadas pelo profissional responsável.</p>
<div class="two">
<label>Nome do responsável técnico<input name="rt_name" maxlength="150" placeholder="Nome completo" value="<?=esc($c['rt_name']??'')?>"></label>
<label>CRO / UF<input name="rt_cro" maxlength="45" placeholder="Ex.: CRO-RJ 00000" value="<?=esc($c['rt_cro']??'')?>"></label>
</div>
<div class="warn">Ao concluir, seu painel será criado com 17 categorias de acompanhamento. A plataforma não declara a clínica regularizada.</div>
</div>
<div class="onboard-actions"><button type="button" id="backStep" class="back" style="visibility:hidden">Voltar</button><button type="button" id="nextStep">Continuar</button><button id="finishStep" type="submit" style="display:none">Concluir e abrir meu painel</button></div>
</form>
</section>
<script>
(function(){const f=document.getElementById('onboarding');let current=0;const steps=[...f.querySelectorAll('.onboard-step')],bars=[...document.querySelectorAll('.onboard span')],back=document.getElementById('backStep'),next=document.getElementById('nextStep'),finish=document.getElementById('finishStep');
function setStep(n){current=Math.max(0,Math.min(steps.length-1,n));steps.forEach((el,i)=>{el.classList.toggle('active',i===current);el.querySelectorAll('input,select').forEach(input=>input.disabled=i!==current)});bars.forEach((el,i)=>el.classList.toggle('active',i<=current));back.style.visibility=current===0?'hidden':'visible';next.style.display=current===steps.length-1?'none':'inline-flex';finish.style.display=current===steps.length-1?'inline-flex':'none';window.scrollTo({top:0,behavior:'smooth'})}
function validate(){const inputs=[...steps[current].querySelectorAll('input,select')];for(const input of inputs){if(!input.checkValidity()){input.reportValidity();return false}}return true}
next.addEventListener('click',()=>{if(validate())setStep(current+1)});back.addEventListener('click',()=>setStep(current-1));f.addEventListener('submit',()=>{steps.forEach(s=>s.querySelectorAll('input,select').forEach(e=>e.disabled=false))});setStep(0)})();
</script>
<?php foot(); ?>