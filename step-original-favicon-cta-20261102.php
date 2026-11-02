<?php
// CIC 2026-11-02: restore original step images, filled brand and site-wide SVG favicon,
// and one clearly separated contextual AutoDMInsta promotion. No processing changes.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$maintenance=$root.'/maintenance-adsense-20261102';
$backup=dirname($root).'/cic-adsense-backup-20261102/steps-favicon-cta-20261102';
$homePath=$root.'/index.html';
$cssPath=$root.'/style.css';
$home=@file_get_contents($homePath);
$css=@file_get_contents($cssPath);
$icon=@file_get_contents($maintenance.'/favicon-mark.svg');
if(!is_string($home)||!is_string($css)||!is_string($icon)){echo "READ_ERROR\n";exit(1);}
if(strpos($home,'CIC_STEPS_AUTODM_20261102')!==false){echo "ALREADY_DONE\n";exit;}
if(strlen($icon)>2048||strpos($icon,'<svg ')!==0||strpos($icon,'#2856e7')===false){echo "FAVICON_SOURCE_ERROR\n";exit(1);}
foreach(['step01.png','step02.png','step03.png'] as $f){
 if(!is_file($root.'/icons/'.$f)){echo "STEP_IMAGE_MISSING ".$f."\n";exit(1);}
}
$steps=<<<'HTML'

<!-- CIC_STEPS_AUTODM_20261102: original URLs maintained for image SEO -->
<section class="step-by-step cic-steps" aria-label="Como cortar uma imagem em três passos">
  <div class="step cic-step">
    <img src="/icons/step01.png" width="80" height="80" loading="lazy" decoding="async" alt="Ícone do primeiro passo: selecionar uma imagem para enviar">
    <div><h3>1. Envie a imagem</h3><p>Selecione sua arte completa.</p></div>
  </div>
  <div class="step cic-step">
    <img src="/icons/step02.png" width="80" height="80" loading="lazy" decoding="async" alt="Ícone do segundo passo: definir a quantidade de linhas e colunas">
    <div><h3>2. Defina os cortes</h3><p>Escolha linhas e colunas.</p></div>
  </div>
  <div class="step cic-step">
    <img src="/icons/step03.png" width="80" height="80" loading="lazy" decoding="async" alt="Ícone do terceiro passo: baixar as imagens divididas">
    <div><h3>3. Baixe os arquivos</h3><p>Salve os recortes em sequência.</p></div>
  </div>
</section>
<section class="cic-promo" aria-labelledby="cic-promo-title">
  <div class="cic-promo-copy">
    <span class="cic-promo-label">DIVULGAÇÃO · AUTODMINSTA</span>
    <h2 id="cic-promo-title">Pare de mandar o link no Direct, um por um.</h2>
    <p>Seu carrossel atrai os comentários. Com o <strong>AutoDMInsta</strong>, você escolhe uma campanha pronta, personaliza e conecta ao post. Quem comentar a palavra-chave definida pode receber sua mensagem e o link automaticamente no Direct.</p>
    <p class="cic-promo-fine">R$ 127 por 1 ano · pagamento único, sem mensalidade e sem renovação automática. Requer Instagram profissional compatível.</p>
  </div>
  <div class="cic-promo-action">
    <a href="https://autodminsta.com/?utm_source=cortarimagenscarrossel.online&amp;utm_medium=referral&amp;utm_campaign=carrossel_guia" target="_blank" rel="noopener sponsored" aria-label="Conhecer as campanhas prontas do AutoDMInsta">Automatizar meus comentários <span aria-hidden="true">↗</span></a>
    <span>Campanhas prontas, sem montar fluxos do zero</span>
  </div>
</section>
HTML;
$anchor="        </form>\n    </div>";
if(substr_count($home,$anchor)!==1){echo "HOME_FORM_ANCHOR_ERROR\n";exit(1);}
$home=str_replace($anchor,$anchor.$steps,$home);
if(substr_count($home,'style.css?v=20261102p')!==1){echo "STYLE_VERSION_ANCHOR_ERROR\n";exit(1);}
$home=str_replace('style.css?v=20261102p','style.css?v=20261102steps',$home);
$cssAddition=<<<'CSS'

