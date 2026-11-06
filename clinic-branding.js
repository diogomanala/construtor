(() => {
  'use strict';
  const form = document.querySelector('#od-brand-form');
  const preview = document.querySelector('#od-brand-preview');
  if (!form || !preview) return;
  const paletteColors = {jade:'#176B58',navy:'#204A70',graphite:'#363E4A',plum:'#684166',clay:'#8B493A'};
  const nameInput = form.querySelector('[name="clinic_name"]');
  const accentInput = form.querySelector('[name="accent"]');
  const fileInput = form.querySelector('[name="clinic_logo"]');
  const label = document.querySelector('#od-brand-preview-name');
  const existingLogo = document.querySelector('#od-brand-preview-logo');
  const remove = form.querySelector('[name="remove_logo"]');
  let objectUrl = null;
  function selectedPalette() {
    return form.querySelector('[name="palette"]:checked')?.value || 'jade';
  }
  function updateColor() {
    const key = selectedPalette();
    preview.style.setProperty('--od-brand-color',key==='custom'?accentInput.value:(paletteColors[key]||paletteColors.jade));
  }
  function updateName() {
    label.textContent = nameInput.value.trim() || 'Nome da clínica';
  }
  function refreshLogo() {
    if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}
    const file = fileInput.files?.[0];
    const useFile = file && ['image/png','image/jpeg','image/webp'].includes(file.type) && file.size<=350*1024;
    if (useFile) {
      objectUrl=URL.createObjectURL(file);
      if(existingLogo){existingLogo.src=objectUrl;existingLogo.hidden=false;}
      else {
        let img=preview.querySelector('.od-brand-preview-user-logo');
        if(!img){img=document.createElement('img');img.className='od-brand-preview-user-logo';img.alt='Prévia do logo';preview.querySelector('.od-brand-preview-head').prepend(img);}
        img.src=objectUrl;img.hidden=false;
      }
    } else {
      const img=preview.querySelector('.od-brand-preview-user-logo');
      if(img)img.hidden=true;
      if(existingLogo){existingLogo.hidden=Boolean(remove?.checked);if(!existingLogo.hidden)existingLogo.src='?page=logo-clinica';}
    }
    const monogram=preview.querySelector('#od-brand-preview-monogram');
    if(monogram)monogram.hidden=Boolean(useFile);
  }
  form.querySelectorAll('[name="palette"]').forEach(r=>r.addEventListener('change',updateColor));
  accentInput.addEventListener('input',()=>{
    const custom=form.querySelector('[name="palette"][value="custom"]');
    if(custom)custom.checked=true;
    updateColor();
  });
  nameInput.addEventListener('input',updateName);
  fileInput.addEventListener('change',refreshLogo);
  remove?.addEventListener('change',refreshLogo);
  window.addEventListener('pagehide',()=>{if(objectUrl)URL.revokeObjectURL(objectUrl);});
  updateColor();
})();
