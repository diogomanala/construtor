<?php
ini_set('display_errors','0');
header('Cache-Control: no-store');
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__.'/payments.php';
od_mp_config();
$names=['OD_MP_PUBLIC_KEY','MP_PUBLIC_KEY','MERCADO_PAGO_PUBLIC_KEY','MERCADOPAGO_PUBLIC_KEY'];
$found=false;
foreach($names as $n){$v=defined($n)?constant($n):getenv($n);if(is_string($v)&&strlen(trim($v))>20){$found=true;break;}}
echo $found?'ODONTO_PUBLIC_KEY_CONFIGURED':'ODONTO_PUBLIC_KEY_NOT_CONFIGURED';