/* CIC_STEPS_AUTODM_20261102: filled mark, original PNG steps and compact contextual promo */
body .cic-brand-mark{gap:2px;padding:5px;background:#2856e7;}
body .cic-brand-mark span{flex:1;height:100%;background:#fff;border:0;border-radius:2px;}
.step-by-step.cic-steps{
  width:calc(100% - 24px);max-width:900px;
  display:grid;grid-template-columns:repeat(3,minmax(0,1fr));
  margin:5px auto 23px;padding:16px 14px;gap:12px;
  border:1px solid #e1e8f3;border-radius:16px;background:#fff;
}
.cic-step{min-width:0;display:flex;flex-direction:column;align-items:center;gap:7px;padding:7px 8px;text-align:center;}
.cic-step img{display:block;width:75px;height:75px;max-width:100%;object-fit:contain;border-radius:0;margin:0;}
.cic-step h3{margin:0 0 4px;color:#162a48;font-family:Arial,sans-serif;font-size:14px;line-height:1.3;font-weight:750;}
.cic-step p{font-size:12px;line-height:1.4;color:#617088;margin:0;}
.cic-promo{
  width:calc(100% - 24px);max-width:900px;
  margin:0 auto 25px;padding:24px 27px;
  display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:20px 30px;
  background:#eff5ff;border:1px solid #d8e6fb;border-radius:17px;
  font-family:Arial,sans-serif;color:#263957;
}
.cic-promo-label{display:block;margin-bottom:8px;font-size:10px;font-weight:800;letter-spacing:.09em;color:#305cb8;}
.cic-promo h2{margin:0 0 10px;font-size:clamp(19px,2.7vw,24px);line-height:1.15;letter-spacing:-.02em;color:#11294d;}
.cic-promo p{margin:0;font-size:14px;line-height:1.55;color:#425775;max-width:590px;}
.cic-promo p.cic-promo-fine{margin-top:9px;color:#566981;font-size:12px;}
.cic-promo-action{display:flex;align-items:center;flex-direction:column;gap:9px;max-width:260px;}
.cic-promo-action a{display:inline-flex;align-items:center;justify-content:center;gap:7px;text-align:center;background:#2856e7;color:#fff;text-decoration:none;font-weight:800;font-size:13px;padding:14px 16px;min-height:46px;border-radius:11px;transition:background-color .15s ease;}
.cic-promo-action a:hover{background:#1c43b8;}
.cic-promo-action a:focus-visible{outline:3px solid #7ca5fd;outline-offset:3px;}
.cic-promo-action span{color:#617088;font-size:11px;text-align:center;line-height:1.35;}
@media(max-width:690px){
 .step-by-step.cic-steps{grid-template-columns:1fr;gap:5px;padding:11px 15px;}
 .cic-step{flex-direction:row;text-align:left;padding:5px 1px;gap:15px;align-items:center;}
 .cic-step img{width:56px;height:56px;flex:0 0 56px;}
 .cic-promo{grid-template-columns:1fr;padding:20px;gap:17px;}
 .cic-promo-action{align-items:stretch;max-width:none;}
 .cic-promo-action a{width:100%;}
}
@media(prefers-reduced-motion:reduce){.cic-promo-action a{transition:none;}}
CSS;
if(strpos($css,'CIC_STEPS_AUTODM_20261102')!==false){echo "CSS_ALREADY_PRESENT\n";exit(1);}
$css.=$cssAddition;
$files=['index.html','calculadora-carrossel.html','dividir-imagem.html','cortar-banner-instagram.html','exemplos-de-corte.html','guias.php','guia.php','sobre.html','politica-privacidade.html','termos-de-uso.html','contato.html'];
$mods=[];
foreach($files as $file){
  $contents=($file==='index.html')?$home:@file_get_contents($root.'/'.$file);
  if(!is_string($contents)){echo "FILE_MISSING ".$file."\n";exit(1);}
  // Replace old CRA favicon where present, otherwise inject a single link.
  $contents=preg_replace('~<link\b[^>]*\brel=["\'](?:shortcut )?icon["\'][^>]*>\s*~i','',$contents);
  if(substr_count($contents,'</head>')!==1){echo "HEAD_ANCHOR_ERR ".$file."\n";exit(1);}
  $contents=str_replace('</head>',"<link rel=\"icon\" type=\"image/svg+xml\" href=\"/favicon.svg?v=20261102\">\n</head>",$contents);
  if(substr_count($contents,'href="/favicon.svg?v=20261102"')!==1){echo "ICON_VALIDATION_ERR ".$file."\n";exit(1);}
  if(in_array($file,['guia.php','guias.php'],true)){
    try{token_get_all($contents,TOKEN_PARSE);}catch(Throwable $e){echo "PHP_PARSE_ERROR ".$file."\n";exit(1);}
  }
  $mods[$file]=$contents;
}
$mods['style.css']=$css;
if(substr_count($mods['index.html'],'src="/icons/step01.png"')!==1 ||
   substr_count($mods['index.html'],'src="/icons/step02.png"')!==1 ||
   substr_count($mods['index.html'],'src="/icons/step03.png"')!==1 ||
   substr_count($mods['index.html'],'action="process.php"')!==1 ||
   substr_count($mods['index.html'],'id="imageForm"')!==1 ||
   substr_count($mods['index.html'],'autodminsta.com/')!==1 ||
   strpos($mods['index.html'],'adsbygoogle.js')===false) {
 echo "HOME_VALIDATION_ERR\n";exit(1);
}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIRECTORY_ERROR\n";exit(1);}
foreach($mods as $file=>$contents){
  if(!file_exists($backup.'/'.$file)&&!copy($root.'/'.$file,$backup.'/'.$file)){
    echo "BACKUP_FAIL ".$file."\n";exit(1);
  }
}
if(is_file($root.'/favicon.svg')&&!file_exists($backup.'/favicon.svg')&&!copy($root.'/favicon.svg',$backup.'/favicon.svg')){
  echo "FAVICON_BACKUP_FAIL\n";exit(1);
}
$written=[];
foreach($mods as $file=>$contents){
 if(file_put_contents($root.'/'.$file,$contents,LOCK_EX)===false){
   foreach($written as $previous){copy($backup.'/'.$previous,$root.'/'.$previous);}
   echo "WRITE_FAIL ".$file." ROLLED_BACK\n";exit(1);
 }
 $written[]=$file;
 echo "UPDATED ".$file."\n";
}
if(file_put_contents($root.'/favicon.svg',$icon,LOCK_EX)===false){echo "FAVICON_WRITE_FAIL\n";exit(1);}
echo "DONE; ORIGINAL_PNGS=3; FAVICON_BYTES=".strlen($icon)."; FILES=".count($written)."\n";
