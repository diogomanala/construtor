<?php
/** Dados cadastrais reutilizáveis nos 30 documentos, isolados por clínica. */
declare(strict_types=1);

function od_profile_extra_fields():array {
    return [
        'razao_social'=>['Razão social','Nome jurídico conforme o CNPJ',180],
        'endereco'=>['Endereço completo','Rua, número, complemento e bairro',255],
        'contato_institucional'=>['Contato institucional','Telefone ou e-mail da clínica, não de pacientes',180],
        'licenca_numero'=>['Número da licença sanitária','Preencha conforme o documento real',100],
        'licenca_validade'=>['Validade da licença','Ex.: 15/08/2027',40],
        'licenca_orgao'=>['Órgão expedidor','Ex.: Vigilância Sanitária Municipal',140],
        'responsavel_legal'=>['Responsável legal','Nome completo do representante legal',150],
        'rt_substituto'=>['RT substituto, se houver','Somente quando aplicável',150],
        'modalidade'=>['Modalidade de atendimento','Ex.: fixa, móvel, portátil',90],
        'classe'=>['Classificação do serviço','Informe apenas se confirmada pelo RT',80],
        'servicos'=>['Serviços oferecidos','Liste somente atividades efetivamente prestadas',600],
        'atendimento_externo'=>['Atendimento fora do estabelecimento','Ex.: não realiza, realiza mediante...',200],
    ];
}

