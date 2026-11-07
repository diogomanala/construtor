(() => {
  'use strict';
  const form=document.getElementById('sv-form');
  if(!form)return;
  const nodes=[...form.querySelectorAll('[data-sv-object]')];
  const inputs=[...form.querySelectorAll('[data-sv-input]')];
  const inputByKey=new Map(inputs.map(input=>[input.dataset.svInput,input]));
  const panel=document.getElementById('sv-detail');
  const mobile=document.getElementById('sv-mobile-modal');
  const media=window.matchMedia('(max-width: 900px)');
  let selected=nodes[0]||null;
  const countLabel=form.querySelector('[data-sv-count]');
  const detailParts=[panel,mobile].filter(Boolean);
  const printItems=[...document.querySelectorAll('[data-sv-print]')];
  const printNotes=document.querySelector('[data-sv-print-notes]');
  const notes=document.getElementById('sv-notes');

  function refreshSelection() {
    const total=nodes.length;
    let count=0;
    nodes.forEach(node=>{
      const included=Boolean(inputByKey.get(node.dataset.svObject)?.checked);
      if(included)count++;
      node.classList.toggle('is-included',included);
      node.classList.toggle('is-excluded',!included);
      node.querySelector('.sv-object-state').textContent=included?'Incluído':'Fora do quadro';
    });
    printItems.forEach(el=>{
      const enabled=Boolean(inputByKey.get(el.dataset.svPrint)?.checked);
      el.hidden=!enabled;
    });
    if(countLabel)countLabel.textContent=count+' de '+total+' objetos incluídos';
    if(selected)detailParts.forEach(detail=>{
      const button=detail.querySelector('[data-sv-toggle]');
      const label=detail.querySelector('[data-sv-toggle-label]');
      const enabled=Boolean(inputByKey.get(selected.dataset.svObject)?.checked);
      if(button){button.setAttribute('aria-pressed',enabled?'true':'false');}
      if(label)label.textContent=enabled?'Incluído na composição':'Adicionar à composição';
    });
  }

  function fillDetails(node) {
    selected=node;
    nodes.forEach(el=>{
      const active=el===node;
      el.classList.toggle('is-active',active);
      el.setAttribute('aria-pressed',active?'true':'false');
    });
    detailParts.forEach(detail=>{
      const name=detail.querySelector('[data-sv-name]');
      const kind=detail.querySelector('[data-sv-kind]');
      const description=detail.querySelector('[data-sv-description]');
      const check=detail.querySelector('[data-sv-check]');
      if(name)name.textContent=node.dataset.svName||'Objeto';
      if(kind)kind.textContent=node.dataset.svKind||'Material';
      if(description)description.textContent=node.dataset.svDescription||'';
      if(check)check.textContent=node.dataset.svCheck||'';
    });
    refreshSelection();
  }

  nodes.forEach(node=>node.addEventListener('click',()=>{
    fillDetails(node);
    if(media.matches && mobile && typeof mobile.showModal==='function' && !mobile.open){
      mobile.showModal();
      mobile.querySelector('[data-sv-close]')?.focus();
    }
  }));

  detailParts.forEach(detail=>{
    detail.querySelector('[data-sv-toggle]')?.addEventListener('click',()=>{
      if(!selected)return;
      const input=inputByKey.get(selected.dataset.svObject);
      if(!input)return;
      input.checked=!input.checked;
      refreshSelection();
    });
  });

  mobile?.querySelector('[data-sv-close]')?.addEventListener('click',()=>mobile.close());
  mobile?.addEventListener('click',event=>{
    // Permite dispensar o modal clicando fora do conteúdo.
    if(event.target!==mobile)return;
    const rect=mobile.getBoundingClientRect();
    if(event.clientY<rect.top || event.clientY>rect.bottom)mobile.close();
  });
  media.addEventListener?.('change',()=>{
    if(!media.matches&&mobile?.open)mobile.close();
  });

  document.getElementById('sv-print')?.addEventListener('click',()=>{
    refreshSelection();
    window.print();
  });

  function syncNotes(){
    if(printNotes && notes)printNotes.textContent=notes.value.trim()
      ?'Observações da clínica: '+notes.value.trim():'';
  }
  notes?.addEventListener('input',syncNotes);
  syncNotes();
  if(nodes.length)fillDetails(nodes[0]);
  else refreshSelection();
})();
