/* Motor do hero: começa parado (estado estático), liga depois do load,
   pausa fora da tela e respeita movimento reduzido. ~1 KB. */
(function(){
  var stage=document.querySelector('[data-od-motion] #odStage');
  if(!stage) return;
  var btn=document.querySelector('[data-od-motion] #odToggle');
  var rm=window.matchMedia?window.matchMedia('(prefers-reduced-motion: reduce)'):{matches:false};
  var inView=true,userPaused=false,started=false;

  function measure(){
    var t=stage.querySelector('.od-typed');
    if(!t) return;
    var fs=parseFloat(getComputedStyle(t).fontSize)||10;
    t.style.setProperty('--tw',(t.scrollWidth/fs+.05).toFixed(3)+'em');
  }
  function sync(){
    stage.classList.toggle('is-paused',userPaused||!inView||document.hidden);
    if(btn){
      btn.setAttribute('aria-pressed',userPaused?'true':'false');
      btn.setAttribute('aria-label',userPaused?'Retomar animação':'Pausar animação');
    }
  }
  function start(){
    if(started||rm.matches) return;
    started=true;
    var ready=(document.fonts&&document.fonts.ready)?document.fonts.ready:Promise.resolve();
    ready.then(function(){measure();stage.classList.add('is-playing');sync();});
  }
  if('IntersectionObserver' in window){
    new IntersectionObserver(function(e){inView=e[0].isIntersecting;sync();},{threshold:.15}).observe(stage);
  }
  document.addEventListener('visibilitychange',sync);
  if(btn) btn.addEventListener('click',function(){userPaused=!userPaused;sync();});
  if(rm.addEventListener) rm.addEventListener('change',function(e){
    if(e.matches){stage.classList.remove('is-playing');started=false;}else{start();}
  });
  if(document.readyState==='complete') setTimeout(start,350);
  else window.addEventListener('load',function(){setTimeout(start,350);});
})();
