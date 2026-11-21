<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
$root = dirname(__DIR__);
$file = $root . '/index.html';
$lock = @fopen(sys_get_temp_dir() . '/cic-home-publish-20261121.lock','c');
if (!$lock || !flock($lock, LOCK_EX)) {http_response_code(503); exit('LOCK_ERROR');}
$original = @file_get_contents($file);
if ($original === false || strlen($original)<6000 || strlen($original)>100000) {http_response_code(500);exit('UNEXPECTED_HOME_FILE');}
if (strpos($original,'CIC_PARTNERSHIPS_BUTTON_20261121')!==false) {echo 'ALREADY_APPLIED';exit;}
$changes=[
  ['    <link rel="canonical" href="https://cortarimagenscarrossel.online/">',
   '    <link rel="canonical" href="https://cortarimagenscarrossel.online/">'."\n".'    <link rel="alternate" hreflang="pt-BR" href="https://cortarimagenscarrossel.online/">'."\n".'    <link rel="alternate" hreflang="en" href="https://cortarimagenscarrossel.online/en/">'."\n".'    <link rel="alternate" hreflang="x-default" href="https://cortarimagenscarrossel.online/">'],
  ['href="/sobre.html">Sobre</a></div>',
   'href="/sobre.html">Sobre</a><a href="/en/" lang="en" hreflang="en" aria-label="English version">EN</a></div>'],
  ['        <p>© 2026 Cortar Imagens Carrossel Online. Todos os direitos reservados.</p>',
   '        <!-- CIC_PARTNERSHIPS_BUTTON_20261121 -->'."\n".'        <p class="cic-partner-cta-wrap"><a class="cic-partner-cta" href="/parcerias/">Anuncie ou seja parceiro</a></p>'."\n".'        <p>© 2026 Cortar Imagens Carrossel Online. Todos os direitos reservados.</p>'],
  ['</head>',
   '    <style id="cic-partner-cta-css">.cic-partner-cta-wrap{text-align:center;margin:18px 0}.cic-partner-cta{display:inline-flex;align-items:center;justify-content:center;padding:10px 20px;border:1px solid #c7d6ef;border-radius:999px;text-decoration:none;font-weight:650;font-size:14px;line-height:1.35;color:#1d4f98;background:#f7faff;transition:background .18s ease}.cic-partner-cta:hover{background:#e9f2ff}.cic-partner-cta:focus-visible{outline:3px solid #4a87df;outline-offset:3px}</style>'."\n".'</head>']
];
$updated=$original;
foreach ($changes as $pair) {
  if (substr_count($updated,$pair[0])!==1) {http_response_code(409); exit('ANCHOR_MISMATCH');}
  $updated=str_replace($pair[0],$pair[1],$updated);
}
$backupDir=dirname($root).'/.cic-backups';
if(!is_dir($backupDir) && !@mkdir($backupDir,0700,true)) {http_response_code(500);exit('BACKUP_DIR_ERROR');}
$backup=$backupDir.'/index-before-partnership-'.gmdate('Ymd-His').'.html';
if(@file_put_contents($backup,$original,LOCK_EX)!==strlen($original)) {http_response_code(500);exit('BACKUP_ERROR');}
@chmod($backup,0600);
$tmp=@tempnam($root,'.cic-update-');
if (!$tmp || @file_put_contents($tmp,$updated)!==strlen($updated)) {http_response_code(500);exit('WRITE_ERROR');}
@chmod($tmp, @fileperms($file)&0777);
if(!@rename($tmp,$file)) {http_response_code(500);exit('RENAME_ERROR');}
flock($lock,LOCK_UN);fclose($lock);
echo 'HOME_UPDATED';
