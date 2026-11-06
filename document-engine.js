(() => {
  'use strict';
  const root = document.getElementById('odx-form');
  const filterButtons = document.querySelectorAll('[data-odx-filter]');
  if (filterButtons.length) {
    const cards = document.querySelectorAll('[data-odx-category]');
    filterButtons.forEach(button => button.addEventListener('click', () => {
      filterButtons.forEach(el => el.classList.toggle('is-selected', el === button));
      const selected = button.dataset.odxFilter;
      cards.forEach(card => { card.hidden = selected !== 'all' && card.dataset.odxCategory !== selected; });
    }));
  }
  if (!root) return;
  const fields = [...root.querySelectorAll('[data-odx-field]')];
  const documentBody = document.getElementById('odx-document-body');
  const hiddenHtml = document.getElementById('odx-body-html');
  const toggle = document.getElementById('odx-edit-toggle');
  const outlineToggle = document.getElementById('odx-sections-toggle');
  const outline = document.getElementById('odx-outline');
  const outlineList = root.querySelector('[data-odx-outline]');
  const progressBar = root.querySelector('[data-odx-progress]');
  const progressLabel = root.querySelector('[data-odx-progress-label]');
  let dirty = false;
  const sync = () => {
    let filled = 0;
    fields.forEach(input => {
      const value = input.value.trim();
      if (value) filled++;
      root.querySelectorAll('[data-odx-display]').forEach(display => {
        if (display.dataset.odxDisplay !== input.dataset.odxField) return;
        display.textContent = value || 'A preencher';
        display.classList.toggle('odx-empty', !value);
      });
    });
    if (progressBar) progressBar.style.width = Math.round(filled / (fields.length || 1) * 100) + '%';
    if (progressLabel) progressLabel.textContent = filled + ' de ' + fields.length;
  };
  fields.forEach(input => input.addEventListener('input', () => { dirty = true; sync(); }));
  sync();
  if (toggle && documentBody) {
    toggle.addEventListener('click', () => {
      const next = documentBody.getAttribute('contenteditable') !== 'true';
      documentBody.setAttribute('contenteditable', String(next));
      toggle.classList.toggle('is-active', next);
      toggle.setAttribute('aria-pressed', String(next));
      toggle.textContent = next ? 'Concluir edição' : 'Editar texto';
      if (next) documentBody.focus();
    });
    documentBody.addEventListener('input', () => { dirty = true; });
  }
  const headings = documentBody ? [...documentBody.querySelectorAll('h2,h3')] : [];
  headings.forEach((heading, i) => {
    const id = 'odx-section-' + (i + 1);
    heading.id = id;
    const item = document.createElement('li');
    const a = document.createElement('a');
    a.href = '#' + id;
    a.textContent = heading.textContent?.trim() || 'Seção ' + (i + 1);
    item.appendChild(a);
    outlineList?.appendChild(item);
  });
  if (outlineToggle && outline) outlineToggle.addEventListener('click', () => {
    outline.hidden = !outline.hidden;
    outlineToggle.classList.toggle('is-active', !outline.hidden);
  });
  root.addEventListener('submit', event => {
    if (!hiddenHtml || !documentBody) return;
    hiddenHtml.value = documentBody.innerHTML;
    dirty = false;
  });
  window.addEventListener('beforeunload', event => {
    if (!dirty) return;
    event.preventDefault();
    event.returnValue = '';
  });
})();