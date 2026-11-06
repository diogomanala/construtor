(() => {
  'use strict';
  const form=document.querySelector('.od-kit-form');
  if(!form)return;
  const boxes=[...form.querySelectorAll('input[name="items[]"]')];
  const count=document.getElementById('od-kit-count');
  const update=()=>{const selected=boxes.filter(b=>b.checked).length;if(count)count.textContent=selected+' de '+boxes.length+' itens selecionados';};
  form.querySelectorAll('[data-kit-action]').forEach(button=>button.addEventListener('click',()=>{
    const select=button.dataset.kitAction==='all';
    boxes.forEach(box=>{box.checked=select;});
    update();
  }));
  boxes.forEach(box=>box.addEventListener('change',update));
  const notes=form.querySelector('textarea[name="notes"]');
  const printNotes=document.querySelector('#od-kit-print-notes p');
  if(notes&&printNotes){
    const sync=()=>{printNotes.textContent=notes.value;};
    notes.addEventListener('input',sync);sync();
  }
  update();
})();
