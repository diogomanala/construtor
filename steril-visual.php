<?php
declare(strict_types=1);

/**
 * Protótipo visual de esterilização. Ícones SVG originais, independentes do pack PNG
 * reprovado. Não são imagens técnicas certificadas nem instruções assistenciais.
 */
function od_steril_visual_items(): array {
    return [
        'autoclave'=>[
            'name'=>'Autoclave',
            'kind'=>'Equipamento',
            'description'=>'Equipamento destinado à esterilização por vapor, quando previsto no processamento do serviço.',
            'check'=>'Conferir modelo, instruções do fabricante, parâmetros aplicáveis e registros mantidos pela clínica.',
        ],
        'seladora'=>[
            'name'=>'Seladora',
            'kind'=>'Preparo e embalagem',
            'description'=>'Equipamento usado na selagem de embalagens compatíveis com o processo adotado.',
            'check'=>'Verificar compatibilidade com a embalagem e as orientações de operação e manutenção do fabricante.',
        ],
        'embalagem'=>[
            'name'=>'Embalagem para esterilização',
            'kind'=>'Preparo e embalagem',
            'description'=>'Invólucro próprio para o acondicionamento de artigos, conforme produto e método utilizados.',
            'check'=>'Confirmar especificação, integridade e informações de identificação exigidas pelo procedimento da unidade.',
        ],
        'quimico'=>[
            'name'=>'Indicador químico',
            'kind'=>'Monitorização',
            'description'=>'Indicador utilizado na monitorização do processamento, de acordo com sua classe e indicação de uso.',
            'check'=>'Confirmar classe, posição, finalidade e interpretação com as instruções do fabricante e o protocolo do RT.',
        ],
        'biologico'=>[
            'name'=>'Indicador biológico',
            'kind'=>'Monitorização',
            'description'=>'Dispositivo empregado para monitorização biológica, conforme método e rotina definidos pelo serviço.',
            'check'=>'Conferir armazenamento, processamento, incubação, interpretação e registros segundo o produto utilizado.',
        ],
        'bandeja'=>[
            'name'=>'Bandeja perfurada',
            'kind'=>'Instrumentais e acessórios',
            'description'=>'Acessório para organização de produtos, quando compatível com o método de processamento.',
            'check'=>'Conferir composição, integridade, posicionamento e compatibilidade com as instruções aplicáveis.',
        ],
        'cassete'=>[
            'name'=>'Cassete de instrumentais',
            'kind'=>'Instrumentais e acessórios',
            'description'=>'Organizador de instrumentais utilizado conforme o fluxo e a compatibilidade do serviço.',
            'check'=>'Confirmar se o cassete é apropriado, sua capacidade e a preparação necessária para o método adotado.',
        ],
    ];
}

