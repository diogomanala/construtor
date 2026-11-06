<?php
declare(strict_types=1);
/**
 * Bancadas ilustradas: apoio visual editável, separado dos 30 documentos regulatórios.
 * Os materiais são ilustrativos e a seleção deve ser revisada pelo RT.
 */
function od_kit_definitions(): array {
    return [
        'profilaxia'=>[
            'title'=>'Bancada ilustrada de profilaxia',
            'subtitle'=>'Materiais e instrumentais de referência',
            'summary'=>'Uma composição visual inicial. Remova o que não se aplica à rotina e confirme a lista com o responsável técnico.',
            'items'=>[
                ['id'=>'ponta_ultrassom','label'=>'Ponta de ultrassom','tile'=>0],
                ['id'=>'curetas_gracey','label'=>'Curetas Gracey','tile'=>1],
                ['id'=>'escova_profilaxia','label'=>'Escova de profilaxia','tile'=>2],
                ['id'=>'pasta_profilatica','label'=>'Pasta profilática','tile'=>3],
                ['id'=>'copo_dappen','label'=>'Copo Dappen','tile'=>4],
                ['id'=>'pedra_afiar','label'=>'Pedra de afiar','tile'=>5]
            ]
        ],
        'exodontia'=>[
            'title'=>'Bancada ilustrada de extração dentária',
            'subtitle'=>'Instrumentais e insumos de referência',
            'summary'=>'A seleção é ilustrativa. Instrumentos, anestésicos e consumíveis dependem do procedimento, da avaliação clínica e dos protocolos locais.',
            'items'=>[
                ['id'=>'seringa_carpule','label'=>'Seringa carpule','tile'=>6],
                ['id'=>'tubete_anestesico','label'=>'Tubete anestésico','tile'=>7],
                ['id'=>'agulha_gengival','label'=>'Agulha gengival','tile'=>8],
                ['id'=>'forceps_dentario','label'=>'Fórceps dentário','tile'=>9],
                ['id'=>'elevador_dentario','label'=>'Elevador dentário','tile'=>10],
                ['id'=>'periotomo','label'=>'Periótomo','tile'=>11],
                ['id'=>'pinca_hemostatica','label'=>'Pinça hemostática','tile'=>12],
                ['id'=>'tesoura_cirurgica','label'=>'Tesoura cirúrgica','tile'=>13],
                ['id'=>'bisturi','label'=>'Bisturi','tile'=>14],
                ['id'=>'fio_sutura','label'=>'Fio de sutura','tile'=>15]
            ]
        ]
    ];
}
function od_kit_schema(): PDO {
    $db=db();
    $db->exec("CREATE TABLE IF NOT EXISTS od_kit_visual_preferences(
      clinic_id INTEGER NOT NULL,
      kit_key TEXT NOT NULL,
      items_json TEXT NOT NULL,
      notes TEXT NOT NULL DEFAULT '',
      revision INTEGER NOT NULL DEFAULT 1,
      updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY(clinic_id,kit_key)
    )");
    return $db;
}
function od_kit_saved(int $clinicId,string $key): ?array {
    $q=od_kit_schema()->prepare('SELECT items_json,notes,revision,updated_at FROM od_kit_visual_preferences WHERE clinic_id=? AND kit_key=?');
    $q->execute([$clinicId,$key]);$r=$q->fetch();
    if(!$r)return null;
    $r['selected']=json_decode((string)$r['items_json'],true);
    if(!is_array($r['selected']))$r['selected']=[];
    return $r;
}
function od_kit_post(): never {
    $c=clinic();if(!$c||!user()){http_response_code(403);exit('Acesso necessário');}
    $key=(string)($_POST['kit']??'');
    $data=od_kit_definitions()[$key]??null;
    if(!$data){http_response_code(400);exit('Conjunto desconhecido');}
    $raw=$_POST['items']??[];
    if(!is_array($raw)||count($raw)>50){http_response_code(400);exit('Materiais inválidos');}
    $allow=array_column($data['items'],'id');
    $selected=array_values(array_unique(array_filter(array_map('strval',$raw),static fn($v)=>in_array($v,$allow,true))));
    $notes=trim((string)($_POST['notes']??''));
    if(mb_strlen($notes)>1200){flash('Observações devem ter até 1.200 caracteres.');redirect('?page=kits-bancada&kit='.$key);}
    $q=od_kit_schema()->prepare(
      "INSERT INTO od_kit_visual_preferences(clinic_id,kit_key,items_json,notes,revision)
       VALUES(?,?,?,?,1)
       ON CONFLICT(clinic_id,kit_key) DO UPDATE SET
       items_json=excluded.items_json,notes=excluded.notes,
       revision=revision+1,updated_at=CURRENT_TIMESTAMP");
    $q->execute([(int)$c['id'],$key,json_encode($selected,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),$notes]);
    flash('Seleção visual da bancada salva. Revise os materiais com o responsável técnico.');
    redirect('?page=kits-bancada&kit='.$key);
}
function od_kit_icon(array $item): string {
    $tile=(int)$item['tile'];
    $col=$tile%4;$row=intdiv($tile,4);
    $x=$col*100/3;$y=$row*100/3;
    $style='background-position:'.number_format($x,4,'.','').'% '.number_format($y,4,'.','').'%';
    return '<span class="od-kit-photo" style="'.$style.'" aria-hidden="true"></span>';
}
function od_kits_page(): never {
    $c=clinic();if(!$c){http_response_code(403);exit('Acesso necessário');}
    $all=od_kit_definitions();
    $key=(string)($_GET['kit']??'');
    h('Bancadas ilustradas',false);
    echo '<div class="od-kit-page"><header class="od-kit-head"><a class="od-kit-return" href="?page=documentos">Documentos / Materiais visuais</a><span class="od-kit-kicker">BIBLIOTECA VISUAL</span><h1>Materiais de bancada, organizados.</h1><p>Selecione um exemplo, adapte os materiais e salve sua composição. As imagens são ilustrativas e não substituem protocolos assistenciais.</p></header>';
    if(!isset($all[$key])) {
        echo '<div class="od-kit-choices">';
        foreach($all as $slug=>$kit){
            echo '<a href="?page=kits-bancada&kit='.esc($slug).'" class="od-kit-choice"><div class="od-kit-choice-icons">';
            foreach(array_slice($kit['items'],0,4) as $item)echo od_kit_icon($item);
            echo '</div><span>'.count($kit['items']).' ILUSTRAÇÕES INDIVIDUAIS</span><h2>'.esc($kit['title']).'</h2><p>'.esc($kit['summary']).'</p><strong>Personalizar este conjunto →</strong></a>';
        }
        echo '</div><div class="od-kit-info">Esses dois conjuntos são complementares e não fazem parte dos 30 modelos regulatórios. A inclusão de um POP clínico exige elaboração e revisão próprias.</div>';
        foot();exit;
    }
    $kit=$all[$key];$saved=od_kit_saved((int)$c['id'],$key);
    $selected=$saved?array_fill_keys($saved['selected'],true):array_fill_keys(array_column($kit['items'],'id'),true);
    echo '<div class="od-kit-workspace"><form class="od-kit-form" method="post">'.token().'<input type="hidden" name="act" value="kit_visual_save"><input type="hidden" name="kit" value="'.esc($key).'">';
    echo '<div class="od-kit-tools"><a href="?page=kits-bancada" class="od-kit-back">← Ver outros conjuntos</a><span>'.($saved?'SALVO · EDIÇÃO '.(int)$saved['revision']:'SUGESTÃO INICIAL').'</span></div><div class="od-kit-intro"><span>CONJUNTO ILUSTRADO</span><h2>'.esc($kit['title']).'</h2><p>'.esc($kit['summary']).'</p></div>';
    echo '<div class="od-kit-controls"><button type="button" data-kit-action="all">Selecionar todos</button><button type="button" data-kit-action="none">Limpar seleção</button><span id="od-kit-count" aria-live="polite"></span></div><div class="od-kit-grid">';
    foreach($kit['items'] as $item){
        $slug=$item['id'];
        echo '<label class="od-kit-item"><input type="checkbox" name="items[]" value="'.esc($slug).'" '.(isset($selected[$slug])?'checked':'').'><span class="od-kit-item-art">'.od_kit_icon($item).'</span><strong>'.esc($item['label']).'</strong><small>Incluir na composição</small></label>';
    }
    echo '</div><label class="od-kit-note-label">Observações específicas da clínica<textarea name="notes" maxlength="1200" rows="3" placeholder="Descreva somente o que foi confirmado na sua rotina.">'.esc($saved['notes']??'').'</textarea></label>';
    echo '<div class="od-kit-actions"><button type="submit">Salvar minha seleção</button><button type="button" class="od-kit-print" onclick="window.print()">Imprimir visualização</button></div>';
    echo '<p class="od-kit-warning">Atenção: os objetos são ilustrativos. O conjunto não corresponde a uma lista mínima ou obrigatória de instrumentais. Uma revisão do RT é necessária antes do uso clínico.</p></form></div></div>';
    foot();exit;
}
