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
