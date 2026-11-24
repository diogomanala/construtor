<?php
ini_set('display_errors','0');
header('Cache-Control: no-store');header('Content-Type: application/json');
if(!hash_equals('b49e2d1c6f7340f0916c8c55deab3295', (string)($_GET['k']??''))){http_response_code(404);exit;}
try{
function db():PDO{static $p=null;if($p)return $p;$p=new PDO('sqlite:'.__DIR__.'/data/odonto_em_dia.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$p->exec('PRAGMA busy_timeout=5000');return $p;}
$p=db();
if(($_GET['recover']??'')==='1'){
 require_once __DIR__.'/payments.php';require_once __DIR__.'/access-delivery.php';
 $q=$p->prepare("SELECT o.* FROM od_orders o JOIN od_order_contacts c ON c.order_id=o.id WHERE o.created_at=? AND o.amount_cents=10400 AND o.status='paid' AND (lower(c.buyer_name) LIKE ? OR lower(c.buyer_name) LIKE ? OR lower(o.buyer_email) LIKE ?) LIMIT 2");
 $q->execute(['2026-11-24 16:57:51','%valeria%','%valéria%','%valeria%']);$matches=$q->fetchAll();
 if(count($matches)!==1){echo json_encode(['recovery'=>'match_not_unique']);exit;}
 $order=$matches[0];$r=od_mp_api('GET','/v1/orders/'.rawurlencode($order['provider_order_id']));$d=$r['data']??[];
 if(!$r['ok']||($d['status']??'')!=='processed'||($d['id']??'')!==$order['provider_order_id']||($d['external_reference']??'')!==$order['id']||od_decimal_cents((string)($d['total_paid_amount']??''))!==10400){echo json_encode(['recovery'=>'payment_not_verified']);exit;}
 od_access_deliver($order);
 $q=$p->prepare("SELECT o.activated_user_id IS NOT NULL AS account_created,e.status AS entitlement,e.addon,d.status AS mail_status,d.attempts FROM od_orders o LEFT JOIN od_entitlements e ON e.order_id=o.id LEFT JOIN od_access_delivery d ON d.order_id=o.id WHERE o.id=?");$q->execute([$order['id']]);echo json_encode(['recovery'=>$q->fetch()]);exit;
}
$p->exec('PRAGMA query_only=ON');
function has_table(PDO $p,string $t):bool{$q=$p->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=?");$q->execute([$t]);return (bool)$q->fetchColumn();}
require_once __DIR__.'/payments.php';
$contacts=has_table($p,'od_order_contacts');$deliver=has_table($p,'od_access_delivery');
$sql="SELECT o.*,".($contacts?"c.buyer_name":"NULL")." AS buyer_name,".($deliver?"d.status":"NULL")." AS delivery_status,".($deliver?"d.attempts":"NULL")." AS delivery_attempts, e.status AS entitlement_status FROM od_orders o ".($contacts?"LEFT JOIN od_order_contacts c ON c.order_id=o.id ":"").($deliver?"LEFT JOIN od_access_delivery d ON d.order_id=o.id ":"")."LEFT JOIN od_entitlements e ON e.order_id=o.id ORDER BY o.created_at DESC LIMIT 15";
$rows=$p->query($sql)->fetchAll();$out=[];
foreach($rows as $i=>$o){
$match=stripos((string)$o['buyer_name'],'valeria')!==false||stripos((string)$o['buyer_name'],'valéria')!==false||stripos($o['buyer_email'],'valeria')!==false;
if(!$match&&$i>=3)continue;
$x=['name_match'=>$match,'time_utc'=>$o['created_at'],'local_status'=>$o['status'],'amount_cents'=>(int)$o['amount_cents'],'account_created'=>!empty($o['activated_user_id']),'entitlement'=>$o['entitlement_status'],'mail_status'=>$o['delivery_status'],'attempts'=>$o['delivery_attempts'],'provider_linked'=>!empty($o['provider_order_id'])];
if($o['provider_order_id']){$r=od_mp_api('GET','/v1/orders/'.rawurlencode($o['provider_order_id']));$d=$r['data']??[];$x['provider_http']=$r['status']??0;$x['provider_status']=$d['status']??null;$x['provider_paid']=$d['total_paid_amount']??null;$x['country']=$d['country_code']??null;$x['currency']=$d['currency']??null;$x['reference_matches']=($d['external_reference']??null)===$o['id'];$x['amount_matches']=od_decimal_cents((string)($d['total_amount']??''))===(int)$o['amount_cents'];}
$out[]=$x;
}
echo json_encode(['orders'=>$out]);
}catch(Throwable $e){echo json_encode(['error'=>get_class($e)]);}
