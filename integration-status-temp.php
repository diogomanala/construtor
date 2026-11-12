<?php
declare(strict_types=1);
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
if($_SERVER['REQUEST_METHOD']!=='GET'||!hash_equals('51c1c6a4f5d182825ce5373cfe80f04e3edc5dd528e500e4fa1e75b6f274f972',(string)($_GET['nonce']??''))){http_response_code(404);exit;}
require_once __DIR__.'/payments.php';
require_once __DIR__.'/access-delivery.php';
$mp=od_mp_config();$mail=od_access_config();
$token=(string)$mp['token'];
$result=['mercado_pago_ready'=>od_mp_ready(),
  'payment_environment'=>str_starts_with($token,'APP_USR-')?'production':(str_starts_with($token,'TEST-')?'test':'unknown'),
  'webhook_secret_present'=>strlen((string)$mp['webhook_secret'])>20,
  'resend_key_present'=>strlen((string)$mail['resend_key'])>20,
  'private_mail_config_exists'=>is_file(dirname(__DIR__,2).'/odonto-mail-env.php')];
header('Content-Type: application/json; charset=UTF-8');echo json_encode($result);