/** Vetores originais, escaláveis e com fundo transparente, sem quadros brancos. */
function od_steril_visual_svg(string $key): string {
    $k=preg_replace('/[^a-z]/','',$key);
    $pre='<svg viewBox="0 0 320 250" role="presentation" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="sv-m-'.$k.'" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#f7faf7"/><stop offset=".47" stop-color="#dce5df"/><stop offset="1" stop-color="#a6b6ae"/></linearGradient>
        <linearGradient id="sv-s-'.$k.'" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#e5eee8"/><stop offset="1" stop-color="#8ca49a"/></linearGradient>
        <linearGradient id="sv-g-'.$k.'" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#267e67"/><stop offset="1" stop-color="#0e4238"/></linearGradient>
        <linearGradient id="sv-glass-'.$k.'" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#ffffff" stop-opacity=".95"/><stop offset="1" stop-color="#bddad0" stop-opacity=".7"/></linearGradient>
      </defs>';
    $end='</svg>';
    $metal='url(#sv-m-'.$k.')';
    $soft='url(#sv-s-'.$k.')';
    $green='url(#sv-g-'.$k.')';
    $glass='url(#sv-glass-'.$k.')';
    switch($key) {
      case 'autoclave':
        $art='<ellipse cx="163" cy="226" rx="112" ry="12" fill="#0d5142" opacity=".12"/>
          <path d="M73 58q0-17 17-17h146q17 0 17 17v145q0 11-11 11H82q-9 0-9-11Z" fill="'.$soft.'" stroke="#718f85" stroke-width="2"/>
          <path d="M79 58q0-11 12-11h142q12 0 12 12v141H79Z" fill="'.$metal.'"/>
          <path d="M85 60h26v140H85Z" fill="#a8b9b0" opacity=".45"/>
          <rect x="91" y="63" width="27" height="41" rx="7" fill="#315e54"/>
          <rect x="98" y="70" width="14" height="12" rx="2" fill="#aee2ce"/><path d="M99 86h12" stroke="#d7efe4" stroke-width="2"/>
          <circle cx="104" cy="94" r="3" fill="#8fdbc3"/>
          <circle cx="177" cy="131" r="77" fill="#89a59a" stroke="#dfe9e3" stroke-width="5"/>
          <circle cx="177" cy="131" r="67" fill="'.$metal.'" stroke="#66867a" stroke-width="4"/>
          <circle cx="177" cy="131" r="56" fill="#c2d1ca" stroke="#fafdf9" stroke-width="3"/>
          <circle cx="177" cy="131" r="47" fill="#264f47"/>
          <circle cx="177" cy="131" r="41" fill="#163a35"/>
          <path d="M149 110q26-23 51 0" fill="none" stroke="#7db39e" stroke-width="4" opacity=".75"/>
          <path d="M157 145h40" stroke="#92c6b2" stroke-width="2" opacity=".75"/>
          <rect x="218" y="119" width="28" height="12" rx="6" fill="#c6d4cd" stroke="#80998f" stroke-width="2"/>
          <path d="M102 213v9m126-9v9" stroke="#6c8980" stroke-width="10" stroke-linecap="round"/>
          <path d="M90 42h143" stroke="#fbfffc" stroke-width="3" opacity=".85"/>';
        break;
      case 'seladora':
        $art='<ellipse cx="155" cy="210" rx="118" ry="9" fill="#0d5142" opacity=".11"/>
          <path d="M42 151h234l-23 42H64Z" fill="'.$soft.'" stroke="#839b91" stroke-width="2"/>
          <path d="M50 150q0-16 16-16h190q16 0 16 16v22H50Z" fill="'.$metal.'" stroke="#91a89c" stroke-width="2"/>
          <rect x="62" y="142" width="192" height="11" rx="3" fill="#41665a"/>
          <path d="M73 134 84 103h146l15 31" fill="#d0ddd5" stroke="#8b9e95" stroke-width="3"/>
          <path d="M91 103h138" stroke="#729385" stroke-width="5" stroke-linecap="round"/>
          <path d="M85 92h145" stroke="#eaf2eb" stroke-width="7" stroke-linecap="round"/>
          <path d="M89 89q-5-2-5-8V65q0-7 7-7h135q7 0 7 7v23" fill="none" stroke="#5c8071" stroke-width="9" stroke-linecap="round"/>
          <path d="M103 85h114" stroke="#ffffff" stroke-opacity=".75" stroke-width="2"/>
          <rect x="191" y="166" width="52" height="16" rx="4" fill="#2d6e5b"/>
          <circle cx="207" cy="174" r="3" fill="#a0dec4"/>
          <circle cx="219" cy="174" r="3" fill="#a0dec4"/>
          <path d="M75 193v9m160-9v9" stroke="#55766a" stroke-width="6" stroke-linecap="round"/>';
        break;
      case 'embalagem':
        $art='<ellipse cx="160" cy="220" rx="83" ry="9" fill="#0d5142" opacity=".1"/>
          <g transform="rotate(-9 156 128)">
          <rect x="96" y="35" width="129" height="182" rx="7" fill="#b3cfc1" opacity=".8"/>
          <rect x="88" y="29" width="129" height="182" rx="7" fill="'.$glass.'" stroke="#83ab9b" stroke-width="2"/>
          <rect x="95" y="38" width="115" height="18" rx="3" fill="#39846d"/>
          <path d="M102 46h100" stroke="#addbc8" stroke-width="2" stroke-dasharray="6 5"/>
          <rect x="108" y="69" width="91" height="113" rx="5" fill="#fff" opacity=".58" stroke="#bdd5ca"/>
          <path d="M128 95l49 69m-43-69 38 55m-32-57 27 40" stroke="#84a99a" stroke-width="4" stroke-linecap="round" opacity=".64"/>
          <path d="M100 193h105" stroke="#7ea893" stroke-width="3"/>
          <path d="M180 187v13" stroke="#3e8a71" stroke-width="4"/>
          </g>';
        break;
      case 'quimico':
        $art='<ellipse cx="162" cy="214" rx="104" ry="10" fill="#0d5142" opacity=".08"/>
          <g transform="rotate(-12 160 126)">
          <rect x="49" y="98" width="230" height="74" rx="10" fill="#d1ddd6" stroke="#86a398" stroke-width="2"/>
          <rect x="56" y="104" width="216" height="62" rx="6" fill="#f6faf7"/>
          <path d="M72 110v50m32-50v50m32-50v50m32-50v50m32-50v50m32-50v50" stroke="#b6cec2" stroke-width="1.5"/>
          <rect x="77" y="119" width="22" height="25" rx="3" fill="'.$green.'"/>
          <rect x="109" y="119" width="22" height="25" rx="3" fill="#519b80"/>
          <rect x="141" y="119" width="22" height="25" rx="3" fill="#a5bfb1"/>
          <rect x="173" y="119" width="22" height="25" rx="3" fill="#31554a"/>
          <rect x="205" y="119" width="22" height="25" rx="3" fill="#90bba9"/>
          <path d="M76 153h155" stroke="#65897a" stroke-width="3" stroke-linecap="round"/>
          </g>';
        break;
      case 'biologico':
        $art='<ellipse cx="159" cy="216" rx="76" ry="10" fill="#0d5142" opacity=".1"/>
          <rect x="118" y="37" width="85" height="177" rx="20" fill="'.$soft.'" stroke="#7e9c8d" stroke-width="2"/>
          <rect x="128" y="39" width="65" height="26" rx="8" fill="#316e5b"/>
          <rect x="135" y="65" width="51" height="123" rx="12" fill="'.$glass.'" stroke="#9dc2b1" stroke-width="2"/>
          <rect x="139" y="112" width="43" height="67" rx="7" fill="#68aa8e" opacity=".45"/>
          <rect x="132" y="90" width="58" height="54" rx="4" fill="#f6faf7" stroke="#8eb9a4"/>
          <path d="M145 103h30m-30 8h30m-30 8h19" stroke="#4e8b72" stroke-width="3" stroke-linecap="round"/>
          <rect x="145" y="125" width="32" height="8" rx="2" fill="#c8ac78" opacity=".85"/>
          <path d="M129 47h62" stroke="#8fc0a9" stroke-width="2"/>';
        break;
      case 'bandeja':
        $holes='';for($i=0;$i<5;$i++)for($j=0;$j<3;$j++)$holes.='<ellipse cx="'.(93+$i*31+$j*9).'" cy="'.(118+$j*17).'" rx="8" ry="4" fill="#68897c" opacity=".65"/>';
        $art='<ellipse cx="167" cy="209" rx="114" ry="12" fill="#0d5142" opacity=".08"/>
          <path d="M48 97 220 59 281 127 102 172Z" fill="'.$metal.'" stroke="#93a99d" stroke-width="3"/>
          <path d="M48 97v24l54 69 179-45v-18l-179 45Z" fill="'.$soft.'" stroke="#7e9c8f" stroke-width="2"/>
          <path d="M60 103 220 70 268 123 104 160Z" fill="#d1e0d7" stroke="#fafdf9" stroke-width="2"/>
          <path d="M60 103 220 70 268 123 104 160" fill="none" stroke="#87a799" stroke-width="2"/>
          '.$holes.'
          <path d="M110 173v12m146-39v13" stroke="#708f80" stroke-width="4"/>';
        break;
      case 'cassete':
        $mesh='';for($i=0;$i<7;$i++)$mesh.='<path d="M'.(83+$i*25).' 102l19 64" stroke="#7d9a8c" stroke-width="2" opacity=".52"/>';
        for($i=0;$i<4;$i++)$mesh.='<path d="M87 '.(110+$i*15).'h157" stroke="#7d9a8c" stroke-width="2" opacity=".47"/>';
        $art='<ellipse cx="162" cy="216" rx="112" ry="11" fill="#0d5142" opacity=".1"/>
          <path d="M53 93 221 67 275 121 103 152Z" fill="'.$metal.'" stroke="#7d9b8c" stroke-width="3"/>
          <path d="M53 94v42l50 58 172-36v-37l-172 31Z" fill="'.$soft.'" stroke="#759183" stroke-width="3"/>
          <path d="M67 102 219 79 260 118 106 144Z" fill="#c9ded2"/>
          '.$mesh.'
          <path d="M94 144v28m165-52v26" stroke="#4f7464" stroke-width="8" stroke-linecap="round"/>
          <path d="M94 145h13m152-24h-15" stroke="#f8fdf8" stroke-width="4"/>
          <rect x="126" y="163" width="62" height="14" rx="5" fill="'.$green.'" opacity=".8"/>';
        break;
      default:
        $art='<circle cx="160" cy="120" r="65" fill="'.$metal.'" stroke="#88a89a" stroke-width="3"/>';
    }
    return $pre.$art.$end;
}

