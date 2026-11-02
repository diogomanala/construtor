<?php
// Install three text-free realistic WebP visuals in complementary tool pages.
// Keep all illustrations below the working tools and load lazily.
if (PHP_SAPI!=='cli') {http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$src=$root.'/maintenance-adsense-20261102/assets';
$dest=$root.'/assets/cic-editorial';
$backup=dirname($root).'/cic-adsense-backup-20261102/editorial-photos';
$images=[
 'visual-envio.webp'=>[185,100],
 'visual-corte.webp'=>[190,94],
 'visual-resultado.webp'=>[190,98]
];
$binary=[];
foreach($images as $name=>$size){
 $encoded=@file_get_contents($src.'/'.$name.'.b64');
 $decoded=is_string($encoded)?base64_decode(trim($encoded),true):false;
 if(!is_string($decoded)||strlen($decoded)<600||strlen($decoded)>12000||substr($decoded,0,4)!=='RIFF'||substr($decoded,8,4)!=='WEBP'){
   echo "IMAGE_INVALID ".$name."\n";exit(1);
 }
 $binary[$name]=$decoded;
}
$pages=[
 'calculadora-carrossel.html'=>['visual-corte.webp',190,94,'Imagem ilustrativa de uma arte sendo dividida em colunas','Visualização ilustrativa das divisões em colunas.'],
 'dividir-imagem.html'=>['visual-envio.webp',185,100,'Fotografia ilustrativa de uma imagem enviada em um computador','Etapa ilustrativa do envio de uma imagem.'],
 'cortar-banner-instagram.html'=>['visual-resultado.webp',190,98,'Recortes ilustrativos de uma paisagem organizados para carrossel','Exemplo ilustrativo de partes de uma imagem panorâmica.']
];
$patches=[];
foreach($pages as $file=>$info){
 $contents=@file_get_contents($root.'/'.$file);
 if(!is_string($contents)){echo "PAGE_READ_ERR ".$file."\n";exit(1);}
 if(strpos($contents,'CIC_EDITORIAL_PHOTOS_20261102')!==false){echo "ALREADY ".$file."\n";continue;}
 if(substr_count($contents,'</main>')!==1){echo "MAIN_ANCHOR_ERR ".$file."\n";exit(1);}
 $figure="\n<!-- CIC_EDITORIAL_PHOTOS_20261102 -->\n".
  '<figure class="cic-editorial-visual">'."\n".
  '<img src="/assets/cic-editorial/'.$info[0].'" alt="'.htmlspecialchars($info[3],ENT_QUOTES,'UTF-8').'" width="'.$info[1].'" height="'.$info[2].'" loading="lazy" decoding="async">'."\n".
  '<figcaption>'.$info[4].'</figcaption>'."\n".
  "</figure>\n";
 $contents=str_replace('</main>',$figure.'</main>',$contents);
 $patches[$file]=$contents;
}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERR\n";exit(1);}
if(!is_dir($dest)&&!mkdir($dest,0755,true)){echo "IMAGE_DIR_ERR\n";exit(1);}
foreach($patches as $file=>$contents){
 if(!file_exists($backup.'/'.$file)&&!copy($root.'/'.$file,$backup.'/'.$file)){echo "BACKUP_ERR ".$file."\n";exit(1);}
}
foreach($binary as $name=>$data){
 if(file_exists($dest.'/'.$name)){
  if(file_get_contents($dest.'/'.$name)!==$data){echo "IMAGE_CONFLICT ".$name."\n";exit(1);}
  continue;
 }
 if(file_put_contents($dest.'/'.$name,$data,LOCK_EX)!==strlen($data)){echo "IMAGE_WRITE_ERR ".$name."\n";exit(1);}
 echo "ASSET ".$name." BYTES=".strlen($data)."\n";
}
$done=[];
foreach($patches as $file=>$contents){
 if(file_put_contents($root.'/'.$file,$contents,LOCK_EX)===false){
  foreach($done as $old)copy($backup.'/'.$old,$root.'/'.$old);
  echo "PAGE_WRITE_ERR ".$file." ROLLED_BACK\n";exit(1);
 }
 $done[]=$file;echo "UPDATED ".$file."\n";
}
echo "EDITORIAL_PHOTOS_COMPLETE; PAGES=".count($done)."; ASSETS=3\n";
