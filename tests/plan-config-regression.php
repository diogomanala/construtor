<?php
/** Teste CLI sem banco nem acesso à internet. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/document-plan-config.php';
$expected=[22,23,25,26,27,28];
$plans=odx_plan_configs();
function assertPlan(bool $ok, string $message): void {
    if (!$ok) {fwrite(STDERR,'FAIL: '.$message.PHP_EOL);exit(1);}
    echo 'PASS: '.$message.PHP_EOL;
}
assertPlan(array_keys($plans)===$expected,'seis planos esperados, na ordem original');
foreach ($plans as $id=>$config) {
    assertPlan(is_array($config['fields']??null)&&count($config['fields'])===4,"$id possui quatro campos específicos");
    $keys=array_column($config['fields'],'key');
    assertPlan(count($keys)===count(array_unique($keys)),"$id não repete chaves");
    foreach ($config['fields'] as $field) {
        assertPlan(in_array($field['type']??'', ['text','textarea'],true)&&strlen($field['hint']??'')>15&&strlen($field['label']??'')>5,"$id campo {$field['key']} orienta a personalização");
    }
    $spec=$config['spec'];
    assertPlan(count($spec['flow']??[])===4,"$id possui mapa de quatro etapas");
    assertPlan(in_array($spec['accent']??'',['jade','blue','amber'],true),"$id segue paleta documental");
}
echo 'PASS: templates adicionais somente de referência; conteúdo clínico não é aprovado neste teste.'.PHP_EOL;