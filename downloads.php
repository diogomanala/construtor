<?php
/** Downloads do material original, restritos aos usuários com acesso. */
function od_download_package(string $kind):never{
    if(!user()||!clinic()){http_response_code(403);exit('Acesso necessário.');}
    if($kind==='auto360'&&!od_360_allowed()){http_response_code(403);exit('O módulo Autoinspeção 360 não está incluído neste acesso.');}
    $files=[
       'kit'=>['storage'=>'kit-odonto-documentos-v1.zip','download'=>'Kit_Odonto_em_Dia_30_documentos.zip'],
       'auto360'=>['storage'=>'autoinspecao-360-v1.zip','download'=>'Autoinspecao_360_Complemento.zip'],
    ];
    if(!isset($files[$kind])){http_response_code(404);exit;}
    $item=$files[$kind];$path=__DIR__.'/data/'.$item['storage'];
    if(!is_file($path)){http_response_code(503);exit('Pacote temporariamente indisponível.');}
    header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="'.$item['download'].'"');header('Content-Length: '.filesize($path));
    header('Cache-Control: no-store, private');header('X-Content-Type-Options: nosniff');readfile($path);exit;
}