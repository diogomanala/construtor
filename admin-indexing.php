<?php
// CIC housekeeping: admin noindex and safe legacy diagnostics. CLI only.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$backup=dirname($root).'/cic-adsense-backup-20261102/admin-indexing';
$changes=[
'dashboard/index.html'=>[
 ['<html lang="en">','<html lang="pt-BR">'],
 ['<meta name="description" content="Web site created using create-react-app"/>','<meta name="description" content="Painel administrativo de acesso restrito"/><meta name="robots" content="noindex,nofollow"/>'],
 ['<title>React App</title>','<title>Painel administrativo | Cortar Imagens Carrossel</title>']
],
'post.php'=>[
 ["error_reporting(E_ALL);\r\nini_set('display_errors', 1);","// CIC_ADMIN_NOINDEX_20261102: não exibir erros PHP publicamente.\r\nini_set('display_errors', '0');\r\nini_set('log_errors', '1');"]
]
];
$planned=[];
foreach($changes as $file=>$pairs){
 $source=@file_get_contents($root.'/'.$file);
 if(!is_string($source)){echo "READ_ERROR ".$file."\n";exit(1);}
 $idempotent=($file==='dashboard/index.html')?strpos($source,'name="robots" content="noindex,nofollow"')!==false:strpos($source,'CIC_ADMIN_NOINDEX_20261102')!==false;
 if($idempotent){echo "ALREADY ".$file."\n";continue;}
 foreach($pairs as $pair){
   if(substr_count($source,$pair[0])!==1){echo "ANCHOR_ERROR ".$file."\n";exit(1);}
   $source=str_replace($pair[0],$pair[1],$source);
 }
 $planned[$file]=$source;
}
if(!$planned){echo "NO_CHANGES\n";exit;}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIRECTORY_ERROR\n";exit(1);}
foreach($planned as $file=>$source){
 $out=$backup.'/'.$file;$dir=dirname($out);
 if(!is_dir($dir)&&!mkdir($dir,0750,true)){echo "BACKUP_SUBDIRECTORY_ERROR\n";exit(1);}
 if(!file_exists($out)&&!copy($root.'/'.$file,$out)){echo "BACKUP_ERROR ".$file."\n";exit(1);}
}
$written=[];
foreach($planned as $file=>$source){
 if(file_put_contents($root.'/'.$file,$source,LOCK_EX)===false){
  foreach($written as $f)copy($backup.'/'.$f,$root.'/'.$f);
  echo "WRITE_ERROR ".$file." ROLLED_BACK\n";exit(1);
 }
 $written[]=$file;echo "UPDATED ".$file."\n";
}
echo "ADMIN_INDEXING_DONE\n";
