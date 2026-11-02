<?php
if(PHP_SAPI!=="cli"){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online';
$paths=[$root.'/logs',$root.'/public_html/logs',$root.'/public_html/.logs','/home/u783725167/logs','/home/u783725167/domains/cortarimagenscarrossel.online/public_html/error_log'];
$terms=['Google-Display-Ads-Bot','Mediapartners-Google','Googlebot','AdsBot-Google','bingbot'];
foreach($paths as $path){
  if(!is_dir($path)&&!is_file($path)){echo 'MISSING '.basename($path)."\n";continue;}
  if(!is_readable($path)){echo 'NOT_READABLE '.basename($path)."\n";continue;}
  $files=is_dir($path)?@glob($path.'/*')?:[]:[$path];
  echo 'PATH '.basename($path).' FILES '.count($files)."\n";
  foreach(array_slice($files,0,15) as $f){
    if(!is_file($f)||!is_readable($f))continue;
    $sz=@filesize($f);
    if($sz>30000000 || preg_match('~\.(gz|zip|db|sql)$~i',$f))continue;
    $fh=@fopen($f,'rb');if(!$fh)continue;
    if($sz>1500000)fseek($fh,$sz-1500000);
    $data=stream_get_contents($fh);fclose($fh);if(!is_string($data))continue;
    $c=[];foreach($terms as $term)$c[$term]=substr_count(strtolower($data),strtolower($term));
    echo 'LOG '.basename($f).' BYTES '.$sz.' COUNTS '.json_encode($c)."\n";
  }
}
