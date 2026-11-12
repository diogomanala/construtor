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
  const readOnlyHistory = root.dataset.odxReadonly === 'true';
  const fields = [...root.querySelectorAll('[data-odx-field]')];
  if (readOnlyHistory) {
    fields.forEach(field => { field.readOnly = true; field.setAttribute('aria-readonly', 'true'); });
    const status = root.querySelector('#odx-status'); if (status) status.disabled = true;
    const save = root.querySelector('.odx-save'); if (save) save.hidden = true;
    const toggle = root.querySelector('#odx-edit-toggle'); if (toggle) toggle.hidden = true;
  }
  const documentBody = document.getElementById('odx-document-body');
  const hiddenHtml = document.getElementById('odx-body-html');
  const toggle = document.getElementById('odx-edit-toggle');
  const outlineToggle = document.getElementById('odx-sections-toggle');
  const outline = document.getElementById('odx-outline');
  const outlineList = root.querySelector('[data-odx-outline]');
  const progressBar = root.querySelector('[data-odx-progress]');
  const progressLabel = root.querySelector('[data-odx-progress-label]');
  const pendingNote = root.querySelector('[data-odx-pending-note]');
  const confirms = [...root.querySelectorAll('[data-odx-confirm]')];
  const confirmByKey = new Map(confirms.map(input => [input.dataset.odxConfirm, input]));
  let dirty = false;
  const updatePending = () => {
    if (!pendingNote || !documentBody) return;
    const placeholders=(documentBody.textContent||'').match(/\[[^\]\n]{0,75}\]/g)||[];
    const unreviewed=confirms.filter(input=>!input.checked).length;
    pendingNote.hidden=placeholders.length+unreviewed===0;
    if(!pendingNote.hidden){
      const parts=[];
      if(unreviewed)parts.push(unreviewed+' sugestão(ões) para conferir na lateral');
      if(placeholders.length)parts.push(placeholders.length+' campo(s) com marcação pendente no texto');
      pendingNote.textContent=parts.join(' e ')+'. Você pode salvar como rascunho, mas confira esses itens antes de finalizar.';
    }
  };
  const sync=()=>{
    let completed=0;
    const displays=[...root.querySelectorAll('[data-odx-display]')];
    const spans=[...(documentBody?.querySelectorAll('[data-odx-bind]')||[])];
    const statuses=[...root.querySelectorAll('[data-odx-status]')];
    fields.forEach(input=>{
      const value=input.value.trim();
      const key=input.dataset.odxField;
      const check=confirmByKey.get(key);
      const confirmed=!check||check.checked;
      if(value && confirmed)completed++;
      spans.forEach(target=>{
        if(target.dataset.odxBind!==key)return;
        target.textContent=value||'[ ]';
        target.classList.toggle('odx-review-pending',!confirmed||!value);
        target.classList.toggle('odx-reviewed-value',confirmed&&!!value);
        target.title=!confirmed?'Sugestão pendente. Clique para conferir no formulário.':'Informação conferida';
        target.tabIndex=0;
        target.setAttribute('role','button');
        target.setAttribute('aria-label','Conferir campo no formulário');
      });
      displays.forEach(display=>{
        if(display.dataset.odxDisplay!==key)return;
        display.textContent=value||'A preencher';
        display.classList.toggle('odx-empty',!value);
        display.classList.toggle('odx-review-pending',!confirmed);
      });
      statuses.forEach(status=>{
        if(status.dataset.odxStatus!==key)return;
        status.textContent=confirmed?'Conferido':'Sugestão. Confira e ajuste';
        status.classList.toggle('is-confirmed',confirmed);
      });
    });
    if(progressBar)progressBar.style.width=Math.round(completed/(fields.length||1)*100)+'%';
    if(progressLabel)progressLabel.textContent=completed+' de '+fields.length;
    updatePending();
  };
  fields.forEach(input=>input.addEventListener('input',()=>{
    dirty=true;
    const check=confirmByKey.get(input.dataset.odxField);
    if(check)check.checked=false;
    sync();
  }));
  confirms.forEach(check=>check.addEventListener('change',()=>{
    const input=fields.find(f=>f.dataset.odxField===check.dataset.odxConfirm);
    if(check.checked && (!input||!input.value.trim())){
      check.checked=false;
      input?.focus();
      return;
    }
    dirty=true;sync();
  }));
  documentBody?.addEventListener('click',event=>{
    if(documentBody.isContentEditable)return;
    const span=event.target.closest?.('[data-odx-bind]');
    if(!span)return;
    const input=fields.find(f=>f.dataset.odxField===span.dataset.odxBind);
    if(input){input.scrollIntoView({behavior:'smooth',block:'center'});input.focus({preventScroll:true});}
  });
  documentBody?.addEventListener('keydown',event=>{
    if(event.key!=='Enter'&&event.key!==' ')return;
    const span=event.target.closest?.('[data-odx-bind]');
    if(!span)return;
    event.preventDefault();
    const input=fields.find(f=>f.dataset.odxField===span.dataset.odxBind);
    if(input){input.scrollIntoView({behavior:'smooth',block:'center'});input.focus({preventScroll:true});}
  });
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
    documentBody.addEventListener('input', () => { dirty = true; updatePending(); });
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