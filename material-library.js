(()=>{'use strict';
const page=document.querySelector('.od-mat-page');if(!page)return;
const query=page.querySelector('#od-mat-search'),buttons=[...page.querySelectorAll('[data-mat-filter]')];
const tiles=[...page.querySelectorAll('.od-mat-tile')],empty=page.querySelector('#od-mat-empty');
const form=page.querySelector('#od-mat-form'),inputs=[...page.querySelectorAll('input[name="materials[]"]')];
const label=page.querySelector('#od-mat-selected'),save=page.querySelector('.od-mat-save');
let cat='all';const normal=s=>(s||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
function filter(){const q=normal(query?.value.trim());let visible=0;
 tiles.forEach(t=>{const show=(cat==='all'||t.dataset.matCategory===cat)&&normal(t.dataset.matSearch).includes(q);t.hidden=!show;if(show)visible++});
 if(empty)empty.hidden=visible!==0;
}
buttons.forEach(b=>b.addEventListener('click',()=>{cat=b.dataset.matFilter;buttons.forEach(x=>x.classList.toggle('is-active',x===b));filter()}));
query?.addEventListener('input',filter);filter();
function count(){const n=inputs.filter(x=>x.checked).length;if(label)label.textContent=n+' de 18 imagens selecionadas';if(save)save.disabled=n>18;}
inputs.forEach(x=>x.addEventListener('change',count));count();
form?.querySelector('[data-mat-action="clear"]')?.addEventListener('click',()=>{inputs.forEach(x=>x.checked=false);count()});
form?.querySelector('[data-mat-action="defaults"]')?.addEventListener('click',()=>{let names=[];try{names=JSON.parse(document.getElementById('od-mat-defaults')?.textContent||'[]')}catch(e){}
 const set=new Set(names);inputs.forEach(x=>x.checked=set.has(x.value));count()});
form?.addEventListener('submit',ev=>{if(inputs.filter(x=>x.checked).length>18){ev.preventDefault();alert('Selecione no máximo 18 imagens.')}})
})();