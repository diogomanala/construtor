<?php
// AdSense review: install site-wide tag only on substantial content pages.
// Run once via PHP CLI; always backup before making modifications.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$backup=dirname($root).'/cic-adsense-backup-20261102/adsense-tags';
$files=['calculadora-carrossel.html','dividir-imagem.html','cortar-banner-instagram.html','guias.php','guia.php','exemplos-de-corte.html'];
$tag="<!-- CIC_ADSENSE_SITEWIDE_20261102 -->\n<script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3155268230972642\" crossorigin=\"anonymous\"></script>\n";
$updated=[];
foreach($files as $f) {
 $path=$root.'/'.$f; $src=@file_get_contents($path);
 if($src===false){echo "READ_ERROR $f\n";exit(1);}
 if(substr_count($src,'</head>')!==1){echo "HEAD_ERROR $f\n";exit(1);}
 if(strpos($src,'adsbygoogle.js')!==false){echo "ALREADY $f\n";continue;}
 $updated[$f]=str_replace('</head>',$tag.'</head>',$src);
}
if(!$updated){echo "NO_CHANGES_REQUIRED\n";exit;}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
foreach($updated as $f=>$src) {
 if(!file_exists($backup.'/'.$f)&&!copy($root.'/'.$f,$backup.'/'.$f)){echo "BACKUP_ERROR $f\n";exit(1);}
}
$written=[];
foreach($updated as $f=>$src) {
 if(@file_put_contents($root.'/'.$f,$src,LOCK_EX)===false){
  foreach($written as $prior){copy($backup.'/'.$prior,$root.'/'.$prior);}
  echo "WRITE_ERROR $f, ROLLED_BACK\n";exit(1);
 }
 $written[]=$f;echo "UPDATED $f\n";
}
echo "DONE_COUNT ".count($written)."\n";
