<?php
declare(strict_types=1);
// Run: php tests/material-library-coverage.php
// Validates structural integrity, NOT dental/clinical accuracy or regulatory conformity.
require_once dirname(__DIR__).'/material-library.php';
$catalog=od_mat_catalog();
$items=$catalog['items'];
if(count($items)!==54)throw new RuntimeException('Expected 54 items');
if((int)$catalog['cols']!==9||(int)$catalog['rows']!==6)throw new RuntimeException('Unexpected atlas grid');
$ids=[];$positions=[];
foreach($items as $item){
    $id=(string)$item['id'];$index=(int)$item['index'];
    if(!preg_match('/^[a-z0-9_]+$/D',$id))throw new RuntimeException('Invalid identifier');
    if(isset($ids[$id])||isset($positions[$index]))throw new RuntimeException('Duplicate item');
    if(!isset($catalog['display_categories'][$item['category']]))throw new RuntimeException('Unknown category');
    if(($item['review']??'')!=='pendente')throw new RuntimeException('Items must remain under review');
    if($index<0||$index>=54)throw new RuntimeException('Index outside atlas');
    $ids[$id]=true;$positions[$index]=true;
}
if(count($positions)!==54)throw new RuntimeException('Missing tile index');
$asset=dirname(__DIR__).'/assets/illustrations/odonto-materiais-54-v1.avif';
if(!is_file($asset)||filesize($asset)<50000)throw new RuntimeException('HD asset missing or truncated');
if((new finfo(FILEINFO_MIME_TYPE))->file($asset)!=='image/avif')throw new RuntimeException('Invalid image mime');
$checks=0;
foreach(range(1,30) as $documentId){
    $items=od_mat_defaults($documentId,['chairs'=>1,'has_xray'=>0]);
    if(count($items)>18||count(array_unique($items))!==count($items))throw new RuntimeException('Bad defaults for '.$documentId);
    foreach($items as $id)if(!isset($ids[$id]))throw new RuntimeException('Missing default image '.$id);
    $checks++;
}
echo 'OK: 54 unique illustrations, 8 categories, HD atlas, 30 default maps validated (technical content requires RT review).'.PHP_EOL;
