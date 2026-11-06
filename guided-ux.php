<?php
declare(strict_types=1);
/** Experiência guiada, sem avaliar conformidade ou preencher fatos não verificados. */
function od_ui_icon(string $name): string {
  $icons=[
    'home'=>'<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M9 21v-7h6v7"/>',
    'building'=>'<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h2M14 7h2M8 11h2M14 11h2M9 21v-5h6v5"/>',
    'palette'=>'<circle cx="12" cy="12" r="9"/><circle cx="7.5" cy="10" r="1"/><circle cx="12" cy="6.7" r="1"/><circle cx="17" cy="10" r="1"/><path d="M12 21a3 3 0 0 1-2-5l1.5-1.5a2 2 0 0 1 3.5 1.5c0 1.2 1.2 2 2.5 2"/>',
    'file'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
    'check'=>'<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/>',
    'shield'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
    'droplets'=>'<path d="M12 3c-3 5-6 8-6 12a6 6 0 1 0 12 0c0-4-3-7-6-12z"/><path d="M9 16c.2 1.1 1 2 2 2.3"/>',
    'sparkles'=>'<path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3zM19 17l.7 2.3L22 20l-2.3.7L19 23l-.7-2.3L16 20l2.3-.7L19 17z"/>',
    'wrench'=>'<path d="M14.7 6.3A6 6 0 0 0 7 13.8L3 18l3 3 4.2-4A6 6 0 0 0 17.7 9.3L14 13l-3-3 3.7-3.7z"/>',
    'users'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v2"/>',
    'clipboard'=>'<rect x="5" y="4" width="14" height="18" rx="2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h6"/>',
    'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
    'search'=>'<circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/>',
    'info'=>'<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>',
    'book'=>'<path d="M3 4h8a4 4 0 0 1 4 4v13H7a4 4 0 0 0-4 1zM21 4h-2a4 4 0 0 0-4 4v13h2a4 4 0 0 1 4 1z"/>',
    'list'=>'<path d="m4 7 2 2 3-4M12 7h8m-16 9 2 2 3-4m3 2h8"/>'
  ];
  return '<svg class="od-ui-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name]??$icons['file']).'</svg>';
}
function od_guided_recommendations(array $c): array {
  $set=[
    ['id'=>3,'why'=>'Documento central para reunir a organização e as rotinas do serviço.'],
    ['id'=>24,'why'=>'Mapeie como a clínica gerencia resíduos e os registros de destinação.'],
    ['id'=>5,'why'=>'Organize a rotina de higienização das mãos e precauções.'],
    ['id'=>7,'why'=>'Defina as rotinas de limpeza aplicáveis aos ambientes.'],
    ['id'=>23,'why'=>'Estruture prevenção, monitoramento e controle de infecções.'],
    ['id'=>16,'why'=>'Defina como a equipe responde a urgências e intercorrências.']
  ];
  if(($c['sterilization']??'interna')==='terceirizada')$set[]= ['id'=>19,'why'=>'Conferir contratos, responsáveis e evidências do prestador de processamento.'];
  else $set[]= ['id'=>11,'why'=>'Descrever processamento e monitorização apenas conforme os equipamentos e procedimentos reais.'];
  if((int)($c['chairs']??1)>=2)$set[]=['id'=>22,'why'=>'Detalhar responsabilidades e ações de segurança do paciente compatíveis com a estrutura.'];
  return $set;
}
function od_journey_data(array $c): array {
  $db=db();
  $profile=(trim((string)($c['name']??''))!=='' && !str_contains(mb_strtolower((string)$c['name']),'demonstração') && trim((string)($c['rt_name']??''))!=='' && trim((string)($c['rt_cro']??''))!=='');
  $q=od_brand_schema()->prepare('SELECT COUNT(*) FROM od_clinic_branding WHERE clinic_id=?');$q->execute([(int)$c['id']]);$brand=(int)$q->fetchColumn()>0;
  $q=odx_schema()->prepare("SELECT COUNT(*) FROM od_documents WHERE clinic_id=?");$q->execute([(int)$c['id']]);$docs=(int)$q->fetchColumn();
  $q=$db->prepare("SELECT COUNT(*) FROM od_documents WHERE clinic_id=? AND stage='revisao_rt'");$q->execute([(int)$c['id']]);$review=(int)$q->fetchColumn()>0;
  $records=false;
  foreach(['od_steril_monitoring','od_cleaning_records','od_ops_records'] as $table){
    $q=$db->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=?");$q->execute([$table]);
    if((int)$q->fetchColumn()===0)continue;
    $q=$db->prepare('SELECT COUNT(*) FROM '.$table.' WHERE clinic_id=?');$q->execute([(int)$c['id']]);
    if((int)$q->fetchColumn()>0){$records=true;break;}
  }
  return [
    ['title'=>'Confira os dados da clínica','description'=>'Informe nome, dados da unidade e responsável técnico. Essas informações entram automaticamente no cabeçalho dos documentos.','route'=>'diagnostico','cta'=>'Completar perfil','done'=>$profile,'icon'=>'building'],
    ['title'=>'Personalize seus documentos','description'=>'Escolha logo e cores uma única vez. A identidade aparecerá nas novas minutas sem precisar refazer o trabalho.','route'=>'identidade','cta'=>'Definir identidade','done'=>$brand,'icon'=>'palette'],
    ['title'=>'Prepare seu primeiro documento','description'=>'Comece pelo Manual de Boas Práticas. O conteúdo de referência já está no editor para adaptar à sua realidade.','route'=>'editar&id=3','cta'=>'Abrir o manual','done'=>$docs>0,'icon'=>'file'],
    ['title'=>'Registre uma atividade real','description'=>'Use um controle digital quando a atividade tiver sido executada. Exemplo: limpeza de um ambiente.','route'=>'registros-limpeza','cta'=>'Ver registro de limpeza','done'=>$records,'icon'=>'clipboard'],
    ['title'=>'Encaminhe uma minuta para revisão','description'=>'Quando adaptar um documento, marque “Aguardando revisão do RT”. Isso não significa aprovação.','route'=>'meus-documentos','cta'=>'Consultar minutas','done'=>$review,'icon'=>'shield'],
  ];
}
function od_guided_steps(array $c,bool $full=false): string {
  $steps=od_journey_data($c);$done=count(array_filter($steps,fn($r)=>$r['done']));$total=count($steps);
  $next=null;foreach($steps as $i=>$step)if(!$step['done']){$next=$i;break;}
  if($next===null)$next=$total-1;
  $s='<section class="od-ui-journey" aria-label="Trilha de primeiros passos"><div class="od-ui-journey-top"><div><span class="od-ui-kicker">SUA TRILHA DE USO</span><h2>'.($done===$total?'Primeiros passos organizados.':'Seu próximo passo, sem complicação.').'</h2><p>Uma etapa de cada vez. O progresso indica uso da plataforma, não conformidade sanitária.</p></div><div class="od-ui-progress"><strong>'.$done.' <small>/ '.$total.'</small></strong><span>passos iniciados ou preparados</span></div></div><div class="od-ui-progress-track" role="progressbar" aria-label="Progresso de configuração" aria-valuemin="0" aria-valuemax="'.$total.'" aria-valuenow="'.$done.'"><span style="width:'.(100*$done/$total).'%"></span></div>';
  if($full){
    $s.='<div class="od-ui-steps">';
    foreach($steps as $i=>$step)$s.='<a class="od-ui-step '.($step['done']?'is-done':($i===$next?'is-next':'')).'" href="?page='.esc($step['route']).'"><span class="od-ui-step-symbol">'.($step['done']?od_ui_icon('check'):od_ui_icon($step['icon'])).'</span><span><small>PASSO '.($i+1).' · '.($step['done']?'Preparado':'A fazer').'</small><strong>'.esc($step['title']).'</strong><em>'.esc($step['description']).'</em></span><span class="od-ui-step-link">'.od_ui_icon('arrow').'</span></a>';
    $s.='</div>';
  }else{
    $item=$steps[$next];$s.='<div class="od-ui-next"><div class="od-ui-next-icon">'.od_ui_icon($item['icon']).'</div><div class="od-ui-next-description"><span>PASSO '.($next+1).' DE '.$total.'</span><h3>'.esc($item['title']).'</h3><p>'.esc($item['description']).'</p></div><a class="od-ui-action" href="?page='.esc($item['route']).'">'.esc($item['cta']).' '.od_ui_icon('arrow').'</a></div><a class="od-ui-journey-more" href="?page=trilha">Ver todos os passos '.od_ui_icon('arrow').'</a>';
  }
  return $s.'</section>';
}
function od_guided_task_guides(): array {
  return [
    1=>['Licença e documentação do serviço','Conferir a licença sanitária, os dados cadastrais do estabelecimento e a documentação exigida pela autoridade sanitária local.','Conferir situação e validade da licença sanitária e reunir os documentos do estabelecimento para revisão pelo responsável legal e pelo RT. Não registrar como regularizado sem verificação.',[3]],
    2=>['Responsável técnico e equipe','Identificar o RT, sua habilitação e os registros de atribuições, equipe e capacitações.','Confirmar o RT e as funções da equipe; conferir comprovantes profissionais e organizar os registros de capacitação disponíveis.',[3,20,27,30]],
    3=>['Estrutura e instalações','Conferir áreas, fluxos e condições físicas em relação às atividades realizadas no serviço.','Levantar ambientes, instalações e eventuais pendências estruturais; submeter as evidências à avaliação técnica e à vigilância competente quando necessário.',[3]],
    4=>['Equipamentos','Identificar os equipamentos em uso e verificar manuais, inventário e registros de manutenção.','Revisar o inventário de equipamentos e reunir manuais, comprovantes de manutenção e registros de verificações técnicas.',[21,25,28]],
    5=>['Água e condições ambientais','Localizar pontos de água, tratamentos e registros de controle aplicáveis ao serviço.','Mapear pontos de água e reunir resultados de verificações e orientações técnicas aplicáveis, sem presumir parâmetros antes da avaliação.',[18,26]],
    6=>['Limpeza de ambientes','Conferir quais ambientes são limpos, por quem, com quais produtos e onde a execução é registrada.','Listar ambientes e superfícies; conferir produtos e instruções do fabricante; definir responsável e registro da execução com a equipe e o RT.',[7]],
    7=>['EPIs e proteção da equipe','Conferir EPIs utilizados de acordo com os riscos e a rotina de entrega e orientação.','Levantar EPIs por atividade e registros existentes de entrega, orientação e reposição para revisão técnica.',[6,15]],
    8=>['Processamento e esterilização','Revisar fluxo de instrumentais, processamento, monitoramento de ciclos ou contratação de terceiros.','Conferir a rotina real de processamento e os registros; verificar responsabilidade, rastreabilidade e contratos de terceiros quando existentes.',[8,9,10,11,12,13,19]],
    9=>['Resíduos de serviços de saúde','Conferir pontos de geração, separação, armazenamento e comprovantes de coleta/destinação.','Reunir dados reais de geração e segregação de resíduos, contratos e comprovantes de destinação para elaboração ou atualização do PGRSS.',[14,24]],
    10=>['Segurança do paciente','Revisar práticas de identificação, atendimento seguro, gestão de riscos e registro de incidentes.','Levantar medidas existentes de identificação segura, gestão de riscos e comunicação de incidentes para avaliação pelo RT.',[4,17,22]],
    11=>['Medicamentos e insumos','Verificar condições de recebimento, armazenamento e controle aplicáveis aos produtos utilizados.','Conferir registros existentes de armazenamento, identificação, validade e controle de insumos utilizados no serviço.',[3]],
    12=>['Prevenção e controle de infecções','Revisar a higienização das mãos, precauções e o acompanhamento de riscos de infecção.','Reunir rotinas de higiene das mãos, precauções e registros do serviço para revisar o plano de prevenção de infecções.',[5,23]],
    13=>['Registros e prontuários','Conferir organização, acesso, guarda e proteção dos registros da clínica, sem inserir dados de pacientes aqui.','Verificar política de guarda e acesso aos prontuários e registros administrativos, com avaliação de confidencialidade e proteção de dados.',[3]],
    14=>['Radiologia, quando aplicável','Se há radiologia, conferir os requisitos específicos de instalações, equipamentos e registros.','Confirmar se há radiologia na unidade; quando houver, levantar licenças, registros de equipamentos e exigências técnicas específicas.',[21,25]],
    15=>['Sedação e urgências, quando aplicável','Conferir riscos, recursos, treinamento e encaminhamento de urgências compatíveis com os serviços prestados.','Revisar com o RT a assistência prestada, os recursos de resposta a urgências e as necessidades específicas de sedação, quando houver.',[16]],
    16=>['Capacitação','Conferir quais orientações a equipe recebeu e como ciência e treinamentos são documentados.','Levantar temas de treinamento, responsáveis e comprovantes reais de participação para estruturar o planejamento de capacitação.',[20,27,30]],
    17=>['Acompanhamento e melhoria','Organizar revisões, registros de ocorrências e acompanhamento de ações corretivas.','Reunir registros de ocorrências e pendências, definir como serão acompanhadas e revisar ações com o RT conforme necessidade.',[17,22]],
  ];
}
function od_guided_doc_links(array $ids):string {
  $s='';
  foreach(array_slice($ids,0,3) as $id){$doc=template_by_id($id);if($doc)$s.='<a href="?page=editar&id='.(int)$id.'">'.od_ui_icon('file').'<span>'.esc($doc['title']).'</span>'.od_ui_icon('arrow').'</a>';}
  return $s;
}

