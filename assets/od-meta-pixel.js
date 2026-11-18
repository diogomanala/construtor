/* Meta Pixel do Odonto em Dia: carregamento somente após consentimento. */
(function () {
  'use strict';
  var ctx = window.odMetaPixelContext;
  if (!ctx || ctx.pixelId !== '1426979209595089') return;
  var cookieName = 'od_meta_ads_consent';
  var initialized = false;
  var banner = null;

  function consent() {
    var m = document.cookie.match(/(?:^|;\s*)od_meta_ads_consent=([01])(?:;|$)/);
    return m ? m[1] : null;
  }

  function setConsent(value) {
    document.cookie = cookieName + '=' + value + '; Max-Age=15552000; Path=/rdc; SameSite=Lax; Secure';
  }

  function startPixel() {
    if (initialized || consent() !== '1') return;
    initialized = true;
    if (!window.fbq) {
      (function (f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function () {
          n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = true;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = true;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s);
      })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    }
    window.fbq('init', ctx.pixelId);
    window.fbq('track', 'PageView');
    if (!ctx.eventName) return;
    if (ctx.eventName === 'Purchase' && ctx.eventId) {
      var key = 'od_meta_purchase_' + ctx.eventId;
      try {
        if (window.localStorage.getItem(key)) return;
        window.localStorage.setItem(key, String(Date.now()));
      } catch (_) { /* Armazenamento indisponível. */ }
    }
    var payload = { currency: 'BRL', value: Number(ctx.value) || 67, content_type: 'product', content_ids: ['kit-rdc'] };
    if (ctx.eventName === 'Purchase' && ctx.eventId) {
      window.fbq('track', ctx.eventName, payload, { eventID: ctx.eventId });
    } else {
      window.fbq('track', ctx.eventName, payload);
    }
  }

  function closeBanner() {
    if (banner) { banner.remove(); banner = null; }
  }

  function showBanner() {
    closeBanner();
    banner = document.createElement('aside');
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-label', 'Preferências de cookies');
    banner.style.cssText = 'position:fixed;z-index:2147483000;bottom:86px;left:14px;right:14px;max-width:540px;margin:auto;padding:16px 18px;background:#10383c;color:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.35);font:14px/1.5 system-ui,sans-serif;text-align:left';
    banner.innerHTML = '<div style="font-weight:700;margin-bottom:5px">Privacidade e anúncios</div>' +
      '<div>Usamos cookies opcionais da Meta para medir visitas e compras. Você pode aceitar ou manter somente os necessários. <a href="?page=politica-privacidade" style="color:#bcf0e5;text-decoration:underline">Saiba mais</a>.</div>' +
      '<div style="display:flex;gap:9px;flex-wrap:wrap;margin-top:12px">' +
      '<button type="button" data-od-ads="0" style="background:#fff;color:#173c40;border:1px solid #d4e8e4;border-radius:8px;padding:9px 13px;cursor:pointer;font:600 13px system-ui">Somente necessários</button>' +
      '<button type="button" data-od-ads="1" style="background:#28b190;color:#061d21;border:0;border-radius:8px;padding:9px 13px;cursor:pointer;font:700 13px system-ui">Aceitar medição</button></div>';
    document.body.appendChild(banner);
    banner.querySelectorAll('[data-od-ads]').forEach(function (button) {
      button.addEventListener('click', function () {
        var value = button.getAttribute('data-od-ads');
        var prior = consent();
        setConsent(value);
        closeBanner();
        if (value === '1') startPixel();
        else if (prior === '1') window.location.reload();
      });
    });
  }

  function init() {
    var footer = document.querySelector('.site-footer .footer-links') || document.querySelector('footer');
    if (footer) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = 'Preferências de cookies';
      btn.style.cssText = 'font:inherit;color:inherit;background:none;border:0;text-decoration:underline;cursor:pointer;padding:0 4px';
      btn.addEventListener('click', showBanner);
      footer.appendChild(btn);
    }
    if (consent() === '1') startPixel();
    else if (consent() === null) showBanner();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
