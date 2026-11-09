/* Odonto em Dia: motion hero em autoplay contínuo (duração definida no CSS).
   Só pausa pelo controle visível ou quando a aba fica em segundo plano.
   Não depende do load total, fontes, IntersectionObserver ou configuração do SO. */
(function () {
  'use strict';
  function init() {
    var root = document.querySelector('[data-od-motion]');
    if (!root || root.dataset.motionReady === '1') return;
    var stage = root.querySelector('#odStage');
    if (!stage) return;
    root.dataset.motionReady = '1';
    var btn = root.querySelector('#odToggle');
    var userPaused = false;

    function measure() {
      var typed = stage.querySelector('.od-typed');
      if (!typed) return;
      var fontSize = parseFloat(getComputedStyle(typed).fontSize) || 10;
      var width = typed.scrollWidth / fontSize + .05;
      if (isFinite(width) && width > 0) typed.style.setProperty('--tw', width.toFixed(3) + 'em');
    }

    function sync() {
      var paused = userPaused || document.hidden;
      stage.classList.toggle('is-paused', paused);
      if (btn) {
        btn.setAttribute('aria-pressed', paused ? 'true' : 'false');
        btn.setAttribute('aria-label', paused ? 'Reproduzir animação' : 'Pausar animação');
        btn.title = paused ? 'Reproduzir animação' : 'Pausar animação';
      }
    }

    // O CSS define a repetição infinita; a cena já começa ativa.
    stage.classList.add('is-playing');
    stage.classList.remove('is-paused', 'user-motion');
    sync();
    measure();
    if (window.requestAnimationFrame) requestAnimationFrame(measure);
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(measure).catch(function () {});
    }

    if (btn) {
      btn.addEventListener('click', function () {
        userPaused = !userPaused;
        sync();
      });
    }
    document.addEventListener('visibilitychange', sync);
    window.addEventListener('pageshow', sync);
    window.addEventListener('resize', measure, {passive:true});
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, {once:true});
  } else {
    init();
  }
})();
