<?php
declare(strict_types=1);
/** Galeria de ilustrações originais. Não equivale a lista de insumos obrigatórios. */
function od_mat_catalog(): array {
    static $map=null;
    if($map!==null)return $map;
    $raw=@file_get_contents(__DIR__.'/data/materials-visual-catalog.json');
    $data=is_string($raw)?json_decode($raw,true):null;
    if(!is_array($data)||count($data['items']??[])!==54)throw new RuntimeException('Acervo visual indisponível');
    $map=$data;return $map;
}
function od_mat_by_id(): array {
    static $items=null;
    if($items!==null)return $items;
    $items=[];
    foreach(od_mat_catalog()['items'] as $item)$items[(string)$item['id']]=$item;
    return $items;
}
function od_mat_defaults(int $templateId,array $clinic): array {
    $map=[
      6=>['mascara_cirurgica','oculos_protecao','luvas_descartaveis','touca_descartavel'],
      8=>['bandeja_perfurada','cassete_instrumentais'],
      9=>['bandeja_perfurada','cassete_instrumentais'],
      10=>['envelope_esterilizacao','seladora','indicador_quimico'],
      11=>['autoclave','seladora','envelope_esterilizacao','indicador_quimico','indicador_biologico','bandeja_perfurada'],
      12=>['cassete_instrumentais','envelope_esterilizacao'],
      14=>['coletor_perfurocortante','recipiente_infectante','recipiente_comum'],
      21=>['cadeira_odontologica','autoclave','compressor','bomba_vacuo'],
      24=>['coletor_perfurocortante','recipiente_infectante','recipiente_comum'],
      25=>['cadeira_odontologica','autoclave','seladora','compressor'],
      28=>['cadeira_odontologica','autoclave','compressor']
    ];
    $items=$map[$templateId]??[];
    if(in_array($templateId,[21,25,28],true)&&!empty($clinic['has_xray']))$items[]='aparelho_raio_x';
    return $items;
}
function od_mat_schema(): PDO {
    $db=db();
    $db->exec('CREATE TABLE IF NOT EXISTS od_material_drafts(
        clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL,
        items_json TEXT NOT NULL, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(clinic_id,template_id))');
    $db->exec('CREATE TABLE IF NOT EXISTS od_material_history(
        clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, version INTEGER NOT NULL,
        items_json TEXT NOT NULL, pack_version TEXT NOT NULL DEFAULT \'materials-v1\',
        PRIMARY KEY(clinic_id,template_id,version))');
    return $db;
}
function od_mat_read(int $clinicId,int $templateId,?int $version=null): ?array {
    $db=od_mat_schema();
    if($version!==null){
        $q=$db->prepare('SELECT items_json FROM od_material_history WHERE clinic_id=? AND template_id=? AND version=?');
        $q->execute([$clinicId,$templateId,$version]);
    }else{
        $q=$db->prepare('SELECT items_json FROM od_material_drafts WHERE clinic_id=? AND template_id=?');
        $q->execute([$clinicId,$templateId]);
    }
    $json=$q->fetchColumn();
    if(!is_string($json))return null;
    $items=json_decode($json,true);
    if(!is_array($items))return [];
    $valid=od_mat_by_id();
    return array_values(array_unique(array_filter($items,static fn($id)=>is_string($id)&&isset($valid[$id]))));
}
function od_mat_view_items(array $clinic,int $templateId,?int $version=null,?int $savedVersion=null): array {
    if($version!==null)return od_mat_read((int)$clinic['id'],$templateId,$version)??[];
    $draft=od_mat_read((int)$clinic['id'],$templateId);
    if($draft!==null)return $draft;
    if($savedVersion!==null)return od_mat_read((int)$clinic['id'],$templateId,$savedVersion)??[];
    return od_mat_defaults($templateId,$clinic);
}
function od_mat_snapshot(PDO $db,array $clinic,int $templateId,int $version): void {
    $draft=od_mat_read((int)$clinic['id'],$templateId);
    $items=$draft??od_mat_defaults($templateId,$clinic);
    $q=$db->prepare('INSERT INTO od_material_history(clinic_id,template_id,version,items_json,pack_version) VALUES(?,?,?,?,?)');
    $q->execute([(int)$clinic['id'],$templateId,$version,json_encode($items,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'materials-v1']);
}
function od_mat_sprite(array $item): string {
    $i=(int)$item['index'];$x=($i%9)*12.5;$y=intdiv($i,9)*20;
    return '<span class="od-mat-image" style="background-position:'.number_format($x,2,'.','').'% '.number_format($y,2,'.','').'%" aria-hidden="true"></span>';
}
function od_mat_post(): never {
    $clinic=clinic();
    if(!$clinic || !user()){http_response_code(403);exit('Acesso necessário');}
    $id=filter_var($_POST['template_id']??null,FILTER_VALIDATE_INT);
    if(!$id || !template_by_id((int)$id)){http_response_code(400);exit('Documento inválido');}
    $raw=$_POST['materials']??[];
    if(!is_array($raw)||count($raw)>54){http_response_code(400);exit('Seleção inválida');}
    $valid=od_mat_by_id();$selected=[];
    foreach($raw as $item){
        if(!is_string($item)||!isset($valid[$item])){http_response_code(400);exit('Material desconhecido');}
        $selected[$item]=true;
    }
    if(count($selected)>18){flash('Selecione até 18 imagens por documento.');redirect('?page=acervo-visual&id='.(int)$id);}
    $q=od_mat_schema()->prepare('INSERT INTO od_material_drafts(clinic_id,template_id,items_json,updated_at) VALUES(?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,template_id) DO UPDATE SET items_json=excluded.items_json,updated_at=CURRENT_TIMESTAMP');
    $q->execute([(int)$clinic['id'],(int)$id,json_encode(array_keys($selected),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
    flash('Quadro ilustrado atualizado. Salve uma nova versão da minuta para guardar essa seleção no histórico.');
    redirect('?page=editar&id='.(int)$id);
}
function od_mat_preview(array $clinic,int $templateId,?int $historicalVersion=null,?int $savedVersion=null): string {
    $items=od_mat_view_items($clinic,$templateId,$historicalVersion,$savedVersion);
    if(!$items)return '';
    $known=od_mat_by_id();
    $html='<section class="od-mat-preview" aria-label="Imagens ilustrativas de apoio"><div class="od-mat-preview-head"><span>REFERÊNCIAS VISUAIS</span><h3>Materiais e equipamentos ilustrativos</h3><p>Exemplos visuais para identificação. Não são uma lista de materiais obrigatórios nem instruções clínicas.</p></div><div class="od-mat-preview-grid">';
    foreach(array_slice($items,0,18) as $id){$item=$known[$id]??null;if(!$item)continue;
        $html.='<div class="od-mat-preview-item">'.od_mat_sprite($item).'<strong>'.esc($item['label']).'</strong></div>';
    }
    return $html.'</div><p class="od-mat-preview-note">Ilustrações em revisão técnica. O responsável técnico deve verificar a pertinência e a identificação dos itens para a unidade.</p></section>';
}
function od_mat_gallery(): never {
    $clinic=clinic();
    if(!$clinic || !user()){http_response_code(403);exit('Acesso necessário');}
    $id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
    $template=$id?template_by_id((int)$id):null;
    $catalog=od_mat_catalog();$items=$catalog['items'];
    $savedVersion=null;
    if($template){
        $q=odx_schema()->prepare('SELECT version FROM od_documents WHERE clinic_id=? AND template_id=?');
        $q->execute([(int)$clinic['id'],(int)$id]);
        $v=$q->fetchColumn();if($v!==false)$savedVersion=(int)$v;
    }
    $selected=$template?array_fill_keys(od_mat_view_items($clinic,(int)$id,null,$savedVersion),true):[];
    h('Biblioteca visual',false);
    echo '<div class="od-mat-page"><header class="od-mat-header"><a href="'.($template?'?page=editar&id='.(int)$id:'?page=documentos').'">← '.($template?'Voltar ao documento':'Voltar à biblioteca').'</a><span>ACERVO ILUSTRADO</span><h1>54 imagens, organizadas por material.</h1><p>Escolha uma categoria, encontre o objeto e monte um quadro visual. Os itens são ilustrativos e precisam de conferência técnica antes de serem usados como referência clínica.</p></header>';
    if($template)echo '<div class="od-mat-context"><div><small>PERSONALIZAÇÃO DO DOCUMENTO</small><strong>'.esc($template['title']).'</strong><p>Os itens selecionados aparecerão na prévia. Depois, salve uma versão do documento para preservar essa seleção.</p></div><a href="?page=editar&id='.(int)$id.'">Voltar ao editor</a></div>';
    echo '<div class="od-mat-toolbar"><label class="od-mat-search">'.od_ui_icon('search').'<input type="search" id="od-mat-search" placeholder="Buscar instrumento ou material" aria-label="Buscar imagens"></label><div class="od-mat-filters" role="group" aria-label="Categorias"><button type="button" data-mat-filter="all" class="is-active">Todos (54)</button>';
    foreach($catalog['display_categories'] as $cat=>$title){
        $n=count(array_filter($items,static fn($x)=>$x['category']===$cat));
        echo '<button type="button" data-mat-filter="'.esc($cat).'">'.esc($title).' ('.$n.')</button>';
    }
    echo '</div></div>';
    if($template)echo '<form method="post" id="od-mat-form" class="od-mat-form">'.token().'<input type="hidden" name="act" value="material_selection_save"><input type="hidden" name="template_id" value="'.(int)$id.'"><div class="od-mat-actions"><strong id="od-mat-selected">0 selecionados</strong><button type="button" data-mat-action="clear">Limpar</button><button type="button" data-mat-action="defaults">Usar sugestões</button><button class="od-mat-save" type="submit">Aplicar ao documento</button></div>';
    echo '<div class="od-mat-grid">';
    foreach($items as $item){
        $id2=(string)$item['id'];$category=(string)$item['category'];
        echo '<label class="od-mat-tile" data-mat-category="'.esc($category).'" data-mat-search="'.esc(mb_strtolower($item['label'].' '.$category)).'">';
        if($template)echo '<input type="checkbox" name="materials[]" value="'.esc($id2).'" '.(isset($selected[$id2])?'checked':'').'>';
        echo od_mat_sprite($item).'<strong>'.esc($item['label']).'</strong><small>'.esc($catalog['display_categories'][$category]??$category).'</small></label>';
    }
    echo '</div><p id="od-mat-empty" class="od-mat-empty" hidden>Nenhuma imagem encontrada. Tente outro termo ou categoria.</p>';
    if($template)echo '</form>';
    echo '<p class="od-mat-disclaimer">Imagens originais produzidas por IA e organizadas para esta plataforma. Identificação e fidelidade técnica ainda dependem de avaliação por profissional habilitado. Cor e símbolo de recipientes de resíduos não definem a segregação exigida para sua unidade.</p></div>';
    echo '<script type="application/json" id="od-mat-defaults">'.json_encode($template?od_mat_defaults((int)$id,$clinic):[],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE).'</script>';
    foot();exit;
}