function od_guided_dashboard_page(): never {
  $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
  h('Início',false);
  echo '<div class="od-ui-page"><header class="od-ui-welcome"><div><span class="od-ui-kicker">ÁREA DA CLÍNICA</span><h1>Bom ter você por aqui.</h1><p>'.esc($c['name']).' · Continue a organização da clínica a partir do próximo passo.</p></div><a href="?page=clinica" class="od-ui-textlink">'.od_ui_icon('building').' Dados da clínica</a></header>';
  echo od_guided_steps($c);
  echo '<section class="od-ui-section"><div class="od-ui-section-head"><div><span class="od-ui-kicker">ACESSO RÁPIDO</span><h2>O que você precisa fazer?</h2></div></div><div class="od-ui-shortcuts">';
  foreach([
    ['documentos','file','Preparar documentos','Comece por sugestões prioritárias e depois consulte os 30 materiais.'],
    ['rotinas','clipboard','Registrar uma rotina','Limpeza, esterilização, manutenção e EPI, somente após atividade real.'],
    ['acompanhamento','list','Organizar pendências','Confira o que falta por tema e use sugestões de próximos passos.']
  ] as [$url,$icon,$title,$desc])echo '<a href="?page='.$url.'" class="od-ui-shortcut"><span class="od-ui-shortcut-icon">'.od_ui_icon($icon).'</span><strong>'.esc($title).'</strong><small>'.esc($desc).'</small><span class="od-ui-shortcut-go">'.od_ui_icon('arrow').'</span></a>';
  echo '</div></section>';
  $q=odx_schema()->prepare('SELECT template_id,version,stage FROM od_documents WHERE clinic_id=? ORDER BY updated_at DESC LIMIT 3');$q->execute([(int)$c['id']]);$last=$q->fetchAll();
  if($last){echo '<section class="od-ui-section"><div class="od-ui-section-head"><div><span class="od-ui-kicker">CONTINUE DE ONDE PAROU</span><h2>Documentos em andamento</h2></div><a href="?page=meus-documentos" class="od-ui-textlink">Ver todos '.od_ui_icon('arrow').'</a></div><div class="od-ui-resume">';
    foreach($last as $row){$d=template_by_id((int)$row['template_id']);if(!$d)continue;echo '<a href="?page=editar&id='.(int)$row['template_id'].'"><span class="od-ui-resume-icon">'.od_ui_icon('file').'</span><span><strong>'.esc($d['title']).'</strong><small>Versão '.(int)$row['version'].' · '.($row['stage']==='revisao_rt'?'Aguardando revisão do RT':'Em elaboração').'</small></span>'.od_ui_icon('arrow').'</a>';}
    echo '</div></section>';
  }
  echo '<p class="od-ui-disclaimer">'.od_ui_icon('info').' As etapas ajudam a usar a plataforma. A avaliação dos requisitos e a aprovação de documentos dependem do responsável técnico e das autoridades competentes.</p></div>';
  foot();exit;
}
function od_guided_trail_page(): never {
  $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
  h('Minha trilha',false);
  echo '<div class="od-ui-page od-ui-narrow"><header class="od-ui-simple-head"><a href="?page=painel" class="od-ui-back">Início / Minha trilha</a><h1>Vamos organizar sua clínica.</h1><p>Estas etapas orientam o uso da plataforma. Você pode voltar a qualquer uma delas quando precisar.</p></header>';
  echo od_guided_steps($c,true);
  echo '<div class="od-ui-help-note">'.od_ui_icon('info').' Não há necessidade de preencher dados fictícios para avançar. Cada conclusão é baseada em informação salva ou atividade registrada de verdade.</div></div>';foot();exit;
}
function od_guided_clinic_page(): never {
  $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
  h('Minha clínica',false);
  echo '<div class="od-ui-page"><header class="od-ui-simple-head"><span class="od-ui-kicker">CONFIGURAÇÕES DA UNIDADE</span><h1>Sua clínica, seus documentos.</h1><p>Os dados e a identidade visual são reaproveitados nas minutas. Configure uma vez e edite quando necessário.</p></header><div class="od-ui-shortcuts od-ui-settings">';
  foreach([
    ['diagnostico','building','Dados e responsável técnico','Nome, CNPJ, endereço básico, equipe técnica e perfil da unidade.'],
    ['identidade','palette','Logo e identidade visual','Nome exibido, logotipo, cores e prévia dos documentos.'],
    ['boas-vindas','list','Mapa da unidade','Pontos de atenção com base nos serviços informados.']
  ] as [$route,$ico,$title,$desc])echo '<a href="?page='.$route.'" class="od-ui-shortcut"><span class="od-ui-shortcut-icon">'.od_ui_icon($ico).'</span><strong>'.esc($title).'</strong><small>'.esc($desc).'</small><span class="od-ui-shortcut-go">'.od_ui_icon('arrow').'</span></a>';
  echo '</div><a class="od-ui-textlink" href="?page=trilha">Voltar à trilha de uso '.od_ui_icon('arrow').'</a></div>';foot();exit;
}
function od_guided_rotinas_page(): never {
  $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
  h('Rotinas',false);
  echo '<div class="od-ui-page"><header class="od-ui-simple-head"><span class="od-ui-kicker">REGISTROS OPERACIONAIS</span><h1>Registre o que foi feito.</h1><p>Escolha uma rotina para registrar uma execução real e consultar seu histórico. Não é necessário abrir uma planilha.</p></header>';
  echo '<div class="od-ui-operational">';
  foreach([
    ['registros-limpeza','sparkles','Limpeza de ambientes','Registre ambiente, horário, responsável e o que ocorreu.','Registro de execução'],
    ['registros-esterilizacao','shield','Esterilização','Anote dados dos ciclos e indicadores observados, sem liberação automática de carga.','Rastreabilidade'],
    ['registros-manutencao','wrench','Manutenção','Acompanhe equipamentos, serviços e verificações realizadas.','Equipamentos'],
    ['registros-epi','users','Entrega e orientação de EPI','Registre entregas, orientações e ocorrências da equipe.','Equipe']
  ] as [$route,$icon,$title,$desc,$tag])echo '<a class="od-ui-operational-card" href="?page='.$route.'"><span class="od-ui-operational-symbol">'.od_ui_icon($icon).'</span><small>'.esc($tag).'</small><h2>'.esc($title).'</h2><p>'.esc($desc).'</p><span>Abrir controle '.od_ui_icon('arrow').'</span></a>';
  echo '</div><div class="od-ui-help-note">'.od_ui_icon('info').' Estes quatro controles são os registros digitais disponíveis agora. Outros assuntos possuem minutas e orientações na biblioteca, mas não necessariamente um formulário de registro próprio.</div></div>';foot();exit;
}
function od_guided_groups_page(): never {
  $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
  $db=db();$q=$db->prepare('SELECT * FROM tasks WHERE clinic_id=?');$q->execute([(int)$c['id']]);$byGroup=[];
  foreach($q as $r)$byGroup[(int)$r['group_no']]=$r;
  $guide=od_guided_task_guides();
  $buckets=[
   ['Comece pela organização', 'Dados, responsáveis e documentos básicos para levantar com a clínica.',[1,2,3,9,10,12]],
   ['Cuide dos processos do dia a dia','Verifique as atividades e registros que fazem parte da rotina real.',[5,6,7,8,11,13,14,15]],
   ['Mantenha o acompanhamento','Controle serviços, treinamentos e ações corretivas.',[4,16,17]]
  ];
  h('Acompanhamento',false);
  echo '<div class="od-ui-page od-ui-follow"><header class="od-ui-simple-head"><span class="od-ui-kicker">ACOMPANHAMENTO DA CLÍNICA</span><h1>O que precisa ser conferido?</h1><p>Escolha um assunto. Cada item explica o que levantar e sugere uma próxima ação. Responsável e prazo só são necessários quando você decidir acompanhar uma pendência concreta.</p></header>';
  echo '<div class="od-ui-help-note">'.od_ui_icon('info').' Os 17 temas organizam o trabalho dentro da plataforma. Eles não são 17 exigências independentes ou uma aprovação automática da Anvisa.</div>';
  foreach($buckets as $k=>$bucket){
    [$title,$sub,$ids]=$bucket;
    echo '<section class="od-ui-bucket"><div class="od-ui-section-head"><div><span class="od-ui-kicker">ETAPA '.($k+1).'</span><h2>'.esc($title).'</h2><p>'.esc($sub).'</p></div><span class="od-ui-count">'.count($ids).' assuntos</span></div><div class="od-ui-accordion">';
    foreach($ids as $i=>$id){
      $t=$byGroup[$id]??[];[$title2,$check,$suggest,$templates]=$guide[$id];
      $status=(string)($t['status']??'pendente');
      $statusName=['pendente'=>'A conferir','em_andamento'=>'Em acompanhamento','revisao_rt'=>'Aguardando RT','concluido'=>'Conferido internamente'][$status]??'A conferir';
      echo '<details class="od-ui-task" '.($k===0 && $i===0?'open':'').'><summary><span class="od-ui-task-no">'.str_pad((string)$id,2,'0',STR_PAD_LEFT).'</span><span class="od-ui-task-main"><strong>'.esc($title2).'</strong><small>'.esc($check).'</small></span><span class="od-ui-task-state">'.esc($statusName).'</span><span class="od-ui-caret"></span></summary><div class="od-ui-task-content">';
      echo '<div class="od-ui-task-explain"><span class="od-ui-kicker">O QUE CONFERIR</span><p>'.esc($check).'</p></div>';
      if($templates)echo '<div class="od-ui-task-related"><span class="od-ui-kicker">MODELOS QUE PODEM AJUDAR</span>'.od_guided_doc_links($templates).'</div>';
      echo '<form method="post" class="od-ui-task-form">'.token().'<input type="hidden" name="act" value="task"><input type="hidden" name="number" value="'.$id.'">';
      echo '<label class="od-ui-label">Andamento da verificação<select name="status">';
      foreach(['pendente'=>'Ainda não conferido','em_andamento'=>'Em acompanhamento','revisao_rt'=>'Solicitar revisão do RT','concluido'=>'Conferido internamente (não é certificação)'] as $value=>$label)echo '<option value="'.$value.'"'.($status===$value?' selected':'').'>'.esc($label).'</option>';
      echo '</select></label>';
      echo '<div class="od-ui-suggestion"><span class="od-ui-kicker">SUGESTÃO DE PRÓXIMO PASSO</span><p>'.esc($suggest).'</p><button type="button" class="od-ui-example" data-ui-target="od-ui-note-'.$id.'" data-ui-text="'.esc($suggest).'">'.od_ui_icon('check').' Usar este texto</button></div>';
      echo '<label class="od-ui-label">Próxima ação ou pendência identificada<textarea id="od-ui-note-'.$id.'" name="notes" maxlength="1000" rows="3" placeholder="Descreva uma ação real a realizar ou use a sugestão acima.">'.esc($t['notes']??'').'</textarea><small>Você pode editar a sugestão antes de salvar. Não marque como conferido sem verificar.</small></label>';
      echo '<details class="od-ui-optional"'.(!empty($t['responsible'])||!empty($t['due_date'])?' open':'').'><summary>'.od_ui_icon('calendar').' Definir responsável e prazo (opcional)</summary><div class="od-ui-two"><label class="od-ui-label">Quem vai acompanhar?<input name="responsible" maxlength="120" value="'.esc($t['responsible']??'').'" placeholder="Nome ou função"></label><label class="od-ui-label">Até quando?<input type="date" name="due_date" value="'.esc($t['due_date']??'').'"></label></div></details>';
      echo '<div class="od-ui-task-actions"><button type="submit" class="od-ui-action">Salvar acompanhamento '.od_ui_icon('arrow').'</button><small>Registro interno, sem validação sanitária automática.</small></div></form></div></details>';
    }
    echo '</div></section>';
  }
  echo '</div>';foot();exit;
}
function od_guided_library_page(): never {
  $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
  $q=odx_schema()->prepare('SELECT template_id,version,stage FROM od_documents WHERE clinic_id=?');$q->execute([(int)$c['id']]);$saved=[];foreach($q as $row)$saved[(int)$row['template_id']]=$row;
  $all=template_catalog();$counts=[];foreach($all as $d)$counts[$d['category']]=($counts[$d['category']]??0)+1;
  h('Documentos',false);
  echo '<div class="od-ui-page od-ui-docs"><header class="od-ui-docs-head"><div><span class="od-ui-kicker">DOCUMENTAÇÃO DA CLÍNICA</span><h1>Seus documentos, por ordem de uso.</h1><p>Os modelos já incluem conteúdo de referência e campos guiados. Comece por uma sugestão ou encontre um dos 30 materiais.</p></div><div class="od-ui-docs-stat"><strong>'.count($saved).' <span>/ '.count($all).'</span></strong><small>minutas iniciadas</small></div></header>';
  echo '<div class="od-ui-section-head od-ui-recommended-head"><div><span class="od-ui-kicker">SUGESTÕES PARA COMEÇAR</span><h2>Primeiros documentos</h2><p>Ordem prática de preparação. Ajuste conforme a atividade da clínica e orientação do RT.</p></div></div>';
  echo '<div class="od-ui-recommend-grid">';
  foreach(od_guided_recommendations($c) as $index=>$rec){
    $id=$rec['id'];$d=template_by_id($id);if(!$d)continue;
    $state=$saved[$id]??null;
    echo '<a class="od-ui-recommend-card" href="?page=editar&id='.$id.'"><div class="od-ui-recommend-symbol">'.od_ui_icon('file').'</div><div class="od-ui-recommend-content"><span>'.esc($d['category']).' · '.($state?'Versão '.(int)$state['version']:'Sugestão '.($index+1)).'</span><strong>'.esc($d['title']).'</strong><small>'.esc($rec['why']).'</small></div><span class="od-ui-recommend-go">'.od_ui_icon('arrow').'</span></a>';
  }
  echo '</div>';
  echo '<details class="od-ui-catalog" id="od-ui-catalog"><summary><span class="od-ui-catalog-icon">'.od_ui_icon('book').'</span><span><strong>Explorar todos os '.count($all).' modelos</strong><small>POPs, planos, SDBPF, formulários e orientações</small></span><span class="od-ui-caret"></span></summary><div class="od-ui-catalog-content"><label class="od-ui-search">'.od_ui_icon('search').'<span class="sr-only">Buscar documentos</span><input type="search" id="od-ui-search" placeholder="Buscar pelo nome do documento" autocomplete="off"></label><div class="od-ui-filters" role="group" aria-label="Filtrar modelos"><button type="button" class="is-selected" data-ui-filter="all">Todos <span>'.count($all).'</span></button>';
  foreach(['SDBPF','POPs','Planos','Formulários','Guia de uso'] as $cat)if(isset($counts[$cat]))echo '<button type="button" data-ui-filter="'.esc($cat).'">'.esc($cat).' <span>'.(int)$counts[$cat].'</span></button>';
  echo '</div><p class="od-ui-empty" id="od-ui-no-results" hidden>Nenhum modelo encontrado. Tente outro termo ou categoria.</p><div class="od-ui-all-grid">';
  foreach($all as $d){
    $id=(int)$d['id'];$state=$saved[$id]??null;
    echo '<a class="od-ui-all-card" data-ui-category="'.esc($d['category']).'" data-ui-search="'.esc(mb_strtolower((string)$d['title'])).'" href="?page=editar&id='.$id.'"><span class="od-ui-all-card-icon">'.od_ui_icon('file').'</span><span><small>'.esc($d['category']).($state?' · Versão '.(int)$state['version']:'').'</small><strong>'.esc($d['title']).'</strong></span>'.od_ui_icon('arrow').'</a>';
  }
  echo '</div></div></details><div class="od-ui-help-note">'.od_ui_icon('info').' Minutas para adaptação e revisão profissional. Nenhum arquivo constitui certificação, aprovação sanitária ou comprovação automática de execução.</div></div>';
  foot();exit;
}
function od_guided_field_suggestion(array $field,array $c): string {
  $key=(string)($field['key']??'');
  $name=trim((string)($c['name']??''));
  $suggestions=[
    'setor'=>'Áreas e processos da unidade a identificar conforme a rotina real; confirmar aplicabilidade com o responsável técnico.',
    'executor'=>'Profissionais designados para o procedimento conforme suas atribuições e a organização real da clínica, a confirmar pelo responsável técnico.',
    'frequencia'=>'Definir a frequência segundo os riscos, as instruções de fabricantes e a rotina efetiva do serviço. Submeter à revisão técnica antes de implementar.',
    'aplicabilidade'=>'Conferir a aplicação deste procedimento à realidade de '.($name?:'sua clínica').', considerando os serviços efetivamente prestados e a avaliação do RT.',
    'evidencias'=>'Relacionar apenas os registros e comprovantes existentes, identificando data, responsável, local de guarda e referência ao processo correspondente.',
    'governanca'=>'Descrever as atribuições do responsável legal, do RT e dos demais profissionais conforme a organização efetiva da clínica. Conferir com os envolvidos.',
    'capacitacao'=>'Relacionar treinamentos realizados e necessidades de orientação da equipe, com responsável, data e evidências quando disponíveis.',
    'registro'=>'Relacionar contratos, comprovantes e registros efetivamente disponíveis e indicar onde serão guardados, após conferência.',
    'controle_limpeza'=>'Definir quem verifica os registros de execução, como serão anotadas ocorrências e como as correções serão acompanhadas pelo RT.',
    'evidencias_epi'=>'Conferir registros de entrega, orientações e treinamentos existentes. Indicar local de guarda e responsável por atualizá-los.',
    'evidencia_manut'=>'Relacionar comprovantes reais de manutenção, laudos e registros de equipamento retirado de uso, com local de guarda.',
    'psp_notificacao'=>'Definir com a equipe como os incidentes serão registrados, comunicados e analisados, incluindo responsável e tratamento de ocorrências.',
    'iras_evidencias'=>'Relacionar apenas os indicadores e registros efetivamente mantidos pela clínica; definir quem os monitora e onde ficam arquivados.',
    'pgts_inventario'=>'Conferir o inventário de equipamentos, número de identificação, localização, situação de uso e documentos técnicos existentes.',
    'edu_evidencias'=>'Relacionar listas de presença, materiais de orientação e comprovantes de treinamentos efetivamente realizados.',
    'manut_pendencias'=>'Descrever como serão registradas falhas e pendências de manutenção, identificando equipamento, responsável e ação corretiva.'
  ];
  return $suggestions[$key]??'';
}

/**
 * Substitui SOMENTE campos cadastrais inequívocos já fornecidos.
 * Outros marcadores, como [DATA], [PREENCHER] e ASSINATURA, exigem ação humana.
 */
function od_guided_prepare_template_html(string $html,array $clinic): string {
  $name=trim((string)($clinic['name']??''));
  $cnpj=trim((string)($clinic['cnpj']??''));
  $rt=trim((string)($clinic['rt_name']??''));
  $cro=trim((string)($clinic['rt_cro']??''));
  $replacements=[];
  if($name!==''&&$cnpj!=='')$replacements['[NOME / CNPJ]']=esc($name.' / '.$cnpj);
  if($rt!==''&&$cro!==''){
    $replacements['[NOME / CRO-UF]']=esc($rt.' / '.$cro);
    $replacements['[NOME / CRO]']=esc($rt.' / '.$cro);
  }
  return strtr($html,$replacements);
}
