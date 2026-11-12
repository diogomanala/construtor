<?php
declare(strict_types=1);
header('Cache-Control: no-store');
if($_SERVER['REQUEST_METHOD']!=='GET'||!hash_equals('4344e8867463ef7b721394c69ce9b7344d40f1dfe3e0f31cf7f496e122d08bd8',(string)($_GET['nonce']??''))){http_response_code(404);exit;}
$path=__DIR__.'/data/.mail-smoke-2';
if(file_exists($path)){header('Content-Type: text/plain');echo file_get_contents($path);exit;}
$headers=['MIME-Version'=>'1.0','Content-Type'=>'text/plain; charset=UTF-8','From'=>'Odonto em Dia <contato@odontonegocios.com>'];
$ok=@mail('titecweb@gmail.com','[Odonto em Dia] Teste de entrega SMTP 2','Este e um teste unico de entrega automatica, sem pedidos reais.',$headers,'-fcontato@odontonegocios.com');
$result=$ok?'MAIL_ACCEPTED':'MAIL_FAILED';
file_put_contents($path,$result,LOCK_EX);
header('Content-Type: text/plain');echo $result;
