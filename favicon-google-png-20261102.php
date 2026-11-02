<?php
// Google Search PNG favicon. SVG remains accessible as an independent vector asset.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$backup=dirname($root).'/cic-adsense-backup-20261102/favicon-google-png';
$files=['index.html','calculadora-carrossel.html','dividir-imagem.html','cortar-banner-instagram.html','exemplos-de-corte.html','guias.php','guia.php','sobre.html','politica-privacidade.html','termos-de-uso.html','contato.html'];
$old='<link rel="icon" type="image/svg+xml" href="/favicon.svg?v=20261102">';
$new='<link rel="icon" type="image/png" sizes="64x64" href="/favicon.png">';
$png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAABAUlEQVR42u3bURLBMBDG8e6OQ+gNuBBn1AvpDbgFT0ynk6ZNUOz331dZ4/tlUw/EmoraHS635ger71or7bEIwV+BsEjBayAsYvASCI8efi6LRw8/l8kVwueyuUr4qYzeiJcr7X4qKxOgtvvjKZCfAFPcfb4FAADgWZtXms+nbfb1/fH6tZ6PT8Dch0qtWauHIwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAO8DWPKD5HjNWj0lxT9EOAIAiAPUXDOJUn3XGhPwkFDcfZ4BQwClKRhmZQKmZBR2PzkBkRFS2Xzpwojhs8+ASAi5LFydLXlD2cvT/wJRc2zvEHhvekDm3MYAAAAASUVORK5CYII=',true);
if(!is_string($png)||strlen($png)!==314||substr($png,0,8)!=="\x89PNG\r\n\x1a\n"){echo "PNG_SOURCE_ERROR\n";exit(1);}
$planned=[];
foreach($files as $file){
 $raw=@file_get_contents($root.'/'.$file);
 if(!is_string($raw)){echo "READ_ERROR ".$file."\n";exit(1);}
 if(substr_count($raw,$new)===1){echo "ALREADY ".$file."\n";continue;}
 if(substr_count($raw,$old)!==1){echo "LINK_ANCHOR_ERROR ".$file."\n";exit(1);}
 $planned[$file]=str_replace($old,$new,$raw);
}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
foreach($planned as $file=>$raw){
 if(!file_exists($backup.'/'.$file)&&!copy($root.'/'.$file,$backup.'/'.$file)){echo "BACKUP_ERROR ".$file."\n";exit(1);}
}
if(is_file($root.'/favicon.png')&&!file_exists($backup.'/favicon.png')&&!copy($root.'/favicon.png',$backup.'/favicon.png')){echo "ICON_BACKUP_ERROR\n";exit(1);}
if(file_put_contents($root.'/favicon.png',$png,LOCK_EX)!==314){echo "ICON_WRITE_ERROR\n";exit(1);}
$done=[];
foreach($planned as $file=>$raw){
 if(file_put_contents($root.'/'.$file,$raw,LOCK_EX)===false){
  foreach($done as $previous){copy($backup.'/'.$previous,$root.'/'.$previous);}
  echo "LINK_WRITE_ERROR ".$file." ROLLED_BACK\n";exit(1);
 }
 $done[]=$file;echo "UPDATED ".$file."\n";
}
echo "PNG_GOOGLE_FAVICON_DONE BYTES=314\n";
