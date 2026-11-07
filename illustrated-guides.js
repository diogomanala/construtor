(() => {
  'use strict';
  document.querySelectorAll('[data-gid-guide]').forEach(section => {
    const hotspots=[...section.querySelectorAll('[data-gid-object]')];
    const list=[...section.querySelectorAll('[data-gid-list]')];
    const byKey=new Map(hotspots.map(item=>[item.dataset.gidObject,item]));
    const detail=section.querySelector('[data-gid-detail]');
    const image=detail?.querySelector('[data-gid-photo]');
    const frame=image?.closest('.si-detail-photo');
    if(!detail||!image||hotspots.length===0)return;
    const values={
      number:detail.querySelector('[data-gid-number]'),
      kind:detail.querySelector('[data-gid-kind]'),
      name:detail.querySelector('[data-gid-name]'),
      description:detail.querySelector('[data-gid-description]'),
      check:detail.querySelector('[data-gid-check]')
    };
    image.addEventListener('load',()=>{
      frame?.classList.remove('is-loading','is-unavailable');
      image.hidden=false;
    });
    image.addEventListener('error',()=>{
      frame?.classList.remove('is-loading');
      frame?.classList.add('is-unavailable');
      image.hidden=true;
    });
    function select(key){
      const item=byKey.get(key);
      if(!item)return;
      const num=String(item.dataset.gidNumber||1).padStart(2,'0');
      if(values.number)values.number.textContent=num+' / '+String(hotspots.length).padStart(2,'0');
      for(const field of ['kind','name','description','check']){
        if(values[field])values[field].textContent=item.dataset['gid'+field[0].toUpperCase()+field.slice(1)]||'';
      }
      const src=item.dataset.gidImage||'';
      if(src&&image.getAttribute('src')!==src){
        frame?.classList.add('is-loading');
        frame?.classList.remove('is-unavailable');
        image.hidden=false;
        image.src=src;
      }
      image.alt=(item.dataset.gidName||'Material')+' isolado, sem fundo';
      hotspots.forEach(button=>{
        const active=button===item;
        button.classList.toggle('is-current',active);
        button.setAttribute('aria-pressed',active?'true':'false');
      });
      list.forEach(button=>{
        const active=button.dataset.gidList===key;
        button.classList.toggle('is-current',active);
        button.setAttribute('aria-pressed',active?'true':'false');
      });
    }
    hotspots.forEach(button=>button.addEventListener('click',()=>select(button.dataset.gidObject)));
    list.forEach(button=>button.addEventListener('click',()=>select(button.dataset.gidList)));
    select(hotspots[0].dataset.gidObject);
  });
})();
