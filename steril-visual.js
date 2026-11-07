(() => {
  'use strict';
  const form=document.getElementById('sv-form');
  const sceneSrc='assets/illustrations/sterilization-bench-photo-v1.avif';
  const positions={autoclave:['18%','22%'],seladora:['53%','39%'],embalagem:['84%','30%'],quimico:['27%','79%'],biologico:['37%','78%'],bandeja:['50%','75%'],cassete:['76%','76%']};
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
        if(art){
          art.style.backgroundImage='url("'+sceneSrc+'")';
          art.style.backgroundPosition=(positions[node.dataset.svObject]||['50%','50%']).join(' ');
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
    buttons.forEach(button=>button.addEventListener('click',()=>{
      buttons.forEach(b=>{const active=b===button;b.classList.toggle('is-active',active);b.setAttribute('aria-pressed',active?'true':'false');});
      if(title)title.textContent=button.dataset.svInlineName||'Material';
      if(description)description.textContent=button.dataset.svInlineText||'';
    }));
  });
})();