function od_profile_schema():PDO {
    $db=db();
    $db->exec("CREATE TABLE IF NOT EXISTS od_clinic_shared_profile (
        clinic_id INTEGER PRIMARY KEY,
        fields_json TEXT NOT NULL DEFAULT '{}',
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}

function od_profile_current(array $clinic):array {
    $q=od_profile_schema()->prepare('SELECT fields_json FROM od_clinic_shared_profile WHERE clinic_id=?');
    $q->execute([(int)$clinic['id']]);
    $json=$q->fetchColumn();
    $data=is_string($json)?json_decode($json,true):null;
    if(!is_array($data))$data=[];
    $clean=[];
    foreach(od_profile_extra_fields() as $key=>$spec) {
        $value=$data[$key]??'';
        $clean[$key]=is_string($value)?$value:'';
    }
    return $clean;
}
function od_profile_context(array $clinic,array $extra):array {
    $ctx=array_merge($clinic,$extra);
    $ster=(string)($clinic['sterilization']??'');
    $ctx['processamento_perfil']=['interna'=>'Realizado na clínica','terceirizada'=>'Terceirizado','mista'=>'Interno e terceirizado'][$ster]??'';
    $ctx['consultorios']=isset($clinic['chairs'])?(string)(int)$clinic['chairs']:'';
    $ctx['radiologia_perfil']=isset($clinic['has_xray'])?((int)$clinic['has_xray']===1?'Sim':'Não'):'';
    $ctx['sedacao_perfil']=isset($clinic['has_sedation'])?((int)$clinic['has_sedation']===1?'Sim':'Não'):'';
    return $ctx;
}
function od_profile_count(array $ctx):array {
    $keys=['name','cnpj','razao_social','endereco','city','state','rt_name','rt_cro','contato_institucional','licenca_numero','licenca_validade','licenca_orgao','responsavel_legal'];
    $filled=0;
    foreach($keys as $key)if(trim((string)($ctx[$key]??''))!=='')$filled++;
    return [$filled,count($keys)];
}

/**
 * Somente valores reais e rótulos cadastrais correspondentes são aplicados.
 * Não completa datas, aprovações, assinaturas ou protocolos clínicos.
 * $html já deve vir da fonte oficial ou do sanitizador do editor.
 */
function od_profile_apply_document(string $html,array $ctx,array $fields=[]):string {
    $labels=[
        'razao_social'=>['~(Razão social:\s*)\[\s*\]~iu'],
        'cnpj'=>['~(CNPJ:\s*)\[\s*\]~iu'],
        'endereco'=>['~(endereço:\s*)\[\s*\]~iu'],
        'contato_institucional'=>['~(contato institucional:\s*)\[\s*\]~iu'],
        'licenca_numero'=>['~(Licença sanitária:\s*nº\s*)\[\s*\]~iu'],
        'licenca_validade'=>['~(Licença sanitária:\s*nº\s*(?:\[\s*\]|[^,<]+),\s*validade\s*)\[\s*\]~iu'],
        'licenca_orgao'=>['~(órgão expedidor\s*)\[\s*\]~iu'],
        'responsavel_legal'=>['~(Responsável legal:\s*)\[\s*\]~iu'],
        'rt_name'=>['~(responsável técnico:\s*)\[\s*\]~iu'],
        'rt_cro'=>['~(CRO:\s*)\[\s*\]~iu'],
        'rt_substituto'=>['~(substituto, se aplicável:\s*)\[\s*\]~iu'],
        'classe'=>['~(Classe:\s*)\[\s*\]~iu'],
        'consultorios'=>['~(Nº consultórios:\s*)\[\s*\]~iu'],
        'servicos'=>['~(Serviços efetivamente ofertados:\s*)\[\s*\]~iu'],
        'sedacao_perfil'=>['~(Presença de sedação:\s*)\[\s*\]~iu'],
        'radiologia_perfil'=>['~(radiologia:\s*)\[\s*\]~iu'],
        'processamento_perfil'=>['~(processamento próprio/externo:\s*)\[\s*\]~iu'],
        'atendimento_externo'=>['~(atendimento extraestabelecimento:\s*)\[\s*\]~iu'],
        'modalidade'=>['~(Modalidade:\s*)\[(?:fixa\s*/\s*móvel\s*/\s*portátil\s*/\s*outra)\]~iu'],
    ];
    foreach($labels as $key=>$patterns){
        $value=trim((string)($ctx[$key]??''));
        if($value==='')continue;
        $safe=esc($value);
        foreach($patterns as $pattern){
            $html=preg_replace_callback($pattern,static fn($m)=>$m[1].$safe,$html)??$html;
        }
    }

    // Campos de identificação repetidos nas tabelas de controle de versão.
    $name=trim((string)($ctx['name']??''));
    $cnpj=trim((string)($ctx['cnpj']??''));
    if($name!=='' && $cnpj!=='')$html=str_replace('[NOME / CNPJ]',esc($name.' / '.$cnpj),$html);
    $rt=trim((string)($ctx['rt_name']??''));
    $cro=trim((string)($ctx['rt_cro']??''));
    if($rt!==''&&$cro!=='') {
        $html=str_replace(['[NOME / CRO-UF]','[NOME / CRO]'],esc($rt.' / '.$cro),$html);
    }
    $legal=trim((string)($ctx['responsavel_legal']??''));
    if($legal!=='') {
        $safe=esc($legal);
        $html=preg_replace_callback('~(<td>\s*Responsável legal\s*</td>\s*<td>)\s*\[NOME\]~iu',static fn($m)=>$m[1].$safe,$html)??$html;
    }

    // As tabelas dos POPs podem reutilizar os campos do preenchimento guiado.
    $bound=[
        'setor'=>'Setor/local',
        'executor'=>'Responsável por executar',
        'frequencia'=>'Frequência',
        'equipamentos_insumos'=>'Equipamentos e insumos',
    ];
    foreach($bound as $key=>$label) {
        $value=trim((string)($fields[$key]??''));
        $reviewed=!empty($fields['_reviewed'][$key]);
        $pattern='~(<td>\s*'.preg_quote($label,'~').'\s*</td>\s*<td>)\s*(\[(?:\s*|PREENCHER|DEFINIR|INFORMAR MARCAS,\s*MODELOS,\s*PRODUTOS)\]|[^<]*?)(\s*</td>)~iu';
        $html=preg_replace_callback($pattern,static function($m)use($key,$value,$reviewed){
            $old=trim(html_entity_decode($m[2],ENT_QUOTES,'UTF-8'));
            $placeholder=(bool)preg_match('/^\[(?:\s*|PREENCHER|DEFINIR|INFORMAR MARCAS,\s*MODELOS,\s*PRODUTOS)\]$/iu',$old);
            // Nunca substitui uma descrição já editada diretamente no texto técnico.
            if(!$placeholder && ($value==='' || $old!==$value))return $m[0];
            $text=$value!==''?esc($value):$m[2];
            $class=$value!==''?($reviewed?'odx-auto-value odx-reviewed-value':'odx-auto-value odx-review-pending'):'odx-auto-value odx-review-pending';
            return $m[1].'<span class="'.$class.'" data-odx-bind="'.$key.'">'.$text.'</span>'.$m[3];
        },$html)??$html;
    }
    return $html;
}

function od_profile_post():never {
    $clinic=clinic();
    if(!user()||!$clinic){http_response_code(403);exit('Acesso necessário');}
    $names=['name'=>180,'cnpj'=>18,'city'=>120,'state'=>2,'rt_name'=>150,'rt_cro'=>45];
    $base=[];
    foreach($names as $key=>$limit){
        $v=$_POST[$key]??null;
        if(!is_string($v)){http_response_code(400);exit('Dados inválidos');}
        $v=trim(strip_tags($v));
        if(mb_strlen($v)>$limit){flash('Um campo ultrapassou o tamanho permitido.');redirect('?page=dados-compartilhados');}
        $base[$key]=$v;
    }
    if($base['name']===''){flash('Informe o nome do estabelecimento.');redirect('?page=dados-compartilhados');}
    if($base['cnpj']!=='' && strlen(preg_replace('/\D/','',$base['cnpj']))!==14) {
        flash('Confira o CNPJ: ele precisa ter 14 dígitos.');redirect('?page=dados-compartilhados');
    }
    $extra=[];
    foreach(od_profile_extra_fields() as $key=>$spec){
        $v=$_POST[$key]??null;
        if(!is_string($v)){http_response_code(400);exit('Dados inválidos');}
        $v=trim(strip_tags($v));
        if(mb_strlen($v)>$spec[2]){flash('Um campo ultrapassou o tamanho permitido.');redirect('?page=dados-compartilhados');}
        $extra[$key]=$v;
    }
    $db=od_profile_schema();
    try{
        $db->beginTransaction();
        $q=$db->prepare('UPDATE clinics SET name=?,cnpj=?,city=?,state=?,rt_name=?,rt_cro=? WHERE id=? AND user_id=?');
        $q->execute([$base['name'],$base['cnpj'],$base['city'],mb_strtoupper($base['state']),$base['rt_name'],$base['rt_cro'],(int)$clinic['id'],(int)user()]);
        $q=$db->prepare("INSERT INTO od_clinic_shared_profile(clinic_id,fields_json,updated_at) VALUES(?,?,CURRENT_TIMESTAMP)
            ON CONFLICT(clinic_id) DO UPDATE SET fields_json=excluded.fields_json,updated_at=CURRENT_TIMESTAMP");
        $q->execute([(int)$clinic['id'],json_encode($extra,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
        $db->commit();
        flash('Dados da clínica salvos. Os campos ainda vazios continuarão pendentes nas minutas.');
    }catch(Throwable $e){
        if($db->inTransaction())$db->rollBack();
        error_log('[Odonto] Falha ao salvar cadastro compartilhado');
        flash('Não foi possível salvar. Tente novamente.');
    }
    $return=filter_var($_POST['return_id']??null,FILTER_VALIDATE_INT);
    redirect($return && template_by_id((int)$return)?'?page=editar&id='.(int)$return:'?page=dados-compartilhados');
}

function od_profile_page():never {
    $c=clinic();
    if(!$c){http_response_code(403);exit('Acesso necessário');}
    $extra=od_profile_current($c);
    [$done,$total]=od_profile_count(od_profile_context($c,$extra));
    $back=filter_var($_GET['return_id']??null,FILTER_VALIDATE_INT);
    if(!$back||!template_by_id((int)$back))$back=0;
    h('Cadastro da clínica',false);
    echo '<div class="od-profile-page"><div class="od-profile-intro"><a href="?page='.($back?'editar&amp;id='.$back:'clinica').'" class="od-profile-back">← Voltar</a><span class="odx-eyebrow">PREENCHA UMA VEZ, REUTILIZE NOS DOCUMENTOS</span><h1>Dados da clínica</h1><p>As informações desta página são usadas na identificação dos documentos. Preencha apenas o que já foi conferido; os campos vazios ficam pendentes.</p><div class="od-profile-count"><strong>'.$done.' de '.$total.'</strong> dados cadastrais informados</div></div>';
    echo '<form method="post" class="od-profile-form">'.token().'<input type="hidden" name="act" value="profile_shared_save"><input type="hidden" name="return_id" value="'.$back.'">';
    echo '<section class="od-profile-panel"><div class="od-profile-section-head"><h2>Identificação</h2><p>Os dados que você já cadastrou em Minha clínica aparecem aqui automaticamente.</p></div><div class="od-profile-grid">';
    foreach([
        ['name','Nome da clínica','Como a unidade é conhecida',180],
        ['razao_social','Razão social','Como consta no CNPJ',180],
        ['cnpj','CNPJ','00.000.000/0000-00',18],
        ['endereco','Endereço completo','Rua, número, complemento e bairro',255],
        ['city','Cidade','Município',120],
        ['state','UF','Ex.: RJ',2],
        ['contato_institucional','Contato institucional','Telefone ou e-mail da clínica',180],
    ] as $f){
        [$key,$label,$hint,$max]=$f;
        $value=(string)($extra[$key]??$c[$key]??'');
        echo '<label class="od-profile-field"><span>'.esc($label).'</span><input name="'.esc($key).'" maxlength="'.$max.'" value="'.esc($value).'" placeholder="'.esc($hint).'"'.($key==='name'?' required':'').'></label>';
    }
    echo '</div></section>';
    echo '<section class="od-profile-panel"><div class="od-profile-section-head"><h2>Licença e responsáveis</h2><p>Somente informações verificadas. Não inventamos número de licença, validade ou assinaturas.</p></div><div class="od-profile-grid">';
    foreach([
        ['licenca_numero','Número da licença sanitária','Número do documento',100],
        ['licenca_validade','Validade da licença','DD/MM/AAAA',40],
        ['licenca_orgao','Órgão expedidor','Órgão emissor',140],
        ['responsavel_legal','Responsável legal','Nome completo',150],
        ['rt_name','Responsável técnico','Nome completo',150],
        ['rt_cro','CRO / UF','Ex.: CRO-RJ 00000',45],
        ['rt_substituto','RT substituto','Se aplicável',150],
    ] as $f){
        [$key,$label,$hint,$max]=$f;$value=(string)($extra[$key]??$c[$key]??'');
        echo '<label class="od-profile-field"><span>'.esc($label).'</span><input name="'.esc($key).'" maxlength="'.$max.'" value="'.esc($value).'" placeholder="'.esc($hint).'"></label>';
    }
    echo '</div></section>';
    echo '<section class="od-profile-panel"><div class="od-profile-section-head"><h2>Atividades da unidade</h2><p>Essas respostas descrevem o serviço, mas precisam ser conferidas pelo responsável técnico.</p></div><div class="od-profile-grid">';
    foreach([
        ['modalidade','Modalidade','Ex.: fixa',90],
        ['classe','Classe','Conforme avaliação do RT',80],
        ['servicos','Serviços oferecidos','Atividades reais da unidade',600],
        ['atendimento_externo','Atendimento extraestabelecimento','Se aplicável',200],
    ] as $f){
        [$key,$label,$hint,$max]=$f;$value=(string)($extra[$key]??'');
        echo '<label class="od-profile-field"><span>'.esc($label).'</span><input name="'.esc($key).'" maxlength="'.$max.'" value="'.esc($value).'" placeholder="'.esc($hint).'"></label>';
    }
    echo '</div><p class="od-profile-note">Quantidade de consultórios, radiologia, sedação e processamento continuam em <a href="?page=perfil-operacional">Perfil operacional da clínica</a> e também são reaproveitados.</p></section>';
    echo '<div class="od-profile-actions"><button type="submit">Salvar dados compartilhados</button><span>As versões antigas dos documentos continuam preservadas.</span></div></form></div>';
    foot();exit;
}


/* Edição operacional sem solicitar novamente nome, CNPJ, endereço ou RT. */
function od_profile_operational_post():never {
    $c=clinic();
    if(!$c||!user()){http_response_code(403);exit('Faça login.');}
    $chairs=filter_var($_POST['chairs']??null,FILTER_VALIDATE_INT);
    $ster=$_POST['sterilization']??null;
    if($chairs===false||$chairs<1||$chairs>200||!is_string($ster)||!in_array($ster,['interna','terceirizada','mista'],true)){
        flash('Confira a quantidade de consultórios e a forma de processamento.');redirect('?page=perfil-operacional');
    }
    $xray=isset($_POST['has_xray'])?1:0;
    $sedation=isset($_POST['has_sedation'])?1:0;
    $q=db()->prepare('UPDATE clinics SET chairs=?,sterilization=?,has_xray=?,has_sedation=? WHERE id=? AND user_id=?');
    $q->execute([$chairs,$ster,$xray,$sedation,(int)$c['id'],(int)user()]);
    flash('Atividades da clínica salvas. Os documentos passam a usar os novos dados de perfil.');
    redirect('?page=perfil-operacional');
}

function od_profile_operational_page():never {
    $c=clinic();
    if(!$c){http_response_code(403);exit('Acesso necessário.');}
    h('Atividades da clínica',false);
    echo '<div class="od-profile-page"><div class="od-profile-intro"><a href="?page=clinica" class="od-profile-back">← Minha clínica</a><span class="odx-eyebrow">PERFIL OPERACIONAL</span><h1>Como funciona sua clínica?</h1><p>Atualize apenas as características da unidade. Nome, CNPJ e responsáveis continuam no <a href="?page=dados-compartilhados">cadastro da clínica</a>.</p></div>';
    echo '<form class="od-profile-form" method="post">'.token().'<input type="hidden" name="act" value="profile_operational_save"><section class="od-profile-panel"><div class="od-profile-section-head"><h2>Estrutura e serviços</h2><p>Os dados abaixo ajudam a indicar quais minutas podem exigir adaptação. Não determinam aprovação sanitária.</p></div>';
    echo '<div class="od-profile-grid"><label class="od-profile-field"><span>Consultórios individuais</span><input type="number" name="chairs" min="1" max="200" required value="'.(int)$c['chairs'].'"></label>';
    echo '<label class="od-profile-field"><span>Processamento de materiais</span><select name="sterilization" required>';
    foreach(['interna'=>'Na própria clínica','terceirizada'=>'Terceirizado','mista'=>'Na clínica e terceirizado'] as $key=>$label){
        echo '<option value="'.esc($key).'"'.($c['sterilization']===$key?' selected':'').'>'.esc($label).'</option>';
    }
    echo '</select></label></div><div class="od-profile-checks">';
    echo '<label><input type="checkbox" name="has_xray" value="1"'.(!empty($c['has_xray'])?' checked':'').'><span>Realiza radiologia odontológica</span></label>';
    echo '<label><input type="checkbox" name="has_sedation" value="1"'.(!empty($c['has_sedation'])?' checked':'').'><span>Realiza procedimentos com sedação</span></label>';
    echo '</div><p class="od-profile-note">Confirme o enquadramento e os requisitos específicos com o responsável técnico.</p></section>';
    echo '<div class="od-profile-actions"><button type="submit">Salvar atividades</button><span>Os dados cadastrais e as versões anteriores das minutas não serão alterados.</span></div></form></div>';
    foot();exit;
}
