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
    window.fbq('consent', 'grant');
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

  function showPreferences(footer) {
    if (!footer || banner) return;
    banner = document.createElement('div');
    banner.setAttribute('role', 'group');
    banner.setAttribute('aria-label', 'Preferências de medição');
    banner.style.cssText = 'display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%;margin-top:12px;padding:12px;border:1px solid currentColor;border-radius:8px;font:13px/1.5 system-ui,sans-serif';
    var intro = document.createElement('span');
    intro.textContent = 'Permitir medição de visitas e compras pela Meta?';
    intro.style.cssText = 'flex:1 1 210px';
    banner.appendChild(intro);
    [['0', 'Não permitir'], ['1', 'Permitir medição']].forEach(function (entry) {
      var button = document.createElement('button');
      button.type = 'button';
      button.textContent = entry[1];
      button.style.cssText = 'padding:8px 12px;border:1px solid currentColor;background:transparent;color:inherit;border-radius:7px;cursor:pointer;font:600 13px system-ui';
      button.addEventListener('click', function () {
        var prior = consent();
        setConsent(entry[0]);
        banner.remove();
        banner = null;
        if (entry[0] === '1') startPixel();
        else if (prior === '1') window.location.reload();
      });
      banner.appendChild(button);
    });
    footer.appendChild(banner);
  }

  function init() {
    var footer = document.querySelector('.site-footer .footer-links') || document.querySelector('footer');
    if (footer) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = consent() === null ? 'Configurar cookies opcionais' : 'Preferências de cookies';
      btn.style.cssText = 'font:inherit;color:inherit;background:none;border:0;text-decoration:underline;cursor:pointer;padding:0 4px';
      btn.addEventListener('click', function () {
        if (banner) {
          banner.remove();
          banner = null;
        } else {
          showPreferences(footer);
        }
      });
      footer.appendChild(btn);
    }
    if (consent() === '1') startPixel();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
