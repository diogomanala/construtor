(() => {
  'use strict';
  const form=document.getElementById('sv-form');
  // Image paths are server-side whitelisted; the main photo remains unchanged.
  if(form){
    const objects=[...form.querySelectorAll('[data-sv-object]')];
    const inputs=[...form.querySelectorAll('[data-sv-input]')];
    const byKey=new Map(inputs.map(input=>[input.dataset.svInput,input]));
    const chips=[...form.querySelectorAll('[data-sv-index-target]')];
    const panel=document.getElementById('sv-detail');
    const modal=document.getElementById('sv-mobile-modal');
    const media=window.matchMedia('(max-width: 900px)');
    const details=[panel,modal].filter(Boolean);
    const count=form.querySelector('[data-sv-count]');
    const printItems=[...document.querySelectorAll('[data-sv-print]')];
    const notes=document.getElementById('sv-notes');
    const printNotes=document.querySelector('[data-sv-print-notes]');
    let active=objects[0]||null;

    function refresh(){
      let selectedCount=0;
      objects.forEach(obj=>{
        const included=Boolean(byKey.get(obj.dataset.svObject)?.checked);
        if(included)selectedCount++;
        obj.classList.toggle('is-excluded',!included);
        obj.classList.toggle('is-included',included);
      });
      chips.forEach(chip=>{
        const included=Boolean(byKey.get(chip.dataset.svIndexTarget)?.checked);
        chip.classList.toggle('is-excluded',!included);
        chip.classList.toggle('is-included',included);
        const label=chip.querySelector('small');if(label)label.textContent=included?'Incluído':'Fora';
      });
      printItems.forEach(el=>el.hidden=!Boolean(byKey.get(el.dataset.svPrint)?.checked));
      if(count)count.textContent=selectedCount+' de '+objects.length+' objetos incluídos';
      if(!active)return;
      details.forEach(detail=>{
        const toggle=detail.querySelector('[data-sv-toggle]');
        const label=detail.querySelector('[data-sv-toggle-label]');
        const included=Boolean(byKey.get(active.dataset.svObject)?.checked);
        if(toggle)toggle.setAttribute('aria-pressed',included?'true':'false');
        if(label)label.textContent=included?'Incluído na composição':'Adicionar à composição';
      });
    }

    function select(node,openMobile=false){
      active=node;
      objects.forEach(obj=>{const current=obj===node;obj.classList.toggle('is-active',current);obj.setAttribute('aria-pressed',current?'true':'false');});
      chips.forEach(chip=>chip.classList.toggle('is-active',chip.dataset.svIndexTarget===node.dataset.svObject));
      details.forEach(detail=>{
        const fields={'[data-sv-name]':node.dataset.svName,'[data-sv-kind]':node.dataset.svKind,'[data-sv-description]':node.dataset.svDescription,'[data-sv-check]':node.dataset.svCheck};
        for(const [selector,value] of Object.entries(fields)){const el=detail.querySelector(selector);if(el)el.textContent=value||'';}
        const art=detail.querySelector('[data-sv-art]');
        const img=art?.querySelector('[data-sv-detail-image]');
        if(img){
          const path=node.dataset.svImage||'';
          if(path && img.getAttribute('src')!==path){
            art.classList.add('is-loading');
            art.classList.remove('is-unavailable');
            img.hidden=false;
            img.src=path;
          }
          img.alt=(node.dataset.svName||'Material')+' isolado, sem fundo';
          img.onload=()=>{art.classList.remove('is-loading','is-unavailable');img.hidden=false;};
          img.onerror=()=>{art.classList.remove('is-loading');art.classList.add('is-unavailable');img.hidden=true;};
        }
      });
      refresh();
      if(openMobile&&media.matches&&modal?.showModal&&!modal.open){modal.showModal();modal.querySelector('[data-sv-close]')?.focus();}
    }

    objects.forEach(obj=>obj.addEventListener('click',()=>select(obj,true)));
    chips.forEach(chip=>chip.addEventListener('click',()=>{
      const obj=objects.find(node=>node.dataset.svObject===chip.dataset.svIndexTarget);
      if(obj)select(obj,true);
    }));
    details.forEach(detail=>detail.querySelector('[data-sv-toggle]')?.addEventListener('click',()=>{
      if(!active)return;const input=byKey.get(active.dataset.svObject);if(!input)return;
      input.checked=!input.checked;refresh();
    }));
    modal?.querySelector('[data-sv-close]')?.addEventListener('click',()=>modal.close());
    media.addEventListener?.('change',()=>{if(!media.matches&&modal?.open)modal.close();});
    document.getElementById('sv-print')?.addEventListener('click',()=>{refresh();window.print();});
    const syncNotes=()=>{if(notes&&printNotes)printNotes.textContent=notes.value.trim()?'Observações da clínica: '+notes.value.trim():'';};
    notes?.addEventListener('input',syncNotes);
    syncNotes();if(active)select(active,false);else refresh();
  }

  document.querySelectorAll('[data-sv-inline]').forEach(root=>{
    const buttons=[...root.querySelectorAll('[data-sv-inline-object]')];
    const title=root.querySelector('[data-sv-inline-name]');
    const description=root.querySelector('[data-sv-inline-description]');
    const image=root.querySelector('[data-sv-inline-image]');
    const preview=root.querySelector('.sv-inline-image');
    const select=button=>{
      buttons.forEach(b=>{const active=b===button;b.classList.toggle('is-active',active);b.setAttribute('aria-pressed',active?'true':'false');});
      if(title)title.textContent=button.dataset.svInlineName||'Material';
      if(description)description.textContent=button.dataset.svInlineText||'';
      if(image){
        const path=button.dataset.svInlineImage||'';
        if(path&&image.getAttribute('src')!==path){
          preview?.classList.add('is-loading');
          preview?.classList.remove('is-unavailable');
          image.hidden=false;
          image.src=path;
        }
        image.alt=(button.dataset.svInlineName||'Material')+' isolado, sem fundo';
      }
    };
    if(image){
      image.onload=()=>{preview?.classList.remove('is-loading','is-unavailable');image.hidden=false;};
      image.onerror=()=>{preview?.classList.remove('is-loading');preview?.classList.add('is-unavailable');image.hidden=true;};
    }
    buttons.forEach(button=>button.addEventListener('click',()=>select(button)));
    if(buttons[0])select(buttons[0]);
  });
})();