(() => {
  'use strict';
  const section=document.getElementById('odx-esteril-visual');
  if(!section)return;
  const hotspots=[...section.querySelectorAll('[data-si-number]')];
  const legend=[...section.querySelectorAll('.si-list-button')];
  const byKey=new Map(hotspots.map(button=>[button.dataset.siKey,button]));
  const panel=section.querySelector('[data-si-detail]');
  if(!panel)return;
  // The scene remains static; only the approved isolated object image changes.
  const photo=panel.querySelector('[data-si-photo]');
  const image=photo?.querySelector('img[data-si-image]');
  if(image){
    image.addEventListener('load',()=>{
      photo.classList.remove('is-loading','is-unavailable');
      image.hidden=false;
    });
    image.addEventListener('error',()=>{
      photo.classList.remove('is-loading');
      photo.classList.add('is-unavailable');
      image.hidden=true;
    });
  }
  const targets={
    number:panel.querySelector('[data-si-number]'),
    kind:panel.querySelector('[data-si-kind]'),
    name:panel.querySelector('[data-si-name]'),
    description:panel.querySelector('[data-si-description]'),
    check:panel.querySelector('[data-si-check]')
  };
  function select(key){
    const item=byKey.get(key);
    if(!item)return;
    const n=String(item.dataset.siNumber||'1').padStart(2,'0');
    if(image){
      const asset=item.dataset.siImage||'';
      image.alt=(item.dataset.siName||'Material')+' isolado, sem fundo';
      if(asset && image.getAttribute('src')!==asset){
        photo.classList.add('is-loading');
        photo.classList.remove('is-unavailable');
        image.hidden=false;
        image.src=asset;
      }
    }
    if(targets.number)targets.number.textContent=n+' / 07';
    if(targets.kind)targets.kind.textContent=item.dataset.siKind||'';
    if(targets.name)targets.name.textContent=item.dataset.siName||'';
    if(targets.description)targets.description.textContent=item.dataset.siDescription||'';
    if(targets.check)targets.check.textContent=item.dataset.siCheck||'';
    hotspots.forEach(button=>{
      const active=button===item;
      button.classList.toggle('is-current',active);
      button.setAttribute('aria-pressed',active?'true':'false');
    });
    legend.forEach(button=>{
      const active=button.dataset.siKey===key;
      button.classList.toggle('is-current',active);
      button.setAttribute('aria-pressed',active?'true':'false');
    });
  }
  [...hotspots,...legend].forEach(button=>button.addEventListener('click',()=>select(button.dataset.siKey)));
  select('autoclave');
})();
