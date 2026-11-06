<?php
/** Odonto em Dia / Document System v1: ferramenta online de minutas, não certificação sanitária. */
declare(strict_types=1);

function odx_fields(int $templateId): array {
    $common = [
        ['key'=>'setor','label'=>'Setor ou área de aplicação','hint'=>'Ex.: consultórios, CME, setor administrativo','type'=>'text'],
        ['key'=>'executor','label'=>'Responsável pela execução','hint'=>'Cargo ou equipe responsável pela rotina','type'=>'text'],
        ['key'=>'frequencia','label'=>'Frequência ou condição de revisão','hint'=>'De acordo com o processo real da clínica','type'=>'text'],
    ];
    $special = [
        3 => [
            ['key'=>'escopo','label'=>'Serviços e atividades abrangidos','hint'=>'Descreva somente os serviços prestados na unidade','type'=>'textarea'],
            ['key'=>'governanca','label'=>'Como são distribuídas as responsabilidades','hint'=>'Descreva os papéis do responsável técnico, direção e equipe','type'=>'textarea'],
            ['key'=>'evidencias','label'=>'Onde ficam os registros e evidências','hint'=>'Indique o local real de guarda e acesso','type'=>'textarea'],
            ['key'=>'capacitacao','label'=>'Como a equipe recebe orientação','hint'=>'Registre como a equipe será treinada e comunicada','type'=>'textarea']
        ],
        24 => [
            ['key'=>'geracao','label'=>'Pontos de geração de resíduos','hint'=>'Liste os ambientes e classes realmente presentes','type'=>'textarea'],
            ['key'=>'segregacao','label'=>'Separação e acondicionamento adotados','hint'=>'Descreva coletores, identificação e locais','type'=>'textarea'],
            ['key'=>'coleta','label'=>'Coleta, contratos e destinação','hint'=>'Identifique prestadores, licenças e comprovantes, quando aplicáveis','type'=>'textarea'],
            ['key'=>'registro','label'=>'Documentos comprobatórios e localização','hint'=>'Anote a localização de contratos e registros de destinação','type'=>'textarea'],
        ],
        11 => [
            ['key'=>'equipamento','label'=>'Equipamento e identificação','hint'=>'Autoclave e identificação utilizada na clínica','type'=>'text'],
            ['key'=>'processo','label'=>'Rotina real de processamento','hint'=>'Descreva o procedimento aprovado, com referência ao fabricante','type'=>'textarea'],
            ['key'=>'monitoramento','label'=>'Como os ciclos são monitorados','hint'=>'Indique os indicadores e critérios definidos pelo RT','type'=>'textarea'],
            ['key'=>'registros','label'=>'Registro, rastreabilidade e liberação','hint'=>'Indique onde guardar registros e quem decide sobre a liberação','type'=>'textarea']
        ]
    ];
    return array_merge($common, $special[$templateId] ?? [
        ['key'=>'aplicabilidade','label'=>'Como se aplica à sua clínica','hint'=>'Descreva o uso real do documento neste estabelecimento','type'=>'textarea'],
        ['key'=>'evidencias','label'=>'Registros e evidências relacionados','hint'=>'Indique onde encontrar evidências da rotina','type'=>'textarea']
    ]);
}
function odx_spec(int $id, string $category): array {
    $all = [
        3 => ['name'=>'Manual de Boas Práticas','eyebrow'=>'Governança e organização documental','number'=>'SDBPF / 01','flow'=>['Identificar','Definir responsabilidades','Organizar evidências','Revisar com RT'],'accent'=>'jade'],
        24 => ['name'=>'PGRSS','eyebrow'=>'Gerenciamento dos resíduos de saúde','number'=>'PLANO / PGRSS','flow'=>['Mapear geração','Definir segregação','Registrar destinação','Revisar com RT'],'accent'=>'amber'],
        11 => ['name'=>'POP de Esterilização','eyebrow'=>'Processamento e monitorização','number'=>'POP / 08','flow'=>['Preparar rotina','Monitorar ciclos','Registrar resultados','Revisar com RT'],'accent'=>'blue']
    ];
    return $all[$id] ?? ['name'=>'Documento da clínica','eyebrow'=>$category,'number'=>strtoupper($category).' / '.str_pad((string)$id,2,'0',STR_PAD_LEFT),'flow'=>['Identificar','Personalizar','Reunir evidências','Revisar com RT'],'accent'=>'jade'];
}
function odx_schema(): PDO {
    $db=od_editor_schema();
    $db->exec('CREATE TABLE IF NOT EXISTS od_document_details (clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, fields_json TEXT NOT NULL DEFAULT \'{}\', updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (clinic_id, template_id))');
    return $db;
}
function odx_get_fields(int $clinicId,int $templateId): array {
    $query=odx_schema()->prepare('SELECT fields_json FROM od_document_details WHERE clinic_id=? AND template_id=?');
    $query->execute([$clinicId,$templateId]);$json=$query->fetchColumn();
    $data=is_string($json)?json_decode($json,true):null;
    return is_array($data)?$data:[];
}
function odx_post(): never {
    if (!user() || !($c=clinic())) {http_response_code(403);exit('Sem acesso');}
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
    $doc=$id?template_by_id($id):null;
    if (!$doc) {http_response_code(400);exit('Documento inexistente');}
    $fields=$_POST['g']??[];
    if (!is_array($fields)) {http_response_code(400);exit('Campos inválidos');}
    $data=[];
    foreach (odx_fields((int)$id) as $field) {
        $key=$field['key'];$v=$fields[$key]??'';
        if(!is_string($v)){http_response_code(400);exit('Campo inválido');}
        $v=trim($v);
        if(mb_strlen($v,'UTF-8')>1500){flash('Um campo ultrapassou o limite de 1500 caracteres.');redirect('?page=editar&id='.$id);}
        $data[$key]=$v;
    }
    $body=(string)($_POST['body_html']??'');
    $html=od_safe_document($body);
    if(strlen($html)<50){flash('O documento precisa ter conteúdo.');redirect('?page=editar&id='.$id);}
    $stage=(string)($_POST['stage']??'rascunho');
    if(!in_array($stage,['rascunho','revisao_rt'],true))$stage='rascunho';
    $db=odx_schema();
    try {
        $db->beginTransaction();
        $q=$db->prepare('SELECT version FROM od_documents WHERE clinic_id=? AND template_id=?');$q->execute([(int)$c['id'],(int)$id]);
        $v=$q->fetchColumn();$next=$v===false?1:(int)$v+1;
        $db->prepare('INSERT INTO od_documents(clinic_id,template_id,content_html,version,stage,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,template_id) DO UPDATE SET content_html=excluded.content_html,version=excluded.version,stage=excluded.stage,updated_at=CURRENT_TIMESTAMP')->execute([$c['id'],$id,$html,$next,$stage]);
        $db->prepare('INSERT INTO od_document_history(clinic_id,template_id,version,content_html,stage) VALUES(?,?,?,?,?)')->execute([$c['id'],$id,$next,$html,$stage]);
        $db->prepare('INSERT INTO od_document_details(clinic_id,template_id,fields_json,updated_at) VALUES(?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,template_id) DO UPDATE SET fields_json=excluded.fields_json,updated_at=CURRENT_TIMESTAMP')->execute([$c['id'],$id,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
        $db->commit();
        flash('Versão '.$next.' salva. A revisão técnica ainda precisa ser realizada pelo RT.');
    } catch(Throwable $e) {
        if($db->inTransaction())$db->rollBack();
        error_log('[Odonto] Falha ao salvar modelo premium');
        flash('Não foi possível salvar as alterações.');
    }
    redirect('?page=editar&id='.$id);
}
function odx_stage(array $saved): string {return ($saved['stage']??'rascunho')==='revisao_rt'?'Aguardando revisão do RT':'Minuta em elaboração';}
function odx_count(array $data,array $schema): array {
    $count=0;foreach($schema as $f){if(trim((string)($data[$f['key']]??''))!=='')$count++;}
    return [$count,count($schema)];
}
function odx_page():never {
    $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
    $id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
    $doc=$id?template_by_id($id):null;
    if(!$doc){http_response_code(404);exit('Documento não encontrado');}
    $saved=od_editor_doc((int)$id,(int)$c['id']);
    $html=$saved['content_html']??od_safe_document((string)$doc['html']);
    $data=odx_get_fields((int)$c['id'],(int)$id);
    $fields=odx_fields((int)$id);$meta=odx_spec((int)$id,(string)$doc['category']);
    [$done,$total]=odx_count($data,$fields);
    h('Documento da clínica');
    echo '<div class="odx-topline"><a href="?page=documentos">← Biblioteca</a><span> / </span><span>'.esc($doc['category']).'</span><span> / </span><strong>'.esc($meta['number']).'</strong></div>';
    echo '<div class="odx-page-title"><div><span class="odx-eyebrow">DOCSYSTEM / DOCUMENTAÇÃO DA CLÍNICA</span><h2>'.esc($doc['title']).'</h2><p>Preencha ao lado, acompanhe o documento e salve novas versões. Os materiais são minutas para revisão do RT.</p></div><span class="odx-version">'.($saved?'Versão '.(int)$saved['version']:'Novo documento').'</span></div>';
    echo '<form method="post" class="odx-workspace" id="odx-form">'.token().'<input type="hidden" name="act" value="premium_save"><input type="hidden" name="id" value="'.(int)$id.'"><textarea hidden name="body_html" id="odx-body-html"></textarea>';
    echo '<aside class="odx-guided" aria-label="Personalização guiada"><div class="odx-side-intro"><span class="odx-side-kicker">PREENCHIMENTO GUIADO</span><h3>Dados da sua unidade</h3><p>Esses campos ficam salvos com o documento e aparecem imediatamente na prévia.</p></div><div class="odx-completion"><div><span>Informações fornecidas</span><strong data-odx-progress-label>'.(int)$done.' de '.(int)$total.'</strong></div><div class="odx-track"><i data-odx-progress style="width:'.($total?round($done/$total*100):0).'%"></i></div></div>';
    foreach($fields as $i=>$f){$key=$f['key'];$value=(string)($data[$key]??'');$tag=$f['type']??'text';$idAttr='odx-f-'.$key;
        echo '<label class="odx-field" for="'.esc($idAttr).'"><span><em>'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT).'</em>'.esc($f['label']).'</span>';
        if($tag==='textarea')echo '<textarea id="'.esc($idAttr).'" name="g['.esc($key).']" data-odx-field="'.esc($key).'" maxlength="1500" rows="3" placeholder="'.esc($f['hint']).'">'.esc($value).'</textarea>';
        else echo '<input id="'.esc($idAttr).'" name="g['.esc($key).']" data-odx-field="'.esc($key).'" maxlength="1500" value="'.esc($value).'" placeholder="'.esc($f['hint']).'">';
        echo '</label>';
    }
    echo '<div class="odx-review-control"><label for="odx-status">Situação da minuta</label><select name="stage" id="odx-status"><option value="rascunho">Em elaboração</option><option value="revisao_rt" '.(($saved['stage']??'')==='revisao_rt'?'selected':'').'>Aguardando revisão do RT</option></select><p>O status é interno e não representa aprovação sanitária.</p></div><button class="odx-save" type="submit">Salvar versão da minuta <span>↗</span></button><p class="odx-safe">Os dados pertencem somente à sua clínica. Nenhuma informação de paciente é necessária para preencher este modelo.</p></aside>';
    echo '<section class="odx-document-area" aria-label="Visualização e conteúdo da minuta"><div class="odx-toolbar"><div><span class="odx-live"></span> PRÉVIA DO DOCUMENTO <span class="odx-subline">atualizada enquanto você preenche</span></div><div class="odx-tools"><button type="button" id="odx-edit-toggle" aria-pressed="false">Editar texto</button><button type="button" id="odx-sections-toggle">Ver seções</button></div></div>';
    echo '<article class="odx-paper odx-accent-'.esc($meta['accent']).'" aria-label="Documento da clínica"><div class="odx-paper-top"><div class="odx-seal"><span>OD</span></div><div><strong>'.esc($c['name']).'</strong><small>'.esc($c['city']).($c['state']?' · '.esc($c['state']):'').' &nbsp;·&nbsp; '.esc($c['cnpj']).'</small></div><div class="odx-paper-code">'.esc($meta['number']).'</div></div>';
    echo '<div class="odx-cover"><div class="odx-cover-lines"><span></span><span></span><span></span></div><span class="odx-cover-kicker">'.esc($meta['eyebrow']).'</span><h2>'.esc($meta['name']).'</h2><p>'.esc($doc['title']).'</p><div class="odx-cover-meta"><span>DOCUMENTO DE TRABALHO</span><strong>'.($saved?'VERSÃO '.(int)$saved['version']:'VERSÃO INICIAL').'</strong></div></div>';
    echo '<div class="odx-paper-inside"><div class="odx-facts"><div><small>Estabelecimento</small><strong>'.esc($c['name']).'</strong></div><div><small>Responsável técnico</small><strong>'.esc($c['rt_name']).($c['rt_cro']?' · '.esc($c['rt_cro']):'').'</strong></div><div><small>Status</small><strong>'.esc(odx_stage($saved??[])).'</strong></div></div>';
    echo '<section class="odx-flow" aria-label="Mapa de organização do documento"><div class="odx-flow-head"><div><span>VISÃO DO PROCESSO</span><h3>Etapas para organizar este material</h3></div><small>Esquema de organização, não instrução clínica</small></div><div class="odx-flow-steps">';
    foreach($meta['flow'] as $i=>$name){echo '<div class="odx-flow-step"><span>'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT).'</span><strong>'.esc($name).'</strong></div>';}
    echo '</div></section>';
    echo '<section class="odx-field-preview"><div class="odx-field-preview-head"><div><span>INFORMAÇÕES PERSONALIZADAS</span><h3>Configuração desta unidade</h3></div><small>Atualização em tempo real</small></div><div class="odx-summary-grid">';
    foreach($fields as $field){$key=$field['key'];$text=trim((string)($data[$key]??''));echo '<div class="odx-summary-item"><span>'.esc($field['label']).'</span><strong data-odx-display="'.esc($key).'" class="'.($text?'':'odx-empty').'">'.($text?nl2br(esc($text)):'A preencher').'</strong></div>';}
    echo '</div></section>';
    echo '<div class="odx-source-head"><div><span>MINUTA DE REFERÊNCIA</span><h3>Conteúdo técnico para adaptação</h3></div><p>Revisar procedimentos, equipamentos e referências com o responsável técnico.</p></div>';
    echo '<div class="odx-document-body" id="odx-document-body" aria-label="Conteúdo técnico" contenteditable="false">'.$html.'</div>';
    echo '<div class="odx-approval"><div><span>CONTROLE E REVISÃO</span><h3>Revisão pelo responsável técnico</h3><p>Este documento não foi aprovado automaticamente. A clínica deve revisar, adaptar, implementar e registrar a aprovação técnica pertinente.</p></div><div class="odx-approval-line"><small>Responsável técnico / CRO</small><span>Assinatura e data após validação</span></div></div>';
    echo '<div class="odx-paper-foot"><span>ODONTO EM DIA · DOCUMENTAÇÃO INTERNA</span><span>Modelo de referência · sem certificação automática</span></div></div></article>';
    echo '<div class="odx-outline" hidden id="odx-outline"><h3>Seções do documento</h3><ol data-odx-outline></ol></div>';
    echo '<div class="odx-bottom-hint"><strong>Este material é uma minuta de referência.</strong> Não substitui a avaliação da aplicabilidade, a revisão e a aprovação profissional.</div></section></form>';
    foot();exit;
}
function odx_library():never {
    $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
    $db=odx_schema();$s=$db->prepare('SELECT template_id,version,stage FROM od_documents WHERE clinic_id=?');$s->execute([(int)$c['id']]);
    $saved=[];foreach($s as $v)$saved[(int)$v['template_id']]=$v;
    $all=template_catalog();
    $counts=[];foreach($all as $d){$counts[$d['category']]=($counts[$d['category']]??0)+1;}
    h('Biblioteca documental');echo '<div class="odx-lib-header"><div><span>ACERVO DA CLÍNICA</span><h2>Seus documentos, organizados.</h2><p>Abra um modelo, preencha os dados do consultório e salve sua própria versão. Tudo dentro da plataforma.</p></div><div class="odx-lib-stats"><b>'.count($all).'</b><span>materiais de referência</span></div></div>';
    echo '<div class="odx-library-alert">As minutas exigem análise, personalização e aprovação por profissional habilitado. Nenhum modelo concede certificação da Anvisa.</div>';
    echo '<div class="odx-category-filter" role="group" aria-label="Filtrar documentos"><button type="button" class="is-selected" data-odx-filter="all">Todos</button>';
    foreach(['SDBPF','POPs','Planos','Formulários','Guia de uso'] as $cat){if(isset($counts[$cat]))echo '<button type="button" data-odx-filter="'.esc($cat).'">'.esc($cat).' <span>'.(int)$counts[$cat].'</span></button>';}
    echo '</div><div class="odx-library-grid">';
    foreach($all as $d){$id=(int)$d['id'];$meta=odx_spec($id,(string)$d['category']);$state=$saved[$id]??null;$isPilot=in_array($id,[3,24,11],true);$labels=['SDBPF'=>'Governança','POPs'=>'Procedimentos','Planos'=>'Planejamento','Formulários'=>'Registros','Guia de uso'=>'Orientações'];
        echo '<a href="?page=editar&id='.$id.'" class="odx-library-card" data-odx-category="'.esc($d['category']).'"><div class="odx-card-preview odx-accent-'.esc($meta['accent']).'"><div class="odx-card-strip"><span>ODONTO EM DIA</span><small>'.esc($meta['number']).'</small></div><div class="odx-card-preview-content"><div class="odx-card-mini-label">'.esc($labels[$d['category']]??$d['category']).'</div><strong>'.esc($d['title']).'</strong><i></i><i></i><i></i></div><div class="odx-card-preview-bottom"><span>MINUTA TÉCNICA</span><em>◈</em></div></div><div class="odx-card-info"><span class="odx-card-status">'.($state?($state['stage']==='revisao_rt'?'Em revisão do RT':'Versão '.(int)$state['version'].' salva'):'Não iniciado').'</span>'.($isPilot?'<span class="odx-card-feature">Layout aprimorado</span>':'').'<h3>'.esc($d['title']).'</h3><p>'.esc($d['category']).' · Editar e acompanhar na plataforma</p><span class="odx-card-go">Abrir documento <b>↗</b></span></div></a>';
    }
    echo '</div>';foot();exit;
}