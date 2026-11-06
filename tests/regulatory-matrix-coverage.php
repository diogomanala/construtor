<?php
/**
 * Coverage regression for official-reference mapping.
 * Run: php tests/regulatory-matrix-coverage.php
 * Tests link integrity and scope markers, NOT legal compliance.
 */
declare(strict_types=1);
$root=dirname(__DIR__);
$docs=json_decode((string)file_get_contents($root.'/data/templates.json'),true,512,JSON_THROW_ON_ERROR);
$map=json_decode((string)file_get_contents($root.'/data/regulatory-map.json'),true,512,JSON_THROW_ON_ERROR);
function fail_check(string $s):never {fwrite(STDERR,"FAIL: ".$s."\n");exit(1);}
if(!is_array($docs)||count($docs)!==30)fail_check('catalog must have 30 documents');
if(!is_array($map['documents']??null)||count($map['documents'])!==30)fail_check('mapping must have 30 documents');
if(!is_array($map['references']??null)||count($map['references'])<1)fail_check('missing official sources');
$catalog=[];foreach($docs as $doc)$catalog[(int)$doc['id']]=$doc;
$seen=[];$links=0;
foreach($map['documents'] as $d) {
    $id=(int)($d['id']??0);
    if(!isset($catalog[$id])||isset($seen[$id]))fail_check('invalid/duplicate template id '.$id);
    $seen[$id]=true;
    if((string)$catalog[$id]['title']!==(string)($d['template']??''))fail_check('title mismatch '.$id);
    if(($d['technical_review']??'')!=='pendente'||($d['legal_review']??'')!=='pendente')fail_check('review status cannot be automatically approved '.$id);
    if(!is_array($d['regulatory_links']??null)||!count($d['regulatory_links']))fail_check('missing regulatory links for '.$id);
    foreach($d['regulatory_links'] as $link){
        $source=$map['references'][(string)($link['code']??'')]??null;
        if(!$source)fail_check('broken reference for '.$id);
        if(!filter_var($source['url']??'',FILTER_VALIDATE_URL)||!str_starts_with((string)$source['url'],'https://'))fail_check('bad source URL for '.$id);
        $links++;
    }
}
for($id=1;$id<=30;$id++)if(!isset($seen[$id]))fail_check('unmapped id '.$id);
echo "OK: 30 templates mapped; ".$links." normative references; all reviews pending professional checks.\n";
