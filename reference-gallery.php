<?php
declare(strict_types=1);

/**
 * Galeria educativa complementar. Não vincular os quatro novos grupos
 * automaticamente aos POPs da SDBPF.
 */
function od_reference_gallery_available(): bool {
    static $available=null;
    if($available!==null)return $available;
    $available=false;
    foreach(od_illustrated_catalog() as $guide){
        if(is_array($guide)&&!empty($guide['reference_only'])&&od_illustrated_ready($guide)){
            $available=true;
            break;
        }
    }
    return $available;
}

function od_reference_gallery_page(): never {
    if(!user()||!clinic()){http_response_code(403);exit('Acesso necessário');}
    if(!od_reference_gallery_available()){
        flash('O acervo visual complementar está em preparação. Os guias disponíveis permanecem nos documentos.');
        redirect('?page=documentos');
    }
    h('Referências visuais',false);
    echo '<div class="od-ref-page"><header class="od-ref-hero"><a href="?page=documentos">Documentos / Referências visuais</a><span>ACERVO COMPLEMENTAR</span><h1>Conheça os materiais da clínica</h1><p>Explore cenas e imagens isoladas para reconhecer os instrumentos e materiais ilustrados. Consulte o responsável técnico antes de aplicar qualquer informação à rotina real.</p></header>';
    $first=true;
    foreach(od_illustrated_catalog() as $guide){
        if(!is_array($guide)||empty($guide['reference_only'])||!od_illustrated_ready($guide))continue;
        echo '<details class="od-ref-group"'.($first?' open':'').'><summary><span><small>REFERÊNCIA ILUSTRADA</small><strong>'.esc((string)$guide['title']).'</strong><em>'.count($guide['items']).' objetos para identificar</em></span><b aria-hidden="true">+</b></summary>';
        echo od_illustrated_guide_markup($guide);
        echo '</details>';
        $first=false;
    }
    echo '<aside class="od-ref-disclaimer"><strong>Referência visual, não instrução clínica.</strong> As ilustrações podem não reproduzir exatamente os modelos comerciais; não representam uma lista obrigatória de materiais nem comprovam adequação sanitária. Conferir nomes, indicação e características com o responsável técnico e as instruções do fabricante.</aside></div>';
    foot();
    exit;
}
