<?php
declare(strict_types=1);
/**
 * Materiais jurídicos e checagem estrutural.
 * Fonte: docs/BASE-NORMATIVA-ODONTO-2026-11-06.md.
 * Importante: o resultado não é laudo, homologação, licença ou aprovação oficial.
 */
function od_legal_reference_map(): array {
    return [
        'RDC 1.002/2025'=>[
            'note'=>'Boas práticas odontológicas, SDBPF e documentação aplicável',
            'url'=>'https://visa.jundiai.sp.gov.br/wp-content/uploads/2025/12/RDC-10022025-BP-ODONTO.pdf',
        ],
        'RDC 63/2011'=>[
            'note'=>'Boas práticas gerais para serviços de saúde, quando aplicáveis',
            'url'=>'https://www.gov.br/anvisa/pt-br/assuntos/servicosdesaude/seguranca-do-paciente/legislacao',
        ],
        'RDC 222/2018'=>[
            'note'=>'Gerenciamento de resíduos de serviços de saúde',
            'url'=>'https://www.gov.br/anvisa/pt-br/assuntos/servicosdesaude/gerenciamento-de-residuos',
        ],
        'RDC 509/2021'=>[
            'note'=>'Gerenciamento de tecnologias em saúde; revogou RDC 2/2010',
            'url'=>'https://anvisalegis.datalegis.net/action/ActionDatalegis.php?acao=abrirTextoAto&cod_menu=9431&cod_modulo=310&link=S&numeroAto=00000509&orgao=RDC%2FDC%2FANVISA%2FMS&seqAto=000&tipo=RDC&valorAno=2021',
        ],
        'RDC 36/2013'=>[
            'note'=>'Segurança do paciente conforme configuração do serviço',
            'url'=>'https://www.gov.br/anvisa/pt-br/assuntos/servicosdesaude/seguranca-do-paciente/legislacao',
        ],
        'NR-32'=>[
            'note'=>'Saúde e segurança ocupacional nos serviços de saúde',
            'url'=>'https://www.gov.br/trabalho-e-emprego/pt-br/acesso-a-informacao/participacao-social/conselhos-e-orgaos-colegiados/comissao-tripartite-partitaria-permanente/normas-regulamentadora/normas-regulamentadoras-vigentes/norma-regulamentadora-no-32-nr-32',
        ],
        'LGPD'=>[
            'note'=>'Tratamento de dados pessoais; aplicabilidade conforme os dados tratados',
            'url'=>'https://www.gov.br/saude/pt-br/acesso-a-informacao/lgpd/legislacao',
        ],
        'CFO 118/2012'=>[
            'note'=>'Código de Ética Odontológica',
            'url'=>'https://sistemas.cfo.org.br/visualizar/atos/RESOLU%C3%87%C3%83O/SEC/2012/118',
        ],
        'RDC 611/2022'=>[
            'note'=>'Radiologia diagnóstica/intervencionista apenas quando houver atividade correspondente',
            'url'=>'https://www.gov.br/saude/pt-br/composicao/sgtes/degerts/qualificacao-de-engenharia-clinica/ementario',
        ],
    ];
}
function od_legal_matrix(): array {
    static $map=null;
    if($map!==null)return $map;
    $json=@file_get_contents(__DIR__.'/data/regulatory-map.json');
    $decoded=is_string($json)?json_decode($json,true):null;
    $map=is_array($decoded) && isset($decoded['references'],$decoded['documents'])
      && is_array($decoded['references']) && is_array($decoded['documents']) ? $decoded : [];
    return $map;
}
function od_legal_for_document(int $id,array $clinic): array {
    $map=od_legal_matrix();$refs=[];
    foreach($map['documents']??[] as $doc) {
        if((int)($doc['id']??0)!==$id)continue;
        foreach($doc['regulatory_links']??[] as $link) {
            $key=(string)($link['code']??'');
            $source=$map['references'][$key]??null;
            if(!is_array($source) || empty($source['url']) || empty($source['title']))continue;
            $label=(string)$source['title'];
            $note=(string)($source['scope']??'Referência normativa relacionada');
            if(!empty($link['articles']))$note='Arts. '.(string)$link['articles'].' · '.$note;
            if(!empty($link['conditional']))$note.=' · Aplicabilidade a confirmar com o RT';
            $refs[$label]=['note'=>$note,'url'=>(string)$source['url']];
        }
        break;
    }
    // Quando a unidade informa radiologia, exibir a referência complementar
    // apenas nos documentos de tecnologia que necessitam dessa avaliação.
    if(!empty($clinic['has_xray']) && in_array($id,[21,25],true)) {
        $source=$map['references']['rdc611']??null;
        if(is_array($source))$refs[(string)$source['title']]=[
            'note'=>(string)$source['scope'].' · Confirmar modalidade e regras específicas',
            'url'=>(string)$source['url']
        ];
    }
    return $refs;
}
function od_legal_review_panel(int $docId,array $clinic,bool $historical=false): string {
    $refs=od_legal_for_document($docId,$clinic);
    $s='<section class="od-legal-review" aria-labelledby="od-legal-review-title" data-legal-clinic="'.(trim((string)($clinic['name']??''))!==''?'1':'0').'" data-legal-rt="'.(trim((string)($clinic['rt_name']??''))!=='' && trim((string)($clinic['rt_cro']??''))!==''?'1':'0').'" data-od-name="'.esc($clinic['name']??'').'" data-od-cnpj="'.esc($clinic['cnpj']??'').'" data-od-rtname="'.esc($clinic['rt_name']??'').'" data-od-cro="'.esc($clinic['rt_cro']??'').'"><header class="od-legal-review-heading"><div class="od-legal-review-symbol" aria-hidden="true">✓</div><div><span class="od-legal-mini">CONFERÊNCIA DE PREENCHIMENTO</span><h3 id="od-legal-review-title">Confira sua minuta antes de enviar ao RT.</h3><p>Verificamos os campos da plataforma. A análise técnica, a aplicação das normas e as assinaturas permanecem sob responsabilidade humana.</p></div></header>';
    $s.='<div class="od-legal-pills"><span class="od-legal-pill">'.od_ui_icon('book').' Referências oficiais identificadas</span><span class="od-legal-pill od-legal-pill-neutral">'.od_ui_icon('shield').' Revisão técnica necessária</span></div>';
    $s.='<details class="od-legal-sources"><summary>Ver normas relacionadas e fontes oficiais</summary><div><p>As referências a seguir foram associadas a este modelo na matriz documental. Elas não significam que todos os requisitos se aplicam à unidade ou foram cumpridos. Confira o texto vigente com o RT.</p>';
    foreach($refs as $name=>$ref)$s.='<a href="'.esc($ref['url']).'" target="_blank" rel="noopener noreferrer"><strong>'.esc($name).'</strong><small>'.esc($ref['note']).'</small><span aria-hidden="true">↗</span></a>';
    $s.='<a href="https://www.gov.br/anvisa/pt-br/assuntos/servicosdesaude/projeto-de-melhoria-do-processo-de-inspecao-sanitaria-em-servicos-de-saude-e-de-interesse-para-a-saude/harmonizacao-de-roteiros-objetivos-de-inspecao-roi/confira-os-materiais-disponibilizados-roi-planilhas-sintese-e-links-de-limesurvey/roteiro-objetivo-de-inspecao-odontologia-versao-1-2.pdf/" target="_blank" rel="noopener noreferrer"><strong>ROI Odontologia v1.2</strong><small>Roteiro de inspeção da Anvisa, referência de avaliação por risco</small><span aria-hidden="true">↗</span></a></div></details>';
    if($historical)$s.='<div class="od-legal-historical">Esta é uma versão anterior, somente leitura. Para realizar nova conferência, abra a versão atual.</div>';
    else {
        $s.='<label class="od-legal-confirm"><input type="checkbox" id="od-legal-confirm" value="1"><span>Conferi que os dados preenchidos e eventuais sugestões correspondem à realidade da clínica.</span></label>';
        $s.='<button type="button" class="od-legal-trigger" id="od-legal-trigger">'.od_ui_icon('search').'<span>Conferir minha minuta</span>'.od_ui_icon('arrow').'</button>';
        $s.='<div class="od-legal-result" id="od-legal-result" aria-live="polite" role="status" hidden></div>';
    }
    return $s.'<p class="od-legal-historical">Referências consultadas em 06/11/2026. Checagem automatizada limitada à estrutura e aos dados declarados, sem aprovação pela Anvisa.</p></section>';
}
