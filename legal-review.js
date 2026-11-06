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
      target.dataset.odxSuggested = '1';
      target.addEventListener('input', () => {if(target.value.trim() !== (button.dataset.suggestion || '').trim())delete target.dataset.odxSuggested;});
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
    // Campos preservados de migrações antigas não são obrigatórios novos.
    const legacyOptional = true;
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
    const suggested = fields.filter(field => field.dataset.odxSuggested === '1');
    const checks = [
      {ok:nameValid,problem:'Confirme o nome do estabelecimento no perfil.',section:'?page=diagnostico'},
      {ok:rtValid,problem:'Informe nome e CRO do responsável técnico.',section:'?page=diagnostico'},
      {ok:empty.length===0,problem:'Preencha ou adapte '+empty.length+' campo(s) guiados; os dados precisam refletir a clínica.',section:'#odx-form'},
      {ok:contentValid,problem:'Confira a presença do conteúdo principal da minuta.',section:'#odx-document-body'},
      {ok:exampleCount===0,problem:'Substitua '+exampleCount+' marcador(es) de exemplo no corpo do documento.',section:'#odx-document-body'},
      {ok:suggested.length===0,problem:'Revise e adapte '+suggested.length+' texto(s) sugerido(s) antes de concluir a checagem.',section:'#odx-form'},
      {ok:accuracy.checked,problem:'Confirme que as informações foram conferidas e correspondem à realidade do serviço.',section:'#od-legal-confirm'}
    ];
    const passed = checks.filter(check=>check.ok).length;
    const problems = checks.filter(check=>!check.ok);
    const complete = problems.length === 0;
    result.classList.toggle('is-complete', complete);
    if (complete) {
      result.innerHTML = '<div class="od-legal-scan-summary"><strong>'+passed+' de '+checks.length+' checagens de estrutura concluídas</strong><span>Verificação local, sem certificação sanitária</span></div>'
      + '<h4>Minuta pronta para revisão técnica</h4>'
      + '<p>A estrutura e os dados declarados passaram nas conferências indicadas. Salve a versão, selecione “Aguardando revisão do RT” e providencie a avaliação técnica e a formalização pertinentes. Isso não verifica a execução dos procedimentos nem confirma conformidade legal.</p>'
      + (finalization.length?'<p><strong>Atenção:</strong> '+finalization.length+' marcador(es) de data ou assinatura permanecem para formalização profissional.</p>':'');
    } else {
      result.innerHTML = '<div class="od-legal-scan-summary"><strong>'+passed+' de '+checks.length+' checagens de estrutura concluídas</strong><span>'+problems.length+' ponto(s) para revisar</span></div>'
      + '<h4>Vamos ajustar alguns detalhes</h4><p>Veja o que precisa ser conferido antes da revisão técnica.</p><ul class="od-legal-review-list">'
      + problems.map((item,index)=>'<li><span>'+escapeText(item.problem)+'</span><a href="'+escapeText(item.section)+'" data-od-legal-link="'+index+'">Corrigir</a></li>').join('')
      + '</ul>';
    }
    result.querySelectorAll('[data-od-legal-link]').forEach(link=>link.addEventListener('click', event=>{
      const href=link.getAttribute('href')||'';
      if(!href.startsWith('#'))return;
      event.preventDefault();
      let target;
      if(href==='#odx-form')target=empty[0]||suggested[0]||form;
      else target=document.querySelector(href);
      target?.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'center'});
      target?.focus({preventScroll:true});
      if(target===documentBody && documentBody?.getAttribute('contenteditable')!=='true')document.getElementById('odx-edit-toggle')?.click();
    }));
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
