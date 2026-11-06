(() => {
  'use strict';
  const form = document.querySelector('#odx-form');
  if (!form) return;

  // Sugestões de texto administrativo. Nunca sobrescreve dados do profissional.
  form.querySelectorAll('[data-odx-suggest]').forEach(button => {
    button.addEventListener('click', () => {
      const target = document.getElementById(button.dataset.odxSuggest || '');
      if (!target || target.readOnly) return;
      if (target.value.trim() && !window.confirm('Este campo já contém informações. Deseja substituir pela sugestão?')) return;
      target.value = button.dataset.suggestion || '';
      target.dispatchEvent(new Event('input', {bubbles:true}));
      target.focus();
      button.textContent = 'Texto inserido. Revise antes de salvar.';
    });
  });

  const trigger = document.getElementById('od-legal-trigger');
  const panel = document.querySelector('.od-legal-review');
  const result = document.getElementById('od-legal-result');
  const paper = document.querySelector('.odx-paper');
  const accuracy = document.getElementById('od-legal-confirm');
  if (!trigger || !result || !panel || !accuracy) return;

  const escapeText = (text) => String(text).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  function scanNow(){
    const fields = [...form.querySelectorAll('[data-odx-field]')];
    const empty = fields.filter(field => !field.value.trim());
    const nameValid = panel.dataset.legalClinic === '1';
    const rtValid = panel.dataset.legalRt === '1';
    const content = document.getElementById('odx-document-body');
    const contentValid = Boolean(content && content.textContent.trim().length >= 100);
    const items = [];
    if(!nameValid) items.push('Confirme o nome do estabelecimento no perfil.');
    if(!rtValid) items.push('Informe o nome e o CRO do responsável técnico no perfil.');
    if(empty.length) items.push('Preencha ou adapte ' + empty.length + ' campo(s) do documento.');
    if(!contentValid) items.push('Confira a presença do conteúdo principal da minuta.');
    if(!accuracy.checked) items.push('Confirme que os dados correspondem à realidade da clínica.');

    const complete = items.length === 0;
    result.classList.toggle('is-complete', complete);
    if (complete) {
      result.innerHTML = '<h4>Checagem estrutural concluída</h4>'
      + '<p>Os campos verificados estão preenchidos. A minuta pode ser salva e encaminhada à revisão do responsável técnico. Isso não é aprovação sanitária nem certificação.</p>'
      + '<p><strong>Próximo passo:</strong> selecione “Aguardando revisão do RT” e salve uma nova versão. Para a SDBPF, a formalização pelo responsável legal continua necessária.</p>';
    } else {
      result.innerHTML = '<h4>Faltam ' + items.length + ' pontos para preparar a minuta</h4>'
      + '<p>Revise os itens abaixo e execute uma nova conferência.</p><ul>'
      + items.map(item => '<li>' + escapeText(item) + '</li>').join('')
      + '</ul><a href="?page=diagnostico">Editar dados da clínica</a>';
    }
    result.hidden = false;
    trigger.disabled = false;
    trigger.querySelector('span:nth-child(2)').textContent = 'Conferir novamente';
    result.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'nearest'});
  }
  trigger.addEventListener('click', () => {
    if(trigger.disabled)return;
    result.hidden = true;
    trigger.disabled = true;
    trigger.querySelector('span:nth-child(2)').textContent = 'Conferindo estrutura...';
    paper?.classList.remove('is-reviewing');
    void paper?.offsetWidth;
    paper?.classList.add('is-reviewing');
    const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 1120;
    window.setTimeout(() => {
      paper?.classList.remove('is-reviewing');
      scanNow();
    },duration);
  });
})();
