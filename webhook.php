<?php
/** Mercado Pago Checkout Pro / Orders API webhook. Sem sessão e sem confiança em parâmetros de retorno. */
declare(strict_types=1);
ini_set('display_errors','0');error_reporting(E_ALL);
header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}
function db():PDO{
    static $db=null;if($db)return $db;
    $db=new PDO('sqlite:'.__DIR__.'/data/odonto_em_dia.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $db->exec('PRAGMA busy_timeout=5000');return $db;
}
require_once __DIR__.'/payments.php';
if(!od_mp_ready()){http_response_code(503);exit('Integração indisponível.');}
$body=file_get_contents('php://input');$msg=json_decode((string)$body,true);
$event=(string)($_GET['type']??($msg['type']??''));
$dataId=(string)($_GET['data_id']??$_GET['data.id']??($msg['data']['id']??''));
if($event!=='order'||!preg_match('/^[A-Za-z0-9_-]{10,128}$/',$dataId)){http_response_code(400);exit('Evento inválido.');}
$headers=$_SERVER['HTTP_X_SIGNATURE']??'';$reqId=(string)($_SERVER['HTTP_X_REQUEST_ID']??'');
if(!preg_match('/(?:^|,)\s*ts=(\d+)/',$headers,$mTs)||!preg_match('/(?:^|,)\s*v1=([a-fA-F0-9]{64})/',$headers,$mSig)||!$reqId){http_response_code(401);exit('Assinatura ausente.');}
$cfg=od_mp_config();
$manifest='id:'.$dataId.';request-id:'.$reqId.';ts:'.$mTs[1].';';
$valid=hash_equals(strtolower($mSig[1]),hash_hmac('sha256',$manifest,$cfg['webhook_secret']));
if(!$valid){http_response_code(401);exit('Assinatura inválida.');}
try{
    $record=od_order_by_mp($dataId);
    if($record)od_order_sync($record);
    http_response_code(200);header('Content-Type: text/plain; charset=UTF-8');echo 'OK';
}catch(Throwable $e){error_log('[Odonto] Erro no webhook Orders');http_response_code(500);echo 'Erro de processamento.';}