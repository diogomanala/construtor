(() => {
  'use strict';
  const form=document.querySelector('#od-brand-form');
  const preview=document.querySelector('#od-brand-preview');
  if(!form||!preview)return;
  const colors={jade:'#176B58',navy:'#204A70',graphite:'#363E4A',plum:'#684166',clay:'#8B493A'};
  const accentInput=form.querySelector('[name="accent"]');
  const fileInput=form.querySelector('[name="clinic_logo"]');
  const existingLogo=preview.querySelector('#od-brand-preview-logo');
  const remove=form.querySelector('[name="remove_logo"]');
  const fileError=form.querySelector('#od-brand-file-error');
  const feedback=form.querySelector('.od-brand-save-feedback');
  const save=form.querySelector('[type="submit"]');
  const nameInput=form.querySelector('[name="clinic_name"]');
  const label=preview.querySelector('#od-brand-preview-name');
  let objectUrl=null;

  const selectedPalette=()=>form.querySelector('[name="palette"]:checked')?.value||'jade';
  const selectedColor=()=>selectedPalette()==='custom'?accentInput.value:(colors[selectedPalette()]||colors.jade);
  function contrastWithWhite(hex){
    const rgb=[1,3,5].map(i=>parseInt(hex.slice(i,i+2),16)/255);
    const linear=rgb.map(v=>v<=.04045?v/12.92:Math.pow((v+.055)/1.055,2.4));
    const lum=linear[0]*.2126+linear[1]*.7152+linear[2]*.0722;
    return 1.05/(lum+.05);
  }
  function message(text){
    if(!fileError)return;
    fileError.textContent=text;
    fileError.hidden=!text;
  }
  function updateColor(){
    preview.style.setProperty('--od-brand-color',selectedColor());
    if(feedback){
      const low=selectedPalette()==='custom'&&contrastWithWhite(accentInput.value)<4.5;
      feedback.hidden=!low;
      feedback.textContent=low?'Esta cor é clara demais para texto branco. Escolha uma tonalidade mais escura.':'';
    }
  }
  function refreshLogo(){
    if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}
    const file=fileInput.files?.[0];
    const accepted=file&&['image/png','image/jpeg','image/webp'].includes(file.type)&&file.size<=6*1024*1024;
    message(file&&!accepted?'Use uma imagem PNG, JPG ou WebP de até 6 MB.':'');
    if(accepted){
      objectUrl=URL.createObjectURL(file);
      if(existingLogo){existingLogo.src=objectUrl;existingLogo.hidden=false;}
      else{
        let img=preview.querySelector('.od-brand-preview-user-logo');
        if(!img){img=document.createElement('img');img.className='od-brand-preview-user-logo';img.alt='Prévia do logo';preview.querySelector('.od-brand-preview-head').prepend(img);}
        img.src=objectUrl;img.hidden=false;
      }
    }else{
      const img=preview.querySelector('.od-brand-preview-user-logo');
      if(img)img.hidden=true;
      if(existingLogo){
        existingLogo.hidden=Boolean(remove?.checked);
        if(!existingLogo.hidden)existingLogo.src='?page=logo-clinica';
      }
    }
    const monogram=preview.querySelector('#od-brand-preview-monogram');
    if(monogram)monogram.hidden=Boolean(accepted);
  }

  form.querySelectorAll('[name="palette"]').forEach(r=>r.addEventListener('change',updateColor));
  accentInput?.addEventListener('input',()=>{
    const custom=form.querySelector('[name="palette"][value="custom"]');
    if(custom)custom.checked=true;
    updateColor();
  });
  nameInput?.addEventListener('input',()=>{if(label)label.textContent=nameInput.value.trim()||'Nome da clínica';});
  fileInput?.addEventListener('change',refreshLogo);
  remove?.addEventListener('change',refreshLogo);
  form.addEventListener('submit',event=>{
    if(fileError&&!fileError.hidden){event.preventDefault();fileInput?.focus();return;}
    if(selectedPalette()==='custom'&&contrastWithWhite(accentInput.value)<4.5){
      event.preventDefault();accentInput.focus();updateColor();return;
    }
    if(save){save.disabled=true;save.textContent='Salvando identidade...';}
  });
  window.addEventListener('pagehide',()=>{if(objectUrl)URL.revokeObjectURL(objectUrl);});
  updateColor();
})();