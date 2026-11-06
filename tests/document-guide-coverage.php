<?php
/** Regression of metadata-only document guides. Never accesses application DB. */
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/document-plan-config.php';
function assertGuide(bool $condition, string $message): void {
    if(!$condition){fwrite(STDERR,'FAIL: '.$message.PHP_EOL);exit(1);}
    echo 'PASS: '.$message.PHP_EOL;
}
$special=[3,6,7,11,21,24];
$configs=odx_document_configs();
$keys=array_keys($configs);sort($keys);
assertGuide(count($configs)===24,'24 schemas carregados do sistema de configuração');
assertGuide(!array_intersect($special,$keys),'schemas não substituem os seis documentos especiais do motor');
$covered=array_unique(array_merge($special,$keys));sort($covered);
assertGuide($covered===range(1,30),'os trinta IDs estão cobertos pelos dois componentes');
foreach($configs as $id=>$config){
    assertGuide(count($config['fields'])===4,"modelo $id tem quatro campos guiados específicos");
    $fieldKeys=array_column($config['fields'],'key');
    assertGuide(count($fieldKeys)===count(array_unique($fieldKeys)),"modelo $id não duplica chaves");
    assertGuide(count($config['spec']['flow']??[])===4,"modelo $id tem quatro fases ilustrativas");
    assertGuide(in_array($config['spec']['accent']??'',['jade','blue','amber'],true),"modelo $id respeita a paleta documental");
}
echo 'PASS: metadados guiados são apoio de organização, não validação normativa.'.PHP_EOL;