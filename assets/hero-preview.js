(() => {
  "use strict";
  const root = document.querySelector("[data-od-showcase]");
  if (!root) return;
  const $ = selector => root.querySelector(selector);
  const $$ = selector => Array.from(root.querySelectorAll(selector));
  const tabs = $$("[data-od-tab]");
  const views = $$("[data-od-view]");
  const tracks = $$("[data-od-track]");
  const device = $(".od-device");
  const pointer = $(".od-demo-pointer");
  const reduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const DURATION = 6400;
  let step = 0, sequence = 0, visible = true, timeouts = [], raf = null, typing = null;
  function setText(selector, value) { const target = $(selector); if (target) target.textContent = value; }
  function later(ms, run, ticket) {
    timeouts.push(window.setTimeout(() => { if (ticket === sequence) run(); }, ms));
  }
  function cancel() {
    sequence += 1;
    timeouts.forEach(window.clearTimeout);
    timeouts = [];
    if (typing !== null) { window.clearInterval(typing); typing = null; }
    if (raf !== null) { window.cancelAnimationFrame(raf); raf = null; }
    if (pointer) {
      pointer.classList.remove("is-visible", "is-clicking");
      pointer.style.transition = "none";
      pointer.style.transform = "translate3d(0,0,0)";
    }
  }
  function moveTo(target, clickAfter = false) {
    if (!pointer || !device || !target) return;
    const outer = device.getBoundingClientRect(), rect = target.getBoundingClientRect();
    const x = Math.max(8, rect.left - outer.left + rect.width * .62);
    const y = Math.max(14, rect.top - outer.top + rect.height * .50);
    pointer.style.transition = "transform .63s cubic-bezier(.16,.74,.28,1), opacity .18s ease";
    pointer.classList.add("is-visible");
    pointer.style.transform = "translate3d(" + x.toFixed(1) + "px," + y.toFixed(1) + "px,0)";
    if (clickAfter) {
      const ticket = sequence;
      later(670, () => {
        pointer.classList.remove("is-clicking");
        void pointer.offsetWidth;
        pointer.classList.add("is-clicking");
        target.classList.add("od-target-active");
        later(440, () => { pointer.classList.remove("is-clicking"); target.classList.remove("od-target-active"); }, ticket);
      }, ticket);
    }
  }
  function setStatus(row, index) {
    row.classList.add("od-animated-in");
    const chip = row.querySelector(".od-chip"), mark = row.querySelector(".od-board-check");
    if (!chip || !mark) return;
    if (index === 0) {
      chip.textContent = "Concluído"; chip.className = "od-chip od-chip-done";
      mark.textContent = "✓"; mark.className = "od-board-check od-checked";
    } else if (index === 1) {
      chip.textContent = "Em revisão"; chip.className = "od-chip od-chip-review";
      mark.textContent = "◷"; mark.className = "od-board-check od-review";
    } else {
      chip.textContent = "Acompanhar"; chip.className = "od-chip od-chip-pending";
      mark.textContent = "·"; mark.className = "od-board-check od-pending";
    }
  }
  function reset(index) {
    $$(".od-kit-tile").forEach(node => node.classList.remove("od-animated-in", "od-selected", "od-target-active"));
    const file = $(".od-kit-file"); if (file) file.classList.remove("od-animated-in","od-target-active","od-opened");
    setText("[data-od-typing]", "");
    setText("[data-od-fieldcount]", "0 de 3");
    setText("[data-od-savestatus]", "Personalizando minuta...");
    $$(".od-doc-bar").forEach(node => node.classList.remove("od-animated-in"));
    const name = $(".od-doc-name"); if (name) name.classList.remove("od-is-saved");
    const fill = $(".od-progress-line i"); if (fill) fill.style.width = "0%";
    const save = $("[data-od-save]"), exportBtn = $("[data-od-export]");
    if (save) save.classList.remove("od-saved","od-target-active");
    if (exportBtn) exportBtn.classList.remove("od-ready","od-target-active");
    $$(".od-board-row").forEach(row => {
      row.classList.remove("od-animated-in", "od-target-active");
      const chip = row.querySelector(".od-chip"), mark = row.querySelector(".od-board-check");
      if (chip) { chip.textContent = "Pendente"; chip.className = "od-chip od-chip-pending"; }
      if (mark) { mark.textContent = "·"; mark.className = "od-board-check od-board-check-idle"; }
    });
    const meter = $("[data-od-board-meter]"); if (meter) meter.style.width = "0%";
    setText("[data-od-done]", "0 de 3 etapas");
    tracks.forEach((node, i) => {
      node.classList.toggle("is-active", i === index);
      node.classList.toggle("is-complete", i < index);
      const bar = node.querySelector("i"); if (bar) bar.style.transform = "scaleX(" + (i < index ? 1 : 0) + ")";
    });
  }
  function finalState(index) {
    if (index === 0) {
      $$(".od-kit-tile").forEach(node => node.classList.add("od-animated-in"));
      const file = $(".od-kit-file"); if(file) file.classList.add("od-animated-in");
    } else if (index === 1) {
      setText("[data-od-typing]", "Clínica Exemplo");
      setText("[data-od-fieldcount]", "3 de 3");
      setText("[data-od-savestatus]", "Minuta salva para revisão do RT");
      $$(".od-doc-bar").forEach(node => node.classList.add("od-animated-in"));
      const fill = $(".od-progress-line i"); if(fill) fill.style.width = "100%";
      const save = $("[data-od-save]"); if (save) { save.classList.add("od-saved"); save.textContent = "✓ Versão salva"; }
      const exp = $("[data-od-export]"); if (exp) exp.classList.add("od-ready");
    } else {
      $$(".od-board-row").forEach((node,i) => setStatus(node,i));
      const meter = $("[data-od-board-meter]"); if (meter) meter.style.width = "100%";
      setText("[data-od-done]", "3 etapas organizadas");
    }
    const bar = tracks[index]?.querySelector("i");
    if (bar) bar.style.transform = "scaleX(1)";
  }
  function play(index) {
    cancel();
    step = (index + views.length) % views.length;
    const ticket = sequence;
    tabs.forEach((tab, i) => {
      const isCurrent = i === step;
      tab.classList.toggle("is-active", isCurrent);
      tab.setAttribute("aria-selected", isCurrent ? "true" : "false");
      tab.setAttribute("tabindex", isCurrent ? "0" : "-1");
    });
    views.forEach((view, i) => {
      const isCurrent = i === step;
      view.hidden = !isCurrent;
      view.classList.toggle("is-active", isCurrent);
    });
    setText("[data-od-counter]", String(step + 1).padStart(2, "0"));
    reset(step);
    const save = $("[data-od-save]"); if (save) save.textContent = "Salvar versão";
    if (reduced || !visible || document.hidden) { finalState(step); return; }
    const t0 = performance.now();
    function progress(now) {
      if (ticket !== sequence) return;
      const pct = Math.min(1, (now - t0) / DURATION);
      const bar = tracks[step]?.querySelector("i");
      if (bar) bar.style.transform = "scaleX(" + pct.toFixed(3) + ")";
      if (pct < 1) raf = window.requestAnimationFrame(progress);
    }
    raf = window.requestAnimationFrame(progress);
    if (step === 0) {
      const tiles = $$(".od-kit-tile");
      tiles.forEach((node, i) => later(350 + i * 350, () => node.classList.add("od-animated-in"), ticket));
      later(1740, () => moveTo(tiles[0], true), ticket);
      later(2550, () => tiles[0]?.classList.add("od-selected"), ticket);
      later(3000, () => { const file = $(".od-kit-file"); if (file) file.classList.add("od-animated-in"); }, ticket);
      later(3500, () => moveTo($(".od-kit-file"), true), ticket);
      later(4720, () => { const file = $(".od-kit-file"); if (file) file.classList.add("od-opened"); }, ticket);
    } else if (step === 1) {
      $$(".od-doc-bar").forEach((node, i) => later(350 + i * 260, () => node.classList.add("od-animated-in"), ticket));
      later(700, () => moveTo($(".od-doc-name"), true), ticket);
      later(1220, () => {
        const input = $("[data-od-typing]"); if (!input) return;
        const word = "Clínica Exemplo";
        let position = 0;
        typing = window.setInterval(() => {
          if (ticket !== sequence) { window.clearInterval(typing); typing=null; return; }
          input.textContent = word.slice(0, ++position);
          if (position === word.length) {
            window.clearInterval(typing); typing = null;
            setText("[data-od-fieldcount]", "1 de 3");
          }
        }, 78);
      }, ticket);
      later(2750, () => setText("[data-od-fieldcount]", "2 de 3"), ticket);
      later(3290, () => { setText("[data-od-fieldcount]", "3 de 3"); const fill = $(".od-progress-line i"); if (fill) fill.style.width = "100%"; }, ticket);
      later(3940, () => moveTo($("[data-od-save]"), true), ticket);
      later(4740, () => {
        const btn = $("[data-od-save]"); if (btn) { btn.classList.add("od-saved"); btn.textContent = "✓ Versão salva"; }
        setText("[data-od-savestatus]", "Minuta salva para revisão do RT");
        const name = $(".od-doc-name"); if (name) name.classList.add("od-is-saved");
      }, ticket);
      later(5220, () => { const btn = $("[data-od-export]"); if(btn) btn.classList.add("od-ready"); moveTo(btn, true); }, ticket);
    } else {
      const rows = $$(".od-board-row");
      rows.forEach((node,i) => later(650 + i*1240, () => {
        moveTo(node, true);
        setStatus(node, i);
        const meter = $("[data-od-board-meter]"); if(meter) meter.style.width = String(Math.round((i+1)/3*100)) + "%";
        setText("[data-od-done]", String(i+1) + " de 3 etapas");
      }, ticket));
    }
    later(DURATION + 300, () => { if(visible && !document.hidden) play(step+1); }, ticket);
  }
  tabs.forEach((tab,i) => {
    tab.addEventListener("click", () => play(i));
    tab.addEventListener("keydown", event => {
      if (event.key !== "ArrowRight" && event.key !== "ArrowLeft") return;
      event.preventDefault();
      const next = (i + (event.key === "ArrowRight" ? 1 : views.length-1)) % views.length;
      tabs[next].focus(); play(next);
    });
  });
  document.addEventListener("visibilitychange", () => {
    if (document.hidden) cancel();
    else if (visible) play(step);
  });
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(entries => {
      const current = entries[0]?.isIntersecting ?? true;
      if (visible === current) return;
      visible = current;
      if (current) play(step);
      else cancel();
    }, {threshold:.20});
    observer.observe(root);
  }
  play(0);
})();