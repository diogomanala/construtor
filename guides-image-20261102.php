<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$src=$root.'/maintenance-adsense-20261102/assets/guides/corte-panorama.webp.b64';
$target=$root.'/assets/guias/corte-panorama.webp';
$file=$root.'/guia.php';
$backup=dirname($root).'/cic-adsense-backup-20261102/guides-image-20261102';
$base=@file_get_contents($src);
$raw=is_string($base)?base64_decode(trim($base),true):false;
$im=is_string($raw)?@getimagesizefromstring($raw):false;
if(!is_string($raw)||strlen($raw)!==22930||!is_array($im)||$im[0]!==800||$im[1]!==600){echo "ASSET_INVALID\n";exit(1);}
$html=@file_get_contents($file);
if(!is_string($html)){echo "READ_FAILED\n";exit(1);}
if(strpos($html,"CIC_GUIDE_IMG_20261102")!==false){echo "ALREADY_DONE\n";exit;}
$old="$imagePath=$images[$post['slug']]??'/dashboard/logo512.png';";
$new="// CIC_GUIDE_IMG_20261102: use original editorial image instead of generic React placeholder.\n$imagePath=$images[$post['slug']]??'/assets/guias/corte-panorama.webp';";
if(substr_count($html,$old)!==1){echo "ANCHOR_FAILED\n";exit(1);}
$updated=str_replace($old,$new,$html);
$updated=str_replace('width="1200" height="630" loading="eager"','width="800" height="600" loading="eager" decoding="async"',$updated);
try{token_get_all($updated,TOKEN_PARSE);}catch(Throwable $e){echo "SYNTAX_FAILED\n";exit(1);}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_FAILED\n";exit(1);}
if(!file_exists($backup.'/guia.php')&&!copy($file,$backup.'/guia.php')){echo "BACKUP_FAILED\n";exit(1);}
if(!is_dir(dirname($target))&&!mkdir(dirname($target),0755,true)){echo "ASSET_DIR_FAILED\n";exit(1);}
if(file_put_contents($target,$raw,LOCK_EX)!==strlen($raw)){echo "ASSET_WRITE_FAILED\n";exit(1);}
if(file_put_contents($file,$updated,LOCK_EX)!==strlen($updated)){echo "FILE_WRITE_FAILED\n";exit(1);}
echo "GUIDE_IMAGE_UPDATED bytes=".strlen($raw)."\n";