function od_steril_visual_schema(): PDO {
    $db=db();
    $db->exec("CREATE TABLE IF NOT EXISTS od_steril_visual_choices (
        clinic_id INTEGER PRIMARY KEY,
        selected_json TEXT NOT NULL,
        notes TEXT NOT NULL DEFAULT '',
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}
function od_steril_visual_saved(int $clinicId): ?array {
    $stmt=od_steril_visual_schema()->prepare('SELECT selected_json,notes,updated_at FROM od_steril_visual_choices WHERE clinic_id=?');
    $stmt->execute([$clinicId]);
    $row=$stmt->fetch();
    if(!$row)return null;
    $decoded=json_decode((string)$row['selected_json'],true);
    $keys=array_keys(od_steril_visual_items());
    $row['selected']=is_array($decoded)?array_values(array_unique(array_intersect($keys,array_filter($decoded,'is_string')))):[];
    return $row;
}
function od_steril_visual_post(): never {
    $c=clinic();
    if(!$c||!user()){http_response_code(403);exit('Acesso necessário');}
    $raw=$_POST['items']??[];
    if(!is_array($raw)||count($raw)>7){http_response_code(400);exit('Seleção inválida');}
    $allowed=array_keys(od_steril_visual_items());
    $items=[];
    foreach($raw as $key){
        if(!is_string($key)||!in_array($key,$allowed,true)){http_response_code(400);exit('Material inválido');}
        $items[$key]=true;
    }
    if(!is_string($_POST['notes']??'')){http_response_code(400);exit('Observação inválida');}
    $notes=trim((string)($_POST['notes']??''));
    if(mb_strlen($notes)>600){flash('A observação deve ter até 600 caracteres.');redirect('?page=ilustracao-esterilizacao');}
    $stmt=od_steril_visual_schema()->prepare("INSERT INTO od_steril_visual_choices(clinic_id,selected_json,notes)
      VALUES(?,?,?)
      ON CONFLICT(clinic_id) DO UPDATE SET
      selected_json=excluded.selected_json,notes=excluded.notes,updated_at=CURRENT_TIMESTAMP");
    $stmt->execute([(int)$c['id'],json_encode(array_keys($items),JSON_THROW_ON_ERROR),$notes]);
    flash('Composição visual salva para esta clínica. Os materiais ainda precisam de revisão técnica.');
    redirect('?page=ilustracao-esterilizacao');
}
function od_steril_visual_detail_html(string $suffix): string {
    $icon='<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>';
    return '<div class="sv-detail-content" data-sv-content>
      <span class="sv-detail-tag" data-sv-kind>Equipamento</span>
      <h2 data-sv-name>Autoclave</h2>
      <p class="sv-detail-intro" data-sv-description>Selecione um objeto para conhecer sua função.</p>
      <div class="sv-detail-divider"></div>
      <span class="sv-detail-section-label">O QUE CONFERIR</span>
      <p class="sv-detail-guidance" data-sv-check>Verifique a aplicabilidade com o responsável técnico.</p>
      <button type="button" class="sv-detail-toggle" data-sv-toggle aria-pressed="true">'.$icon.' <span data-sv-toggle-label>Incluído na composição</span></button>
      <p class="sv-detail-note">A composição é uma referência visual, não um checklist de liberação ou aprovação sanitária.</p>
    </div>';
}
function od_steril_visual_page(): never {
    $c=clinic();
    if(!$c||!user()){http_response_code(403);exit('Acesso necessário');}
    $items=od_steril_visual_items();
    $saved=od_steril_visual_saved((int)$c['id']);
    $selected=$saved?array_fill_keys($saved['selected'],true):array_fill_keys(array_keys($items),true);
    $brand=od_brand_current($c);
    h('Esterilização ilustrada',false);
    echo '<div class="sv-page" style="--sv-clinic-accent:'.esc($brand['accent']).'">';
    echo '<header class="sv-head"><div class="sv-head-copy"><a class="sv-back" href="?page=editar&amp;id=11">Documentos / POP 08 / Ilustração</a><div class="sv-eyebrow"><span class="sv-dot"></span> BIBLIOTECA VISUAL · PROTÓTIPO</div><h1>Conheça os materiais de esterilização.</h1><p>Toque em um objeto para ver para que serve, o que conferir e decidir se ele faz parte da sua composição visual.</p></div><div class="sv-head-side"><span class="sv-folio">01 <span>/ 01</span></span><span class="sv-head-pill">7 objetos ilustrados</span></div></header>';
    echo '<div class="sv-warning"><span class="sv-warning-icon">i</span><p>Ilustrações vetoriais conceituais, não fotografias nem instruções de operação. A seleção e o conteúdo técnico exigem revisão do responsável técnico.</p></div>';
    echo '<form method="post" id="sv-form" class="sv-form">'.token().'<input type="hidden" name="act" value="steril_visual_save">';
    echo '<div class="sv-workspace"><section class="sv-stage" aria-label="Objetos de processamento e esterilização"><div class="sv-stage-top"><div><span class="sv-small-label">QUADRO INTERATIVO</span><h2>Selecione um objeto</h2></div><span class="sv-stage-status"><i></i> Ilustração técnica conceitual</span></div><div class="sv-objects">';
    $index=0;
    foreach($items as $key=>$info){
        $index++;
        $on=isset($selected[$key]);
        echo '<button type="button" class="sv-object '.($index===1?'is-active ':'').($on?'is-included':'is-excluded').'" data-sv-object="'.esc($key).'" data-sv-name="'.esc($info['name']).'" data-sv-kind="'.esc($info['kind']).'" data-sv-description="'.esc($info['description']).'" data-sv-check="'.esc($info['check']).'" data-sv-index="'.$index.'" aria-pressed="'.($index===1?'true':'false').'"><span class="sv-object-number">'.str_pad((string)$index,2,'0',STR_PAD_LEFT).'</span><span class="sv-object-art">'.od_steril_visual_svg($key).'</span><span class="sv-object-footer"><strong>'.esc($info['name']).'</strong><span class="sv-object-state">'.($on?'Incluído':'Fora do quadro').'</span></span></button>';
        echo '<input type="checkbox" hidden name="items[]" value="'.esc($key).'" data-sv-input="'.esc($key).'"'.($on?' checked':'').'>';
    }
    echo '</div><p class="sv-stage-caption">Os objetos podem variar de acordo com o método, os equipamentos e a rotina da unidade.</p></section>';
    echo '<aside class="sv-detail sv-detail-desktop" id="sv-detail" aria-label="Detalhes do objeto selecionado">'.od_steril_visual_detail_html('desktop').'</aside></div>';
    echo '<div class="sv-bottom"><div class="sv-bottom-text"><span class="sv-small-label">PERSONALIZAÇÃO DA CLÍNICA</span><strong data-sv-count>'.count($selected).' de '.count($items).' objetos incluídos</strong><p>Salve somente os materiais pertinentes, depois da conferência do RT.</p></div><div class="sv-bottom-actions"><button type="button" class="sv-btn sv-btn-secondary" id="sv-print">Imprimir quadro</button><button type="submit" class="sv-btn sv-btn-primary">Salvar composição</button></div></div>';
    echo '<label class="sv-notes-label">Observações da unidade <span>(opcional, sem dados de pacientes)</span><textarea rows="2" maxlength="600" name="notes" id="sv-notes" placeholder="Ex.: confirmar compatibilidade dos acessórios com o equipamento utilizado.">'.esc($saved['notes']??'').'</textarea></label>';
    echo '</form>';
    echo '<section class="sv-print-legend" aria-label="Legenda para impressão"><div class="sv-print-identity">';
    if($brand['logo_mime'])echo '<img src="?page=logo-clinica" alt="Logotipo da clínica">';
    echo '<div><strong>'.esc($c['name']).'</strong><span>QUADRO ILUSTRATIVO DE MATERIAIS · SUJEITO À REVISÃO DO RT</span></div></div>';
    echo '<h2>Legenda dos objetos</h2><ol>';
    $index=0;foreach($items as $key=>$info){$index++;echo '<li data-sv-print="'.esc($key).'"'.(!isset($selected[$key])?' hidden':'').'><span>'.str_pad((string)$index,2,'0',STR_PAD_LEFT).'</span><strong>'.esc($info['name']).'</strong><p>'.esc($info['description']).'</p></li>';}
    echo '</ol><p class="sv-print-note" data-sv-print-notes></p><p class="sv-print-disclaimer">Material visual de apoio. Não substitui procedimentos aprovados, instruções de fabricantes ou avaliação do responsável técnico.</p></section>';
    echo '<a class="sv-footer-link" href="?page=editar&amp;id=11">Voltar ao POP 08 · Esterilização e monitoramento</a></div>';
    echo '<dialog class="sv-modal" id="sv-mobile-modal" aria-label="Detalhes do material"><div class="sv-modal-header"><strong>DETALHES DO OBJETO</strong><button type="button" class="sv-modal-close" data-sv-close aria-label="Fechar detalhes">×</button></div>'.od_steril_visual_detail_html('mobile').'</dialog>';
    foot();exit;
}
