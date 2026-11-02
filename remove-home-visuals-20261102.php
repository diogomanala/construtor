<?php
// Remove entirely the visual extras from the public home; do not touch image processing.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$path=$root.'/index.html';
$back=dirname($root).'/cic-adsense-backup-20261102/home-remove-visuals';
$html=@file_get_contents($path);
if(!is_string($html)){echo "READ_ERROR\n";exit(1);}
if(strpos($html,'CIC_HOME_NO_IMAGES_20261102')!==false){echo "ALREADY_DONE\n";exit;}
$patterns=[
  '~\s*<section class="cic-evidence"\b.*?</section>\s*~s',
  '~\s*<div class="step-by-step">.*?(?=\s*<!-- ============================= -->)~s',
  '~\s*<figure id="bmd-banner-visual-20261031"\b.*?</figure>\s*~s'
];
$new=$html;
foreach($patterns as $i=>$pattern){
 $count=0;
 $new=preg_replace($pattern,"\n",$new,1,$count);
 if($count!==1){echo "SECTION_NOT_UNIQUE ".$i." ".$count."\n";exit(1);}
}
if(preg_match('~<img\b~i',$new)){echo "IMAGES_STILL_PRESENT\n";exit(1);}
if(substr_count($new,'id="imageForm"')!==1 ||
   substr_count($new,'action="process.php"')!==1 ||
   strpos($new,'cic-nav')===false ||
   strpos($new,'Perguntas frequentes (FAQ)')===false ||
   strpos($new,'Qual ferramenta usar para cada tipo de corte?')===false){
 echo "VALIDATION_FAILED\n";exit(1);
}
$new=str_replace('<body>', '<body>\n<!-- CIC_HOME_NO_IMAGES_20261102 -->', $new);
if(!is_dir($back)&&!mkdir($back,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
if(!file_exists($back.'/index.html')&&!copy($path,$back.'/index.html')){echo "BACKUP_ERROR\n";exit(1);}
if(file_put_contents($path,$new,LOCK_EX)===false){echo "WRITE_ERROR\n";exit(1);}
echo "HOME_IMAGES_REMOVED; images=0; form=preserved\n";
