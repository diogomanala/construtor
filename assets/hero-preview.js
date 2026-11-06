(() => {
  "use strict";
  const root=document.querySelector("[data-od-showcase]");
  if(!root)return;
  const tabs=[...root.querySelectorAll("[data-od-tab]")];
  const scenes=[...root.querySelectorAll("[data-od-view]")];
  const tracks=[...root.querySelectorAll("[data-od-track]")];
  const reduced=window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const $=selector=>root.querySelector(selector);
  const $$=selector=>[...root.querySelectorAll(selector)];
  let index=0,generation=0,visible=true;
  let timers=[],typeTimer=null,frame=null;
  function after(ms,fn){timers.push(setTimeout(fn,ms));}
  function stop(){timers.forEach(clearTimeout);timers=[];if(typeTimer!==null){clearInterval(typeTimer);typeTimer=null;}if(frame!==null){cancelAnimationFrame(frame);frame=null;}generation++;}
  function textOf(sel,text){const el=$(sel);if(el)el.textContent=text;}
  function reset(idx){
    $$(".od-kit-tile").forEach(el=>el.classList.remove("od-animated-in","od-selected"));
    const f=$(".od-kit-file");if(f)f.classList.remove("od-animated-in");
    textOf("[data-od-typing]","");
    textOf("[data-od-fieldcount]","0 de 3");
    textOf("[data-od-savestatus]","Preenchendo os campos...");
    $$(".od-doc-bar").forEach(el=>el.classList.remove("od-animated-in"));
    const name=$(".od-doc-name");if(name)name.classList.remove("od-is-saved");
    const pb=$(".od-progress-line i");if(pb)pb.style.width="0%";
    $$(".od-board-row").forEach(el=>{
      el.classList.remove("od-animated-in");
      const label=el.querySelector(".od-chip"),check=el.querySelector(".od-board-check");
      label.textContent="Pendente";label.className="od-chip od-chip-pending";
      check.textContent="·";check.className="od-board-check od-board-check-idle";
    });
    textOf("[data-od-done]","0 de 3 etapas");
    tracks.forEach((el,i)=>{
      el.classList.remove("is-active","is-complete");
      if(i<idx)el.classList.add("is-complete");
      if(i===idx)el.classList.add("is-active");
      const bar=el.querySelector("i");if(bar)bar.style.transform=i<idx?"scaleX(1)":"scaleX(0)";
    });
    $$(".od-screen-dots i").forEach((el,i)=>el.classList.toggle("od-dot-active",i===idx));
  }
  function updateRow(el,i){
    el.classList.add("od-animated-in");
    const chip=el.querySelector(".od-chip"),mark=el.querySelector(".od-board-check");
    if(i===0){chip.textContent="Concluído";chip.className="od-chip od-chip-done";mark.textContent="✓";mark.className="od-board-check od-checked";}
    else if(i===1){chip.textContent="Em revisão";chip.className="od-chip od-chip-review";mark.textContent="◷";mark.className="od-board-check od-review";}
    else{chip.textContent="Acompanhar";chip.className="od-chip od-chip-pending";mark.textContent="·";mark.className="od-board-check od-pending";}
  }
  function finalScene(idx){
    if(idx===0){$$(".od-kit-tile").forEach(el=>el.classList.add("od-animated-in"));$(".od-kit-tile").classList.add("od-selected");$(".od-kit-file").classList.add("od-animated-in");}
    if(idx===1){textOf("[data-od-typing]","Clínica Exemplo");textOf("[data-od-fieldcount]","3 de 3");textOf("[data-od-savestatus]","Minuta salva para revisão do RT");$$(".od-doc-bar").forEach(el=>el.classList.add("od-animated-in"));$(".od-progress-line i").style.width="100%";$(".od-doc-name").classList.add("od-is-saved");}
    if(idx===2){$$(".od-board-row").forEach((el,i)=>updateRow(el,i));textOf("[data-od-done]","3 etapas organizadas");}
    const bar=tracks[idx].querySelector("i");if(bar)bar.style.transform="scaleX(1)";
  }
  function show(next){
    stop();index=(next+scenes.length)%scenes.length;const ticket=generation;
    tabs.forEach((tab,i)=>{let selected=i===index;tab.classList.toggle("is-active",selected);tab.setAttribute("aria-selected",selected?"true":"false");});
    scenes.forEach((scene,i)=>{let selected=i===index;scene.hidden=!selected;scene.classList.toggle("is-active",selected);});
    textOf("[data-od-counter]",String(index+1).padStart(2,"0"));reset(index);
    if(reduced||!visible||document.hidden){finalScene(index);return;}
    const start=performance.now(),duration=5850;
    function animate(now){
      if(ticket!==generation)return;
      const width=Math.min(1,(now-start)/duration);
      const bar=tracks[index].querySelector("i");if(bar)bar.style.transform="scaleX("+width+")";
      if(width<1)frame=requestAnimationFrame(animate);
    }
    frame=requestAnimationFrame(animate);
    if(index===0){
      $$(".od-kit-tile").forEach((el,i)=>after(300+i*440,()=>el.classList.add("od-animated-in")));
      after(2500,()=>$(".od-kit-tile").classList.add("od-selected"));
      after(3120,()=>$(".od-kit-file").classList.add("od-animated-in"));
    }else if(index===1){
      after(290,()=>$$(".od-doc-bar").forEach((el,i)=>after(i*320,()=>el.classList.add("od-animated-in"))));
      after(570,()=>{
        const el=$("[data-od-typing]"),word="Clínica Exemplo";let n=0;
        typeTimer=setInterval(()=>{
          if(ticket!==generation){clearInterval(typeTimer);typeTimer=null;return;}
          el.textContent=word.slice(0,++n);
          if(n>=word.length){clearInterval(typeTimer);typeTimer=null;textOf("[data-od-fieldcount]","1 de 3");}
        },92);
      });
      after(2900,()=>textOf("[data-od-fieldcount]","2 de 3"));
      after(3600,()=>{textOf("[data-od-fieldcount]","3 de 3");$(".od-progress-line i").style.width="100%";});
      after(4600,()=>{textOf("[data-od-savestatus]","Minuta salva para revisão do RT");$(".od-doc-name").classList.add("od-is-saved");});
    }else{
      $$(".od-board-row").forEach((el,i)=>after(590+i*1220,()=>{updateRow(el,i);textOf("[data-od-done]",String(i+1)+" de 3 etapas");}));
      after(4500,()=>textOf("[data-od-done]","3 etapas organizadas"));
    }
    after(duration+330,()=>{if(ticket===generation&&visible&&!document.hidden)show(index+1);});
  }
  tabs.forEach((tab,i)=>tab.addEventListener("click",()=>show(i)));
  document.addEventListener("visibilitychange",()=>{if(document.hidden)stop();else if(visible)show(index);});
  if("IntersectionObserver" in window){
    const observer=new IntersectionObserver(entries=>{
      const next=entries[0].isIntersecting;if(visible===next)return;visible=next;
      if(visible)show(index);else stop();
    },{threshold:.18});observer.observe(root);
  }
  show(0);
})();