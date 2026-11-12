<?php
/** Odonto em Dia / Document System v1: ferramenta online de minutas, não certificação sanitária. */
declare(strict_types=1);
require_once __DIR__.'/document-plan-config.php';

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
        ],
        6 => [
            ['key'=>'epis','label'=>'EPIs conforme riscos identificados','hint'=>'Relacione os itens adequados conforme o procedimento e a avaliação do RT','type'=>'textarea'],
            ['key'=>'entrega','label'=>'Como ocorre entrega e reposição','hint'=>'Descreva local, responsáveis e critério para reposição','type'=>'textarea'],
            ['key'=>'orientacoes','label'=>'Orientação, utilização e guarda','hint'=>'Referencie as orientações efetivamente adotadas na clínica','type'=>'textarea'],
            ['key'=>'evidencias_epi','label'=>'Registros e comprovações internas','hint'=>'Informe onde ficam os registros de entrega e treinamento','type'=>'textarea']
        ],
        7 => [
            ['key'=>'ambientes','label'=>'Ambientes e superfícies abrangidos','hint'=>'Descreva as áreas reais do estabelecimento','type'=>'textarea'],
            ['key'=>'produtos','label'=>'Produtos e diluições conforme validação técnica','hint'=>'Registre os produtos aprovados, com referência às instruções do fabricante','type'=>'textarea'],
            ['key'=>'rotina_limpeza','label'=>'Sequência, responsáveis e frequência','hint'=>'Descreva a rotina efetivamente adotada e aprovada','type'=>'textarea'],
            ['key'=>'controle_limpeza','label'=>'Registro e tratamento de ocorrências','hint'=>'Indique como registrar, conferir e tratar desvios','type'=>'textarea']
        ],
        21 => [
            ['key'=>'inventario','label'=>'Inventário e identificação dos equipamentos','hint'=>'Indique localização do inventário e forma de identificação','type'=>'textarea'],
            ['key'=>'cronograma','label'=>'Plano e periodicidade de manutenção','hint'=>'Definir conforme fabricante, risco e avaliação técnica','type'=>'textarea'],
            ['key'=>'prestadores','label'=>'Responsáveis e prestadores envolvidos','hint'=>'Informe responsáveis internos e contratos quando existirem','type'=>'textarea'],
            ['key'=>'evidencia_manut','label'=>'Comprovantes e tratamento de falhas','hint'=>'Onde registrar serviços, laudos e equipamentos indisponíveis','type'=>'textarea']
        ]
    ];
    // Preserve os campos legados mesmo quando uma minuta passa a ter preenchimento especializado.
    // Dessa forma, conteúdo já salvo permanece visível e editável na plataforma.
    $legacy = [
        ['key'=>'aplicabilidade','label'=>'Aplicabilidade já informada','hint'=>'Informações do modelo original, se já preenchido','type'=>'textarea'],
        ['key'=>'evidencias','label'=>'Evidências já informadas','hint'=>'Registros do modelo anterior, se já preenchidos','type'=>'textarea']
    ];
    $plan = odx_document_configs()[$templateId] ?? null;
    $result=$plan!==null ? array_merge($common,$plan['fields'],$legacy) : array_merge($common,$special[$templateId] ?? [
        ['key'=>'aplicabilidade','label'=>'Como se aplica à sua clínica','hint'=>'Descreva o uso real do documento neste estabelecimento','type'=>'textarea'],
        ['key'=>'evidencias','label'=>'Registros e evidências relacionados','hint'=>'Indique onde encontrar evidências da rotina','type'=>'textarea']
    ]);
    // Evita campos duplicados (a chave "evidencias" também existe no cadastro legado).
    $byKey=[];
    foreach($result as $field){$key=(string)$field['key'];if(!isset($byKey[$key]))$byKey[$key]=$field;}
    return array_values($byKey);
}
function odx_spec(int $id, string $category): array {
    $all = [
        3 => ['name'=>'Manual de Boas Práticas','eyebrow'=>'Governança e organização documental','number'=>'SDBPF / 01','flow'=>['Identificar','Definir responsabilidades','Organizar evidências','Revisar com RT'],'accent'=>'jade'],
        24 => ['name'=>'PGRSS','eyebrow'=>'Gerenciamento dos resíduos de saúde','number'=>'PLANO / PGRSS','flow'=>['Mapear geração','Definir segregação','Registrar destinação','Revisar com RT'],'accent'=>'amber'],
        11 => ['name'=>'POP de Esterilização','eyebrow'=>'Processamento e monitorização','number'=>'POP / 08','flow'=>['Preparar rotina','Monitorar ciclos','Registrar resultados','Revisar com RT'],'accent'=>'blue'],
        6 => ['name'=>'POP de EPI','eyebrow'=>'Saúde ocupacional e rotinas da equipe','number'=>'POP / 03','flow'=>['Avaliar riscos','Definir itens','Orientar equipe','Registrar entregas'],'accent'=>'jade'],
        7 => ['name'=>'POP de Limpeza de Ambientes','eyebrow'=>'Higienização, execução e registros','number'=>'POP / 04','flow'=>['Mapear áreas','Definir produtos','Executar rotina','Registrar limpeza'],'accent'=>'blue'],
        21 => ['name'=>'Inventário e Manutenção','eyebrow'=>'Gestão de equipamentos e intervenções','number'=>'POP / 18','flow'=>['Identificar equipamentos','Programar cuidados','Registrar serviços','Revisar evidências'],'accent'=>'amber']
    ];
    $plans = odx_document_configs();
    return $all[$id] ?? ($plans[$id]['spec'] ?? ['name'=>'Documento da clínica','eyebrow'=>$category,'number'=>strtoupper($category).' / '.str_pad((string)$id,2,'0',STR_PAD_LEFT),'flow'=>['Identificar','Personalizar','Reunir evidências','Revisar com RT'],'accent'=>'jade']);
}
function odx_schema(): PDO {
    $db=od_editor_schema();
    $db->exec('CREATE TABLE IF NOT EXISTS od_document_details (clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, fields_json TEXT NOT NULL DEFAULT \'{}\', updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (clinic_id, template_id))');
    $db->exec('CREATE TABLE IF NOT EXISTS od_document_fields_history (clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, version INTEGER NOT NULL, fields_json TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(clinic_id,template_id,version))');
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
    od_visual_schema();
    od_mat_schema();
    try {
        $db->beginTransaction();
        $q=$db->prepare('SELECT version FROM od_documents WHERE clinic_id=? AND template_id=?');$q->execute([(int)$c['id'],(int)$id]);
        $v=$q->fetchColumn();$next=$v===false?1:(int)$v+1;
        $db->prepare('INSERT INTO od_documents(clinic_id,template_id,content_html,version,stage,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,template_id) DO UPDATE SET content_html=excluded.content_html,version=excluded.version,stage=excluded.stage,updated_at=CURRENT_TIMESTAMP')->execute([$c['id'],$id,$html,$next,$stage]);
        $db->prepare('INSERT INTO od_document_history(clinic_id,template_id,version,content_html,stage) VALUES(?,?,?,?,?)')->execute([$c['id'],$id,$next,$html,$stage]);
        $fieldsJson=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $db->prepare('INSERT INTO od_document_details(clinic_id,template_id,fields_json,updated_at) VALUES(?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,template_id) DO UPDATE SET fields_json=excluded.fields_json,updated_at=CURRENT_TIMESTAMP')->execute([$c['id'],$id,$fieldsJson]);
        $db->prepare('INSERT INTO od_document_fields_history(clinic_id,template_id,version,fields_json) VALUES(?,?,?,?)')->execute([$c['id'],$id,$next,$fieldsJson]);
        od_brand_snapshot($db,$c,(int)$id,$next);
        od_visual_snapshot($db,(int)$c['id'],(int)$id,$next);
        od_mat_snapshot($db,$c,(int)$id,$next);
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
    $requestedVersion=filter_var($_GET['version']??null,FILTER_VALIDATE_INT);
    $historical=null;
    if($requestedVersion && $saved && $requestedVersion<=(int)$saved['version']){
        $query=odx_schema()->prepare('SELECT version,stage,content_html FROM od_document_history WHERE clinic_id=? AND template_id=? AND version=?');
        $query->execute([(int)$c['id'],(int)$id,$requestedVersion]);
        $historical=$query->fetch()?:null;
    }
    $html=$historical['content_html']??$saved['content_html']??od_safe_document(od_guided_prepare_template_html((string)$doc['html'],$c));
    $data=odx_get_fields((int)$c['id'],(int)$id);
    if($historical){
        $q=odx_schema()->prepare('SELECT fields_json FROM od_document_fields_history WHERE clinic_id=? AND template_id=? AND version=?');
        $q->execute([(int)$c['id'],(int)$id,$requestedVersion]);
        $snapshot=$q->fetchColumn();$data=is_string($snapshot)?(json_decode($snapshot,true)?:[]):[];
    }
    $profileExtra=od_profile_current($c);
    $profileContext=od_profile_context($c,$profileExtra);
    if(!$historical)$html=od_profile_apply_document($html,$profileContext,$data);
    $brand=od_brand_current($c);
    $paperClinic=$c;
    $brandLogoUrl='?page=logo-clinica';
    if($historical){
        $brandAtVersion=od_brand_historical((int)$c['id'],(int)$id,(int)$requestedVersion);
        if($brandAtVersion){
            $brand=$brandAtVersion;
            $paperClinic=array_merge($c,$brandAtVersion['clinic_info']);
            $brandLogoUrl='?page=logo-clinica&template_id='.(int)$id.'&version='.(int)$requestedVersion;
        }
    }
    // Documentos existentes exibem a arte gravada na própria versão.
    // Para versões anteriores ao pack, não adicionamos uma ilustração retroativamente.
    $visual=$historical
        ? od_visual_historical((int)$c['id'],(int)$id,(int)$requestedVersion)
        : ($saved ? od_visual_historical((int)$c['id'],(int)$id,(int)$saved['version']) : od_visual_current((int)$id));
    $fields=odx_fields((int)$id);$meta=odx_spec((int)$id,(string)$doc['category']);
    [$done,$total]=odx_count($data,$fields);
    h('Documento da clínica',false);
    echo '<div class="odx-topline"><a href="?page=documentos">← Biblioteca</a><span> / </span><span>'.esc($doc['category']).'</span><span> / </span><strong>'.esc($meta['number']).'</strong></div>';
    echo '<div class="odx-page-title"><div><span class="odx-eyebrow">DOCSYSTEM / DOCUMENTAÇÃO DA CLÍNICA</span><h2>'.esc($doc['title']).'</h2><p>Preencha somente os detalhes deste documento. Os dados da clínica já cadastrados são aproveitados automaticamente. Revise com o responsável técnico.</p></div><span class="odx-version">'.($saved?'Versão '.(int)$saved['version']:'Novo documento').'</span></div>';
    [$profileDone,$profileTotal]=od_profile_count($profileContext);
    if(!$historical)echo '<div class="odx-shared-banner"><div><span>DADOS REUTILIZADOS</span><strong>'.(int)$profileDone.' de '.(int)$profileTotal.' dados cadastrais informados</strong><p>As informações salvas são inseridas nos campos correspondentes. Dados editados manualmente não são substituídos.</p></div><div class="odx-shared-actions"><a href="?page=dados-compartilhados&amp;return_id='.(int)$id.'">Completar cadastro ↗</a><a href="?page=identidade&amp;return_id='.(int)$id.'">Logo e cores ↗</a></div></div>';
    if($historical)echo '<div class="odx-history-banner"><strong>Visualizando versão '.(int)$historical['version'].'.</strong> Arquivo histórico somente para leitura. <a href="?page=editar&id='.(int)$id.'">Voltar à versão atual</a></div>';
    echo '<form method="post" class="odx-workspace" id="odx-form"'.($historical?' data-odx-readonly="true"':'').'>'.token().'<input type="hidden" name="act" value="premium_save"><input type="hidden" name="id" value="'.(int)$id.'"><textarea hidden name="body_html" id="odx-body-html"></textarea>';
    echo '<aside class="odx-guided" aria-label="Personalização guiada"><div class="odx-side-intro"><span class="odx-side-kicker">PREENCHIMENTO DO DOCUMENTO</span><h3>Detalhes desta minuta</h3><p>Informe apenas o que é específico deste material. Nome, CNPJ e demais dados da unidade vêm do cadastro compartilhado.</p></div><div class="odx-completion"><div><span>Informações fornecidas</span><strong data-odx-progress-label>'.(int)$done.' de '.(int)$total.'</strong></div><div class="odx-track"><i data-odx-progress style="width:'.($total?round($done/$total*100):0).'%"></i></div></div>';
    foreach($fields as $i=>$f){$key=$f['key'];$value=(string)($data[$key]??'');$tag=$f['type']??'text';$idAttr='odx-f-'.$key;
        echo '<label class="odx-field" for="'.esc($idAttr).'"><span><em>'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT).'</em>'.esc($f['label']).'</span>';
        if($tag==='textarea')echo '<textarea id="'.esc($idAttr).'" name="g['.esc($key).']" data-odx-field="'.esc($key).'" maxlength="1500" rows="3" placeholder="'.esc($f['hint']).'">'.esc($value).'</textarea>';
        else echo '<input id="'.esc($idAttr).'" name="g['.esc($key).']" data-odx-field="'.esc($key).'" maxlength="1500" value="'.esc($value).'" placeholder="'.esc($f['hint']).'">';
        echo '</label>';
        $suggestion=od_guided_field_suggestion($f,$c);
        if(!$historical && $value==='' && $suggestion!=='')echo '<div class="odx-suggestion-control"><button type="button" data-odx-suggest="'.esc($idAttr).'" data-suggestion="'.esc($suggestion).'">Usar sugestão de texto</button><small>Rascunho administrativo. Confirme com o RT.</small></div>';
    }
    $histQ=odx_schema()->prepare('SELECT version,stage,created_at FROM od_document_history WHERE clinic_id=? AND template_id=? ORDER BY version DESC LIMIT 5');
    $histQ->execute([(int)$c['id'],(int)$id]);
    $revisions=$histQ->fetchAll();
    if($revisions){echo '<div class="odx-history"><h4>Versões anteriores</h4><div class="odx-history-items">';foreach($revisions as $revision){echo '<a href="?page=editar&id='.(int)$id.'&version='.(int)$revision['version'].'">v'.(int)$revision['version'].' <span>'.esc($revision['created_at']).'</span></a>';}echo '</div></div>';}
    if((int)$id===11)echo '<div class="odx-inline-tool"><span>FERRAMENTA OPERACIONAL</span><h4>Monitorização de esterilização</h4><p>Registre os ciclos e consulte o histórico de sua clínica, sem planilhas separadas.</p><a href="?page=registros-esterilizacao">Abrir registros de ciclos ↗</a><a href="#odx-esteril-visual" style="display:block;margin-top:12px">Ver quadro ilustrado neste documento ↑</a></div>';
    if((int)$id===7)echo '<div class="odx-inline-tool"><span>FERRAMENTA OPERACIONAL</span><h4>Controle de limpeza dos ambientes</h4><p>Registre cada execução e acompanhe pendências e ocorrências sem arquivos avulsos.</p><a href="?page=registros-limpeza">Abrir registro de limpeza ↗</a></div>';
    if(in_array((int)$id,[21,25,28],true))echo '<div class="odx-inline-tool"><span>FERRAMENTA OPERACIONAL</span><h4>Manutenção de equipamentos</h4><p>Registre serviços, verificações e próximas datas por equipamento, mantendo histórico da clínica.</p><a href="?page=registros-manutencao">Abrir controle de manutenção ↗</a></div>';
    if((int)$id===6)echo '<div class="odx-inline-tool"><span>FERRAMENTA OPERACIONAL</span><h4>Entrega e orientação sobre EPI</h4><p>Documente entregas e orientações internas, com histórico e acompanhamento de ocorrências.</p><a href="?page=registros-epi">Abrir controle de EPI ↗</a></div>';
    echo '<div class="odx-review-control"><label for="odx-status">Situação da minuta</label><select name="stage" id="odx-status"><option value="rascunho">Em elaboração</option><option value="revisao_rt" '.(($saved['stage']??'')==='revisao_rt'?'selected':'').'>Aguardando revisão do RT</option></select><p>O status é interno e não representa aprovação sanitária.</p></div>'.od_legal_review_panel((int)$id,$c,$historical!==null).'<button class="odx-save" type="submit">Salvar versão da minuta <span>↗</span></button><p class="odx-safe">Os dados pertencem somente à sua clínica. Nenhuma informação de paciente é necessária para preencher este modelo.</p></aside>';
    echo '<section class="odx-document-area" aria-label="Visualização e conteúdo da minuta"><div class="odx-toolbar"><div><span class="odx-live"></span> PRÉVIA DO DOCUMENTO <span class="odx-subline">atualizada enquanto você preenche</span></div><div class="odx-tools"><button type="button" id="odx-edit-toggle" aria-pressed="false">Editar texto</button><button type="button" id="odx-sections-toggle">Ver seções</button></div></div>';
    $brandMark=$brand['logo_mime']?'<img class="odx-brand-logo" src="'.esc($brandLogoUrl).'" alt="Logo da clínica">':'<div class="odx-seal"><span>'.esc(mb_strtoupper(mb_substr((string)$paperClinic['name'],0,1))).'</span></div>';
    echo '<article class="odx-paper odx-accent-'.esc($meta['accent']).' odx-branded" style="--odx-brand-cover:'.esc($brand['accent']).'" aria-label="Documento da clínica"><div class="odx-paper-top">'.$brandMark.'<div><strong>'.esc($paperClinic['name']).'</strong><small>'.esc($paperClinic['city']).($paperClinic['state']?' · '.esc($paperClinic['state']):'').' &nbsp;·&nbsp; '.esc($paperClinic['cnpj']).'</small></div><div class="odx-paper-code">'.esc($meta['number']).'</div></div>';
    echo '<div class="odx-cover'.($visual?' odx-cover--with-art':'').'"><div class="odx-cover-lines"><span></span><span></span><span></span></div><span class="odx-cover-kicker">'.esc($meta['eyebrow']).'</span><h2>'.esc($meta['name']).'</h2><p>'.esc($doc['title']).'</p><div class="odx-cover-meta"><span>DOCUMENTO DE TRABALHO</span><strong>'.($historical?'VERSÃO '.(int)$historical['version']:($saved?'VERSÃO '.(int)$saved['version'] :'VERSÃO INICIAL')).'</strong></div>'.od_visual_cover_markup($visual).'</div>';
    echo '<div class="odx-paper-inside"><div class="odx-facts"><div><small>Estabelecimento</small><strong>'.esc($paperClinic['name']).'</strong></div><div><small>Responsável técnico</small><strong>'.esc($paperClinic['rt_name']).($paperClinic['rt_cro']?' · '.esc($paperClinic['rt_cro']):'').'</strong></div><div><small>Status</small><strong>'.esc(odx_stage($historical?:($saved??[]))).'</strong></div></div>';
    if((int)$id===11 && !$historical)echo od_steril_inline_html();
    // Guias adicionais só são exibidos quando a cena e todos os assets individuais passam pelo gate de qualidade.
    if(!$historical)echo od_illustrated_guide_html((int)$id);
    // O mapa ilustrativo repetia a trilha de preenchimento e aumentava desnecessariamente a prévia.
    echo '<section class="odx-field-preview"><div class="odx-field-preview-head"><div><span>DADOS ESPECÍFICOS</span><h3>Informações deste documento</h3></div><small>Atualização em tempo real</small></div><div class="odx-summary-grid">';
    foreach($fields as $field){$key=$field['key'];$text=trim((string)($data[$key]??''));echo '<div class="odx-summary-item"><span>'.esc($field['label']).'</span><strong data-odx-display="'.esc($key).'" class="'.($text?'':'odx-empty').'">'.($text?nl2br(esc($text)):'A preencher').'</strong></div>';}
    echo '</div></section>';
    echo '<div class="odx-source-head"><div><span>MINUTA EM REVISÃO</span><h3>Texto completo do documento</h3></div><p>Os dados cadastrais são reaproveitados. Confira os campos pendentes e revise as informações técnicas com o RT.</p></div>';
    echo '<p class="odx-pending-note" data-odx-pending-note hidden></p>';
    echo '<div class="odx-document-body" id="odx-document-body" aria-label="Conteúdo técnico" contenteditable="false">'.$html.'</div>';
    echo '<div class="odx-approval"><div><span>CONTROLE E REVISÃO</span><h3>Revisão e formalização</h3><p>Esta minuta não recebe aprovação automática. O RT deve revisar o conteúdo; a SDBPF exige também aprovação e assinatura do responsável legal, conforme a RDC 1.002/2025, art. 111. A assinatura exibida aqui é apenas um espaço de referência, não uma assinatura eletrônica.</p></div><div class="odx-approval-line"><small>Responsável técnico / CRO</small><span>Assinatura e data após validação</span></div></div>';
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
    echo '<div class="odx-operational-tools"><a href="?page=registros-limpeza"><span>REGISTROS DO DIA A DIA</span><strong>Limpeza de ambientes</strong><small>Lançar execução e consultar histórico ↗</small></a><a href="?page=registros-esterilizacao"><span>RASTREABILIDADE</span><strong>Esterilização</strong><small>Registro de cargas e indicadores observados ↗</small></a><a href="?page=registros-manutencao"><span>TECNOLOGIAS E EQUIPAMENTOS</span><strong>Manutenção</strong><small>Intervenções, verificações e histórico ↗</small></a><a href="?page=registros-epi"><span>ROTINA DE EQUIPE</span><strong>Entrega de EPI</strong><small>Entregas e orientações registradas ↗</small></a></div>';
    echo '<div class="odx-category-filter" role="group" aria-label="Filtrar documentos"><button type="button" class="is-selected" data-odx-filter="all">Todos</button>';
    foreach(['SDBPF','POPs','Planos','Formulários','Guia de uso'] as $cat){if(isset($counts[$cat]))echo '<button type="button" data-odx-filter="'.esc($cat).'">'.esc($cat).' <span>'.(int)$counts[$cat].'</span></button>';}
    echo '</div><div class="odx-library-grid">';
    foreach($all as $d){$id=(int)$d['id'];$meta=odx_spec($id,(string)$d['category']);$state=$saved[$id]??null;$isPilot=in_array($id,[3,24,11,6,7,21],true);$labels=['SDBPF'=>'Governança','POPs'=>'Procedimentos','Planos'=>'Planejamento','Formulários'=>'Registros','Guia de uso'=>'Orientações'];
        echo '<a href="?page=editar&id='.$id.'" class="odx-library-card" data-odx-category="'.esc($d['category']).'"><div class="odx-card-preview odx-accent-'.esc($meta['accent']).'"><div class="odx-card-strip"><span>ODONTO EM DIA</span><small>'.esc($meta['number']).'</small></div><div class="odx-card-preview-content"><div class="odx-card-mini-label">'.esc($labels[$d['category']]??$d['category']).'</div><strong>'.esc($d['title']).'</strong><i></i><i></i><i></i></div><div class="odx-card-preview-bottom"><span>MINUTA TÉCNICA</span><em>◈</em></div></div><div class="odx-card-info"><span class="odx-card-status">'.($state?($state['stage']==='revisao_rt'?'Em revisão do RT':'Versão '.(int)$state['version'].' salva'):'Não iniciado').'</span>'.($isPilot?'<span class="odx-card-feature">Layout aprimorado</span>':'').'<h3>'.esc($d['title']).'</h3><p>'.esc($d['category']).' · Editar e acompanhar na plataforma</p><span class="odx-card-go">Abrir documento <b>↗</b></span></div></a>';
    }
    echo '</div>';foot();exit;
}
function odx_saved():never {
    $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
    $s=odx_schema()->prepare('SELECT template_id,version,stage,updated_at FROM od_documents WHERE clinic_id=? ORDER BY updated_at DESC');
    $s->execute([(int)$c['id']]);$saved=$s->fetchAll();
    h('Minhas versões',false);
    echo '<div class="odx-lib-header"><div><span>ACERVO PERSONALIZADO</span><h2>Seus documentos em andamento.</h2><p>Retome as minutas e consulte versões anteriores sem precisar localizar arquivos no computador.</p></div><div class="odx-lib-stats"><b>'.count($saved).'</b><span>documentos iniciados</span></div></div>';
    if(!$saved){echo '<div class="odx-library-alert">Você ainda não personalizou nenhum documento. <a href="?page=documentos">Abra a biblioteca e escolha um modelo para começar.</a></div>';foot();exit;}
    echo '<div class="odx-saved-grid">';
    foreach($saved as $row){$d=template_by_id((int)$row['template_id']);if(!$d)continue;
        $meta=odx_spec((int)$d['id'],(string)$d['category']);
        echo '<a class="odx-saved-card" href="?page=editar&id='.(int)$d['id'].'"><div class="odx-saved-icon">▤</div><div><small>'.esc($meta['number']).' · Versão '.(int)$row['version'].'</small><h3>'.esc($d['title']).'</h3><p>'.esc($row['stage']==='revisao_rt'?'Aguardando revisão do responsável técnico':'Minuta em elaboração').' · Atualizado em '.esc($row['updated_at']).'</p></div><strong>Continuar ↗</strong></a>';
    }
    echo '</div><div class="odx-bottom-hint">Todos os documentos continuam sendo minutas e dependem de avaliação profissional.</div>';foot();exit;
}
function odx_steril_schema():PDO {
    $db=odx_schema();
    $db->exec('CREATE TABLE IF NOT EXISTS od_steril_monitoring (id INTEGER PRIMARY KEY AUTOINCREMENT, clinic_id INTEGER NOT NULL, cycle_date TEXT NOT NULL, autoclave_id TEXT NOT NULL, cycle_code TEXT NOT NULL, operator_name TEXT NOT NULL DEFAULT \'\', indicator_notes TEXT NOT NULL DEFAULT \'\', result_status TEXT NOT NULL DEFAULT \'pendente\', notes TEXT NOT NULL DEFAULT \'\', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    $db->exec('CREATE INDEX IF NOT EXISTS od_steril_monitoring_by_clinic ON od_steril_monitoring(clinic_id, cycle_date DESC)');
    return $db;
}
function odx_steril_post():never {
    if(!user()||!($c=clinic())){http_response_code(403);exit('Acesso necessário');}
    $date=trim((string)($_POST['cycle_date']??''));
    $autoclave=trim((string)($_POST['autoclave_id']??''));
    $code=trim((string)($_POST['cycle_code']??''));
    $operator=trim((string)($_POST['operator_name']??''));
    $indicators=trim((string)($_POST['indicator_notes']??''));
    $status=(string)($_POST['result_status']??'pendente');
    $notes=trim((string)($_POST['notes']??''));
    $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
    if(!$dt || $dt->format('Y-m-d')!==$date || mb_strlen($autoclave)>90 || mb_strlen($code)>100 || $autoclave==='' || $code==='' || mb_strlen($operator)>120 || mb_strlen($indicators)>1000 || mb_strlen($notes)>1000 || !in_array($status,['pendente','conferido','intercorrencia'],true)){
        flash('Confira os campos do registro antes de salvar.');redirect('?page=registros-esterilizacao');
    }
    try{
        $db=odx_steril_schema();
        $q=$db->prepare('INSERT INTO od_steril_monitoring(clinic_id,cycle_date,autoclave_id,cycle_code,operator_name,indicator_notes,result_status,notes) VALUES(?,?,?,?,?,?,?,?)');
        $q->execute([(int)$c['id'],$date,$autoclave,$code,$operator,$indicators,$status,$notes]);
        flash('Registro adicionado ao histórico da clínica. Isso não significa liberação técnica da carga.');
    }catch(Throwable $e){error_log('[Odonto] Falha no registro de monitorização');flash('Não foi possível salvar o registro.');}
    redirect('?page=registros-esterilizacao');
}
function odx_steril_page():never {
    if(!user()||!($c=clinic())){http_response_code(403);exit('Acesso necessário');}
    $db=odx_steril_schema();
    $q=$db->prepare('SELECT cycle_date,autoclave_id,cycle_code,operator_name,indicator_notes,result_status,notes,created_at FROM od_steril_monitoring WHERE clinic_id=? ORDER BY cycle_date DESC,id DESC LIMIT 200');
    $q->execute([(int)$c['id']]);$rows=$q->fetchAll();
    h('Monitorização da esterilização');
    echo '<div class="odx-topline"><a href="?page=editar&id=11">← POP de Esterilização</a><span> / </span><strong>REGISTROS OPERACIONAIS</strong></div>';
    echo '<div class="odx-page-title"><div><span class="odx-eyebrow">FERRAMENTA ONLINE / CONTROLE OPERACIONAL</span><h2>Histórico de ciclos e verificações.</h2><p>Registre informações conforme a rotina aprovada pela clínica. O sistema não interpreta indicadores nem autoriza o uso de materiais.</p></div><span class="odx-version">'.count($rows).' registros recentes</span></div>';
    echo '<div class="odx-register-layout"><section class="odx-register-form"><span class="odx-side-kicker">NOVO REGISTRO</span><h3>Dados do ciclo</h3><form method="post">'.token().'<input type="hidden" name="act" value="steril_record"><div class="odx-register-grid"><label>Data do ciclo<input type="date" name="cycle_date" required value="'.esc(date('Y-m-d')).'"></label><label>Autoclave / identificação<input name="autoclave_id" required maxlength="90" placeholder="Identificação interna"></label><label>ID da carga/ciclo<input name="cycle_code" required maxlength="100" placeholder="Código ou lote do ciclo"></label><label>Operador / responsável<input name="operator_name" maxlength="120" placeholder="Nome conforme rotina interna"></label></div><label>Indicadores e parâmetros observados<textarea name="indicator_notes" maxlength="1000" rows="3" placeholder="Anote resultados observados conforme registros do equipamento e rotina validada"></textarea></label><label>Situação registrada<select name="result_status"><option value="pendente">Aguardando conferência</option><option value="conferido">Conferido internamente</option><option value="intercorrencia">Intercorrência identificada</option></select></label><label>Observações ou encaminhamento<textarea name="notes" maxlength="1000" rows="3" placeholder="Descreva a ocorrência e providência tomada, quando existir"></textarea></label><button type="submit" class="odx-save">Registrar ciclo <span>↗</span></button></form><p class="odx-safe">Não inclua dados pessoais de pacientes. Registre resultados somente depois da execução real do procedimento.</p></section><section class="odx-register-history"><div class="odx-register-title"><div><span>HISTÓRICO DA UNIDADE</span><h3>Registros lançados</h3></div><small>Até 200 registros mais recentes</small></div>';
    if(!$rows)echo '<div class="odx-register-empty"><b>Nenhum ciclo registrado ainda.</b><p>Depois de preencher o primeiro registro, ele aparecerá aqui com data, equipamento e situação.</p></div>';
    foreach($rows as $r){$labels=['pendente'=>'Aguardando conferência','conferido'=>'Conferido internamente','intercorrencia'=>'Intercorrência'];$status=$r['result_status'];
        echo '<article class="odx-register-row"><div class="odx-register-row-head"><b>'.esc($r['cycle_date']).' · '.esc($r['cycle_code']).'</b><span class="odx-cycle-'.esc($status).'">'.esc($labels[$status]??'Pendente').'</span></div><div class="odx-register-row-meta"><span>Equipamento: '.esc($r['autoclave_id']).'</span><span>Operador: '.esc($r['operator_name']?:'Não informado').'</span></div>';
        if($r['indicator_notes']!=='')echo '<p><strong>Indicadores e parâmetros:</strong> '.nl2br(esc($r['indicator_notes'])).'</p>';
        if($r['notes']!=='')echo '<p><strong>Observações:</strong> '.nl2br(esc($r['notes'])).'</p>';
        echo '<small>Inserido em '.esc($r['created_at']).'</small></article>';
    }
    echo '<div class="odx-bottom-hint"><strong>Controle administrativo.</strong> A decisão técnica sobre a carga depende dos critérios, indicadores e procedimentos definidos pelo RT e das normas aplicáveis.</div></section></div>';foot();exit;
}

/* Controles de limpeza: registro de execução e histórico, sem validação sanitária automática. */
function odx_cleaning_schema(): PDO {
    $db=odx_schema();
    $db->exec("CREATE TABLE IF NOT EXISTS od_cleaning_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        clinic_id INTEGER NOT NULL,
        executed_on TEXT NOT NULL,
        executed_at TEXT NOT NULL,
        area_name TEXT NOT NULL,
        product_info TEXT NOT NULL DEFAULT '',
        responsible TEXT NOT NULL,
        stage TEXT NOT NULL DEFAULT 'registrado',
        notes TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec('CREATE INDEX IF NOT EXISTS od_cleaning_by_clinic ON od_cleaning_records(clinic_id, executed_on DESC, id DESC)');
    return $db;
}
function odx_cleaning_post(): never {
    if(!user() || !($c=clinic())){http_response_code(403);exit('Acesso necessário');}
    $date=trim((string)($_POST['executed_on']??''));
    $time=trim((string)($_POST['executed_at']??''));
    $area=trim((string)($_POST['area_name']??''));
    $product=trim((string)($_POST['product_info']??''));
    $responsible=trim((string)($_POST['responsible']??''));
    $stage=(string)($_POST['stage']??'registrado');
    $notes=trim((string)($_POST['notes']??''));
    $day=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
    $validDate=$day && $day->format('Y-m-d')===$date;
    if(!$validDate || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$time) ||
        $area==='' || mb_strlen($area)>150 || mb_strlen($product)>400 ||
        $responsible==='' || mb_strlen($responsible)>120 || mb_strlen($notes)>1500 ||
        !in_array($stage,['registrado','aguardando_conferencia','ocorrencia'],true)) {
        flash('Confira os dados do registro de limpeza.');
        redirect('?page=registros-limpeza');
    }
    try{
        $db=odx_cleaning_schema();
        $stmt=$db->prepare('INSERT INTO od_cleaning_records(clinic_id,executed_on,executed_at,area_name,product_info,responsible,stage,notes) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->execute([(int)$c['id'],$date,$time,$area,$product,$responsible,$stage,$notes]);
        flash('Execução registrada no histórico da clínica. A conferência técnica continua sendo responsabilidade da equipe.');
    }catch(Throwable $e){
        error_log('[Odonto] Falha ao registrar limpeza');
        flash('Não foi possível salvar o registro de limpeza.');
    }
    redirect('?page=registros-limpeza');
}
function odx_cleaning_page(): never {
    if(!user() || !($c=clinic())){http_response_code(403);exit('Acesso necessário');}
    $db=odx_cleaning_schema();
    $status=(string)($_GET['status']??'');
    $validStatuses=['registrado','aguardando_conferencia','ocorrencia'];
    if(!in_array($status,$validStatuses,true))$status='';
    $date=trim((string)($_GET['date']??''));
    $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
    if(!$dt || $dt->format('Y-m-d')!==$date)$date='';
    $sql='SELECT executed_on,executed_at,area_name,product_info,responsible,stage,notes,created_at FROM od_cleaning_records WHERE clinic_id=?';
    $params=[(int)$c['id']];
    if($status!==''){$sql.=' AND stage=?';$params[]=$status;}
    if($date!==''){$sql.=' AND executed_on=?';$params[]=$date;}
    $sql.=' ORDER BY executed_on DESC, executed_at DESC, id DESC LIMIT 200';
    $q=$db->prepare($sql);$q->execute($params);$rows=$q->fetchAll();
    h('Controle de limpeza de ambientes');
    echo '<div class="odx-topline"><a href="?page=editar&id=7">← POP de Limpeza de Ambientes</a><span> / </span><strong>REGISTRO NA PLATAFORMA</strong></div>';
    echo '<div class="odx-page-title"><div><span class="odx-eyebrow">ROTINAS DA CLÍNICA / REGISTROS</span><h2>Limpeza documentada, sem planilhas avulsas.</h2><p>Registre a execução de cada rotina conforme o POP aprovado da sua clínica e consulte o histórico por data e situação.</p></div><span class="odx-version">Registro operacional</span></div>';
    echo '<div class="odx-register-layout"><section class="odx-register-form"><span class="odx-side-kicker">NOVO REGISTRO</span><h3>Informações da execução</h3><form method="post">'.token().'<input type="hidden" name="act" value="cleaning_record">';
    echo '<div class="odx-register-grid"><label>Data da execução<input name="executed_on" type="date" required value="'.esc(date('Y-m-d')).'"></label><label>Horário<input name="executed_at" type="time" required></label><label>Ambiente / área<input name="area_name" required maxlength="150" placeholder="Ex.: consultório 1"></label><label>Responsável pela execução<input name="responsible" required maxlength="120" placeholder="Nome ou matrícula interna"></label></div>';
    echo '<label>Produto e diluição conforme rotina validada<input name="product_info" maxlength="400" placeholder="Identificação do produto e concentração utilizada"></label>';
    echo '<label>Situação do registro<select name="stage"><option value="registrado">Execução registrada</option><option value="aguardando_conferencia">Aguardando conferência interna</option><option value="ocorrencia">Ocorrência / desvio registrado</option></select></label>';
    echo '<label>Observações ou intercorrências<textarea name="notes" maxlength="1500" rows="3" placeholder="Somente fatos observados durante a execução"></textarea></label>';
    echo '<button class="odx-save" type="submit">Salvar registro <span>↗</span></button></form>';
    echo '<p class="odx-safe">Não inclua dados de pacientes. O registro deve refletir uma atividade realmente executada e não substitui a validação do POP pelo responsável técnico.</p></section>';
    echo '<section class="odx-register-history"><div class="odx-register-title"><div><span>HISTÓRICO DA CLÍNICA</span><h3>Execuções registradas</h3></div><small>Até 200 resultados</small></div>';
    echo '<form method="get" class="odx-clean-filter"><input type="hidden" name="page" value="registros-limpeza"><label>Filtrar por data<input type="date" name="date" value="'.esc($date).'"></label><label>Situação<select name="status"><option value="">Todas</option>';
    foreach(['registrado'=>'Registrada','aguardando_conferencia'=>'Aguardando conferência','ocorrencia'=>'Ocorrência'] as $v=>$label)echo '<option value="'.esc($v).'" '.($status===$v?'selected':'').'>'.esc($label).'</option>';
    echo '</select></label><button type="submit">Filtrar</button><a href="?page=registros-limpeza">Limpar</a></form>';
    if(!$rows)echo '<div class="odx-register-empty"><b>Nenhum registro encontrado.</b><p>Preencha a rotina executada ou remova os filtros para consultar o histórico.</p></div>';
    $labels=['registrado'=>'Registrada','aguardando_conferencia'=>'Aguardando conferência','ocorrencia'=>'Ocorrência'];
    foreach($rows as $row){
        $stage=$row['stage'];$class=match($stage){'registrado'=>'odx-cycle-conferido','aguardando_conferencia'=>'odx-cycle-pendente','ocorrencia'=>'odx-cycle-intercorrencia',default=>'odx-cycle-pendente'};
        echo '<article class="odx-register-row"><div class="odx-register-row-head"><b>'.esc($row['executed_on']).' · '.esc($row['executed_at']).' · '.esc($row['area_name']).'</b><span class="'.esc($class).'">'.esc($labels[$stage]??'Registrada').'</span></div>';
        echo '<div class="odx-register-row-meta"><span>Executado por: '.esc($row['responsible']).'</span></div>';
        if($row['product_info']!=='')echo '<p><strong>Produto / diluição informada:</strong> '.esc($row['product_info']).'</p>';
        if($row['notes']!=='')echo '<p><strong>Observações:</strong> '.nl2br(esc($row['notes'])).'</p>';
        echo '<small>Registrado no sistema em '.esc($row['created_at']).'</small></article>';
    }
    echo '<div class="odx-bottom-hint"><strong>Registro administrativo:</strong> o sistema armazena os dados lançados, mas não atesta a desinfecção, a eficácia do produto nem a conformidade sanitária.</div></section></div>';
    foot();exit;
}
