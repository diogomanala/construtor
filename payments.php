<?php
/** Checkout Pro Orders API | Mercado Pago. Segredos fora do document root. */
function od_mp_config():array{
    $private=dirname(__DIR__,2).'/odonto-env.php'; // ..../domains/odontonegocios.com/odonto-env.php
    if(is_file($private))require_once $private;
    return [
      'token'=>defined('OD_MP_ACCESS_TOKEN')?(string)OD_MP_ACCESS_TOKEN:(string)(getenv('OD_MP_ACCESS_TOKEN')?:''),
      'webhook_secret'=>defined('OD_MP_WEBHOOK_SECRET')?(string)OD_MP_WEBHOOK_SECRET:(string)(getenv('OD_MP_WEBHOOK_SECRET')?:''),
    ];
}
function od_mp_ready():bool{
    $cfg=od_mp_config();return strlen($cfg['token'])>30&&strlen($cfg['webhook_secret'])>20;
}
function od_orders_db():PDO{
    $p=db();
    $p->exec('CREATE TABLE IF NOT EXISTS od_orders (id TEXT PRIMARY KEY, public_code TEXT UNIQUE NOT NULL, buyer_email TEXT NOT NULL, addon INTEGER NOT NULL DEFAULT 0, amount_cents INTEGER NOT NULL, provider_order_id TEXT UNIQUE, checkout_url TEXT DEFAULT \'\', status TEXT NOT NULL DEFAULT \'pending\', activated_user_id INTEGER, created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP)');
    $p->exec('CREATE TABLE IF NOT EXISTS od_entitlements (user_id INTEGER PRIMARY KEY, order_id TEXT NOT NULL, addon INTEGER NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT \'active\', started_at TEXT DEFAULT CURRENT_TIMESTAMP, expires_at TEXT DEFAULT (datetime(\'now\',\'+365 days\')))');
    return $p;
}
function od_mp_api(string $method,string $path,?array $body=null,?string $idempotency=null):array{
    $cfg=od_mp_config();if(!$cfg['token'] || !function_exists('curl_init'))return ['ok'=>false,'error'=>'Mercado Pago indisponível.'];
    if(!preg_match('~^/v1/orders(?:/[A-Za-z0-9_-]{1,128})?$~',$path))return ['ok'=>false,'error'=>'Caminho inválido.'];
    $ch=curl_init('https://api.mercadopago.com'.$path);
    $headers=['Authorization: Bearer '.$cfg['token'],'Accept: application/json'];
    if($body!==null){$headers[]='Content-Type: application/json';$json=json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);curl_setopt($ch,CURLOPT_POSTFIELDS,$json);}
    if($idempotency)$headers[]='X-Idempotency-Key: '.$idempotency;
    curl_setopt_array($ch,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>$headers,CURLOPT_SSL_VERIFYPEER=>true]);
    $raw=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    $data=is_string($raw)?json_decode($raw,true):null;
    return ['ok'=>$status>=200&&$status<300&&is_array($data),'status'=>$status,'data'=>is_array($data)?$data:[]];
}
function od_order_code():string{return 'od'.bin2hex(random_bytes(14));}
function od_order_by_code(string $code):?array{if(!preg_match('/^[a-f0-9]{48}$/',$code))return null;$s=od_orders_db()->prepare('SELECT * FROM od_orders WHERE public_code=?');$s->execute([$code]);return $s->fetch()?:null;}
function od_order_by_mp(string $providerId):?array{$s=od_orders_db()->prepare('SELECT * FROM od_orders WHERE provider_order_id=?');$s->execute([$providerId]);return $s->fetch()?:null;}
function od_decimal_cents(string $amount):int{if(!preg_match('/^\d+(?:\.\d{1,2})?$/',$amount))return -1;$parts=explode('.',$amount);return (int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');}
function od_order_sync(array $order):array{
    if(!$order['provider_order_id'])return $order;
    $result=od_mp_api('GET','/v1/orders/'.rawurlencode($order['provider_order_id']));if(!$result['ok'])return $order;
    $d=$result['data'];
    if((string)($d['id']??'')!==$order['provider_order_id']||(string)($d['external_reference']??'')!==$order['id']||(string)($d['currency']??'')!=='BRL'||od_decimal_cents((string)($d['total_amount']??''))!==(int)$order['amount_cents'])return $order;
    $state=(string)($d['status']??'');$paid=od_decimal_cents((string)($d['total_paid_amount']??'0'));
    $status=($state==='processed' && $paid>=(int)$order['amount_cents'])?'paid':(in_array($state,['cancelled','canceled','refunded','charged_back'],true)?'canceled':'pending');
    $db=od_orders_db();
    if($status!==$order['status']){
       $db->prepare('UPDATE od_orders SET status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$status,$order['id']]);
       if($status==='canceled' && $order['activated_user_id'])$db->prepare('UPDATE od_entitlements SET status=\'revoked\' WHERE user_id=? AND order_id=?')->execute([$order['activated_user_id'],$order['id']]);
    }
    $order['status']=$status;return $order;
}
function od_mp_start():never{
    if(!od_mp_ready()){flash('O pagamento ainda está em configuração. Nenhuma cobrança foi iniciada.');redirect('?page=checkout');}
    if(!empty($_SESSION['od_last_checkout']) && time()-(int)$_SESSION['od_last_checkout']<30){flash('Aguarde alguns segundos antes de tentar novamente.');redirect('?page=checkout');}
    $_SESSION['od_last_checkout']=time();
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>180){flash('Informe um e-mail válido para receber o acesso.');redirect('?page=checkout');}
    $addon=isset($_POST['addon'])?1:0;$amount=6700+($addon?3700:0);
    $id=od_order_code();$code=bin2hex(random_bytes(24));$uuid=sprintf('%s-%s-%s-%s-%s',bin2hex(random_bytes(4)),bin2hex(random_bytes(2)),bin2hex(random_bytes(2)),bin2hex(random_bytes(2)),bin2hex(random_bytes(6)));
    $p=od_orders_db();$p->prepare('INSERT INTO od_orders(id,public_code,buyer_email,addon,amount_cents) VALUES(?,?,?,?,?)')->execute([$id,$code,$email,$addon,$amount]);
    $return='https://odontonegocios.com/rdc/?page=pedido&code='.$code;
    $items=[['title'=>'Odonto em Dia - kit documental e plataforma','unit_price'=>'67.00','quantity'=>1]];
    if($addon)$items[]=['title'=>'Autoinspecao 360 - modulo complementar','unit_price'=>'37.00','quantity'=>1];
    $payload=['type'=>'online','processing_mode'=>'manual','capture_mode'=>'automatic_async','total_amount'=>number_format($amount/100,2,'.',''),'external_reference'=>$id,'description'=>'Odonto em Dia','payer'=>['email'=>$email],'items'=>$items,'config'=>['online'=>['success_url'=>$return,'failure_url'=>$return,'pending_url'=>$return,'auto_return'=>'approved']]];
    $result=od_mp_api('POST','/v1/orders',$payload,$uuid);
    $d=$result['data']??[];$url=(string)($d['checkout_url']??'');$host=strtolower((string)parse_url($url,PHP_URL_HOST));
    if(!$result['ok']||!in_array($host,['www.mercadopago.com.br','www.mercadopago.com'],true)||parse_url($url,PHP_URL_SCHEME)!=='https'||empty($d['id'])){
       error_log('[Odonto] Checkout Orders indisponível HTTP '.($result['status']??0));flash('Não foi possível abrir o Mercado Pago agora. Nenhuma cobrança foi concluída.');redirect('?page=checkout');
    }
    $p->prepare('UPDATE od_orders SET provider_order_id=?,checkout_url=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([(string)$d['id'],$url,$id]);
    header('Location: '.$url,true,303);exit;
}
function od_mp_activate():never{
    $order=od_order_by_code((string)($_POST['code']??''));if(!$order){http_response_code(404);exit('Pedido não encontrado.');}
    $order=od_order_sync($order);
    if($order['status']!=='paid'){flash('O pagamento ainda não foi confirmado.');redirect('?page=pedido&code='.$order['public_code']);}
    $password=(string)($_POST['password']??'');if(strlen($password)<12||strlen($password)>256){flash('Use uma senha de pelo menos 12 caracteres.');redirect('?page=pedido&code='.$order['public_code']);}
    $db=od_orders_db();$db->beginTransaction();
    try{
       $s=$db->prepare('SELECT id FROM users WHERE email=?');$s->execute([$order['buyer_email']]);$existing=$s->fetchColumn();
       if($existing){
           $db->prepare('INSERT INTO od_entitlements(user_id,order_id,addon,status,expires_at) VALUES(?,?,?,\'active\',datetime(\'now\',\'+365 days\')) ON CONFLICT(user_id) DO UPDATE SET order_id=excluded.order_id,addon=MAX(od_entitlements.addon,excluded.addon),status=\'active\',expires_at=excluded.expires_at')->execute([(int)$existing,$order['id'],(int)$order['addon']]);
           $db->prepare('UPDATE od_orders SET activated_user_id=? WHERE id=?')->execute([(int)$existing,$order['id']]);$db->commit();
           flash('Pagamento confirmado. Use a senha da conta existente para acessar.');redirect('?page=entrar');
       }
       $db->prepare('INSERT INTO users(email,password) VALUES(?,?)')->execute([$order['buyer_email'],password_hash($password,PASSWORD_DEFAULT)]);
       $uid=(int)$db->lastInsertId();
       $db->prepare('INSERT INTO od_entitlements(user_id,order_id,addon,status,expires_at) VALUES(?,?,?,\'active\',datetime(\'now\',\'+365 days\'))')->execute([$uid,$order['id'],(int)$order['addon']]);
       $db->prepare('UPDATE od_orders SET activated_user_id=? WHERE id=?')->execute([$uid,$order['id']]);$db->commit();
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();error_log('[Odonto] Erro na ativacao');flash('Não foi possível ativar este cadastro.');redirect('?page=pedido&code='.$order['public_code']);}
    session_regenerate_id(true);$_SESSION['uid']=$uid;redirect('?page=diagnostico');
}
function od_is_pilot(int $userId):bool {
    if($userId!==1)return false;
    $q=od_orders_db()->prepare('SELECT 1 FROM od_orders WHERE activated_user_id=? LIMIT 1');
    $q->execute([$userId]);
    return $q->fetchColumn()===false;
}
function od_mp_entitlement(int $userId):?array{
    $s=od_orders_db()->prepare('SELECT * FROM od_entitlements WHERE user_id=? AND status=\'active\' AND (expires_at IS NULL OR expires_at>=CURRENT_TIMESTAMP)');$s->execute([$userId]);return $s->fetch()?:null;
}
function od_mp_checkout_page():never{
    h('Seu kit por R$ 67');
    echo '<style>.pay-layout{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(260px,.8fr);gap:22px;max-width:1000px;margin:25px auto}.pay-box{padding:26px;border:1px solid #d7e5dd;background:#fff;border-radius:18px}.pay-box h2{font-size:23px;margin:0 0 16px}.pay-addon{padding:15px;margin:16px 0;border:1px solid #bee1d1;border-radius:12px;background:#f3faf6}.pay-addon label{display:flex;align-items:center;gap:12px;cursor:pointer;font-weight:700}.pay-addon input{width:20px;height:20px;accent-color:#167764}.pay-line{display:flex;justify-content:space-between;margin:12px 0;gap:20px}.pay-line.total{padding-top:18px;border-top:1px solid #dce8e1;font-size:24px;font-weight:800}.pay-note{font-size:13px;color:#657b72;line-height:1.6}.pay-box input[type=email]{width:100%;padding:13px;border:1px solid #cbdcd4;border-radius:10px}@media(max-width:750px){.pay-layout{grid-template-columns:1fr}}</style><p class="lead">Revise sua compra e escolha se deseja acrescentar o módulo de autoinspeção.</p><div class="pay-layout"><form class="pay-box" method="post">'.token().'<input type="hidden" name="act" value="checkout_start"><h2>Dados para receber o acesso</h2><label>E-mail de acesso<input type="email" name="email" placeholder="voce@consultorio.com.br" autocomplete="email" required maxlength="180"></label><div class="pay-addon"><label><input type="checkbox" name="addon" id="odAddon"><span>Adicionar Autoinspeção 360<br><small>45 verificações guiadas, plano de ação e relatório A4 para imprimir. + R$ 37 (opcional)</small></span></label></div><p class="pay-note">Você poderá conferir os itens antes de finalizar. O pagamento será realizado no ambiente do Mercado Pago.</p>';
    if(od_mp_ready())echo '<button class="btn" type="submit">Continuar para pagamento seguro</button>';else echo '<div class="warn">Checkout em configuração. Ainda não é possível efetuar o pagamento. Nenhuma cobrança será realizada.</div><button class="btn" disabled aria-disabled="true">Pagamento indisponível no momento</button>';
    echo '<p class="pay-note" style="margin:15px 0 0">Ao continuar, você concorda com os <a href="?page=termos" target="_blank" rel="noopener">Termos de uso</a> e declara ter lido a <a href="?page=politica-privacidade" target="_blank" rel="noopener">Política de privacidade</a>. Sua compra é única e não há renovação automática.</p></form><aside class="pay-box"><h2>Resumo do pedido</h2><div class="pay-line"><span>Kit documental e plataforma</span><b>R$ 67,00</b></div><div class="pay-line" id="odAddonLine" style="display:none"><span>Autoinspeção 360</span><b>R$ 37,00</b></div><div class="pay-line total"><span>Total</span><span id="odTotal">R$ 67,00</span></div><p class="pay-note">Compra única. Acesso online por 12 meses. Documentos já baixados podem continuar sendo utilizados após o período, sujeitos à validação técnica.</p><p class="pay-note">O kit não emite licenças, selos de conformidade ou certificados de órgãos públicos.</p></aside></div><script>let cb=document.getElementById("odAddon");cb.addEventListener("change",()=>{document.getElementById("odAddonLine").style.display=cb.checked?"flex":"none";document.getElementById("odTotal").textContent=cb.checked?"R$ 104,00":"R$ 67,00"})</script>';
    foot();exit;
}
function od_mp_order_page():never{
    $order=od_order_by_code((string)($_GET['code']??''));if(!$order){http_response_code(404);exit('Pedido não encontrado.');}
    $order=od_order_sync($order);
    h('Acompanhar meu pedido');
    echo '<div class="card" style="max-width:670px;margin:25px auto;padding:26px"><p>Pedido <b>'.esc($order['id']).'</b> • '.esc($order['buyer_email']).'</p>';
    if($order['status']==='paid'){
        if($order['activated_user_id'])echo '<h2>Pagamento confirmado</h2><p>O acesso desta compra já foi ativado. Entre com seu e-mail e senha.</p><a class="btn" href="?page=entrar">Entrar na plataforma</a>';
        else echo '<h2>Pagamento confirmado!</h2><p>Crie a senha do seu acesso. Os documentos permanecem como minutas para revisão pelo RT.</p><form method="post">'.token().'<input type="hidden" name="act" value="order_activate"><input type="hidden" name="code" value="'.esc($order['public_code']).'"><label>Crie sua senha (mínimo de 12 caracteres)<input type="password" name="password" required minlength="12" autocomplete="new-password"></label><button>Ativar meu acesso</button></form>';
    } elseif($order['status']==='canceled')echo '<h2>Pagamento não concluído</h2><p>Este pedido foi cancelado ou estornado. Se necessário, inicie uma nova compra.</p><a class="btn" href="?page=checkout">Voltar ao checkout</a>';
    else echo '<h2>Aguardando confirmação do pagamento</h2><p>No Pix, a confirmação pode ocorrer depois do retorno ao site. Atualize esta página para verificar novamente.</p><a class="btn" href="?page=pedido&amp;code='.esc($order['public_code']).'">Verificar novamente</a>';
    echo '</div>';foot();exit;
}