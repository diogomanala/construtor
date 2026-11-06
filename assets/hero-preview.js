(() => {
  "use strict";
  const root = document.querySelector("[data-od-showcase]");
  if (!root) return;
  const tabs = Array.from(root.querySelectorAll("[data-od-tab]"));
  const views = Array.from(root.querySelectorAll("[data-od-view]"));
  const tracks = Array.from(root.querySelectorAll("[data-od-track]"));
  const counter = root.querySelector("[data-od-counter]");
  const reduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let step = 0, timer = null, visible = true, paused = false;
  const DURATION = 5400;
  function show(index) {
    step = ((index % views.length) + views.length) % views.length;
    tabs.forEach((tab, i) => {
      const active = i === step;
      tab.classList.toggle("is-active", active);
      tab.setAttribute("aria-selected", active ? "true" : "false");
    });
    views.forEach((view, i) => {
      const active = i === step;
      view.hidden = !active;
      view.classList.toggle("is-active", active);
    });
    tracks.forEach((track, i) => {
      track.classList.remove("is-active", "is-complete");
      if (i < step) track.classList.add("is-complete");
      if (i === step) {
        void track.offsetWidth;
        track.classList.add("is-active");
      }
    });
    if (counter) counter.textContent = String(step + 1).padStart(2,"0");
  }
  function stop() { if (timer !== null) { window.clearInterval(timer); timer = null; } }
  function resume() {
    stop();
    if (reduced || !visible || paused || document.hidden) return;
    timer = window.setInterval(() => show(step + 1), DURATION);
  }
  tabs.forEach((tab, i) => tab.addEventListener("click", () => {
    show(i);
    resume();
  }));
  root.addEventListener("mouseenter", () => { paused = true; stop(); });
  root.addEventListener("mouseleave", () => { paused = false; resume(); });
  root.addEventListener("focusin", () => { paused = true; stop(); });
  root.addEventListener("focusout", e => {
    if (!root.contains(e.relatedTarget)) { paused = false; resume(); }
  });
  document.addEventListener("visibilitychange", resume);
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(entries => {
      visible = entries[0].isIntersecting;
      resume();
    }, { threshold: 0.25 });
    observer.observe(root);
  }
  show(0);
  resume();
})();
