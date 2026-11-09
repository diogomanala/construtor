(() => {
  'use strict';
  const uid = Number(document.querySelector('meta[name="od-tour-user-id"]')?.getAttribute('content') || 0);
  if (!Number.isSafeInteger(uid) || uid < 1) return;
  const page = new URLSearchParams(location.search).get('page') || 'painel';
  const version = 'v1';
  const welcomeKey = `odonto-onboard-${version}-${uid}`;
  let current = null;
  let highlighted = null;
  let lastFocus = null;
  let popup = null;

  const screens = {
    painel: [
      ['.od-ui-welcome', 'Sua visão geral', 'Este é o ponto de partida. Você vê os dados da clínica e retoma o que ficou em andamento.'],
      ['.od-ui-journey', 'Seu próximo passo', 'A trilha indica a etapa seguinte de acordo com as informações já salvas. Você pode voltar a qualquer momento.'],
      ['.od-ui-shortcuts', 'Acessos rápidos', 'Abra a biblioteca para preparar minutas, rotinas para registrar tarefas reais ou acompanhamento para organizar pendências.']
    ],
    trilha: [
      ['.od-ui-simple-head', 'Uma etapa de cada vez', 'Esta trilha orienta o uso da plataforma. Você não precisa preencher dados fictícios para avançar.'],
      ['.od-ui-journey', 'Progresso da sua clínica', 'Os passos refletem dados salvos, documentos iniciados e registros realizados. Escolha o próximo que fizer sentido.'],
      ['.pasta-trail-cta', 'Pasta para impressão', 'Quando as minutas estiverem revisadas, reúna capa, índice e documentos numa sequência A4.']
    ],
    documentos: [
      ['.od-ui-docs-head', 'Biblioteca de modelos', 'São 30 materiais de referência para adaptar à realidade da sua clínica, com revisão do responsável técnico.'],
      ['.od-ui-recommend-grid', 'Sugestões de início', 'Estas opções ajudam a começar, mas a ordem pode ser ajustada conforme a necessidade da clínica.'],
      ['#od-ui-catalog', 'Encontrar outros documentos', 'Abra a biblioteca completa e use a busca ou os filtros por categoria.'],
      ['.pasta-library-callout', 'Organizar impressão', 'Reúna as minutas em uma pasta com capa, índice, divisórias e campos de assinatura.']
    ],
    editar: [
      ['main h1', 'Documento em edição', 'Confira o título e a finalidade da minuta antes de adaptar o conteúdo à rotina real da clínica.'],
      ['main form', 'Campos e conteúdo', 'Preencha os dados de que realmente dispõe. Salve uma nova versão antes de sair.'],
      ['.od-legal-review', 'Conferência e fontes', 'Use os links normativos para consultar referências; a plataforma não substitui a avaliação do responsável técnico.']
    ],
    'meus-documentos': [
      ['main h1', 'Minutas salvas', 'Consulte aqui os documentos iniciados e as versões que você já preparou.'],
      ['main .card', 'Histórico e revisão', 'Abra um documento salvo para continuar a edição ou encaminhar a minuta para revisão técnica.']
    ],
    'pasta-documental': [
      ['.pasta-intro', 'Prepare a pasta documental', 'A impressão organiza seus modelos em folhas A4, com identidade visual e índice.'],
      ['.pasta-presets', 'Escolha o conjunto', 'Use a seleção sugerida, apenas os personalizados ou marque documentos manualmente.'],
      ['.pasta-group', 'Revise os modelos', 'Inclua somente os itens aplicáveis e confira o estado de cada minuta antes de imprimir.'],
      ['.pasta-actions', 'Abra a prévia A4', 'O navegador abre a pasta pronta para impressão ou para salvar como PDF. Isso não significa aprovação técnica.']
    ],
    auto360: [
      ['.od360-hero', 'Autoinspeção 360', 'Este módulo complementar ajuda a registrar 45 verificações internas. Não é uma inspeção oficial.'],
      ['[data-od360-progress]', 'Progresso de preenchimento', 'O percentual mostra respostas registradas, não uma nota de conformidade.'],
      ['[data-od360-next]', 'Continue de onde parou', 'Use este botão para ir direto ao próximo item que ainda não foi avaliado.'],
      ['[data-od360-toolbar]', 'Busque e filtre', 'Encontre rapidamente uma pergunta por assunto, situação ou palavra.'],
      ['[data-od360-card]', 'Registre a verificação', 'Escolha a situação, informe responsável e evidência e clique em Salvar e ir para o próximo.'],
      ['[data-od360-report]', 'Relatório de acompanhamento', 'Gere um relatório A4 com os itens, as pendências e o plano de ação para revisão com o RT.']
    ],
    inspecao: [
      ['main h1', 'Autoavaliação inicial', 'Este é o roteiro simples incluído no kit principal. A Autoinspeção 360 possui um roteiro separado, com 45 verificações.'],
      ['main form', 'Responder cada ponto', 'Registre somente o que foi realmente verificado. Os dados serão usados como acompanhamento interno.']
    ],
    acompanhamento: [
      ['main h1', 'Temas e pendências', 'Consulte as frentes de acompanhamento e registre o que está sendo verificado.'],
      ['main form', 'Responsável e próximo passo', 'Adicione ações e observações reais, depois salve. Uma marcação não equivale a licença ou conformidade.']
    ],
    diagnostico: [
      ['main h1', 'Cadastro da clínica', 'Informe os dados corretos da unidade e do responsável técnico. Eles são reutilizados nos documentos.'],
      ['main form', 'Informações do consultório', 'Preencha apenas o que souber. Você pode retornar depois para complementar ou corrigir informações.']
    ],
    clinica: [
      ['.od-ui-simple-head', 'Sua clínica', 'Aqui ficam os dados que serão utilizados para personalizar os documentos.'],
      ['.od-ui-settings', 'Configurações disponíveis', 'Entre em Dados da clínica, Identidade visual ou Mapa da unidade conforme o que deseja atualizar.']
    ],
    identidade: [
      ['main h1', 'Identidade dos documentos', 'Configure logotipo, nome e cores para aplicar à apresentação dos documentos.'],
      ['main form', 'Salve as alterações', 'Use dados e imagens reais da clínica. Depois confira a prévia antes de imprimir.']
    ],
    'boas-vindas': [
      ['main h1', 'Mapa da sua unidade', 'As informações da clínica ajudam a organizar a trilha. As recomendações não substituem a avaliação do RT.'],
      ['main a[href*="trilha"]', 'Ir para a trilha', 'Abra os primeiros passos e continue a preparação dos documentos.']
    ],
    rotinas: [
      ['.od-ui-simple-head', 'Registros operacionais', 'Use esta área para documentar atividades que realmente aconteceram na clínica.'],
      ['.od-ui-operational', 'Escolha uma rotina', 'Registre limpezas, esterilização, manutenção ou EPIs com informações verdadeiras de execução.']
    ],
    'acervo-visual': [
      ['.od-ref-hero', 'Materiais ilustrados', 'Explore cenas e objetos para ajudar no reconhecimento visual de instrumentos.'],
      ['.od-ref-group', 'Abra cada conjunto', 'Selecione uma categoria e toque nos números sobre a cena para ver imagens isoladas. A indicação técnica deve ser conferida com o RT.']
    ]
  };
  const recordPages = ['registros-limpeza','registros-esterilizacao','registros-manutencao','registros-epi'];
  if (recordPages.includes(page)) screens[page] = [
    ['main h1', 'Registro da rotina', 'Registre apenas atividades realmente executadas. Estas informações podem servir de apoio ao histórico interno.'],
    ['main form', 'Preencha e salve', 'Informe data, responsável e observações reais; confira os campos antes de salvar.'],
    ['main table', 'Histórico', 'Consulte os registros anteriores para acompanhar o que foi realizado.']
  ];
  const fallback = [
    ['main h1', 'Esta tela', 'Confira o objetivo da página e as orientações antes de preencher informações.'],
    ['main form, main .card, main section', 'Use os elementos disponíveis', 'Edite somente informações reais e salve seus registros. Você pode voltar a esta ajuda sempre que precisar.'],
    ['.member-menu', 'Navegação', 'O menu reúne documentos, rotinas, acompanhamento e configurações da clínica.']
  ];

  function rememberWelcome() {
    try { localStorage.setItem(welcomeKey, 'done'); } catch (_) {
      try { sessionStorage.setItem(welcomeKey, 'done'); } catch (_2) { /* opt-out on blocked storage */ }
    }
  }
  function hasSeenWelcome() {
    try { return localStorage.getItem(welcomeKey) === 'done' || sessionStorage.getItem(welcomeKey) === 'done'; }
    catch (_) { return false; }
  }
  function clearHighlight() {
    if (highlighted) highlighted.classList.remove('od-tour-focus');
    highlighted = null;
  }
  function close() {
    clearHighlight();
    current = null;
    popup?.remove();
    popup = null;
    document.body.classList.remove('od-tour-open');
    if (lastFocus?.isConnected) lastFocus.focus({preventScroll:true});
  }
  function make(tag, cls, content) {
    const el = document.createElement(tag);
    if (cls) el.className = cls;
    if (content !== undefined) el.textContent = content;
    return el;
  }
  function addButton(wrapper,text,cls,handler) {
    const b = make('button',cls,text);
    b.type = 'button';
    b.addEventListener('click',handler);
    wrapper.append(b);
    return b;
  }
  function welcome() {
    if (popup) close();
    lastFocus = document.activeElement;
    document.body.classList.add('od-tour-open');
    popup = make('div','od-tour-backdrop');
    const modal = make('div','od-tour-welcome');
    modal.setAttribute('role','dialog');modal.setAttribute('aria-modal','true');modal.setAttribute('aria-labelledby','od-tour-welcome-title');
    modal.append(make('span','od-tour-kicker','SEU PRIMEIRO ACESSO'));
    const title = make('h2','', 'Bem-vindo ao Odonto em Dia.');title.id='od-tour-welcome-title';modal.append(title);
    modal.append(make('p','', 'Em poucos passos você organiza os dados da clínica, prepara as minutas e acompanha o que falta. Vou mostrar onde fica cada função.'));
    const steps = make('div','od-tour-welcome-steps');
    [
      ['01','Cadastre a clínica','Nome, dados da unidade e responsável técnico entram nos documentos.'],
      ['02','Siga sua trilha','Comece pelas tarefas sugeridas e avance no seu ritmo.'],
      ['03','Edite e organize','Salve minutas e prepare uma pasta A4 para impressão após revisão.']
    ].forEach(([n,name,description])=>{
      const row = make('div','od-tour-welcome-step');row.append(make('span','',n));
      const body = make('div','');body.append(make('strong','',name),make('small','',description));row.append(body);steps.append(row);
    });
    modal.append(steps);
    const actions = make('div','od-tour-welcome-actions');
    addButton(actions,'Agora não','od-tour-skip',()=>{rememberWelcome();close();});
    addButton(actions,'Me mostre esta tela','od-tour-primary',()=>{rememberWelcome();close();showGuide();});
    modal.append(actions);
    const foot = make('p','od-tour-note','Você pode reabrir a orientação em “Como usar”, no topo de cada tela.');modal.append(foot);
    popup.append(modal);document.body.append(popup);
    actions.querySelector('.od-tour-primary')?.focus();
  }
  function showGuide() {
    if (popup) close();
    lastFocus = document.activeElement;
    let steps = screens[page] || fallback;
    steps = steps.map(([selector,title,description])=>({target:document.querySelector(selector),title,description}));
    steps = steps.filter(step=>step.target);
    if (steps.length === 0) steps = fallback.map(([selector,title,description])=>({target:document.querySelector(selector),title,description})).filter(step=>step.target);
    if (!steps.length) steps = [{target:null,title:'Como usar esta tela',description:'Use o menu superior para navegar. Registre dados reais e salve antes de sair. Você pode reabrir esta ajuda quando quiser.'}];
    current = {steps,index:0};
    popup=make('div','od-tour-guide');
    popup.setAttribute('role','dialog');popup.setAttribute('aria-label','Orientação desta tela');
    const header=make('div','od-tour-guide-top');header.append(make('strong','', 'Como usar esta tela'));
    addButton(header,'×','od-tour-x',close).setAttribute('aria-label','Fechar ajuda');
    popup.append(header);
    const label=make('span','od-tour-guide-counter');label.dataset.counter='';popup.append(label);
    const title=make('h2','');title.dataset.title='';popup.append(title);
    const description=make('p','');description.dataset.description='';popup.append(description);
    const actions=make('div','od-tour-guide-actions');
    addButton(actions,'Anterior','od-tour-prev',()=>{if(!current)return;current.index=Math.max(0,current.index-1);renderGuide();});
    addButton(actions,'Próximo','od-tour-primary',()=>{if(!current)return;if(current.index>=current.steps.length-1){close();return;}current.index++;renderGuide();});
    popup.append(actions);
    document.body.append(popup);
    renderGuide();
  }
  function renderGuide() {
    if (!current || !popup) return;
    clearHighlight();
    const step=current.steps[current.index];
    popup.querySelector('[data-counter]').textContent=`PASSO ${current.index+1} DE ${current.steps.length}`;
    popup.querySelector('[data-title]').textContent=step.title;
    popup.querySelector('[data-description]').textContent=step.description;
    popup.querySelector('.od-tour-prev').disabled=current.index===0;
    popup.querySelector('.od-tour-primary').textContent=current.index===current.steps.length-1?'Concluir':'Próximo';
    if(step.target?.isConnected){
      const details=step.target.closest('details');
      if(details)details.open=true;
      highlighted=step.target;
      highlighted.classList.add('od-tour-focus');
      highlighted.scrollIntoView({behavior:'smooth',block:'center'});
    }
    popup.querySelector('.od-tour-primary')?.focus({preventScroll:true});
  }
  document.addEventListener('click',e=>{
    const btn=e.target.closest('[data-od-help-open]');
    if(btn){e.preventDefault();showGuide();}
  });
  document.addEventListener('keydown',e=>{
    if(e.key==='Escape'&&popup){e.preventDefault();rememberWelcome();close();}
    if(e.key==='Tab'&&popup?.classList.contains('od-tour-backdrop')){
      const controls=Array.from(popup.querySelectorAll('button:not([disabled])'));
      if(!controls.length)return;
      if(e.shiftKey&&document.activeElement===controls[0]){e.preventDefault();controls[controls.length-1].focus();}
      else if(!e.shiftKey&&document.activeElement===controls[controls.length-1]){e.preventDefault();controls[0].focus();}
    }
  });
  if(!hasSeenWelcome()) window.requestAnimationFrame(()=>welcome());
})();
