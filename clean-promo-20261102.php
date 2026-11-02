<?php
// Replace promo section with one responsive image link, removing embedded white padding.
// CLI only. Original banners and original HTML/CSS retained for rollback.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$src=$root.'/maintenance-adsense-20261102/assets/banners';
$backup=dirname($root).'/cic-adsense-backup-20261102/autodminsta-banner-clean';
$target=$root.'/assets/autodminsta';
$assets=[
 'autodminsta-desktop-clean.webp'=>['bytes'=>45982,'width'=>1567,'height'=>322],
 'autodminsta-mobile-clean.webp'=>['bytes'=>75316,'width'=>856,'height'=>1077]
];
$bin=[];
foreach($assets as $name=>$info){
 $b64=@file_get_contents($src.'/'.$name.'.b64');
 $raw=is_string($b64)?base64_decode(trim($b64),true):false;
 $dimensions=is_string($raw)?@getimagesizefromstring($raw):false;
 if(!is_string($raw)||strlen($raw)!==$info['bytes']||substr($raw,0,4)!=='RIFF'||
 substr($raw,8,4)!=='WEBP'||!is_array($dimensions)||$dimensions[0]!==$info['width']||$dimensions[1]!==$info['height']){
   echo "ASSET_VALIDATION_FAILED ".$name."\n";exit(1);
 }
 $bin[$name]=$raw;
}
$home=$root.'/index.html';
$stylesheet=$root.'/site-brand.css';
$html=@file_get_contents($home);$css=@file_get_contents($stylesheet);
if(!is_string($html)||!is_string($css)){echo "READ_FAILED\n";exit(1);}
if(strpos($html,'CIC_CLEAN_PROMO_20261102')!==false){echo "ALREADY_APPLIED\n";exit;}
if(substr_count($html,'<section class="cic-promo cic-promo-picture"')!==1||
   substr_count($html,'site-brand.css?v=20261102brand3')!==1||
   strpos($css,'CIC_PROMO_RESPONSIVE_20261102')===false){
 echo "SOURCE_ANCHOR_FAILED\n";exit(1);
}
$new=<<<'HTML'
<!-- CIC_CLEAN_PROMO_20261102: banner-only, no captions or extra framing -->
<section class="cic-promo cic-promo-picture" aria-label="Conheça o AutoDMInsta">
  <a class="cic-promo-link" href="https://autodminsta.com/?utm_source=cortarimagenscarrossel.online&amp;utm_medium=referral&amp;utm_campaign=carrossel_banner_responsivo" target="_blank" rel="noopener sponsored" aria-label="Conhecer o AutoDMInsta e automatizar comentários e mensagens no Instagram">
    <picture>
      <source media="(max-width: 640px)" type="image/webp" srcset="/assets/autodminsta/autodminsta-mobile-clean.webp">
      <img src="/assets/autodminsta/autodminsta-desktop-clean.webp" width="1567" height="322" alt="AutoDMInsta: automatize seu Instagram por valor único. Conheça a ferramenta de respostas a comentários e mensagens." loading="lazy" decoding="async">
    </picture>
  </a>
</section>
HTML;
$pattern='~<section class="cic-promo cic-promo-picture"[^>]*>[\s\S]*?</section>~';
$updated=preg_replace($pattern,$new,$html,1,$matches);
if($matches!==1){echo "SECTION_REPLACE_FAILED\n";exit(1);}
$updated=str_replace('site-brand.css?v=20261102brand3','site-brand.css?v=20261102brand4',$updated);
if(substr_count($updated,'<picture>')!==1||
 substr_count($updated,'id="imageForm"')!==1||
 substr_count($updated,'action="process.php"')!==1||
 strpos($updated,'/icons/step02.png')===false||
 strpos($updated,'adsbygoogle.js')===false ||
 strpos($updated,'cic-promo-terms')!==false ||
 strpos($updated,'DIVULGAÇÃO · AUTODMINSTA')!==false){
 echo "HOME_VALIDATION_FAILED\n";exit(1);
}
$css.="\n/* CIC_CLEAN_PROMO_20261102: nothing around the creative */\n".
"body .cic-promo.cic-promo-picture{display:block;margin:0 auto 25px;padding:0;width:calc(100% - 24px);max-width:900px;border:0;box-shadow:none;background:transparent;}\n".
"body .cic-promo-picture .cic-promo-link,body .cic-promo-picture picture{display:block;width:100%;border-radius:19px;overflow:hidden;}\n".
"body .cic-promo-picture .cic-promo-link img{display:block;width:100%;height:auto;max-width:none;aspect-ratio:1567 / 322;object-fit:contain;border:0;margin:0;}\n".
"@media(max-width:640px){body .cic-promo-picture .cic-promo-link img{aspect-ratio:856 / 1077;object-fit:contain;}}\n";
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_FAILED\n";exit(1);}
if(!is_dir($target)&&!mkdir($target,0755,true)){echo "ASSET_DIR_FAILED\n";exit(1);}
foreach(['index.html'=>$home,'site-brand.css'=>$stylesheet] as $name=>$source){
 if(!is_file($backup.'/'.$name)&&!copy($source,$backup.'/'.$name)){echo "BACKUP_FAILED ".$name."\n";exit(1);}
}
foreach($bin as $name=>$data){
 if(is_file($target.'/'.$name)&&file_get_contents($target.'/'.$name)!==$data){echo "ASSET_CONFLICT ".$name."\n";exit(1);}
 if(!is_file($target.'/'.$name)&&file_put_contents($target.'/'.$name,$data,LOCK_EX)!==strlen($data)){echo "ASSET_WRITE_FAILED ".$name."\n";exit(1);}
 echo "ASSET_READY ".$name." ".strlen($data)."\n";
}
if(file_put_contents($stylesheet,$css,LOCK_EX)!==strlen($css)){echo "CSS_WRITE_FAILED\n";exit(1);}
if(file_put_contents($home,$updated,LOCK_EX)!==strlen($updated)){
 copy($backup.'/site-brand.css',$stylesheet);echo "HTML_WRITE_FAILED CSS_ROLLED_BACK\n";exit(1);
}
echo "CLEAN_PROMO_DONE ONE_IMAGE_ONLY NO_EXTERNAL_TEXT\n";
