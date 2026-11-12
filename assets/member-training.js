/* Odonto em Dia: carrega YouTube apenas após um clique real do comprador. */
(() => {
  "use strict";
  for (const player of document.querySelectorAll("[data-od-training-video]")) {
    const trigger = player.querySelector("[data-od-training-launch]");
    if (!trigger) continue;
    trigger.addEventListener("click", () => {
      if (player.querySelector("iframe")) return;
      const frame = document.createElement("iframe");
      frame.src = "https://www.youtube-nocookie.com/embed/VQEmTrlaotk?autoplay=1&playsinline=1&controls=1&rel=0&iv_load_policy=3";
      frame.title = "Treinamento completo Odonto em Dia";
      frame.allow = "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen";
      frame.allowFullscreen = true;
      frame.referrerPolicy = "strict-origin-when-cross-origin";
      frame.loading = "eager";
      player.replaceChildren(frame);
    }, { once: true });
  }
})();
