(() => {
  'use strict';

  // Filtro da biblioteca, disponível também nas páginas sem editor.
  const filterButtons=[...document.querySelectorAll('[data-odx-filter]')];
  if(filterButtons.length){
    const cards=[...document.querySelectorAll('[data-odx-category]')];
    filterButtons.forEach(button=>button.addEventListener('click',()=>{
      filterButtons.forEach(el=>el.classList.toggle('is-selected',el===button));
      const selected=button.dataset.odxFilter;
      cards.forEach(card=>{card.hidden=selected!=='all'&&card.dataset.odxCategory!==selected;});
    }));
  }

  const root=document.getElementById('odx-form');
  if(!root)return;
  const readOnlyHistory=root.dataset.odxReadonly==='true';
  const fields=[...root.querySelectorAll('[data-odx-field]')];
  const byKey=new Map(fields.map(input=>[input.dataset.odxField,input]));
  const confirms=[...root.querySelectorAll('[data-odx-confirm]')];
  const confirmByKey=new Map(confirms.map(input=>[input.dataset.odxConfirm,input]));
  const body=document.getElementById('odx-document-body');
  const hiddenHtml=document.getElementById('odx-body-html');
  const toggle=document.getElementById('odx-edit-toggle');
  const outlineToggle=document.getElementById('odx-sections-toggle');
  const outline=document.getElementById('odx-outline');
  const outlineList=root.querySelector('[data-odx-outline]');
  const progressBar=root.querySelector('[data-odx-progress]');
  const progressLabel=root.querySelector('[data-odx-progress-label]');
  const pendingNote=root.querySelector('[data-odx-pending-note]');
  const guide=document.querySelector('.odx-inline-guide');
  const quick=document.getElementById('odx-quick-edit');
  const quickInput=document.getElementById('odx-quick-text');
  const quickTitle=document.getElementById('odx-quick-title');
  const quickHelp=document.getElementById('odx-quick-help');
  const quickProfile=document.getElementById('odx-quick-profile');
  const quickApply=quick?.querySelector('[data-odx-quick-apply]');
  let dirty=false;
  let activeTarget=null;
  let activeField=null;

  if(readOnlyHistory){
    fields.forEach(field=>{
      field.readOnly=true;
      field.setAttribute('aria-readonly','true');
    });
    confirms.forEach(check=>{check.disabled=true;});
    const status=root.querySelector('#odx-status');
    if(status)status.disabled=true;
    const save=root.querySelector('.odx-save');
    if(save)save.hidden=true;
    if(toggle)toggle.hidden=true;
    if(quick)quick.hidden=true;
    if(guide)guide.hidden=true;
  }

  const markerRE=/\[[^\]\n]{0,75}\]/g;
  const isMarker=text=>/^\[[^\]\n]{0,75}\]$/.test((text||'').trim());

  // Destaca os [ ] legados sem alterar o conteúdo textual original.
  function decorateMarkers(){
    if(!body||readOnlyHistory)return;
    const walker=document.createTreeWalker(body,NodeFilter.SHOW_TEXT);
    const candidates=[];
    let node;
    while((node=walker.nextNode())){
      if(!node.parentElement||node.parentElement.closest('[data-odx-bind],[data-odx-inline]'))continue;
      if(!node.nodeValue||!markerRE.test(node.nodeValue)){markerRE.lastIndex=0;continue;}
      markerRE.lastIndex=0;
      candidates.push(node);
    }
    candidates.forEach(textNode=>{
      const text=textNode.nodeValue;
      const fragment=document.createDocumentFragment();
      let previous=0,match;
      markerRE.lastIndex=0;
      while((match=markerRE.exec(text))){
        if(match.index>previous)fragment.appendChild(document.createTextNode(text.slice(previous,match.index)));
        const span=document.createElement('span');
        span.dataset.odxInline='1';
        span.textContent=match[0];
        fragment.appendChild(span);
        previous=match.index+match[0].length;
      }
      if(previous<text.length)fragment.appendChild(document.createTextNode(text.slice(previous)));
      if(previous)textNode.replaceWith(fragment);
      markerRE.lastIndex=0;
    });
    body.querySelectorAll('[data-odx-inline]').forEach(target=>{
      const pending=isMarker(target.textContent);
      target.classList.toggle('odx-review-pending',pending);
      target.classList.toggle('odx-inline-complete',!pending);
      target.title=pending?'Clique para preencher este campo':'Clique para corrigir este trecho';
      target.setAttribute('role','button');
      target.setAttribute('tabindex','0');
      target.setAttribute('aria-label',pending?'Preencher campo pendente':'Editar trecho preenchido');
    });
  }

  function updatePending(){
    if(!pendingNote||!body)return;
    const text=body.textContent||'';
    const placeholders=text.match(markerRE)||[];
    const unreviewed=confirms.filter(check=>!check.checked).length;
    pendingNote.hidden=!unreviewed&&!placeholders.length;
    if(!pendingNote.hidden){
      const parts=[];
      if(placeholders.length)parts.push(placeholders.length+' campo(s) para preencher');
      if(unreviewed)parts.push(unreviewed+' sugestão(ões) para conferir');
      pendingNote.textContent=parts.join(' · ')+'. Clique nos destaques amarelos da prévia para editar diretamente.';
    }
  }

  function sync(){
    let completed=0;
    const spans=[...(body?.querySelectorAll('[data-odx-bind]')||[])];
    const displays=[...root.querySelectorAll('[data-odx-display]')];
    const statuses=[...root.querySelectorAll('[data-odx-status]')];

    fields.forEach(input=>{
      const key=input.dataset.odxField;
      const value=input.value.trim();
      const confirm=confirmByKey.get(key);
      const reviewed=!confirm||confirm.checked;
      if(value&&reviewed)completed++;
      spans.forEach(target=>{
        if(target.dataset.odxBind!==key)return;
        if(!body?.isContentEditable)target.textContent=value||'[ ]';
        target.classList.toggle('odx-review-pending',!reviewed||!value);
        target.classList.toggle('odx-reviewed-value',reviewed&&!!value);
        if(!readOnlyHistory){
          target.title=!reviewed?'Clique para editar e confirmar':'Clique para corrigir';
          target.setAttribute('role','button');
          target.setAttribute('tabindex','0');
          target.setAttribute('aria-label','Editar informação na prévia');
        }
      });
      displays.forEach(display=>{
        if(display.dataset.odxDisplay!==key)return;
        display.textContent=value||'A preencher';
        display.classList.toggle('odx-empty',!value);
        display.classList.toggle('odx-review-pending',!reviewed);
        if(!readOnlyHistory){
          display.setAttribute('tabindex','0');
          display.setAttribute('role','button');
          display.setAttribute('aria-label','Editar '+(display.parentElement?.querySelector('span')?.textContent||'informação'));
        }
      });
      statuses.forEach(status=>{
        if(status.dataset.odxStatus!==key)return;
        status.textContent=reviewed?'Conferido':'Sugestão para conferir';
        status.classList.toggle('is-confirmed',reviewed);
      });
    });
    if(progressBar)progressBar.style.width=Math.round(completed/(fields.length||1)*100)+'%';
    if(progressLabel)progressLabel.textContent=completed+' de '+fields.length;
    updatePending();
  }

  fields.forEach(input=>input.addEventListener('input',()=>{
    dirty=true;
    const confirm=confirmByKey.get(input.dataset.odxField);
    if(confirm)confirm.checked=false;
    sync();
  }));
  confirms.forEach(check=>check.addEventListener('change',()=>{
    const input=byKey.get(check.dataset.odxConfirm);
    if(check.checked&&!input?.value.trim()){
      check.checked=false;
      input?.focus();
      return;
    }
    dirty=true;
    sync();
  }));

  function closeQuick(restoreFocus=false){
    if(!quick||quick.hidden)return;
    quick.hidden=true;
    quick.style.left='';
    quick.style.top='';
    if(restoreFocus&&activeTarget?.isConnected)activeTarget.focus({preventScroll:true});
    activeTarget=null;
    activeField=null;
  }

  function openQuick(target){
    if(readOnlyHistory||!quick||!quickInput||!body||body.isContentEditable)return;
    const key=target.dataset.odxBind||target.dataset.odxDisplay||null;
    const field=key?byKey.get(key):null;
    if(key&&!field)return;
    closeQuick(false);
    activeTarget=target;
    activeField=field||null;
    const rowTitle=target.closest('tr')?.querySelector('td:first-child,th:first-child')?.textContent?.trim();
    const fieldTitle=field?.closest('.odx-field')?.querySelector('span')?.textContent?.replace(/^\s*\d+\s*/,'').trim();
    const label=fieldTitle||rowTitle||'Trecho do documento';
    quickTitle.textContent=label;
    const current=field?field.value:(target.textContent||'');
    quickInput.value=isMarker(current)?'':current;
    quickInput.maxLength=field?Number(field.maxLength||1500):3000;
    const needReview=Boolean(field&&confirmByKey.get(field.dataset.odxField));
    quickApply.textContent=needReview?'Aplicar e confirmar':'Aplicar alteração';
    const profileHint=!field&&/\[(?:NOME\s*\/\s*CRO(?:-UF)?|NOME\s*\/\s*CNPJ|CNPJ|RESPONS[AÁ]VEL T[EÉ]CNICO)\]/i.test(target.textContent||'');
    quickProfile.hidden=!profileHint;
    quickHelp.textContent=needReview
      ?'Confira se o texto descreve sua clínica. Ao aplicar, a sugestão será marcada como conferida.'
      :field
        ?'A alteração também aparecerá no formulário lateral.'
        :'O texto será atualizado somente neste ponto do documento. Revise antes de salvar.';
    quick.hidden=false;
    const box=target.getBoundingClientRect();
    const popupWidth=Math.min(420,Math.max(280,window.innerWidth-24));
    const horizontal=Math.min(Math.max(12,box.left),Math.max(12,window.innerWidth-popupWidth-12));
    quick.style.width=popupWidth+'px';
    quick.style.left=horizontal+'px';
    const popupHeight=quick.offsetHeight;
    let vertical=box.bottom+10;
    if(vertical+popupHeight>window.innerHeight-14){
      vertical=box.top-popupHeight-10;
      if(vertical<12)vertical=Math.max(12,window.innerHeight-popupHeight-12);
    }
    quick.style.top=vertical+'px';
    quickInput.focus({preventScroll:true});
    if(quickInput.value)quickInput.select();
  }

  function applyQuick(){
    if(!activeTarget||!quickInput||!body)return false;
    const value=quickInput.value.trim();
    if(!value){
      quickHelp.textContent='Preencha o campo antes de aplicar. Se o dado ainda não está disponível, mantenha-o pendente.';
      quickInput.focus();
      return false;
    }
    if(activeField){
      activeField.value=value;
      activeField.dispatchEvent(new Event('input',{bubbles:true}));
      const confirm=confirmByKey.get(activeField.dataset.odxField);
      if(confirm)confirm.checked=true;
    }else{
      activeTarget.textContent=value;
      activeTarget.dataset.odxInline='1';
      activeTarget.classList.remove('odx-review-pending');
      activeTarget.classList.add('odx-inline-complete');
    }
    dirty=true;
    sync();
    closeQuick(true);
    if(guide)guide.innerHTML='<strong>Alteração aplicada na prévia.</strong> Use “Salvar versão da minuta” para guardar as mudanças.';
    return true;
  }

  quickApply?.addEventListener('click',applyQuick);
  quick?.querySelectorAll('[data-odx-quick-close],[data-odx-quick-cancel]')
    .forEach(button=>button.addEventListener('click',()=>closeQuick(true)));
  quickInput?.addEventListener('keydown',event=>{
    if(event.key==='Escape'){event.preventDefault();closeQuick(true);}
    if(event.key==='Enter'&&(event.ctrlKey||event.metaKey)){event.preventDefault();applyQuick();}
  });

  // Os destaques e o quadro resumido abrem o mesmo editor, sem rolar até a lateral.
  function handleEditTarget(event){
    if(readOnlyHistory||body?.isContentEditable)return;
    const target=event.target.closest?.('[data-odx-bind],[data-odx-inline],[data-odx-display]');
    if(!target||!root.contains(target)||quick?.contains(target))return;
    if(event.type==='keydown'){
      if(event.key!=='Enter'&&event.key!==' ')return;
      event.preventDefault();
    }
    openQuick(target);
  }
  root.addEventListener('click',handleEditTarget);
  root.addEventListener('keydown',handleEditTarget);
  document.addEventListener('pointerdown',event=>{
    if(!quick||quick.hidden)return;
    if(quick.contains(event.target))return;
    const editTarget=event.target.closest?.('[data-odx-bind],[data-odx-inline],[data-odx-display]');
    if(!editTarget)closeQuick(false);
  });
  document.addEventListener('keydown',event=>{
    if(event.key==='Escape'&&!quick?.hidden)closeQuick(true);
  });
  window.addEventListener('resize',()=>closeQuick(false));

  if(toggle&&body){
    toggle.addEventListener('click',()=>{
      closeQuick(false);
      const next=body.getAttribute('contenteditable')!=='true';
      body.setAttribute('contenteditable',String(next));
      toggle.classList.toggle('is-active',next);
      toggle.setAttribute('aria-pressed',String(next));
      toggle.textContent=next?'Concluir edição completa':'Editar texto completo';
      if(guide){
        guide.textContent=next
          ?'Edição completa ativada: altere parágrafos e tabelas com cuidado. Clique em “Concluir edição completa” para voltar aos destaques.'
          :'Clique em qualquer trecho amarelo para editar ali mesmo. Use a lateral apenas se preferir.';
      }
      if(next)body.focus();
      else{decorateMarkers();sync();}
    });
    body.addEventListener('input',()=>{
      dirty=true;
      updatePending();
    });
  }

  // Índice das seções do texto técnico.
  const headings=body?[...body.querySelectorAll('h2,h3')]:[];
  headings.forEach((heading,i)=>{
    const id='odx-section-'+(i+1);
    heading.id=id;
    const item=document.createElement('li');
    const a=document.createElement('a');
    a.href='#'+id;
    a.textContent=heading.textContent?.trim()||'Seção '+(i+1);
    item.appendChild(a);
    outlineList?.appendChild(item);
  });
  if(outlineToggle&&outline){
    outlineToggle.addEventListener('click',()=>{
      outline.hidden=!outline.hidden;
      outlineToggle.classList.toggle('is-active',!outline.hidden);
    });
  }

  decorateMarkers();
  sync();
  root.addEventListener('submit',event=>{
    if(!hiddenHtml||!body)return;
    if(quick&&!quick.hidden){
      event.preventDefault();
      quickHelp.textContent='Clique em “'+quickApply.textContent+'” antes de salvar a versão.';
      quickInput?.focus();
      return;
    }
    hiddenHtml.value=body.innerHTML;
    dirty=false;
  });
  window.addEventListener('beforeunload',event=>{
    if(!dirty)return;
    event.preventDefault();
    event.returnValue='';
  });
})();