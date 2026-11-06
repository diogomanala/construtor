<?php
declare(strict_types=1);
/**
 * Identidade por clínica. As imagens ficam no SQLite, não em diretório público.
 * A marca da plataforma e os modelos técnicos permanecem independentes.
 */
function od_brand_palettes(): array {
    return [
        'jade'=>['name'=>'Verde clínico','accent'=>'#176B58'],
        'navy'=>['name'=>'Azul profundo','accent'=>'#204A70'],
        'graphite'=>['name'=>'Grafite','accent'=>'#363E4A'],
        'plum'=>['name'=>'Ameixa discreta','accent'=>'#684166'],
        'clay'=>['name'=>'Terracota','accent'=>'#8B493A'],
    ];
}
function od_brand_schema(): PDO {
    $db=db();
    $db->exec("CREATE TABLE IF NOT EXISTS od_clinic_branding (
        clinic_id INTEGER PRIMARY KEY,
        palette TEXT NOT NULL DEFAULT 'jade',
        accent TEXT NOT NULL DEFAULT '#176B58',
        logo_mime TEXT DEFAULT NULL,
        logo_blob BLOB DEFAULT NULL,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS od_document_brand_history (
        clinic_id INTEGER NOT NULL, template_id INTEGER NOT NULL, version INTEGER NOT NULL,
        clinic_json TEXT NOT NULL, palette TEXT NOT NULL, accent TEXT NOT NULL,
        logo_mime TEXT DEFAULT NULL, logo_blob BLOB DEFAULT NULL,
        PRIMARY KEY (clinic_id,template_id,version)
    )");
    return $db;
}
function od_brand_contrast(string $hex): float {
    if(!preg_match('/^#[0-9a-fA-F]{6}$/D',$hex))return 0.0;
    $parts=[hexdec(substr($hex,1,2)),hexdec(substr($hex,3,2)),hexdec(substr($hex,5,2))];
    $linear=array_map(static function(int $n):float {
        $v=$n/255;
        return $v<=0.04045?$v/12.92:pow(($v+0.055)/1.055,2.4);
    },$parts);
    $lum=.2126*$linear[0]+.7152*$linear[1]+.0722*$linear[2];
    return 1.05/($lum+0.05);
}
function od_brand_current(array $c): array {
    $defaults=['palette'=>'jade','accent'=>'#176B58','logo_mime'=>null,'logo_blob'=>null];
    $q=od_brand_schema()->prepare('SELECT palette,accent,logo_mime,logo_blob FROM od_clinic_branding WHERE clinic_id=?');
    $q->execute([(int)$c['id']]);
    $stored=$q->fetch()?:[];
    $b=array_merge($defaults,$stored);
    if(!isset(od_brand_palettes()[$b['palette']]) && $b['palette']!=='custom')$b['palette']='jade';
    if(od_brand_contrast((string)$b['accent'])<4.5)$b['accent']=$defaults['accent'];
    if(!in_array($b['logo_mime'],['image/png','image/jpeg','image/webp'],true)){$b['logo_mime']=null;$b['logo_blob']=null;}
    return $b;
}
function od_brand_save(): never {
    $c=clinic();
    if(!$c || !user()){http_response_code(403);exit('Acesso necessário');}
    $name=trim((string)($_POST['clinic_name']??''));
    if($name==='' || mb_strlen($name)>180){flash('Informe um nome de clínica com até 180 caracteres.');redirect('?page=identidade');}
    $palette=(string)($_POST['palette']??'jade');
    $presets=od_brand_palettes();
    if($palette==='custom'){
        $accent=strtoupper(trim((string)($_POST['accent']??'')));
        if(od_brand_contrast($accent)<4.5){
            flash('A cor personalizada precisa ter contraste suficiente com texto branco. Escolha um tom mais escuro.');
            redirect('?page=identidade');
        }
    }elseif(isset($presets[$palette]))$accent=$presets[$palette]['accent'];
    else {flash('Escolha uma paleta válida.');redirect('?page=identidade');}
    $b=od_brand_current($c);
    $logoMime=$b['logo_mime'];$logoBlob=$b['logo_blob'];
    if(isset($_POST['remove_logo'])){$logoMime=null;$logoBlob=null;}
    $upload=$_FILES['clinic_logo']??null;
    if(is_array($upload)){
        $error=(int)($upload['error']??UPLOAD_ERR_NO_FILE);
        if($error!==UPLOAD_ERR_NO_FILE){
            if($error!==UPLOAD_ERR_OK || !is_string($upload['tmp_name']??null) || !is_uploaded_file($upload['tmp_name'])){
                flash('Não foi possível receber a imagem. Tente PNG, JPG ou WebP com até 350 KB.');
                redirect('?page=identidade');
            }
            $size=(int)($upload['size']??0);
            if($size<1 || $size>350*1024){
                flash('O logo deve ter até 350 KB.');redirect('?page=identidade');
            }
            $bytes=file_get_contents($upload['tmp_name']);
            $info=is_string($bytes)?@getimagesizefromstring($bytes):false;
            $mime=is_string($bytes)?(new finfo(FILEINFO_MIME_TYPE))->buffer($bytes):'';
            if(!is_array($info) || !in_array($mime,['image/png','image/jpeg','image/webp'],true) ||
               ($info['mime']??'')!==$mime || (int)$info[0]<1 || (int)$info[1]<1 ||
               (int)$info[0]>3000 || (int)$info[1]>3000){
                flash('Imagem inválida. Envie PNG, JPG ou WebP de até 3000 × 3000 px.');
                redirect('?page=identidade');
            }
            $logoMime=$mime;$logoBlob=$bytes;
        }
    }
    $db=od_brand_schema();
    odx_schema(); // Garante as tabelas de versões para instalações ainda sem documentos.
    try {
        $db->beginTransaction();
        // Congela a identidade anterior dos documentos legados antes de uma alteração de marca.
        $past=$db->prepare('SELECT h.template_id,h.version FROM od_document_history h WHERE h.clinic_id=? AND NOT EXISTS (SELECT 1 FROM od_document_brand_history b WHERE b.clinic_id=h.clinic_id AND b.template_id=h.template_id AND b.version=h.version)');
        $past->execute([(int)$c['id']]);
        foreach($past->fetchAll() as $v)od_brand_snapshot($db,$c,(int)$v['template_id'],(int)$v['version']);
        $q=$db->prepare('UPDATE clinics SET name=? WHERE id=? AND user_id=?');
        $q->execute([$name,(int)$c['id'],(int)user()]);
        $q=$db->prepare("INSERT INTO od_clinic_branding(clinic_id,palette,accent,logo_mime,logo_blob,updated_at)
            VALUES(?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id) DO UPDATE SET
            palette=excluded.palette,accent=excluded.accent,logo_mime=excluded.logo_mime,
            logo_blob=excluded.logo_blob,updated_at=CURRENT_TIMESTAMP");
        $q->bindValue(1,(int)$c['id'],PDO::PARAM_INT);
        $q->bindValue(2,$palette);
        $q->bindValue(3,$accent);
        $q->bindValue(4,$logoMime,$logoMime===null?PDO::PARAM_NULL:PDO::PARAM_STR);
        $q->bindValue(5,$logoBlob,$logoBlob===null?PDO::PARAM_NULL:PDO::PARAM_LOB);
        $q->execute();
        $db->commit();
        flash('Identidade visual salva. Ela aparece na prévia e nas próximas versões dos documentos.');
    } catch(Throwable $e) {
        if($db->inTransaction())$db->rollBack();
        error_log('[Odonto] Falha ao salvar identidade visual');
        flash('Não foi possível salvar a identidade. Tente novamente.');
    }
    redirect('?page=identidade');
}
function od_brand_snapshot(PDO $db,array $c,int $templateId,int $version): void {
    $brand=od_brand_current($c);
    $info=[];
    foreach(['name','cnpj','city','state','rt_name','rt_cro'] as $key)$info[$key]=(string)($c[$key]??'');
    $stmt=$db->prepare('INSERT INTO od_document_brand_history(clinic_id,template_id,version,clinic_json,palette,accent,logo_mime,logo_blob) VALUES(?,?,?,?,?,?,?,?)');
    $stmt->bindValue(1,(int)$c['id'],PDO::PARAM_INT);
    $stmt->bindValue(2,$templateId,PDO::PARAM_INT);
    $stmt->bindValue(3,$version,PDO::PARAM_INT);
    $stmt->bindValue(4,json_encode($info,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
    $stmt->bindValue(5,$brand['palette']);
    $stmt->bindValue(6,$brand['accent']);
    $stmt->bindValue(7,$brand['logo_mime'],$brand['logo_mime']===null?PDO::PARAM_NULL:PDO::PARAM_STR);
    $stmt->bindValue(8,$brand['logo_blob'],$brand['logo_blob']===null?PDO::PARAM_NULL:PDO::PARAM_LOB);
    $stmt->execute();
}
function od_brand_historical(int $clinicId,int $templateId,int $version): ?array {
    $stmt=od_brand_schema()->prepare('SELECT clinic_json,palette,accent,logo_mime,logo_blob FROM od_document_brand_history WHERE clinic_id=? AND template_id=? AND version=?');
    $stmt->execute([$clinicId,$templateId,$version]);
    $row=$stmt->fetch();
    if(!$row)return null;
    $info=json_decode((string)$row['clinic_json'],true);
    if(!is_array($info))return null;
    $row['clinic_info']=$info;
    if(od_brand_contrast((string)$row['accent'])<4.5)$row['accent']='#176B58';
    return $row;
}
function od_brand_logo(): never {
    $c=clinic();
    if(!$c||!user()){http_response_code(403);exit('Sem acesso');}
    $tid=filter_var($_GET['template_id']??null,FILTER_VALIDATE_INT);
    $ver=filter_var($_GET['version']??null,FILTER_VALIDATE_INT);
    if(isset($_GET['template_id']) || isset($_GET['version'])){
        if(!$tid||!$ver){http_response_code(404);exit;}
        $brand=od_brand_historical((int)$c['id'],(int)$tid,(int)$ver);
    }else $brand=od_brand_current($c);
    if(!$brand || !in_array(($brand['logo_mime']??null),['image/png','image/jpeg','image/webp'],true) || !is_string($brand['logo_blob']) || $brand['logo_blob']===''){
        http_response_code(404);exit;
    }
    header('Content-Type: '.$brand['logo_mime']);
    header('Content-Length: '.strlen($brand['logo_blob']));
    header('Content-Disposition: inline; filename="logo-clinica"');
    header('Cache-Control: private, no-store');
    echo $brand['logo_blob'];exit;
}
function od_brand_page(): never {
    $c=clinic();
    if(!$c||!user()){http_response_code(403);exit('Sem acesso');}
    $brand=od_brand_current($c);
    h('Identidade da clínica');
    echo '<div class="od-brand-top"><span>PERSONALIZAÇÃO DA DOCUMENTAÇÃO</span><h2>A identidade da sua clínica, em cada documento.</h2><p>Escolha as cores, o nome e o logo utilizados nas minutas. A navegação Odonto em Dia continua igual para todos.</p></div>';
    echo '<div class="od-brand-grid"><section class="od-brand-card"><form method="post" enctype="multipart/form-data" id="od-brand-form">'.token().'<input type="hidden" name="act" value="brand_save">';
    echo '<h3>Informações da clínica</h3><label class="od-brand-label">Nome exibido nos documentos<input type="text" name="clinic_name" maxlength="180" required value="'.esc($c['name']).'"></label>';
    echo '<h3>Paleta de cores</h3><p class="od-brand-help">Cores selecionadas para manter boa leitura em fundo claro e escuro.</p><div class="od-brand-palettes">';
    foreach(od_brand_palettes() as $key=>$p){
        echo '<label class="od-brand-choice"><input type="radio" name="palette" value="'.esc($key).'" '.($brand['palette']===$key?'checked':'').'><span class="od-brand-color" style="background:'.esc($p['accent']).'"></span><span>'.esc($p['name']).'</span></label>';
    }
    echo '<label class="od-brand-choice"><input type="radio" name="palette" value="custom" '.($brand['palette']==='custom'?'checked':'').'><span class="od-brand-color od-brand-color-custom"></span><span>Minha cor</span></label></div>';
    echo '<label class="od-brand-label">Cor personalizada<input type="color" name="accent" id="od-brand-accent" value="'.esc($brand['accent']).'"><small>Para usar esta cor, selecione “Minha cor”. Tons claros não são aceitos quando prejudicam a leitura.</small></label>';
    echo '<h3>Logotipo da clínica</h3>';
    echo '<label class="od-brand-label">Enviar logo<input type="file" name="clinic_logo" id="od-brand-file" accept="image/png,image/jpeg,image/webp"><small>PNG, JPG ou WebP, até 350 KB. Prefira imagem horizontal com fundo transparente.</small></label>';
    if($brand['logo_mime'])echo '<label class="od-brand-remove"><input type="checkbox" name="remove_logo" value="1"> Remover o logo atual</label>';
    echo '<button type="submit" class="od-brand-submit">Salvar identidade visual</button></form></section>';
    echo '<aside class="od-brand-preview"><div class="od-brand-preview-cap">PRÉVIA DA IDENTIDADE</div><div class="od-brand-preview-paper" id="od-brand-preview" style="--od-brand-color:'.esc($brand['accent']).'"><div class="od-brand-preview-head">';
    if($brand['logo_mime'])echo '<img id="od-brand-preview-logo" src="?page=logo-clinica" alt="Logo atual da clínica">';
    else echo '<div class="od-brand-preview-monogram" id="od-brand-preview-monogram">C</div>';
    echo '<div><strong id="od-brand-preview-name">'.esc($c['name']).'</strong><small>DOCUMENTO DA UNIDADE</small></div></div>';
    echo '<div class="od-brand-preview-cover"><span>DOCUMENTAÇÃO INTERNA</span><h3>Procedimentos e rotinas da clínica.</h3><p>Modelo de referência, sujeito à revisão do responsável técnico.</p><div class="od-brand-preview-rule"></div></div>';
    echo '<div class="od-brand-preview-body"><span>01 / IDENTIFICAÇÃO</span><div class="od-brand-preview-line"></div><div class="od-brand-preview-line short"></div><p>As informações e cores aparecem no editor. Ao salvar um documento, a identidade utilizada é registrada junto da versão.</p></div></div>';
    echo '<p class="od-brand-note">As versões já registradas permanecem no histórico. Novos ajustes de marca serão aplicados às versões futuras.</p><a class="od-brand-back" href="?page=documentos">Abrir biblioteca de documentos →</a></aside></div>';
    foot();exit;
}
