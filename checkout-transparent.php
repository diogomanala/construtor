<?php
/** Checkout Transparente via Orders API. Disabled until verified in sandbox. */
function od_transparent_key():string {
    od_mp_config();
    return defined('OD_MP_PUBLIC_KEY') ? (string)OD_MP_PUBLIC_KEY : (string)(getenv('OD_MP_PUBLIC_KEY')?:'');
}
function od_transparent_enabled():bool {
    od_mp_config();
    $flag=defined('OD_MP_TRANSPARENT_ENABLED') ? (string)OD_MP_TRANSPARENT_ENABLED : (string)getenv('OD_MP_TRANSPARENT_ENABLED');
    return $flag==='1' && od_mp_ready() && strlen(od_transparent_key())>20;
}
function od_transparent_json(array $data,int $status=200):never {
    http_response_code($status);header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;
}
function od_transparent_submit():never {
    if(!od_transparent_enabled())od_transparent_json(['error'=>'Pagamento indisponível.'],503);
    $attempt=(string)($_POST['attempt']??'');
    $state=$_SESSION['od_transparent_attempt']??[];
    if(!isset($state['key'])||!hash_equals($state['key'],$attempt))od_transparent_json(['error'=>'Sessão expirada. Atualize a página.'],409);
    if(!empty($state['order_code']))od_transparent_json(['order_url'=>'?page=pedido&code='.$state['order_code']]);
    if(!empty($state['uncertain']))od_transparent_json(['error'=>'A tentativa anterior ainda está sendo verificada. Não faça outro pagamento. Entre em contato com o suporte.'],409);
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>180)od_transparent_json(['error'=>'Informe um e-mail válido.'],422);
    $addon=($_POST['addon']??'')==='1'?1:0;
    $amount=6700+($addon?3700:0);$value=number_format($amount/100,2,'.','');
    $kind=(string)($_POST['kind']??'');$payer=['email'=>$email];
    if($kind==='pix'){
        $payment=['amount'=>$value,'payment_method'=>['id'=>'pix','type'=>'bank_transfer'],'expiration_time'=>'PT30M'];
    } elseif($kind==='card'){
        $token=(string)($_POST['card_token']??'');$method=(string)($_POST['method']??'');$type=(string)($_POST['payment_type']??'');$installments=filter_var($_POST['installments']??null,FILTER_VALIDATE_INT);
        if(!preg_match('/^[A-Za-z0-9_-]{12,256}$/',$token)||!preg_match('/^[a-zA-Z0-9_]{2,40}$/',$method)||!in_array($type,['credit_card','debit_card'],true)||$installments===false||$installments<1||$installments>12)od_transparent_json(['error'=>'Confira os dados do cartão.'],422);
        $docType=(string)($_POST['doc_type']??'');$doc=preg_replace('/\D/','',(string)($_POST['doc_number']??''));
        if(in_array($docType,['CPF','CNPJ'],true)&&strlen($doc)===($docType==='CPF'?11:14))$payer['identification']=['type'=>$docType,'number'=>$doc];
        $payment=['amount'=>$value,'payment_method'=>['id'=>$method,'type'=>$type,'token'=>$token,'installments'=>$installments]];
    } else od_transparent_json(['error'=>'Escolha Pix ou cartão.'],422);
    // Do not trust totals supplied by the browser. Do not store raw card data or token.
    $id=od_order_code();$code=bin2hex(random_bytes(24));
    $p=od_orders_db();$p->prepare('INSERT INTO od_orders(id,public_code,buyer_email,addon,amount_cents) VALUES(?,?,?,?,?)')->execute([$id,$code,$email,$addon,$amount]);
    od_meta_capture_checkout($id);
    $_SESSION['od_transparent_attempt']['uncertain']=true;
    $payload=['type'=>'online','processing_mode'=>'automatic','total_amount'=>$value,'external_reference'=>$id,'payer'=>$payer,'transactions'=>['payments'=>[$payment]]];
    $r=od_mp_api('POST','/v1/orders',$payload,$attempt);
    $d=$r['data']??[];$provider=(string)($d['id']??'');
    if(!$r['ok']||!preg_match('/^[A-Za-z0-9_-]{10,128}$/',$provider)){
        $http=(int)($r['status']??0);
        // A timeout/5xx is ambiguous: no automatic second charge.
        if($http>=400&&$http<500&&!in_array($http,[408,409,429],true)){
            $_SESSION['od_transparent_attempt']['uncertain']=false;
            $_SESSION['od_transparent_attempt']['key']=bin2hex(random_bytes(24));
            od_transparent_json(['error'=>'Não foi possível processar este pagamento. Confira os dados ou escolha outro meio.','new_attempt'=>$_SESSION['od_transparent_attempt']['key']],422);
        }
        od_transparent_json(['error'=>'Não foi possível confirmar a solicitação. Não tente pagar novamente antes de consultar o suporte.'],503);
    }
    if((string)($d['external_reference']??'')!==$id||od_decimal_cents((string)($d['total_amount']??''))!==$amount){
        od_transparent_json(['error'=>'Não foi possível validar o pedido. Entre em contato com o suporte.'],502);
    }
    $p->prepare('UPDATE od_orders SET provider_order_id=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$provider,$id]);
    $_SESSION['od_checkout_proof']=$code;
    $_SESSION['od_transparent_attempt']['order_code']=$code;
    $_SESSION['od_transparent_attempt']['uncertain']=false;
    $order=od_order_by_code($code);
    if($order)$order=od_order_sync($order);
    $out=['order_url'=>'?page=pedido&code='.$code,'status'=>$order['status']??'pending'];
    $pm=$d['transactions']['payments'][0]['payment_method']??[];
    if($kind==='pix'&&isset($pm['qr_code'],$pm['qr_code_base64'])&&strlen($pm['qr_code'])<=4096&&strlen($pm['qr_code_base64'])<=500000){
        $out['qr_code']=(string)$pm['qr_code'];$out['qr_image']=(string)$pm['qr_code_base64'];
    }
    od_transparent_json($out);
}
function od_transparent_status():never{
    $code=(string)($_GET['code']??'');$proof=(string)($_SESSION['od_checkout_proof']??'');
    if($proof===''||!hash_equals($proof,$code))od_transparent_json(['error'=>'Pedido indisponível.'],403);
    $o=od_order_by_code($code);if(!$o)od_transparent_json(['error'=>'Pedido indisponível.'],404);
    $o=od_order_sync($o);od_transparent_json(['status'=>$o['status']]);
}
function od_transparent_page():never {
    if(!od_transparent_enabled())od_mp_checkout_page();
    // The SDK is only allowed on this route; the member area retains its existing CSP.
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://*.mlstatic.com; script-src 'self' 'unsafe-inline' https://sdk.mercadopago.com https://*.mercadopago.com https://*.mlstatic.com https://connect.facebook.net; img-src 'self' data: blob: https://*.mercadopago.com https://*.mlstatic.com https://www.facebook.com; connect-src 'self' https://*.mercadopago.com https://*.mercadopago.com.br https://*.mlstatic.com https://*.mercadolibre.com https://www.facebook.com https://connect.facebook.net; frame-src https://*.mercadopago.com https://*.mercadopago.com.br https://*.mlstatic.com; font-src 'self' data: https://*.mlstatic.com; base-uri 'self'; form-action 'self'");
    if(!empty($_SESSION['od_transparent_attempt']['uncertain'])){h('Pagamento em verificação');echo '<p>Se você iniciou um pagamento, aguarde a confirmação ou fale com o suporte antes de tentar novamente.</p>';foot();exit;}
    $previous=(string)($_SESSION['od_transparent_attempt']['order_code']??'');
    if($previous!==''){
        $existing=od_order_by_code($previous);
        if($existing){$existing=od_order_sync($existing);if($existing['status']==='pending')redirect('?page=pedido&code='.$previous);}
    }
    $attempt=bin2hex(random_bytes(24));$_SESSION['od_transparent_attempt']=['key'=>$attempt];
    h('Finalize sua compra',false);
    echo '<style>.odtc{max-width:920px;margin:28px auto;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.58fr);gap:20px}.odtc-panel{background:#fff;border:1px solid #d6e4db;border-radius:18px;padding:24px;min-width:0}.odtc h2{margin-top:0}.odtc input[type=email]{width:100%;padding:14px;border:1px solid #bacfc4;border-radius:10px}.odtc label{display:block;margin:12px 0}.odtc-total{font-size:30px;font-weight:800}.odtc-note{font-size:13px;color:#526e61}.odtc-methods{display:flex;gap:10px;margin:18px 0}.odtc-methods button{flex:1}.odtc button{cursor:pointer}.odtc button[disabled]{opacity:.6;cursor:wait}.odtc-qr{max-width:210px;width:100%;display:block;margin:15px auto}.odtc textarea{width:100%;height:75px;word-break:break-all}.odtc-error{color:#a22929;margin:12px 0}@media(max-width:720px){.odtc{grid-template-columns:1fr}.odtc-panel{padding:18px}.odtc-summary{order:-1}}</style>';
    echo '<div class="odtc"><section class="odtc-panel"><h2>Seus dados</h2><label>E-mail para receber o acesso<input id="odtc-email" type="email" autocomplete="email" required maxlength="180" placeholder="voce@consultorio.com.br"></label><label><input type="checkbox" id="odtc-addon"> Adicionar Autoinspeção 360 por R$ 37 (opcional)</label><h2>Forma de pagamento</h2><div class="odtc-methods"><button class="btn" type="button" id="odtc-pix">Pix</button><button class="btn" type="button" id="odtc-card">Cartão</button></div><div id="odtc-error" class="odtc-error" role="alert"></div><div id="odtc-cardbrick"></div><div id="odtc-result" aria-live="polite"></div><p class="odtc-note">Ao pagar, você concorda com os <a href="?page=termos">Termos de uso</a> e a <a href="?page=politica-privacidade">Política de privacidade</a>.</p></section><aside class="odtc-panel odtc-summary"><h2>Odonto em Dia</h2><p>Kit documental, editor e videoaulas</p><p id="odtc-total" class="odtc-total">R$ 67,00</p><p>Pagamento único · sem renovação automática</p><p class="odtc-note">12 meses de acesso online. Pagamento processado pelo Mercado Pago.</p></aside></div>';
    echo '<script src="https://sdk.mercadopago.com/js/v2"></script><script>window.odtcConfig='.json_encode(['publicKey'=>od_transparent_key(),'csrf'=>csrf(),'attempt'=>$attempt],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';</script>';
    echo <<<'HTML'
<script>
(()=>{const c=window.odtcConfig,email=document.getElementById('odtc-email'),addon=document.getElementById('odtc-addon'),pix=document.getElementById('odtc-pix'),card=document.getElementById('odtc-card'),err=document.getElementById('odtc-error'),result=document.getElementById('odtc-result');let brick=null,busy=false,finished=false,rendering=false;
const amount=()=>addon.checked?104:67;
function controls(){pix.disabled=card.disabled=addon.disabled=email.disabled=busy||finished||rendering}
function message(t){err.textContent=t||''}
async function closeBrick(){if(brick){await brick.unmount();brick=null}}
async function submit(kind,extra={}){if(busy||finished)throw Error('Pagamento em andamento.');if(!email.reportValidity())throw Error('Informe seu e-mail.');busy=true;controls();message('');
try{const body=new URLSearchParams({csrf:c.csrf,act:'transparent_submit',attempt:c.attempt,email:email.value,addon:addon.checked?'1':'0',kind,...extra});
const res=await fetch('?page=checkout',{method:'POST',body,credentials:'same-origin'});const d=await res.json();
if(!res.ok){if(d.new_attempt)c.attempt=d.new_attempt;else if(res.status>=500||res.status===409)finished=true;throw Error(d.error||'Não foi possível confirmar o pagamento.');}
finished=true;
if(d.qr_code&&d.qr_image){await closeBrick();result.replaceChildren();const h=document.createElement('h2');h.textContent='Pague com Pix';const im=document.createElement('img');im.className='odtc-qr';im.alt='QR Code Pix';im.src='data:image/png;base64,'+d.qr_image;const t=document.createElement('textarea');t.readOnly=true;t.value=d.qr_code;const copy=document.createElement('button');copy.type='button';copy.className='btn';copy.textContent='Copiar código Pix';copy.onclick=async()=>{try{await navigator.clipboard.writeText(t.value);copy.textContent='Código copiado'}catch(e){t.select()}};const link=document.createElement('a');link.href=d.order_url;link.textContent='Verificar pagamento';result.append(h,im,t,copy,document.createElement('br'),link);
const u=new URL(d.order_url,location.href);let polls=0;const timer=setInterval(async()=>{if(++polls>120){clearInterval(timer);return}try{const p=await fetch('?page=checkout&odtc_status=1&code='+encodeURIComponent(u.searchParams.get('code')),{cache:'no-store'});if(p.ok){const j=await p.json();if(j.status==='paid'||j.status==='canceled'){clearInterval(timer);location.assign(d.order_url)}}}catch(e){}},15000);
}else location.assign(d.order_url);
}catch(e){message(e.message);if(e instanceof TypeError){finished=true;message('Conexão interrompida. Verifique o pagamento antes de tentar novamente.')}throw e}finally{busy=false;controls()}}
pix.onclick=async()=>{try{await closeBrick();await submit('pix')}catch(e){message(e.message)}};
async function render(){if(busy||finished||rendering)return;if(!email.reportValidity())return;rendering=true;controls();message('');try{await closeBrick();if(!window.MercadoPago)throw Error('Cartão indisponível. Você pode pagar por Pix.');const mp=new MercadoPago(c.publicKey,{locale:'pt-BR'});brick=await mp.bricks().create('cardPayment','odtc-cardbrick',{initialization:{amount:amount(),payer:{email:email.value}},callbacks:{onReady:()=>{},onError:()=>message('Confira os dados do cartão ou escolha Pix.'),onSubmit:(f,a)=>submit('card',{card_token:f.token||'',method:f.payment_method_id||'',payment_type:a?.paymentTypeId||'credit_card',installments:String(f.installments||1),doc_type:f.payer?.identification?.type||'',doc_number:f.payer?.identification?.number||''})}})}catch(e){message(e.message)}finally{rendering=false;controls()}}
card.onclick=render;addon.onchange=()=>{document.getElementById('odtc-total').textContent=amount()===104?'R$ 104,00':'R$ 67,00';if(brick)render()};email.onchange=()=>{if(brick)render()};
})();
</script>
HTML;
    foot();exit;
}
