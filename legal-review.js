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

  const autofill = document.getElementById('odx-fill-clinic');
  const documentBody = document.getElementById('odx-document-body');
  autofill?.addEventListener('click', () => {
    if (!documentBody) return;
    const name = (panel.dataset.odName || '').trim();
    const cnpj = (panel.dataset.odCnpj || '').trim();
    const rt = (panel.dataset.odRtname || '').trim();
    const cro = (panel.dataset.odCro || '').trim();
    const replaces = [];
    if(name && cnpj)replaces.push(['[NOME / CNPJ]', name+' / '+cnpj]);
    if(rt && cro){
      replaces.push(['[NOME / CRO-UF]',rt+' / '+cro]);
      replaces.push(['[NOME / CRO]',rt+' / '+cro]);
    }
    let updated=0;
    const walker=document.createTreeWalker(documentBody,NodeFilter.SHOW_TEXT);
    let node;
    while((node=walker.nextNode())){
      let next=node.nodeValue;
      for(const [from,to] of replaces){
        if(next.includes(from)){next=next.split(from).join(to);updated++;}
      }
      if(next!==node.nodeValue)node.nodeValue=next;
    }
    if(updated)documentBody.dispatchEvent(new Event('input',{bubbles:true}));
    autofill.textContent=updated?'Dados inseridos. Revise e salve.':'Não há dados cadastrais pendentes para preencher.';
  });

  const escapeText = (text) => String(text).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  function scanNow(){
    const fields = [...form.querySelectorAll('[data-odx-field]')];
    const templateId = form.querySelector('input[name="id"]')?.value || '';
    // Campos legados mantidos nos seis planos não são exigências adicionais para novas minutas.
    const legacyOptional = new Set(['22','23','25','26','27','28']).has(templateId);
    const empty = fields.filter(field => !field.value.trim() && !(legacyOptional && ['g[aplicabilidade]','g[evidencias]'].includes(field.name)));
    const nameValid = panel.dataset.legalClinic === '1';
    const rtValid = panel.dataset.legalRt === '1';
    const content = document.getElementById('odx-document-body');
    const contentValid = Boolean(content && content.textContent.trim().length >= 100);
    // A minuta não está pronta enquanto houver marcadores de exemplo no seu corpo.
    const markers = content?.textContent.match(/\[[^\]\n]{2,90}\]/g) || [];
    // Datas de vigência/revisão e assinatura são concluídas na formalização humana.
    const finalization = markers.filter(marker => /^\[(?:DATA|ASSINATURA\b[^\]]*)\]$/i.test(marker));
    const preparationMarkers = markers.filter(marker => !finalization.includes(marker));
    const exampleCount = preparationMarkers.length;
    const items = [];
    if(!nameValid) items.push('Confirme o nome do estabelecimento no perfil.');
    if(!rtValid) items.push('Informe o nome e o CRO do responsável técnico no perfil.');
    if(empty.length) items.push('Preencha ou adapte ' + empty.length + ' campo(s) do documento.');
    if(!contentValid) items.push('Confira a presença do conteúdo principal da minuta.');
    if(exampleCount) items.push('Substitua ' + exampleCount + ' marcador(es) de exemplo no corpo do documento, incluindo datas e assinaturas.');
    if(!accuracy.checked) items.push('Confirme que os dados correspondem à realidade da clínica.');

    const complete = items.length === 0;
    result.classList.toggle('is-complete', complete);
    if (complete) {
      result.innerHTML = '<h4>Checagem estrutural concluída</h4>'
      + '<p>Os campos verificados estão preenchidos. A minuta pode ser salva e encaminhada à revisão do responsável técnico. Isso não é aprovação sanitária nem certificação.</p>'
      + '<p><strong>Próximo passo:</strong> selecione “Aguardando revisão do RT” e salve uma nova versão. As datas de revisão e assinaturas ainda pendentes devem ser formalizadas pelos responsáveis competentes, especialmente a aprovação e assinatura do responsável legal para a SDBPF.</p>';
    } else {
      result.innerHTML = '<h4>Faltam ' + items.length + ' pontos para preparar a minuta</h4>'
      + '<p>Revise os itens abaixo e execute uma nova conferência.</p><ul>'
      + items.map(item => '<li>' + escapeText(item) + '</li>').join('')
      + '</ul><button type="button" class="od-legal-edit-cta" id="od-legal-edit-cta">Abrir edição do texto</button>'
      + ' <a href="?page=diagnostico">Editar dados da clínica</a>';
    }
    result.hidden = false;
    result.querySelector('#od-legal-edit-cta')?.addEventListener('click',()=>{
      const toggle=document.getElementById('odx-edit-toggle');
      if(documentBody?.getAttribute('contenteditable')!=='true')toggle?.click();
      documentBody?.scrollIntoView({behavior:'smooth',block:'start'});
    });
    trigger.disabled = false;
    trigger.querySelector('span:nth-child(2)').textContent = 'Conferir novamente';
    result.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'nearest'});
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
