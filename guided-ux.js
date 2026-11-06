(() => {
  'use strict';
  const items = [...document.querySelectorAll('[data-ui-category]')];
  const filters = [...document.querySelectorAll('[data-ui-filter]')];
  const search = document.getElementById('od-ui-search');
  const empty = document.getElementById('od-ui-no-results');
  let selected = 'all';
  function updateList(){
    const term = (search?.value || '').trim().toLocaleLowerCase('pt-BR');
    let visible=0;
    items.forEach(card=>{
      const matchesCategory=selected==='all'||card.dataset.uiCategory===selected;
      const matchesText=(card.dataset.uiSearch||'').toLocaleLowerCase('pt-BR').includes(term);
      card.hidden=!(matchesCategory&&matchesText);
      if(!card.hidden)visible++;
    });
    if(empty)empty.hidden=visible!==0;
  }
  filters.forEach(filter=>filter.addEventListener('click',()=>{
    selected=filter.dataset.uiFilter||'all';
    filters.forEach(el=>el.classList.toggle('is-selected',el===filter));
    updateList();
  }));
  search?.addEventListener('input',updateList);
  updateList();

  document.querySelectorAll('.od-ui-example[data-ui-target]').forEach(button=>{
    button.addEventListener('click',()=>{
      const input=document.getElementById(button.dataset.uiTarget||'');
      if(!input)return;
      if(input.value.trim()&&!window.confirm('O campo já contém informações. Substituir pelo texto de exemplo?'))return;
      input.value=button.dataset.uiText||'';
      input.dispatchEvent(new Event('input',{bubbles:true}));
      input.focus();
      button.textContent='Sugestão inserida. Revise antes de salvar.';
    });
  });
})();
