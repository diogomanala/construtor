(() => {
  'use strict';
  const root = document.querySelector('[data-od-demo]');
  if (!root) return;
  const $ = (s) => root.querySelector(s);
  const $$ = (s) => Array.from(root.querySelectorAll(s));
  const scenes = $$('[data-od-scene]');
  const navigation = $$('[data-od-nav]');
  const progress = $$('[data-od-progress]');
  const pointer = $('.od-sim-cursor');
  const app = $('.od-app');
  const toggle = $('[data-od-toggle]');
  if (scenes.length !== 3 || navigation.length !== 3 || progress.length !== 3 || !app || !pointer) return;
  root.classList.add('is-enhanced');
  const prefersReduced = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  const lengths = [3900, 4600, 4100];
  let active = 0, serial = 0, paused = false, forceMotion = false, cycle = 0;
  let pending = [], currentRAF = null, typingTimer = null;
  const captions = ['ABRIR UM POP', 'EDITAR E SALVAR', 'ACOMPANHAR PENDÊNCIAS'];
  const labels = ['ETAPA 01/03', 'ETAPA 02/03', 'ETAPA 03/03'];
  const reduced = () => prefersReduced && !forceMotion;
  function schedule(delay, callback) {
    const id = serial;
    pending.push(window.setTimeout(() => { if (serial === id && !paused) callback(); }, delay));
  }
  function clearRunning() {
    serial++;
    pending.forEach(window.clearTimeout); pending = [];
    if (typingTimer !== null) { window.clearInterval(typingTimer); typingTimer = null; }
    if (currentRAF !== null) { window.cancelAnimationFrame(currentRAF); currentRAF = null; }
    pointer.classList.remove('is-visible', 'is-click');
    pointer.style.transition = 'none';
    pointer.style.transform = 'translate3d(0,0,0)';
  }
  function text(selector,value) { const target = $(selector); if (target) target.textContent = value; }
  function reset() {
    $$('.od-folder').forEach(el => el.classList.remove('is-arrived','is-selected'));
    const file = $('[data-od-file]'); if (file) file.classList.remove('is-arrived','is-selected');
    text('[data-od-typed]', '');
    $$('.od-editor-line').forEach(el => el.classList.remove('is-written'));
    $$('.od-editor-check').forEach(el => el.classList.remove('is-done'));
    const save = $('[data-od-save]'); if (save) { save.classList.remove('is-done'); save.textContent='Salvar versão'; }
    const download = $('[data-od-download]'); if(download) download.classList.remove('is-ready');
    const toast = $('[data-od-toast]'); if (toast) toast.classList.remove('is-visible');
    $$('.od-task').forEach(el => {
      el.classList.remove('is-arrived','is-active','is-done');
      const icon = el.querySelector('.od-task-icon'), badge = el.querySelector('em');
      if (icon) icon.textContent = '·';
      if (badge) badge.textContent = 'Pendente';
    });
    const gauge = $('[data-od-meter]'); if(gauge) gauge.style.width='0%';
    text('[data-od-meter-value]','0');
  }
  function cursor(target, click = false) {
    if(!target || reduced())return;
    const base = app.getBoundingClientRect(), r = target.getBoundingClientRect();
    const x = r.left - base.left + Math.min(r.width*.58, r.width-4);
    const y = r.top - base.top + Math.min(r.height*.62, r.height-4);
    pointer.style.transition = 'transform .63s cubic-bezier(.16,.77,.2,1),opacity .16s';
    pointer.classList.add('is-visible');
    pointer.style.transform = `translate3d(${x.toFixed(1)}px,${y.toFixed(1)}px,0)`;
    if(click) schedule(700, () => {
      pointer.classList.remove('is-click'); void pointer.offsetWidth;
      pointer.classList.add('is-click');
    });
  }
  function completedState(step) {
    if(step===0) {
      $$('.od-folder').forEach(el=>el.classList.add('is-arrived'));
      $('[data-od-file]').classList.add('is-arrived');
    } else if(step===1) {
      text('[data-od-typed]', 'Clínica Exemplo');
      $$('.od-editor-line').forEach(el=>el.classList.add('is-written'));
      $$('.od-editor-check').forEach(el=>el.classList.add('is-done'));
      $('[data-od-save]').textContent='✓ Versão salva';
      $('[data-od-save]').classList.add('is-done');
      $('[data-od-download]').classList.add('is-ready');
      $('[data-od-toast]').classList.add('is-visible');
    } else {
      $$('.od-task').forEach((el,i)=>{
        el.classList.add('is-arrived');
        if(i===0){el.classList.add('is-done');el.querySelector('.od-task-icon').textContent='✓';el.querySelector('em').textContent='Concluído';}
        else if(i===1){el.classList.add('is-active');el.querySelector('.od-task-icon').textContent='◷';el.querySelector('em').textContent='Em revisão';}
        else{el.querySelector('em').textContent='A acompanhar';}
      });
      $('[data-od-meter]').style.width='67%';
      text('[data-od-meter-value]','2');
    }
    progress[step].querySelector('i').style.width='100%';
  }
  function animateTimer(step, token) {
    const started=performance.now();
    const length=lengths[step];
    function tick(now){
      if(serial!==token||paused)return;
      const ratio=Math.min(1,(now-started)/length);
      const fill=progress[step].querySelector('i');
      if(fill)fill.style.width=(ratio*100).toFixed(1)+'%';
      if(ratio<1)currentRAF=window.requestAnimationFrame(tick);
    }
    currentRAF=window.requestAnimationFrame(tick);
  }
  function sequenceStep(step) {
    if(step===0){
      const folders=$$('.od-folder');
      folders.forEach((el,i)=>schedule(120+i*300,()=>el.classList.add('is-arrived')));
      schedule(1200,()=>cursor(folders[0],true));
      schedule(2020,()=>folders[0].classList.add('is-selected'));
      schedule(2300,()=>{const f=$('[data-od-file]');f.classList.add('is-arrived');cursor(f,true);});
      schedule(3180,()=> $('[data-od-file]').classList.add('is-selected'));
    }else if(step===1){
      schedule(180,()=>cursor($('.od-editor-field'),true));
      schedule(690,()=>{
        const target=$('[data-od-typed]');let count=0;const content='Clínica Exemplo', token=serial;
        typingTimer=window.setInterval(()=>{
          if(token!==serial){window.clearInterval(typingTimer);typingTimer=null;return;}
          target.textContent=content.slice(0,++count);
          if(count===content.length){window.clearInterval(typingTimer);typingTimer=null;}
        },76);
      });
      schedule(1490,()=>$('.od-editor-line-one').classList.add('is-written'));
      schedule(1830,()=>$('.od-editor-line-two').classList.add('is-written'));
      $$('.od-editor-check').forEach((el,i)=>schedule(2190+i*340,()=>el.classList.add('is-done')));
      schedule(2920,()=>cursor($('[data-od-save]'),true));
      schedule(3580,()=>{
        const button=$('[data-od-save]');button.classList.add('is-done');button.textContent='✓ Versão salva';
        $('[data-od-toast]').classList.add('is-visible');
      });
      schedule(3850,()=>{const target=$('[data-od-download]');target.classList.add('is-ready');cursor(target,true);});
    }else{
      const tasks=$$('.od-task');
      tasks.forEach((el,i)=>schedule(200+i*260,()=>el.classList.add('is-arrived')));
      schedule(1040,()=>{
        cursor(tasks[0],true);tasks[0].classList.add('is-done');
        tasks[0].querySelector('.od-task-icon').textContent='✓';tasks[0].querySelector('em').textContent='Concluído';
        $('[data-od-meter]').style.width='34%';text('[data-od-meter-value]','1');
      });
      schedule(2090,()=>{
        cursor(tasks[1],true);tasks[1].classList.add('is-active');
        tasks[1].querySelector('.od-task-icon').textContent='◷';tasks[1].querySelector('em').textContent='Em revisão';
        $('[data-od-meter]').style.width='67%';text('[data-od-meter-value]','2');
      });
      schedule(3110,()=>{cursor(tasks[2],true);tasks[2].querySelector('em').textContent='A acompanhar';});
    }
  }
  function show(step, immediate = false){
    clearRunning();active=(step+3)%3;root.dataset.step=String(active);
    navigation.forEach((el,i)=>{
      const activeNode=i===active;el.classList.toggle('is-active',activeNode);
      el.setAttribute('aria-current',activeNode?'step':'false');
    });
    scenes.forEach((el,i)=>el.classList.toggle('is-current',i===active));
    progress.forEach((el,i)=>{
      el.classList.toggle('is-done',i<active);
      el.classList.toggle('is-current',i===active);
      const fill=el.querySelector('i');if(fill)fill.style.width=i<active?'100%':'0%';
    });
    text('[data-od-stage-counter]',labels[active]);
    text('[data-od-stage-caption]',captions[active]);
    reset();
    if(immediate||reduced()){completedState(active);return;}
    const token=serial;
    window.requestAnimationFrame(()=>{if(serial===token)animateTimer(active,token);});
    sequenceStep(active);
    schedule(lengths[active]+260,()=>{
      if(active===2)cycle++;
      root.dataset.cycle=String(cycle);
      show(active+1);
    });
  }
  function pause(){ paused=true;clearRunning();root.classList.add('is-paused');if(toggle){toggle.textContent='Reproduzir';toggle.setAttribute('aria-label','Reproduzir animação');} }
  function resume(){ paused=false;root.classList.remove('is-paused'); const message=root.querySelector('.od-demo-caption>span:nth-child(2)');if(message)message.textContent='Demonstração automática de como usar o kit';if(toggle){toggle.textContent='Pausar';toggle.setAttribute('aria-label','Pausar animação');}show(active); }
  navigation.forEach((button,i)=>button.addEventListener('click',()=>{paused=false;root.classList.remove('is-paused');if(toggle)toggle.textContent='Pausar';show(i);}));
  if(toggle)toggle.addEventListener('click',()=>{
    if(paused){forceMotion=true;resume();}else pause();
  });
  document.addEventListener('visibilitychange',()=>{
    if(document.hidden){clearRunning();}
    else if(!paused)show(active);
  });
  if(reduced()) { paused=true; root.classList.add('is-paused'); if(toggle){toggle.textContent='Reproduzir';toggle.setAttribute('aria-label','Reproduzir animação');} const message = root.querySelector('.od-demo-caption>span:nth-child(2)'); if(message) message.textContent = 'Animação pausada pela preferência de movimento'; show(0,true); }
  else show(0);
})();