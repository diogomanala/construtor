(() => {
  'use strict';
  const root = document.querySelector('[data-od360-page]');
  if (!root) return;
  const search = root.querySelector('[data-od360-search]');
  const filter = root.querySelector('[data-od360-filter]');
  const cards = Array.from(root.querySelectorAll('[data-od360-card]'));
  const groups = Array.from(root.querySelectorAll('[data-od360-group]'));
  const results = root.querySelector('[data-od360-results]');
  const empty = root.querySelector('[data-od360-empty]');
  const normalise = value => (value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
  function matchesStatus(card, mode) {
    const status = card.dataset.status || 'nao_avaliado';
    if (mode === 'todos') return true;
    if (mode === 'atencao') return status === 'pendente' || status === 'em_andamento';
    return status === mode;
  }
  function applyFilters() {
    const needle = normalise(search?.value);
    const mode = filter?.value || 'todos';
    let visible = 0;
    for (const card of cards) {
      const ok = matchesStatus(card, mode) && normalise(card.dataset.query).includes(needle);
      card.hidden = !ok;
      if (ok) visible++;
    }
    for (const group of groups) {
      const groupVisible = Array.from(group.querySelectorAll('[data-od360-card]')).some(card => !card.hidden);
      group.hidden = !groupVisible;
      if (needle || mode !== 'todos') group.open = groupVisible;
    }
    if (results) results.textContent = `${visible} ${visible === 1 ? 'item encontrado' : 'itens encontrados'}`;
    if (empty) empty.hidden = visible !== 0;
  }
  search?.addEventListener('input', applyFilters);
  filter?.addEventListener('change', applyFilters);
  // Cada chip da área abre o grupo mesmo se ele estiver recolhido.
  root.querySelectorAll('[data-od360-area-link]').forEach(link => link.addEventListener('click', () => {
    if (filter) filter.value = 'todos';
    if (search) search.value = '';
    applyFilters();
    const target = document.getElementById((link.getAttribute('href') || '').slice(1));
    if (target?.tagName === 'DETAILS') target.open = true;
  }));
  function revealHash() {
    const id = location.hash.slice(1);
    if (!/^B(?:0[1-9]|[1-3][0-9]|4[0-5])$/.test(id)) return;
    const target = document.getElementById(id);
    if (!target) return;
    if (filter) filter.value = 'todos';
    if (search) search.value = '';
    applyFilters();
    const section = target.closest('details');
    if (section) section.open = true;
    requestAnimationFrame(() => target.scrollIntoView({block:'start',behavior:'auto'}));
  }
  window.addEventListener('hashchange', revealHash);
  if (location.hash) revealHash();
  // Feedback de preenchimento: a decisão é sempre humana, não calculada pela interface.
  root.querySelectorAll('[data-od360-form]').forEach(form => {
    const state = form.querySelector('[data-od360-status]');
    const evidence = form.querySelector('[data-od360-evidence]');
    function sync() {
      const required = state?.value === 'verificado' || state?.value === 'nao_aplicavel';
      if (evidence) evidence.required = !!required;
      const card = form.closest('[data-od360-card]');
      const badge = card?.querySelector('[data-od360-current]');
      if (badge && state && state.value !== card.dataset.status) {
        badge.textContent = 'Alterado, ainda não salvo';
      }
    }
    state?.addEventListener('change', sync);
    sync();
  });
})();
