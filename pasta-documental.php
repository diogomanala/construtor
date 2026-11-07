<?php
/** Odonto em Dia: impressão unificada e organizada da documentação da clínica. */
declare(strict_types=1);

function od_pasta_sections(): array {
    return [
        'SDBPF'=>['number'=>'01','title'=>'Governança e Boas Práticas','description'=>'Manual e estrutura de documentos da unidade'],
        'Planos'=>['number'=>'02','title'=>'Planos de gestão','description'=>'Planejamento, riscos, resíduos e tecnologias'],
        'POPs'=>['number'=>'03','title'=>'Protocolos e procedimentos','description'=>'Rotinas operacionais adequadas ao serviço'],
        'Formulários'=>['number'=>'04','title'=>'Formulários e registros','description'=>'Instrumentos de documentação e ciência da equipe'],
        'Guia de uso'=>['number'=>'05','title'=>'Orientações de uso','description'=>'Material auxiliar, opcional na pasta física'],
    ];
}
function od_pasta_suggestion(): array {
    // Sugestão editorial de montagem, NÃO rol de obrigações universais.
    return [2,3,5,6,7,11,14,15,16,21,22,23,24,25,26,27,28,30];
}
function od_pasta_saved(): array {
    $c=clinic();
    $q=odx_schema()->prepare('SELECT template_id,version,stage,content_html,updated_at FROM od_documents WHERE clinic_id=?');
    $q->execute([(int)$c['id']]);
    $result=[];
    foreach($q as $row)$result[(int)$row['template_id']]=$row;
    return $result;
}
function od_pasta_selected(): array {
    $raw=$_GET['ids']??[];
    if(!is_array($raw)||count($raw)>30)return [];
    $ids=[];
    foreach($raw as $v){
        if(!is_string($v)&&!is_int($v))continue;
        $id=filter_var($v,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>30]]);
        if($id!==false && template_by_id((int)$id))$ids[(int)$id]=true;
    }
    return array_keys($ids);
}
function od_pasta_status(?array $row): string {
    if(!$row)return 'Modelo ainda não personalizado';
    return ($row['stage']??'rascunho')==='revisao_rt'?'Aguardando revisão do RT':'Minuta em elaboração';
}
function od_pasta_groups(array $selected): array {
    $selected=array_fill_keys($selected,true);
    $byCat=[];
    foreach(template_catalog() as $doc){
        if(!isset($selected[(int)$doc['id']]))continue;
        $byCat[$doc['category']][]=$doc;
    }
    $result=[];
    foreach(od_pasta_sections() as $cat=>$meta){
        if(!empty($byCat[$cat]))$result[$cat]=['meta'=>$meta,'docs'=>$byCat[$cat]];
    }
    return $result;
}
function od_pasta_page(): never {
    $c=clinic();if(!$c||!user()){http_response_code(403);exit('Acesso necessário');}
    $saved=od_pasta_saved();$suggestion=array_flip(od_pasta_suggestion());
    $groups=[];foreach(template_catalog() as $doc)$groups[$doc['category']][]=$doc;
    h('Preparar pasta documental',false);
    echo '<div class="pasta-page"><div class="pasta-back"><a href="?page=documentos">'.od_ui_icon('arrow').' Voltar aos documentos</a></div>';
    echo '<div class="pasta-intro"><div><span class="pasta-eyebrow">ORGANIZAÇÃO DOCUMENTAL · IMPRESSÃO A4</span><h1>Sua pasta, organizada para imprimir.</h1><p>Reúna os documentos da clínica em uma única sequência, com capa personalizada, índice, divisórias e espaços para assinatura. Escolha o que realmente se aplica à unidade.</p></div><div class="pasta-intro-icon">'.od_ui_icon('book').'</div></div>';
    echo '<div class="pasta-benefits"><div><span>01</span><strong>Identidade da clínica</strong><p>Nome, logo e cores que você já configurou.</p></div><div><span>02</span><strong>Ordem de impressão</strong><p>Sumário e seções organizadas automaticamente.</p></div><div><span>03</span><strong>Conferência técnica</strong><p>Situação da minuta e assinaturas sem falsa aprovação.</p></div></div>';
    echo '<div class="pasta-warning">'.od_ui_icon('info').'<div><strong>Preparada para impressão não significa aprovada.</strong><p>A RDC Anvisa 1.002/2025 exige uma SDBPF acessível, adaptada à realidade da clínica e aprovada conforme suas regras. A impressão não substitui licenças, PBA, evidências reais nem revisão do RT. A norma não estabelece, de modo geral, que toda documentação deva existir somente em papel.</p></div></div>';
    echo '<form id="pasta-prepare-form" action="?page=pasta-impressao" method="get" target="_blank" rel="noopener"><input type="hidden" name="page" value="pasta-impressao">';
    echo '<div class="pasta-select-bar"><div><span class="pasta-eyebrow">MONTE SUA PASTA</span><h2>Escolha os documentos</h2><p><strong id="pasta-count">0</strong> materiais selecionados. Você pode incluir modelos ainda não preenchidos; eles ficarão claramente marcados como minuta.</p></div><div class="pasta-presets" role="group" aria-label="Selecionar documentos"><button type="button" data-pasta-preset="suggested">Sugestão inicial</button><button type="button" data-pasta-preset="saved">Apenas personalizados</button><button type="button" data-pasta-preset="all">Todo o acervo</button></div></div>';
    foreach(od_pasta_sections() as $cat=>$info){
        if(empty($groups[$cat]))continue;
        echo '<section class="pasta-group"><div class="pasta-group-head"><div><small>SEÇÃO '.esc($info['number']).'</small><h3>'.esc($info['title']).'</h3><p>'.esc($info['description']).'</p></div><span>'.count($groups[$cat]).' materiais</span></div><div class="pasta-rows">';
        foreach($groups[$cat] as $doc){
            $id=(int)$doc['id'];$row=$saved[$id]??null;
            $checked=isset($suggestion[$id]);
            echo '<div class="pasta-row"><label><input type="checkbox" name="ids[]" value="'.$id.'" data-pasta-doc data-saved="'.($row?'1':'0').'" data-suggested="'.($checked?'1':'0').'"'.($checked?' checked':'').'><span class="pasta-row-title"><strong>'.esc($doc['title']).'</strong><small>'.($row?'Versão '.(int)$row['version'].' · ':'').esc(od_pasta_status($row)).'</small></span></label><a href="?page=editar&amp;id='.$id.'" aria-label="Editar '.esc($doc['title']).'">Editar '.od_ui_icon('arrow').'</a></div>';
        }
        echo '</div></section>';
    }
    echo '<div class="pasta-print-options"><label><input type="checkbox" name="divisorias" value="1" checked> Incluir divisórias de seção</label><label><input type="checkbox" name="anexos" value="1" checked> Incluir lista de anexos e evidências a reunir</label><p>O índice é sempre incluído. Documentos externos não são inventados nem anexados automaticamente.</p></div>';
    echo '<div class="pasta-actions"><div><strong>Próximo passo: conferir a prévia</strong><small>A impressão abre em uma nova aba. Você pode escolher a impressora ou “Salvar como PDF” pelo navegador.</small></div><button class="pasta-generate" id="pasta-generate" type="submit">Preparar pasta para impressão '.od_ui_icon('arrow').'</button></div></form>';
    echo '</div>';foot();exit;
}
function od_pasta_print(): never {
    $c=clinic();if(!$c||!user()){http_response_code(403);exit('Acesso necessário');}
    $selected=od_pasta_selected();
    if(!$selected){flash('Selecione ao menos um documento antes de preparar a pasta.');redirect('?page=pasta-documental');}
    $saved=od_pasta_saved();$groups=od_pasta_groups($selected);
    if(!$groups){http_response_code(400);exit('Não há documentos para imprimir');}
    $brand=od_brand_current($c);
    $accent=(string)$brand['accent'];
    $date=(new DateTimeImmutable('now',new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y');
    $clinicName=(string)$c['name'];
    $location=trim((string)($c['city']??'').((!empty($c['state']))?' / '.$c['state']:''));
    $rt=trim((string)($c['rt_name']??'').(empty($c['rt_cro'])?'':' · '.$c['rt_cro']));
    $logo=$brand['logo_mime']?'<img src="?page=logo-clinica" class="pasta-clinic-logo" alt="Logo da clínica">':'<span class="pasta-lettermark">'.esc(mb_strtoupper(mb_substr($clinicName,0,1))).'</span>';
    $incomplete=0;foreach($selected as $id)if(!isset($saved[$id]))$incomplete++;
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Pasta documental · '.esc($clinicName).'</title><link rel="stylesheet" href="pasta-documental.css?v=1"></head><body class="pasta-print-body" style="--pasta-accent:'.esc($accent).'">';
    echo '<div class="pasta-print-toolbar"><div><strong>Pasta documental da clínica</strong><small>'.count($selected).' materiais · '.(int)$incomplete.' modelos sem versão salva · revisão profissional necessária</small></div><div><button type="button">Imprimir pasta A4</button><a href="?page=pasta-documental">Alterar seleção</a></div></div>';
    echo '<main class="pasta-print-book">';
    echo '<section class="pasta-cover"><div class="pasta-cover-top">'.$logo.'<div><strong>'.esc($clinicName).'</strong><span>'.esc($location).'</span></div></div><div class="pasta-cover-main"><span class="pasta-cover-eyebrow">SÉRIE DE DOCUMENTOS DE BOAS PRÁTICAS</span><h1>Pasta de organização documental</h1><p>Rotinas, protocolos, planos e documentos de apoio da unidade odontológica.</p><div class="pasta-cover-line"></div><span class="pasta-cover-status">DOCUMENTOS EM PREPARAÇÃO · SUJEITOS À REVISÃO E FORMALIZAÇÃO</span></div><div class="pasta-cover-bottom"><div><small>ESTABELECIMENTO</small><strong>'.esc($clinicName).'</strong><span>'.esc((string)($c['cnpj']??'')).'</span></div><div><small>RESPONSÁVEL TÉCNICO</small><strong>'.esc($rt?:'A informar').'</strong><span>Data de montagem: '.$date.'</span></div></div></section>';
    echo '<section class="pasta-index pasta-break"><div class="pasta-sheet-header"><span>DOCUMENTAÇÃO DA CLÍNICA</span><strong>ÍNDICE</strong></div><h2>Conteúdo desta pasta</h2><p>Documentos organizados por tipo, na sequência de impressão. A ordem não substitui a análise de aplicabilidade pelo RT.</p>';
    $sequence=0;
    foreach($groups as $group){
        $meta=$group['meta'];echo '<div class="pasta-index-group"><div><span>'.esc($meta['number']).'</span><strong>'.esc($meta['title']).'</strong></div><ol>';
        foreach($group['docs'] as $doc){$id=(int)$doc['id'];$sequence++;echo '<li><span>'.str_pad((string)$sequence,2,'0',STR_PAD_LEFT).'</span>'.esc($doc['title']).'<small>'.esc(od_pasta_status($saved[$id]??null)).'</small></li>';}
        echo '</ol></div>';
    }
    echo '<div class="pasta-index-note"><strong>Antes de formalizar</strong><p>Confira os dados e protocolos reais, a revisão técnica, a aprovação do responsável legal, a assinatura e a ciência da equipe. Nenhuma dessas etapas é presumida pela impressão.</p></div></section>';
    $divisorias=isset($_GET['divisorias'])&&$_GET['divisorias']==='1';
    foreach($groups as $group){
        $meta=$group['meta'];
        if($divisorias){
            echo '<section class="pasta-divider pasta-break"><div class="pasta-divider-top"><span>ODONTO EM DIA</span><span>'.esc($meta['number']).' / '.count($group['docs']).' DOCUMENTOS</span></div><div class="pasta-divider-main"><span>SEÇÃO '.esc($meta['number']).'</span><h2>'.esc($meta['title']).'</h2><p>'.esc($meta['description']).'</p></div><div class="pasta-divider-foot"><span>'.esc($clinicName).'</span><span>Material para revisão · '.$date.'</span></div></section>';
        }
        foreach($group['docs'] as $doc){
            $id=(int)$doc['id'];$state=$saved[$id]??null;
            $html=$state?od_safe_document((string)$state['content_html']):od_safe_document(od_guided_prepare_template_html((string)$doc['html'],$c));
            $fields=odx_get_fields((int)$c['id'],$id);
            $metaDoc=odx_spec($id,(string)$doc['category']);
            echo '<article class="pasta-document pasta-break"><div class="pasta-document-head">'.$logo.'<div class="pasta-document-head-clinic"><strong>'.esc($clinicName).'</strong><small>'.esc($location).'</small></div><span>'.esc($metaDoc['number']).'</span></div>';
            echo '<div class="pasta-document-title"><small>'.esc($doc['category']).' · '.esc($meta['title']).'</small><h2>'.esc($doc['title']).'</h2><div class="pasta-document-meta"><span>'.($state?'Versão '.(int)$state['version']:'Sem versão personalizada').'</span><span>'.esc(od_pasta_status($state)).'</span><span>Montagem '.$date.'</span></div></div>';
            echo '<div class="pasta-draft-banner">MINUTA PARA REVISÃO. Esta impressão não constitui aprovação técnica nem documentação comprobatória de execução.</div>';
            echo '<div class="pasta-clinic-facts"><div><small>Estabelecimento</small><strong>'.esc($clinicName).'</strong></div><div><small>CNPJ</small><strong>'.esc((string)($c['cnpj']??'')?:'A informar').'</strong></div><div><small>Responsável técnico</small><strong>'.esc($rt?:'A informar').'</strong></div></div>';
            $fieldSchema=odx_fields($id);
            if($fieldSchema){
                echo '<section class="pasta-field-info"><h3>Informações da unidade</h3><div>';
                foreach($fieldSchema as $field){$key=$field['key'];$value=trim((string)($fields[$key]??''));if($value==='')continue;echo '<div><span>'.esc($field['label']).'</span><p>'.nl2br(esc($value)).'</p></div>';}
                if(!array_filter($fields,static fn($x)=>is_string($x)&&trim($x)!==''))echo '<p class="pasta-not-filled">Campos de personalização ainda não preenchidos. Complete no editor antes da aprovação.</p>';
                echo '</div></section>';
            }
            echo '<section class="pasta-document-content">'.$html.'</section>';
            echo '<section class="pasta-approval"><h3>Revisão, aprovação e assinaturas</h3><p>Validar o conteúdo com o responsável técnico e formalizar a SDBPF conforme a RDC Anvisa 1.002/2025. As linhas abaixo são espaços para assinatura manual, não assinaturas digitais.</p><div class="pasta-signatures"><div><span></span><strong>Responsável técnico / CRO</strong><small>Assinatura e data</small></div><div><span></span><strong>Responsável legal</strong><small>Aprovação, assinatura e data</small></div></div></section>';
            echo '<footer class="pasta-doc-footer"><span>ODONTO EM DIA · DOCUMENTAÇÃO INTERNA</span><span>'.esc(od_pasta_status($state)).'</span></footer></article>';
        }
    }
    if(isset($_GET['anexos'])&&$_GET['anexos']==='1'){
        echo '<section class="pasta-appendix pasta-break"><div class="pasta-sheet-header"><span>DOCUMENTAÇÃO COMPLEMENTAR</span><strong>ANEXOS</strong></div><h2>Documentos e evidências a reunir</h2><p>Esta folha serve para organizar documentos reais da unidade. A plataforma <strong>não cria</strong> licenças, projetos técnicos, contratos, certificados, comprovações ou laudos emitidos por terceiros.</p><ul>';
        $attachments=[
            'Licença sanitária e demais autorizações aplicáveis',
            'Projeto Básico de Arquitetura aprovado, quando exigido pela norma aplicável',
            'Documentação do responsável técnico e equipe',
            'Contratos e regularidade dos prestadores terceirizados',
            'Registros reais de manutenção, limpeza, água e controle de vetores',
            'Comprovantes de coleta, transporte e destinação de resíduos',
            'Comprovações de capacitação e ciência da equipe',
            'Registros reais dos processos de esterilização e monitoramento',
        ];
        if(!empty($c['has_xray']))$attachments[]='Documentação específica da radiologia, quando houver serviço de diagnóstico por imagem';
        foreach($attachments as $attachment)echo '<li><span class="pasta-box-check"></span>'.esc($attachment).'<small>Conferir e anexar conforme aplicabilidade</small></li>';
        echo '</ul><p class="pasta-appendix-note">A lista é um apoio de organização, não uma relação exaustiva nem um atestado de regularidade. Conferir exigências estaduais, municipais e do perfil assistencial.</p></section>';
    }
    echo '</main><script>document.querySelectorAll(".pasta-print-toolbar button").forEach(b=>b.addEventListener("click",()=>window.print()));</script></body></html>';exit;
}
