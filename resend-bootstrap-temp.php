<?php
declare(strict_types=1);
ini_set('display_errors','0');
error_reporting(E_ALL);
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
if($_SERVER['REQUEST_METHOD']!=='GET' || !hash_equals('f2b608b23b74bbbeaa345f2a653710f23ac4f4110f91bbd7f9bf38f642993ef0',(string)($_GET['nonce']??''))){
    http_response_code(404);exit('Not found');
}
$act=(string)($_GET['action']??'');
$private=dirname(__DIR__,2).'/odonto-mail-env.php';
$keypath=__DIR__.'/data/.od-resend-bootstrap.pem';
function reply(string $text,int $code=200):never {
    http_response_code($code);header('Content-Type: text/plain; charset=UTF-8');echo $text;exit;
}
if($act==='pub'){
    if(file_exists($private))reply('ALREADY_INSTALLED');
    if(!is_file($keypath)){
        $key=openssl_pkey_new(['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
        if(!$key)reply('CRYPTO_UNAVAILABLE',500);
        if(!openssl_pkey_export($key,$pem))reply('EXPORT_FAILED',500);
        if(file_put_contents($keypath,$pem,LOCK_EX)===false)reply('STORE_FAILED',500);
        chmod($keypath,0600);
    }
    $key=openssl_pkey_get_private(file_get_contents($keypath));
    if(!$key)reply('KEY_MISSING',500);
    $details=openssl_pkey_get_details($key);
    if(!is_array($details)||empty($details['key']))reply('PUBLIC_KEY_FAILED',500);
    reply(base64_encode((string)$details['key']));
}
if($act==='install'){
    if(file_exists($private))reply('ALREADY_INSTALLED');
    $encoded=(string)($_GET['cipher']??'');
    if(!preg_match('/^[A-Za-z0-9_-]{100,500}$/D',$encoded))reply('INVALID_CIPHER',400);
    $pem=is_file($keypath)?file_get_contents($keypath):false;
    if(!$pem)reply('NO_KEY',400);
    $priv=openssl_pkey_get_private($pem);
    $cipher=base64_decode(strtr($encoded,'-_','+/'),true);
    if(!$priv||!is_string($cipher)||!openssl_private_decrypt($cipher,$secret,$priv,OPENSSL_PKCS1_OAEP_PADDING))reply('DECRYPT_FAILED',400);
    if(!preg_match('/^re_[A-Za-z0-9_]{20,120}$/D',$secret))reply('INVALID_TOKEN',400);
    $code='<?php declare(strict_types=1); if(!defined("OD_RESEND_API_KEY")) define("OD_RESEND_API_KEY",'.var_export($secret,true).');';
    $tmp=$private.'.tmp-'.bin2hex(random_bytes(5));
    if(file_put_contents($tmp,$code,LOCK_EX)===false)reply('WRITE_FAILED',500);
    chmod($tmp,0600);
    if(!rename($tmp,$private)){@unlink($tmp);reply('INSTALL_FAILED',500);}
    @unlink($keypath);
    reply('INSTALLED');
}
if($act==='test'){
    if(!is_file($private))reply('NOT_INSTALLED',400);
    $guard=__DIR__.'/data/.od-resend-tested';
    if(is_file($guard))reply((string)file_get_contents($guard));
    require $private;
    if(!defined('OD_RESEND_API_KEY'))reply('KEY_UNAVAILABLE',500);
    $payload=['from'=>'Odonto em Dia <acesso@notificacoes.odontonegocios.com>',
       'to'=>['titecweb@gmail.com'],
       'subject'=>'[Odonto em Dia] Teste Resend confirmado',
       'text'=>'Este é um teste do envio transacional do Odonto em Dia. Nenhuma compra foi realizada.'];
    $ch=curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),
      CURLOPT_HTTPHEADER=>['Authorization: Bearer '.OD_RESEND_API_KEY,'Content-Type: application/json'],
      CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>true]);
    $raw=curl_exec($ch);$http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    $success=$http>=200&&$http<300;
    $result=$success?'RESEND_ACCEPTED':'RESEND_FAILED_HTTP_'.$http;
    file_put_contents($guard,$result,LOCK_EX);
    reply($result);
}
reply('NOT_FOUND',404);
