<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$backup=dirname($root).'/cic-adsense-backup-20261102/legacy-urls';
$patches=[
  'post.php'=>[
    "close_db_connection(\$mysqli);",
    "close_db_connection(\$mysqli);\r\n\r\n// CIC_CANONICAL_LEGACY_20261102: antigos links levam ao guia atual.\r\nif (\$post && preg_match('/^[a-z0-9-]+$/', \$slug)) {\r\n    \$publicSlug = (\$slug === 'melhor-ferramenta-para-cortar-imagens-pra-carrolssel-no-instagram')\r\n        ? 'melhor-ferramenta-para-cortar-imagens-pra-carrossel-no-instagram' : \$slug;\r\n    header('Location: https://cortarimagenscarrossel.online/guias/'.rawurlencode(\$publicSlug).'/', true, 301);\r\n    exit;\r\n}"
  ],
  'guia.php'=>[
    "\$canonical='https://cortarimagenscarrossel.online/guias/'.rawurlencode(\$publicSlug).'/';",
    "\$canonical='https://cortarimagenscarrossel.online/guias/'.rawurlencode(\$publicSlug).'/';\n// CIC_CANONICAL_LEGACY_20261102: apenas acesso direto a guia.php, nao reescrita interna.\nif (preg_match('~^/guia\\.php(?:\\?|$)~i', \$_SERVER['REQUEST_URI'] ?? '')) {\n    header('Location: '.\$canonical, true, 301);\n    exit;\n}"
  ]
];
$plan=[];
foreach($patches as $f=>$p) {
 $target=$root.'/'.$f;
 $current=@file_get_contents($target);
 if(!is_string($current)){echo "READ_ERROR ".$f."\n";exit(1);}
 if(strpos($current,'CIC_CANONICAL_LEGACY_20261102')!==false){echo "ALREADY ".$f."\n";continue;}
 if(substr_count($current,$p[0])!==1){echo "ANCHOR_ERROR ".$f." COUNT ".substr_count($current,$p[0])."\n";exit(1);}
 $plan[$f]=str_replace($p[0],$p[1],$current);
}
if(!$plan){echo "NO_CHANGES\n";exit;}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIRECTORY_ERROR\n";exit(1);}
foreach($plan as $f=>$text){if(!file_exists($backup.'/'.$f)&&!copy($root.'/'.$f,$backup.'/'.$f)){echo "BACKUP_ERROR ".$f."\n";exit(1);}}
$done=[];
foreach($plan as $f=>$text){
 if(file_put_contents($root.'/'.$f,$text,LOCK_EX)===false){
  foreach($done as $x){copy($backup.'/'.$x,$root.'/'.$x);}
  echo "WRITE_ERROR ".$f." ROLLED_BACK\n";exit(1);
 }
 $done[]=$f;echo "UPDATED ".$f."\n";
}
echo "LEGACY_REDIRECTS_DONE\n";
