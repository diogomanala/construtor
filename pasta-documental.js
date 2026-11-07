(() => {
  'use strict';
  const form=document.getElementById('pasta-prepare-form');
  if(!form)return;
  const checks=[...form.querySelectorAll('[data-pasta-doc]')];
  const count=document.getElementById('pasta-count');
  const submit=document.getElementById('pasta-generate');
  const update=()=>{
    const number=checks.filter(x=>x.checked).length;
    if(count)count.textContent=String(number);
    if(submit)submit.disabled=number===0;
  };
  checks.forEach(c=>c.addEventListener('change',update));
  form.querySelectorAll('[data-pasta-preset]').forEach(button=>button.addEventListener('click',()=>{
    const preset=button.dataset.pastaPreset;
    checks.forEach(box=>{
      box.checked=preset==='all' || (preset==='saved'&&box.dataset.saved==='1') || (preset==='suggested'&&box.dataset.suggested==='1');
    });
    update();
  }));
  update();
})();
