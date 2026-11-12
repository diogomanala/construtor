<?php
declare(strict_types=1);
header('Cache-Control: no-store');
$nonce=(string)($_GET['nonce']??'');
if($_SERVER['REQUEST_METHOD']!=='GET'||!hash_equals('902752927b3d1d05f3dab4ee1eecd363b79edcdc5cf2e3d48a04c6a388b5a273',$nonce)){http_response_code(404);exit('Não encontrado');}
$guard=__DIR__.'/data/.mail-smoke-20261112';
if(file_exists($guard)){header('Content-Type: text/plain');echo 'Teste já executado';exit;}
if(file_put_contents($guard,'1',LOCK_EX)===false){http_response_code(500);exit('Guard indisponível');}
$body="Teste técnico do transporte de e-mail PHP da plataforma Odonto em Dia em produção.\r\nNenhuma compra foi criada.\r\nData: ".gmdate('c')."\r\n";
$headers=['MIME-Version'=>'1.0','Content-Type'=>'text/plain; charset=UTF-8','From'=>'Odonto em Dia <acesso@odontonegocios.com>'];
$ok=@mail('titecweb@gmail.com','[Odonto em Dia] Teste técnico de envio 20261112',$body,$headers);
header('Content-Type: text/plain; charset=UTF-8');
echo $ok?'MAIL_ACCEPTED':'MAIL_FAILED';
