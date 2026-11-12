<?php
/** Odonto em Dia - editor documental, controle por clinica e exportacao Word. */
function od_editor_schema(): PDO {
    $db = db();
    $db->exec('CREATE TABLE IF NOT EXISTS od_documents (clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, content_html TEXT NOT NULL, version INTEGER NOT NULL DEFAULT 1, stage TEXT NOT NULL DEFAULT \'rascunho\', updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(clinic_id, template_id))');
    $db->exec('CREATE TABLE IF NOT EXISTS od_document_history (id INTEGER PRIMARY KEY AUTOINCREMENT, clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, version INTEGER NOT NULL, content_html TEXT NOT NULL, stage TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(clinic_id,template_id,version))');
    return $db;
}
function od_safe_document(string $html): string {
    if (strlen($html) > 180000 || !class_exists('DOMDocument')) return '';
    $dom=new DOMDocument('1.0','UTF-8');
    $prev=libxml_use_internal_errors(true);
    $ok=$dom->loadHTML('<?xml encoding="UTF-8"><div id="content-root">'.$html.'</div>',LIBXML_NONET|LIBXML_NOERROR|LIBXML_NOWARNING);
    libxml_clear_errors(); libxml_use_internal_errors($prev);
    if (!$ok) return '';
    $root=$dom->getElementById('content-root');
    if (!$root) {foreach($dom->getElementsByTagName('div') as $el){if($el->getAttribute('id')==='content-root'){$root=$el;break;}}}
    if (!$root) return '';
    $allowed=array_fill_keys(['p','h2','h3','h4','div','span','b','strong','i','em','u','ul','ol','li','br','hr','blockquote','table','thead','tbody','tr','th','td','small','sup','sub'],true);
    $danger=array_fill_keys(['script','style','iframe','object','embed','form','input','button','svg','math','img','link','meta','video','audio'],true);
    $walk=function($node)use(&$walk,$allowed,$danger){
        if ($node->nodeType===XML_COMMENT_NODE){$node->parentNode?->removeChild($node);return;}
        if ($node->nodeType===XML_ELEMENT_NODE) {
            $name=strtolower($node->nodeName);
            if (isset($danger[$name])){$node->parentNode?->removeChild($node);return;}
            // Preserva apenas os vínculos internos seguros para edição dos campos.
            // Qualquer outro atributo recebido no HTML continua sendo removido.
            $guidedBind=null;$inlineMark=null;
            if($name==='span'){
                $candidate=$node->getAttribute('data-odx-bind');
                if(in_array($candidate,['setor','executor','frequencia','equipamentos_insumos'],true))$guidedBind=$candidate;
                if($node->getAttribute('data-odx-inline')==='1')$inlineMark='1';
            }
            if($node->hasAttributes())while($node->attributes->length)$node->removeAttributeNode($node->attributes->item(0));
            if($guidedBind!==null)$node->setAttribute('data-odx-bind',$guidedBind);
            elseif($inlineMark!==null)$node->setAttribute('data-odx-inline','1');
            foreach(iterator_to_array($node->childNodes) as $child)$walk($child);
            if(!isset($allowed[$name])){
                $p=$node->parentNode;if(!$p)return;
                while($node->firstChild)$p->insertBefore($node->firstChild,$node);
                $p->removeChild($node);
            }
        }
    };
    foreach(iterator_to_array($root->childNodes) as $n)$walk($n);
    $html='';foreach($root->childNodes as $n)$html.=$dom->saveHTML($n);
    return trim($html);
}
function od_editor_doc(int $template, int $clinicId):?array{
    $s=od_editor_schema()->prepare('SELECT * FROM od_documents WHERE clinic_id=? AND template_id=?');$s->execute([$clinicId,$template]);return $s->fetch()?:null;
}
function od_editor_post(): never {
    if (!user() || !($c=clinic())) {http_response_code(403);exit('Acesso não autorizado');}
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
    if(!$id || !template_by_id($id)){http_response_code(400);exit('Modelo inexistente.');}
    $html=od_safe_document((string)($_POST['body_html']??''));
    if(strlen($html)<30){flash('O documento precisa ter conteúdo antes de salvar.');redirect('?page=editar&id='.$id);}
    $stage=(string)($_POST['stage']??'rascunho');
    if(!in_array($stage,['rascunho','revisao_rt'],true))$stage='rascunho';
    $db=od_editor_schema();$db->beginTransaction();
    try {
        $s=$db->prepare('SELECT version FROM od_documents WHERE clinic_id=? AND template_id=?');$s->execute([$c['id'],$id]);
        $prior=$s->fetchColumn();$version=$prior===false?1:(int)$prior+1;
        $db->prepare('INSERT INTO od_documents(clinic_id,template_id,content_html,version,stage,updated_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,template_id) DO UPDATE SET content_html=excluded.content_html,version=excluded.version,stage=excluded.stage,updated_at=CURRENT_TIMESTAMP')->execute([$c['id'],$id,$html,$version,$stage]);
        $db->prepare('INSERT INTO od_document_history(clinic_id,template_id,version,content_html,stage) VALUES(?,?,?,?,?)')->execute([$c['id'],$id,$version,$html,$stage]);
        $db->commit();
    } catch(Throwable $e){$db->rollBack();error_log('[OdontoEditor] Falha ao salvar documento');flash('Não foi possível salvar. Tente novamente.');redirect('?page=editar&id='.$id);}
    flash('Minuta salva, versão '.$version.'. A aprovação técnica continua pendente.');redirect('?page=editar&id='.$id);
}
function od_editor_styles():void{echo <<<'CSS'
<style>
.ed-shell{max-width:1130px;margin:24px auto 64px;display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:24px}.ed-panel{border:1px solid #d9e8e0;background:#fff;border-radius:18px;box-shadow:0 14px 45px #062a2620;overflow:hidden}.ed-bar{padding:12px 18px;border-bottom:1px solid #e4eee9;background:#f8fbfa;display:flex;gap:8px;align-items:center;flex-wrap:wrap}.ed-bar button,.ed-bar select{border-radius:9px;border:1px solid #d8e5df;background:white;color:#134239;padding:9px 13px;min-height:36px;font:inherit;cursor:pointer}.ed-bar button:hover{background:#e9f6ee}.ed-content{padding:28px;min-height:460px;line-height:1.7;outline:none;font:16px/1.6 system-ui,sans-serif;color:#193e36}.ed-content:focus{box-shadow:inset 0 0 0 2px #1b826c}.ed-content h2{font-size:23px}.ed-content h3{font-size:18px;margin-top:25px}.ed-content table{border-collapse:collapse;width:100%;font-size:13px}.ed-content td,.ed-content th{border:1px solid #d6e0dc;padding:9px}.ed-side{align-self:start;position:sticky;top:20px;background:#fff;border:1px solid #dce6e1;border-radius:16px;padding:19px}.ed-side h3{font-size:16px}.ed-side p{font-size:13px;line-height:1.6;color:#52736b}.ed-side label{font-size:13px;display:block;margin-top:12px}.ed-side select{width:100%;padding:11px;border:1px solid #d1ded8;border-radius:9px}.ed-side button{display:block;width:100%;margin-top:13px}.ed-mini{font-size:13px;color:#517069}.ed-history{display:flex;flex-direction:column;gap:8px;padding:0;list-style:none}.ed-history li{padding:10px;border-radius:9px;background:#f3f8f5;font-size:12px}.ed-tip{background:#fff8ed;border:1px solid #e9d8b6;color:#704f22;border-radius:12px;padding:12px 14px;font-size:13px;margin-bottom:14px}.ed-actions{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0 0}.ed-actions a{font-size:13px;padding:9px 13px;border:1px solid #cee0d8;border-radius:10px}.ed-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}.ed-item{padding:17px;background:#fff;border:1px solid #d7e7df;border-radius:13px}.ed-item h3{margin:2px 0 9px;font-size:16px}.ed-item small{font-size:12px;color:#688278}.ed-item a{font-weight:700;color:#137867}@media(max-width:800px){.ed-shell{grid-template-columns:1fr}.ed-side{position:static}.ed-content{padding:18px}.ed-bar{padding:10px}}
</style>
CSS;}
function od_docx_xml(string $html,array $c,string $title):string{
    $dom=new DOMDocument('1.0','UTF-8');libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>',LIBXML_NONET);libxml_clear_errors();
    $paragraphs=[['text'=>'ODONTO EM DIA | MINUTA DE TRABALHO','style'=>'Title'],['text'=>$title,'style'=>'Heading1'],['text'=>'Clínica: '.$c['name'].' | CNPJ: '.$c['cnpj'].' | RT: '.$c['rt_name'].' / '.$c['rt_cro'],'style'=>'Normal'],['text'=>'DOCUMENTO NÃO APROVADO. Adaptar e submeter à revisão do responsável técnico.','style'=>'Normal']];
    $nodes=$dom->getElementsByTagName('body')->item(0);
    if($nodes){$walker=function($node)use(&$walker,&$paragraphs){if($node->nodeType!==XML_ELEMENT_NODE)return; $tag=strtolower($node->nodeName);if(in_array($tag,['h2','h3','h4','p','li','tr','blockquote'],true)){$text=trim(preg_replace('/\s+/u',' ',$node->textContent));if($text!=='')$paragraphs[]=['text'=>($tag==='li'?'• ':'').$text,'style'=>in_array($tag,['h2','h3','h4'],true)?'Heading2':'Normal']; if($tag==='tr')return;}foreach($node->childNodes as $child)$walker($child);};$walker($nodes);}
    $paragraphs[]=['text'=>'Aprovação do RT: _______________________    Data: ____/____/______','style'=>'Normal'];
    $out='';foreach($paragraphs as $p){$text=htmlspecialchars($p['text'],ENT_XML1|ENT_QUOTES,'UTF-8');$style=$p['style'];$out.='<w:p><w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr><w:r><w:t xml:space="preserve">'.$text.'</w:t></w:r></w:p>';}
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$out.'<w:sectPr/></w:body></w:document>';
}
function od_send_docx(string $html,array $c,array $d):never{
    if(!class_exists('ZipArchive')){http_response_code(503);exit('Exportação Word temporariamente indisponível.');}
    $temp=tempnam(sys_get_temp_dir(),'oddoc');if(!$temp){http_response_code(500);exit('Não foi possível gerar o documento.');}
    $zip=new ZipArchive();if($zip->open($temp,ZipArchive::OVERWRITE)!==true){@unlink($temp);http_response_code(500);exit('Erro ao criar o arquivo.');}
    $zip->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>');
    $zip->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
    $zip->addFromString('word/document.xml',od_docx_xml($html,$c,$d['title']));
    $zip->addFromString('word/styles.xml','<?xml version="1.0" encoding="UTF-8"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:sz w:val="22"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:rPr><w:b/><w:color w:val="147763"/><w:sz w:val="26"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="Heading1"/><w:rPr><w:b/><w:sz w:val="34"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="Heading2"/><w:rPr><w:b/><w:sz w:val="28"/></w:rPr></w:style></w:styles>');
    $zip->close();
    $name='odonto-em-dia-'.(int)$d['id'].'-minuta.docx';header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');header('Content-Disposition: attachment; filename="'.$name.'"');header('Content-Length: '.filesize($temp));header('X-Content-Type-Options: nosniff');readfile($temp);@unlink($temp);exit;
}
function od_editor_page(string $page):never{
    if(!user() || !($c=clinic())){http_response_code(403);exit('Acesso necessário.');}
    $db=od_editor_schema();
    if($page==='meus-documentos'){
        $q=$db->prepare('SELECT * FROM od_documents WHERE clinic_id=? ORDER BY updated_at DESC');$q->execute([$c['id']]);$docs=$q->fetchAll();
        h('Meus documentos');od_editor_styles();echo '<p class="lead">Suas minutas salvas ficam disponíveis para continuar editando e baixar quando precisar.</p><div class="ed-cards">';
        foreach($docs as $row){$d=template_by_id((int)$row['template_id']);if(!$d)continue;echo '<article class="ed-item"><small>Versão '.(int)$row['version'].' · '.esc($row['stage']==='revisao_rt'?'Aguardando revisão do RT':'Em elaboração').'</small><h3>'.esc($d['title']).'</h3><p class="ed-mini">Atualizado em '.esc($row['updated_at']).'</p><a href="?page=editar&id='.(int)$d['id'].'">Continuar documento →</a></article>';}
        if(!$docs)echo '<p>Você ainda não salvou documentos. <a href="?page=documentos">Abra a biblioteca para começar.</a></p>';echo '</div>';foot();exit;
    }
    $id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);$d=$id?template_by_id($id):null;if(!$d){http_response_code(404);exit('Modelo não encontrado.');}
    $saved=od_editor_doc((int)$id,(int)$c['id']);$content=$saved['content_html']??od_safe_document((string)$d['html']);
    if($page==='baixar-docx')od_send_docx($content,$c,$d);
    if($page==='imprimir'){
        header('Content-Type: text/html; charset=UTF-8');header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'');
        echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><title>'.esc($d['title']).'</title><style>body{font:15px/1.65 Arial,sans-serif;max-width:820px;margin:45px auto;color:#20372e;padding:0 20px}h1{font-size:30px}h2{font-size:23px;margin-top:35px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #c4cdc7;padding:9px}aside{background:#fff5db;border-left:4px solid #bb874d;padding:12px}@media print{body{margin:0;max-width:initial}}</style></head><body><p>ODONTO EM DIA · MINUTA</p><h1>'.esc($d['title']).'</h1><p><b>Clínica:</b> '.esc($c['name']).' · <b>CNPJ:</b> '.esc($c['cnpj']).' · <b>RT:</b> '.esc($c['rt_name']).' / '.esc($c['rt_cro']).'</p><aside>Minuta não aprovada. A adaptação, revisão e implementação são de responsabilidade do RT.</aside>'.$content.'<p style="margin-top:50px">Assinatura do RT: ___________________ Data: ____/____/______</p></body></html>';exit;
    }
    h('Editor de documentos');od_editor_styles();
    echo '<p class="lead">'.esc($d['title']).'</p><div class="ed-tip">Este documento é uma minuta. Revise os procedimentos com o responsável técnico antes de aprovar ou implementar.</div><div class="ed-shell"><div class="ed-panel"><div class="ed-bar"><button type="button" onclick="odCmd(\'bold\')"><b>Negrito</b></button><button type="button" onclick="odCmd(\'italic\')"><i>Itálico</i></button><button type="button" onclick="odCmd(\'insertUnorderedList\')">• Lista</button><button type="button" onclick="odBlock(\'h3\')">Título</button><button type="button" onclick="odCmd(\'undo\')">Desfazer</button></div><div id="odDocument" class="ed-content" contenteditable="true" role="textbox" aria-label="Conteúdo editável do documento">'.$content.'</div></div><aside class="ed-side"><h3>Documento da clínica</h3><p><b>'.esc($c['name']).'</b><br>CNPJ: '.esc($c['cnpj']).'<br>RT: '.esc($c['rt_name']).' / '.esc($c['rt_cro']).'</p><p>'.($saved?'Versão '.(int)$saved['version'].' salva.':'Você está começando a versão 1.').'</p><form method="post" id="odSave">'.token().'<input type="hidden" name="act" value="editor_save"><input type="hidden" name="id" value="'.(int)$id.'"><textarea name="body_html" id="odBody" hidden></textarea><label>Status<select name="stage"><option value="rascunho">Em elaboração</option><option value="revisao_rt" '.(($saved['stage']??'')==='revisao_rt'?'selected':'').'>Aguardando revisão pelo RT</option></select></label><button class="btn" type="submit">Salvar minha versão</button></form><div class="ed-actions"><a href="?page=baixar-docx&id='.(int)$id.'">↓ Baixar Word</a><a href="?page=imprimir&id='.(int)$id.'" target="_blank" rel="noopener">▤ Imprimir / PDF</a></div><p>Para que Word e impressão reflitam suas últimas alterações, salve primeiro.</p><hr><h3>Histórico recente</h3><ul class="ed-history">';
    $q=$db->prepare('SELECT version,stage,created_at FROM od_document_history WHERE clinic_id=? AND template_id=? ORDER BY version DESC LIMIT 5');$q->execute([$c['id'],$id]);foreach($q as $r)echo '<li>Versão '.(int)$r['version'].' · '.esc($r['created_at']).'<br>'.esc($r['stage']==='revisao_rt'?'Em revisão':'Em elaboração').'</li>';
    echo '</ul><a href="?page=documentos">← Voltar à biblioteca</a></aside></div><script>function odCmd(c){document.getElementById("odDocument").focus();document.execCommand(c,false,null)}function odBlock(t){document.getElementById("odDocument").focus();document.execCommand("formatBlock",false,t)}document.getElementById("odSave").addEventListener("submit",function(){document.getElementById("odBody").value=document.getElementById("odDocument").innerHTML});</script>';foot();exit;
}