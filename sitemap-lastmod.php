<?php
// CIC sitemap: accurate dates for significantly changed pages. CLI only.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$path=$root.'/sitemap.php';
$backup=dirname($root).'/cic-adsense-backup-20261102/sitemap';
$src=@file_get_contents($path);
if(!is_string($src)){echo "READ_ERROR\n";exit(1);}
if(strpos($src,'CIC_SITEMAP_LASTMOD_20261102')!==false){echo "ALREADY_APPLIED\n";exit;}
$before1=<<<'ORIGINAL'
$posts=[];
ORIGINAL;
$after1=<<<'UPDATED'
// CIC_SITEMAP_LASTMOD_20261102: datas de alterações significativas reais.
$updatedStatic=[
  '/'=>'2026-11-02',
  '/guias/'=>'2026-11-02',
  '/sobre.html'=>'2026-11-02',
  '/politica-privacidade.html'=>'2026-11-02'
];
$updatedPosts=[
  'carrossel-splitter-como-dividir-uma-arte-em-slides-automaticamente'=>'2026-11-02',
  'ferramenta-online-para-cortar-imagens-de-carrossel-no-instagram'=>'2026-11-02'
];
$posts=[];
UPDATED;
$before2=<<<'ORIGINAL'
<?php foreach($staticUrls as $path): ?><url><loc><?php echo xmlsafe($base.$path); ?></loc></url>
ORIGINAL;
$after2=<<<'UPDATED'
<?php foreach($staticUrls as $path): ?><url><loc><?php echo xmlsafe($base.$path); ?></loc><?php if(isset($updatedStatic[$path])): ?><lastmod><?php echo $updatedStatic[$path]; ?></lastmod><?php endif; ?></url>
UPDATED;
$before3=<<<'ORIGINAL'
<?php foreach($posts as $post): $slug=publicSlug($post['slug']); ?><url><loc><?php echo xmlsafe($base.'/guias/'.rawurlencode($slug).'/'); ?></loc><?php if(!empty($post['created_at'])&&strtotime($post['created_at'])): ?><lastmod><?php echo date('Y-m-d',strtotime($post['created_at'])); ?></lastmod><?php endif; ?></url>
ORIGINAL;
$after3=<<<'UPDATED'
<?php foreach($posts as $post): $slug=publicSlug($post['slug']); $changed=$updatedPosts[$slug]??(!empty($post['created_at'])&&strtotime($post['created_at'])?date('Y-m-d',strtotime($post['created_at'])):null); ?><url><loc><?php echo xmlsafe($base.'/guias/'.rawurlencode($slug).'/'); ?></loc><?php if($changed): ?><lastmod><?php echo xmlsafe($changed); ?></lastmod><?php endif; ?></url>
UPDATED;
$pairs=[[$before1,$after1],[$before2,$after2],[$before3,$after3]];
foreach($pairs as $pair){
  if(substr_count($src,$pair[0])!==1){echo "ANCHOR_ERROR\n";exit(1);}
  $src=str_replace($pair[0],$pair[1],$src);
}
try{token_get_all($src,TOKEN_PARSE);}catch(ParseError $e){echo "PHP_SYNTAX_ERROR\n";exit(1);}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
if(!file_exists($backup.'/sitemap.php')&&!copy($path,$backup.'/sitemap.php')){echo "BACKUP_ERROR\n";exit(1);}
if(file_put_contents($path,$src,LOCK_EX)===false){echo "WRITE_ERROR\n";exit(1);}
echo "SITEMAP_LASTMOD_UPDATED\n";
