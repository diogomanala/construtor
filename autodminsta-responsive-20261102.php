<?php
// Responsive AutoDMInsta banner: exact approved images, optimized desktop and mobile WebP.
// Also refreshes the shared style cache version for the splitter drop-zone fix.
// CLI only, no processing logic modified.
if(PHP_SAPI!=='cli'){ http_response_code(404); exit; }
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$source=$root.'/maintenance-adsense-20261102';
$dest=$root.'/assets/autodminsta';
$backup=dirname($root).'/cic-adsense-backup-20261102/autodminsta-responsive';
$defs=[
 'autodminsta-desktop.webp'=>['file'=>$source.'/assets/banners/autodminsta-desktop.webp.b64','width'=>1600,'height'=>533,'bytes'=>48196],
 'autodminsta-mobile.webp'=>['file'=>$source.'/assets/banners/autodminsta-mobile.webp.b64','width'=>900,'height'=>1125,'bytes'=>76456]
];
$images=[];
foreach($defs as $name=>$def) {
 $encoded=@file_get_contents($def['file']);
 if(!is_string($encoded)){echo "MISSING_SOURCE $name\n";exit(1);}
 $raw=base64_decode(preg_replace('/\s+/','',$encoded),true);
 $size=is_string($raw)?@getimagesizefromstring($raw):false;
 if(!is_string($raw) || strlen($raw)!==$def['bytes'] ||
    substr($raw,0,4)!=='RIFF'||substr($raw,8,4)!=='WEBP' ||
    $size===false||$size[0]!==$def['width']||$size[1]!==$def['height']){
   echo "ASSET_CORRUPT $name\n";exit(1);
 }
 $images[$name]=$raw;
}
$newCss=@file_get_contents($source.'/site-brand.css');
if(!is_string($newCss) || !str_contains($newCss,'CIC_PROMO_RESPONSIVE_20261102') ||
   !str_contains($newCss,'CIC_SPLITTER_DROP_FIX_20261102')){
 echo "CSS_SOURCE_INVALID\n";exit(1);
}
$home=$root.'/index.html';
$current=@file_get_contents($home);
if(!is_string($current)){echo "HOME_READ_ERROR\n";exit(1);}
$oldPattern='~<section class="cic-promo" aria-labelledby="cic-promo-title">.*?</section>~s';
$count=preg_match_all($oldPattern,$current);
if(str_contains($current,'CIC_AUTODMINSTA_PICTURE_20261102')){
 echo "ALREADY_APPLIED\n";exit;
}
if($count!==1){echo "PROMO_ANCHOR_ERROR COUNT=$count\n";exit(1);}
$newBanner=<<<'HTML'
<!-- CIC_AUTODMINSTA_PICTURE_20261102: desktop/mobile Art Direction, just one image is fetched -->
<section class="cic-promo cic-promo-picture" aria-labelledby="cic-promo-title">
  <span class="cic-promo-label">DIVULGAÇÃO · AUTODMINSTA</span>
  <h2 id="cic-promo-title" class="cic-sr-only">Automatize seu Instagram por valor único</h2>
  <p class="cic-sr-only">Responda comentários e envie mensagens automaticamente pelo Direct com campanhas prontas.</p>
  <a class="cic-promo-link" href="https://autodminsta.com/?utm_source=cortarimagenscarrossel.online&amp;utm_medium=referral&amp;utm_campaign=carrossel_banner_responsivo" rel="noopener sponsored" target="_blank" aria-label="Conhecer o AutoDMInsta, campanhas prontas de automação para Instagram, plano anual">
    <picture>
      <source media="(max-width: 640px)" type="image/webp" srcset="/assets/autodminsta/autodminsta-mobile.webp">
      <img src="/assets/autodminsta/autodminsta-desktop.webp" width="1600" height="533" alt="AutoDMInsta: automatize comentários e mensagens no Instagram. Conheça as campanhas prontas." loading="lazy" decoding="async">
    </picture>
  </a>
  <p class="cic-promo-terms">Acesso por R$ 127 durante 1 ano, em pagamento único, sem mensalidade nem renovação automática. Requer conta profissional do Instagram compatível.</p>
</section>
HTML;
$updated=preg_replace($oldPattern,$newBanner,$current,1,$count);
if($count!==1){echo "PROMO_REPLACEMENT_FAILED\n";exit(1);}
$files=[
 'index.html'=>$updated
];
$pageList=[
'calculadora-carrossel.html',
'dividir-imagem.html',
'cortar-banner-instagram.html',
'exemplos-de-corte.html',
'guias.php',
'guia.php',
'sobre.html',
'politica-privacidade.html',
'termos-de-uso.html',
'contato.html'
];
foreach($pageList as $file){
 $content=@file_get_contents($root.'/'.$file);
 if(!is_string($content)){echo "PAGE_MISSING $file\n";exit(1);}
 $files[$file]=$content;
}
foreach($files as $file=>&$contents) {
 $search='site-brand.css?v=20261102brand';
 if(substr_count($contents,$search)!==1){echo "CSS_ANCHOR_ERR $file\n";exit(1);}
 $contents=str_replace($search,'site-brand.css?v=20261102brand3',$contents);
 if($file==='index.html'){
  if(substr_count($contents,'<picture>')!==1||
     substr_count($contents,'id="imageForm"')!==1||
     substr_count($contents,'action="process.php"')!==1||
     substr_count($contents,'/icons/step02.png')!==1||
     substr_count($contents,'adsbygoogle.js')!==1) {
    echo "HOME_VALIDATION_FAILED\n";exit(1);
  }
 }
 if(in_array($file,['guia.php','guias.php'],true)) {
   try{token_get_all($contents,TOKEN_PARSE);}catch(Throwable $e){echo "PHP_PARSE_FAILED $file\n";exit(1);}
 }
}
unset($contents);
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
if(!is_dir($dest)&&!mkdir($dest,0755,true)){echo "ASSET_DIR_ERROR\n";exit(1);}
foreach($files as $file=>$contents){
 if(!file_exists($backup.'/'.$file)&&!copy($root.'/'.$file,$backup.'/'.$file)){
  echo "BACKUP_FAILED $file\n";exit(1);
 }
}
if(!file_exists($backup.'/site-brand.css')&&!copy($root.'/site-brand.css',$backup.'/site-brand.css')){echo "CSS_BACKUP_FAILED\n";exit(1);}
foreach($images as $name=>$content){
 $path=$dest.'/'.$name;
 if(file_exists($path)&&file_get_contents($path)!==$content){echo "ASSET_CONFLICT $name\n";exit(1);}
 if(!file_exists($path)&&file_put_contents($path,$content,LOCK_EX)!==strlen($content)){echo "ASSET_WRITE_FAILED $name\n";exit(1);}
 echo "ASSET_OK $name ".strlen($content)."\n";
}
if(file_put_contents($root.'/site-brand.css',$newCss,LOCK_EX)!==strlen($newCss)){echo "CSS_WRITE_FAILED\n";exit(1);}
$written=[];
foreach($files as $file=>$contents) {
 if(file_put_contents($root.'/'.$file,$contents,LOCK_EX)!==strlen($contents)) {
  foreach($written as $prev)copy($backup.'/'.$prev,$root.'/'.$prev);
  copy($backup.'/site-brand.css',$root.'/site-brand.css');
  echo "PAGE_WRITE_FAILED $file ROLLED_BACK\n";exit(1);
 }
 $written[]=$file;echo "UPDATED $file\n";
}
echo "BANNERS_RESPONSIVE_DONE FILES=".count($written)."\n";
