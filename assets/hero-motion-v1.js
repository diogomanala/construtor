/* Motion do hero do Odonto em Dia.
   Inicializa assim que o DOM estiver pronto, sem aguardar window.load ou fontes externas.
   Para fora da tela, respeita redução de movimento e permite reprodução manual. */
(function () {
  'use strict';
  function init() {
    var root = document.querySelector('[data-od-motion]');
    if (!root || root.dataset.motionReady === '1') return;
    var stage = root.querySelector('#odStage');
    if (!stage) return;
    root.dataset.motionReady = '1';
    var btn = root.querySelector('#odToggle');
    var rm = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : {matches:false};
    var inView = true;
    var userPaused = !!rm.matches;
    var manualMotion = false;
    var observer = null;

    function measure() {
      var label = stage.querySelector('.od-typed');
      if (!label) return;
      var fs = parseFloat(getComputedStyle(label).fontSize) || 10;
      var width = label.scrollWidth / fs + 0.05;
      if (Number.isFinite(width) && width > 0) label.style.setProperty('--tw', width.toFixed(3) + 'em');
    }

    function sync() {
      var paused = userPaused || !inView || document.hidden;
      stage.classList.toggle('is-paused', paused);
      var playing = stage.classList.contains('is-playing') && !paused;
      if (btn) {
        btn.setAttribute('aria-pressed', playing ? 'false' : 'true');
        btn.setAttribute('aria-label', playing ? 'Pausar animação' : 'Reproduzir animação');
        btn.title = playing ? 'Pausar animação' : 'Reproduzir animação';
      }
    }

    function start() {
      stage.classList.add('is-playing');
      stage.classList.toggle('user-motion', manualMotion);
      sync();
    }

    function setMotionPreference() {
      if (rm.matches && !manualMotion) {
        userPaused = true;
        stage.classList.remove('is-playing', 'user-motion');
      } else {
        start();
      }
      sync();
    }

    // Primeira cena sempre visível: não depende de eventos externos de carregamento.
    setMotionPreference();
    measure();
    if (window.requestAnimationFrame) requestAnimationFrame(measure);
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(measure).catch(function () {});
    }

    if ('IntersectionObserver' in window) {
      observer = new IntersectionObserver(function (entries) {
        if (!entries.length) return;
        inView = entries[0].isIntersecting;
        sync();
      }, {threshold:0.08});
      observer.observe(stage);
    }
    document.addEventListener('visibilitychange', sync);
    window.addEventListener('pageshow', function () {
      if (!rm.matches || manualMotion) start();
      measure();
      sync();
    });
    window.addEventListener('resize', measure, {passive:true});

    if (btn) {
      btn.addEventListener('click', function () {
        if (rm.matches && !manualMotion) {
          manualMotion = true;
          userPaused = false;
          start();
        } else {
          userPaused = !userPaused;
          if (!userPaused && !stage.classList.contains('is-playing')) start();
        }
        sync();
      });
    }
    if (rm.addEventListener) {
      rm.addEventListener('change', function () {
        manualMotion = false;
        userPaused = !!rm.matches;
        setMotionPreference();
      });
    } else if (rm.addListener) {
      rm.addListener(function () {
        manualMotion = false;
        userPaused = !!rm.matches;
        setMotionPreference();
      });
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, {once:true});
  } else {
    init();
  }
})();
