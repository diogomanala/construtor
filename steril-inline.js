(() => {
  'use strict';
  const section=document.getElementById('odx-esteril-visual');
  if(!section)return;
  const hotspots=[...section.querySelectorAll('[data-si-number]')];
  const legend=[...section.querySelectorAll('.si-list-button')];
  const byKey=new Map(hotspots.map(button=>[button.dataset.siKey,button]));
  const panel=section.querySelector('[data-si-detail]');
  if(!panel)return;
  const positions={autoclave:'4% 1%',seladora:'54% 29%',embalagem:'94% 22%',quimico:'21% 90%',biologico:'36% 90%',bandeja:'50% 86%',cassete:'79% 86%'};
  const photo=panel.querySelector('[data-si-photo]');
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
    if(photo){
      photo.style.backgroundPosition=positions[key]||'50% 50%';
      photo.setAttribute('aria-label','Detalhe ilustrativo de '+(item.dataset.siName||'material'));
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
