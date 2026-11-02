<?php
// Shared pill navigation and consistent site visual language across every page.
// CLI only. No tool/form handlers are modified. Backups outside public_html.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$backup=dirname($root).'/cic-adsense-backup-20261102/sitewide-brand-nav';
$source=$root.'/maintenance-adsense-20261102/site-brand.css';
$css=@file_get_contents($source);
if(!is_string($css)||strpos($css,'Cortar Carrossel design system')===false){echo "STYLE_SOURCE_MISSING\n";exit(1);}
$files=[
'index.html'=>'cortador',
'calculadora-carrossel.html'=>'calculadora',
'dividir-imagem.html'=>'',
'cortar-banner-instagram.html'=>'',
'exemplos-de-corte.html'=>'exemplos',
'guias.php'=>'guias',
'guia.php'=>'guias',
'sobre.html'=>'sobre',
'politica-privacidade.html'=>'',
'termos-de-uso.html'=>'',
'contato.html'=>''
];
function sharedNav($current) {
 $items=[
  ['cortador','/','Cortador'],
  ['calculadora','/calculadora-carrossel.html','Calculadora'],
  ['exemplos','/exemplos-de-corte.html','Exemplos'],
  ['guias','/guias/','Guias'],
  ['sobre','/sobre.html','Sobre']
 ];
 $links=[];
 foreach($items as $item) {
   $on=$current===$item[0]?' aria-current="page"':'';
   $links[]='<a href="'.$item[1].'"'.$on.'>'.$item[2].'</a>';
 }
 return '<!-- CIC_BRAND_SITEWIDE_20261102 -->'."\n".
 '<nav class="cic-nav" aria-label="Navegação principal">'."\n".
 '  <a class="cic-brand" href="/" aria-label="Cortar Carrossel, página inicial">'."\n".
 '    <span class="cic-brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>'."\n".
 '    <span>Cortar Carrossel</span>'."\n".
 '  </a>'."\n".
 '  <div class="cic-navlinks">'.implode('',$links).'</div>'."\n".
 '</nav>';
}
$plan=[];
foreach($files as $file=>$current){
 $path=$root.'/'.$file;
 $data=@file_get_contents($path);
 if(!is_string($data)){echo "READ_ERROR ".$file."\n";exit(1);}
 if(strpos($data,'CIC_BRAND_SITEWIDE_20261102')!==false){echo "ALREADY ".$file."\n";continue;}
 $nav=sharedNav($current);
 if($file==='index.html') {
   $pattern='~<nav class="cic-nav" aria-label="Navegação principal">[\s\S]*?</nav>~';
   $data=preg_replace($pattern,$nav,$data,1,$count);
 } elseif($file==='exemplos-de-corte.html') {
   $pattern='~<header>\s*<nav>[\s\S]*?</nav>\s*</header>~';
   $data=preg_replace($pattern,$nav,$data,1,$count);
   $data=str_replace('<body>','<body class="cic-example">',$data);
 } elseif(in_array($file,['guias.php','guia.php','calculadora-carrossel.html','dividir-imagem.html','cortar-banner-instagram.html'],true)) {
   $pattern='~<header class="sitebar">[\s\S]*?</header>~';
   $data=preg_replace($pattern,$nav,$data,1,$count);
 } else {
   $count=substr_count($data,'<body>');
   if($count===1) {
     $data=str_replace('<body>','<body class="cic-legal">'."\n".$nav,$data);
   }
 }
 if($count!==1||substr_count($data,'class="cic-nav"')!==1){echo "NAV_ANCHOR_ERROR ".$file." COUNT=".$count."\n";exit(1);}
 if(substr_count($data,'</head>')!==1){echo "HEAD_ANCHOR_ERROR ".$file."\n";exit(1);}
 $data=str_replace('</head>',"<link rel=\"stylesheet\" href=\"/site-brand.css?v=20261102brand\">\n</head>",$data);
 if(substr_count($data,'/site-brand.css?v=20261102brand')!==1){echo "CSS_LINK_ERROR ".$file."\n";exit(1);}
 if(substr_count($data,'/favicon.png')!==1){echo "FAVICON_LINK_ERROR ".$file."\n";exit(1);}
 if(in_array($file,['guias.php','guia.php'],true)) {
   try {token_get_all($data,TOKEN_PARSE);} catch(Throwable $e){echo "PHP_PARSE_ERROR ".$file."\n";exit(1);}
 }
 if($file==='index.html'&&(strpos($data,'id="imageForm"')===false||strpos($data,'action="process.php"')===false||strpos($data,'/icons/step02.png')===false)) {
   echo "FORM_OR_STEPS_CHANGED\n";exit(1);
 }
 $plan[$file]=$data;
}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
foreach($plan as $file=>$data){
  if(!file_exists($backup.'/'.$file)&&!copy($root.'/'.$file,$backup.'/'.$file)){echo "BACKUP_ERROR ".$file."\n";exit(1);}
}
if(is_file($root.'/site-brand.css')&&!file_exists($backup.'/site-brand.css')&&!copy($root.'/site-brand.css',$backup.'/site-brand.css')){echo "CSS_BACKUP_ERROR\n";exit(1);}
if(file_put_contents($root.'/site-brand.css',$css,LOCK_EX)===false){echo "CSS_WRITE_ERROR\n";exit(1);}
$written=[];
foreach($plan as $file=>$data){
 if(file_put_contents($root.'/'.$file,$data,LOCK_EX)===false){
   foreach($written as $f){copy($backup.'/'.$f,$root.'/'.$f);}
   echo "PAGE_WRITE_ERROR ".$file." ROLLED_BACK\n";exit(1);
 }
 $written[]=$file;echo "UPDATED ".$file."\n";
}
echo "SITEWIDE_NAV_COMPLETE PAGES=".count($written)." CSS_BYTES=".strlen($css)."\n";
