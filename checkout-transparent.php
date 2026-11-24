<?php
/** Checkout Transparente via Orders API. Explicit preview until validated. */
function od_transparent_key():string {
    od_mp_config();
    return defined('OD_MP_PUBLIC_KEY') ? (string)OD_MP_PUBLIC_KEY : (string)(getenv('OD_MP_PUBLIC_KEY')?:'APP_USR-97e93f42-7702-4ffd-ad0b-473f5829e453');
}
function od_transparent_enabled():bool {
    od_mp_config();
    $flag=defined('OD_MP_TRANSPARENT_ENABLED') ? (string)OD_MP_TRANSPARENT_ENABLED : (string)getenv('OD_MP_TRANSPARENT_ENABLED');
    return $flag!=='0' && od_mp_ready() && strlen(od_transparent_key())>20;
}
function od_transparent_json(array $data,int $status=200):never {
    http_response_code($status);header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;
}
function od_transparent_valid_cpf(string $cpf):bool {
    if(!preg_match('/^\d{11}$/D',$cpf)||preg_match('/^(\d)\1{10}$/D',$cpf))return false;
    for($t=9;$t<11;$t++){ $sum=0; for($i=0;$i<$t;$i++)$sum+=(int)$cpf[$i]*(($t+1)-$i); $d=(10*$sum)%11; if($d===10)$d=0; if($d!==(int)$cpf[$t])return false; }
    return true;
}
function od_transparent_submit():never {
    if(!od_transparent_enabled())od_transparent_json(['error'=>'Pagamento indisponível.'],503);
    $attempt=(string)($_POST['attempt']??'');
    $state=$_SESSION['od_transparent_attempt']??[];
    if(empty($state['preview']))od_transparent_json(['error'=>'Inicie o pagamento pelo checkout.'],403);
    if(!isset($state['key'])||!hash_equals($state['key'],$attempt))od_transparent_json(['error'=>'Sessão expirada. Atualize a página.'],409);
    if(!empty($state['order_code']))od_transparent_json(['order_url'=>'?page=pedido&code='.$state['order_code']]);
    if(!empty($state['uncertain']))od_transparent_json(['error'=>'A tentativa anterior ainda está sendo verificada. Não faça outro pagamento. Entre em contato com o suporte.'],409);
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>180)od_transparent_json(['error'=>'Informe um e-mail válido.'],422);
    $name=trim(preg_replace('/\s+/u',' ',(string)($_POST['buyer_name']??''))??'');
    $cpf=preg_replace('/\D/','',(string)($_POST['buyer_cpf']??''));
    $phone=preg_replace('/\D/','',(string)($_POST['buyer_phone']??''));
    if(strlen($name)>180||!preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\s\x{0027}\x{2019}-]+$/u',$name)||count(explode(' ',$name))<2)od_transparent_json(['error'=>'Informe seu nome completo.'],422);
    if(!od_transparent_valid_cpf($cpf))od_transparent_json(['error'=>'Informe um CPF válido.'],422);
    if(strlen($phone)>=12&&str_starts_with($phone,'55'))$phone=substr($phone,2);
    if(!preg_match('/^[1-9][0-9][2-9][0-9]{7,8}$/',$phone))od_transparent_json(['error'=>'Informe seu WhatsApp com DDD.'],422);
    $parts=explode(' ',$name,2);
    $addon=($_POST['addon']??'')==='1'?1:0;
    $amount=6700+($addon?3700:0);$value=number_format($amount/100,2,'.','');
    $kind=(string)($_POST['kind']??'');$payer=['email'=>$email,'first_name'=>$parts[0],'last_name'=>$parts[1],'identification'=>['type'=>'CPF','number'=>$cpf]];
    if($kind==='pix'){
        $payment=['amount'=>$value,'payment_method'=>['id'=>'pix','type'=>'bank_transfer'],'expiration_time'=>'PT30M'];
    } elseif($kind==='card'){
        $token=(string)($_POST['card_token']??'');$method=(string)($_POST['method']??'');$type=(string)($_POST['payment_type']??'');$installments=filter_var($_POST['installments']??null,FILTER_VALIDATE_INT);
        if(!preg_match('/^[A-Za-z0-9_-]{12,256}$/',$token)||!preg_match('/^[a-zA-Z0-9_]{2,40}$/',$method)||!in_array($type,['credit_card','debit_card'],true)||$installments===false||$installments<1||$installments>12)od_transparent_json(['error'=>'Confira os dados do cartão.'],422);
        $docType=(string)($_POST['doc_type']??'');$doc=preg_replace('/\D/','',(string)($_POST['doc_number']??''));
        if($docType==='CPF'&&od_transparent_valid_cpf($doc))$payer['identification']=['type'=>'CPF','number'=>$doc];
        $payment=['amount'=>$value,'payment_method'=>['id'=>$method,'type'=>$type,'token'=>$token,'installments'=>$installments]];
    } else od_transparent_json(['error'=>'Escolha Pix ou cartão.'],422);
    // Do not trust totals supplied by the browser. Do not store raw card data or token.
    $id=od_order_code();$code=bin2hex(random_bytes(24));
    $p=od_orders_db();
    $p->exec('CREATE TABLE IF NOT EXISTS od_order_contacts (order_id TEXT PRIMARY KEY, buyer_name TEXT NOT NULL, buyer_phone TEXT NOT NULL, created_at TEXT DEFAULT CURRENT_TIMESTAMP)');
    $p->beginTransaction();
    try{
        $p->prepare('INSERT INTO od_orders(id,public_code,buyer_email,addon,amount_cents) VALUES(?,?,?,?,?)')->execute([$id,$code,$email,$addon,$amount]);
        $p->prepare('INSERT INTO od_order_contacts(order_id,buyer_name,buyer_phone) VALUES(?,?,?)')->execute([$id,$name,$phone]);
        $p->commit();
    }catch(Throwable $e){if($p->inTransaction())$p->rollBack();od_transparent_json(['error'=>'Não foi possível registrar o pedido. Tente novamente.'],500);}
    od_meta_capture_checkout($id);
    $_SESSION['od_transparent_attempt']['uncertain']=true;
    $payload=['type'=>'online','processing_mode'=>'automatic','total_amount'=>$value,'external_reference'=>$id,'description'=>'Odonto em Dia','payer'=>$payer,'transactions'=>['payments'=>[$payment]]];
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
    if($kind==='pix'&&$out['status']==='pending'&&isset($pm['qr_code'],$pm['qr_code_base64'])&&strlen($pm['qr_code'])<=4096&&strlen($pm['qr_code_base64'])<=500000){
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
        if($existing){$existing=od_order_sync($existing);if(in_array($existing['status'],['pending','paid'],true))redirect('?page=pedido&code='.$previous);}
    }
    $attempt=bin2hex(random_bytes(24));$_SESSION['od_transparent_attempt']=['key'=>$attempt,'preview'=>true];
    h('Finalize sua compra',false);
    echo '<style>
.odtc{--green:#167764;--dark:#17382e;--muted:#61746b;max-width:980px;margin:26px auto 44px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.64fr);gap:24px;align-items:start;color:var(--dark)}.odtc-panel{background:#fff;border:1px solid #dce8e1;border-radius:18px;padding:25px;min-width:0;box-shadow:0 8px 24px #193b2f08}.odtc h2{font-size:21px;line-height:1.25;margin:0 0 14px}.odtc-kicker{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.11em;color:var(--green);margin-bottom:8px}.odtc input[type=email]{box-sizing:border-box;width:100%;padding:14px;border:1px solid #b8cec2;border-radius:10px;font-size:16px;background:#fff;color:#193b2f}.odtc-field{display:block;margin:0 0 20px;font-size:14px;font-weight:650}.odtc-field input{display:block;margin-top:8px}.odtc-note{font-size:13px;color:var(--muted);line-height:1.55}.odtc-addon{border:1px solid #a7c9b6;border-radius:14px;background:#f4faf6;margin:0 0 24px;overflow:hidden}.odtc-addon-head{padding:12px 15px;display:flex;align-items:center;gap:10px;background:#e1f1e7;color:#205c41;font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.odtc-addon-body{display:grid;grid-template-columns:54px minmax(0,1fr);gap:14px;padding:16px}.odtc-addon-icon{height:60px;border-radius:10px;background:#fff;border:1px solid #bedac9;display:grid;place-items:center;color:#167764}.odtc-addon h3{font-size:17px;line-height:1.25;margin:0 0 5px}.odtc-addon p{font-size:13px;line-height:1.5;margin:0;color:#456453}.odtc-features{list-style:none;padding:0;margin:10px 0 0;font-size:13px;line-height:1.65}.odtc-features li{display:flex;gap:7px;align-items:baseline}.odtc-features li:before{content:"✓";color:#167764;font-weight:800}.odtc-add-choice{display:flex;align-items:center;gap:10px;border-top:1px solid #c7e1d1;padding:13px 15px;background:#fff;cursor:pointer;font-size:14px;font-weight:700}.odtc-add-choice input{flex:0 0 auto;width:20px;height:20px;accent-color:#167764;margin:0}.odtc-add-choice strong{margin-left:auto;white-space:nowrap}.odtc-addon:has(input:checked){outline:2px solid #167764;outline-offset:-2px}.odtc-methods{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:14px 0}.odtc-method{display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid #cbdcd2;border-radius:11px;background:white;color:#214b3b;padding:14px 7px;min-height:52px;font-weight:750;font-size:15px;cursor:pointer}.odtc-method[aria-pressed=true]{border-color:#167764;box-shadow:inset 0 0 0 1px #167764;background:#edf8f0}.odtc-method svg,.odtc-lock svg{width:20px;height:20px;flex-shrink:0}.odtc button[disabled]{opacity:.6;cursor:wait}.odtc-qr{max-width:210px;width:100%;display:block;margin:15px auto}.odtc textarea{width:100%;height:75px;word-break:break-all}.odtc-error{color:#a22929;font-size:14px;margin:12px 0}.odtc-summary{position:sticky;top:16px}.odtc-product{display:flex;gap:12px;align-items:center;padding:0 0 17px;border-bottom:1px solid #e4ebe6}.odtc-product img{width:45px;height:45px;border-radius:9px;background:#e5f2e9;padding:6px}.odtc-product strong{display:block;font-size:16px}.odtc-product small{display:block;font-size:12px;color:var(--muted);margin-top:3px}.odtc-line{display:flex;gap:14px;justify-content:space-between;font-size:14px;margin:13px 0}.odtc-line b{white-space:nowrap}.odtc-price{border-top:1px solid #e4ebe6;padding-top:15px;margin-top:17px;display:flex;justify-content:space-between;align-items:center}.odtc-total{font-size:28px;font-weight:800;color:var(--dark)}.odtc-lock{display:flex;align-items:center;gap:8px;font-size:12px;color:#527261;margin:16px 0 0}.odtc-pixaction{width:100%;margin:8px 0 0}.odtc-brick-status{font-size:13px;color:#61746b}.odtc-proof{display:grid;gap:11px;padding:15px 0 0;margin:14px 0 0;border-top:1px solid #e4ebe6}.odtc-proof-item{display:grid;grid-template-columns:26px minmax(0,1fr);gap:9px;align-items:start}.odtc-proof-icon{width:25px;height:25px;background:#e9f5ed;color:#167764;display:grid;place-items:center;border-radius:7px;font-weight:800}.odtc-proof-item strong{display:block;font-size:13px;color:#25483a;line-height:1.3}.odtc-proof-item small{display:block;font-size:12px;color:#61746b;line-height:1.4;margin-top:2px}.odtc-field small{display:block;font-size:12px;font-weight:400;color:#61746b;margin-top:6px}.odtc-paytrust{font-size:12px;text-align:center;color:#61746b;line-height:1.4;margin:10px 0 0}.odtc-fields{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}.odtc-fields .wide{grid-column:1/-1}.odtc input[type=text],.odtc input[type=tel]{box-sizing:border-box;width:100%;padding:14px;border:1px solid #b8cec2;border-radius:10px;font-size:16px;background:#fff;color:#193b2f}.odtc-fields .odtc-field{margin-bottom:14px}@media(max-width:480px){.odtc-fields{grid-template-columns:1fr}.odtc-fields .wide{grid-column:auto}}.odtc [hidden]{display:none!important}@media(max-width:720px){.odtc{grid-template-columns:1fr;gap:13px;margin:12px auto 28px}.odtc-panel{padding:17px}.odtc-summary{position:static;order:-1}.odtc-summary .odtc-features{display:none}.odtc-product{padding-bottom:10px}.odtc-price{margin-top:10px;padding-top:10px}.odtc-total{font-size:25px}.odtc-addon-body{grid-template-columns:42px minmax(0,1fr);gap:10px;padding:12px}.odtc-addon-icon{height:49px}.odtc-add-choice{padding:12px;font-size:13px}.odtc h2{font-size:19px}}</style>';
    echo '<div class="odtc"><section class="odtc-panel"><div class="odtc-kicker">Finalizar compra</div><h2>Seus dados</h2><div class="odtc-fields"><label class="odtc-field wide">Nome completo<input id="odtc-name" type="text" autocomplete="name" required maxlength="180" placeholder="Seu nome e sobrenome"></label><label class="odtc-field wide">E-mail<input id="odtc-email" type="email" autocomplete="email" required maxlength="180" placeholder="voce@consultorio.com.br"><small>É neste e-mail que você receberá seu acesso.</small></label><label class="odtc-field">CPF<input id="odtc-cpf" type="text" inputmode="numeric" autocomplete="off" required maxlength="14" placeholder="000.000.000-00"></label><label class="odtc-field">WhatsApp com DDD<input id="odtc-phone" type="tel" inputmode="tel" autocomplete="tel-national" required maxlength="19" placeholder="(21) 99999-9999"></label></div><div class="odtc-addon"><div class="odtc-addon-head">Complemento opcional para sua clínica</div><div class="odtc-addon-body"><div class="odtc-addon-icon" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M8 10l1 1 2-2m2 1h3M8 15l1 1 2-2m2 1h3"/></svg></div><div><h3>Autoinspeção 360</h3><p>Confira os pontos da sua clínica que precisam de atenção e organize as próximas ações.</p><ul class="odtc-features"><li>45 verificações guiadas</li><li>Plano de ação para pendências</li><li>Relatório A4 para imprimir</li></ul></div></div><label class="odtc-add-choice"><input type="checkbox" id="odtc-addon"><span>Sim, adicionar à minha compra</span><strong>+ R$ 37,00</strong></label></div><h2>Forma de pagamento</h2><div class="odtc-methods"><button class="odtc-method" type="button" id="odtc-pix" aria-pressed="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 10l6-6a4 4 0 0 1 6 0l6 6-6 6a4 4 0 0 1-6 0z"/><path d="M3 14l6 6a4 4 0 0 0 6 0l6-6M7 10l5 5 5-5"/></svg>Pix</button><button class="odtc-method" type="button" id="odtc-card" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20M6 15h4"/></svg>Cartão</button></div><div id="odtc-pixinfo"><p class="odtc-note">Pague com QR Code ou copie o código Pix no aplicativo do seu banco. O acesso é liberado após a confirmação.</p><button class="btn odtc-pixaction" type="button" id="odtc-generate">Gerar Pix e finalizar compra</button><p class="odtc-paytrust">Pagamento único · Sem mensalidade · 7 dias para desistência</p></div><div id="odtc-error" class="odtc-error" role="alert"></div><p id="odtc-brick-status" class="odtc-brick-status" role="status" hidden></p><div id="odtc-cardbrick"></div><div id="odtc-result" aria-live="polite"></div><p class="odtc-note">Ao pagar, você concorda com os <a href="?page=termos">Termos de uso</a> e declara ter lido a <a href="?page=politica-privacidade">Política de privacidade</a>.</p></section><aside class="odtc-panel odtc-summary"><div class="odtc-kicker">Resumo do pedido</div><div class="odtc-product"><img src="assets/favicon.svg?v=9" alt=""><div><strong>Odonto em Dia</strong><small>Kit documental e plataforma</small></div></div><ul class="odtc-features"><li>30 materiais de referência</li><li>Editor online e videoaulas</li><li>Pasta A4 com sua identidade visual</li></ul><div class="odtc-line"><span>Kit principal</span><b>R$ 67,00</b></div><div id="odtc-addonline" class="odtc-line" hidden><span>Autoinspeção 360</span><b>R$ 37,00</b></div><div class="odtc-price"><strong>Total</strong><span id="odtc-total" class="odtc-total">R$ 67,00</span></div><p class="odtc-note">Pagamento único, sem renovação automática. 12 meses de acesso online.</p><div class="odtc-lock"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4"/></svg>Pagamento processado pelo Mercado Pago</div><div class="odtc-proof"><div class="odtc-proof-item"><span class="odtc-proof-icon" aria-hidden="true">✓</span><div><strong>7 dias para desistência</strong><small>Direito de arrependimento conforme o CDC.</small></div></div><div class="odtc-proof-item"><span class="odtc-proof-icon" aria-hidden="true">✉</span><div><strong>Acesso enviado por e-mail</strong><small>Após a confirmação do pagamento.</small></div></div><div class="odtc-proof-item"><span class="odtc-proof-icon" aria-hidden="true">✓</span><div><strong>Compra única</strong><small>Sem cobranças recorrentes.</small></div></div></div></aside></div>';
    echo '<script src="https://sdk.mercadopago.com/js/v2"></script><script>window.odtcConfig='.json_encode(['publicKey'=>od_transparent_key(),'csrf'=>csrf(),'attempt'=>$attempt],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';</script>';
    echo <<<'HTML'
<script>
(()=>{const c=window.odtcConfig,email=document.getElementById('odtc-email'),addon=document.getElementById('odtc-addon'),pix=document.getElementById('odtc-pix'),card=document.getElementById('odtc-card'),err=document.getElementById('odtc-error'),result=document.getElementById('odtc-result');const name=document.getElementById('odtc-name'),cpf=document.getElementById('odtc-cpf'),phone=document.getElementById('odtc-phone');const generate=document.getElementById('odtc-generate'),pixInfo=document.getElementById('odtc-pixinfo'),brickStatus=document.getElementById('odtc-brick-status');let brick=null,busy=false,finished=false,rendering=false,selected='pix';
const amount=()=>addon.checked?104:67;
function controls(){pix.disabled=card.disabled=addon.disabled=email.disabled=name.disabled=cpf.disabled=phone.disabled=generate.disabled=busy||finished||rendering}
function message(t){err.textContent=t||''}
const digits=x=>x.replace(/\D/g,'');cpf.addEventListener('input',()=>{const v=digits(cpf.value).slice(0,11);cpf.value=v.replace(/^(\d{3})(\d)/,'$1.$2').replace(/^(\d{3}\.\d{3})(\d)/,'$1.$2').replace(/\.(\d{3})(\d{1,2})$/,'.$1-$2')});
function validData(){for(const el of [name,email,cpf,phone])if(!el.reportValidity())return false;const n=name.value.trim().split(/\s+/);if(n.length<2){message('Informe seu nome completo.');name.focus();return false}const c=digits(cpf.value);if(c.length!==11||/^(\d)\1{10}$/.test(c)){message('Confira o CPF.');cpf.focus();return false}for(let t=9;t<11;t++){let sum=0;for(let i=0;i<t;i++)sum+=Number(c[i])*(t+1-i);let d=(10*sum)%11;if(d===10)d=0;if(d!==Number(c[t])){message('Informe um CPF válido.');cpf.focus();return false}}let p=digits(phone.value);if(p.length>=12&&p.startsWith('55'))p=p.slice(2);if(!/^[1-9][0-9][2-9][0-9]{7,8}$/.test(p)){message('Informe o WhatsApp com DDD.');phone.focus();return false}return true}
async function closeBrick(){if(brick){await brick.unmount();brick=null}}
async function submit(kind,extra={}){if(busy||finished)throw Error('Pagamento em andamento.');if(!validData())throw Error('Confira seus dados antes de continuar.');busy=true;controls();message('');
try{const body=new URLSearchParams({csrf:c.csrf,act:'transparent_submit',attempt:c.attempt,email:email.value,buyer_name:name.value,buyer_cpf:cpf.value,buyer_phone:phone.value,addon:addon.checked?'1':'0',kind,...extra});
const res=await fetch('?page=checkout',{method:'POST',body,credentials:'same-origin'});const d=await res.json();
if(!res.ok){if(d.new_attempt)c.attempt=d.new_attempt;else if(res.status>=500||res.status===409)finished=true;throw Error(d.error||'Não foi possível confirmar o pagamento.');}
finished=true;pixInfo.hidden=true;brickStatus.hidden=true;
if(d.qr_code&&d.qr_image){await closeBrick();result.replaceChildren();const h=document.createElement('h2');h.textContent='Pague com Pix';const im=document.createElement('img');im.className='odtc-qr';im.alt='QR Code Pix';im.src='data:image/png;base64,'+d.qr_image;const t=document.createElement('textarea');t.readOnly=true;t.value=d.qr_code;const copy=document.createElement('button');copy.type='button';copy.className='btn';copy.textContent='Copiar código Pix';copy.onclick=async()=>{try{await navigator.clipboard.writeText(t.value);copy.textContent='Código copiado'}catch(e){t.select()}};const link=document.createElement('a');link.href=d.order_url;link.textContent='Verificar pagamento';result.append(h,im,t,copy,document.createElement('br'),link);
const u=new URL(d.order_url,location.href);let polls=0;const timer=setInterval(async()=>{if(++polls>120){clearInterval(timer);return}try{const p=await fetch('?page=checkout&odtc_status=1&code='+encodeURIComponent(u.searchParams.get('code')),{cache:'no-store'});if(p.ok){const j=await p.json();if(j.status==='paid'||j.status==='canceled'){clearInterval(timer);location.assign(d.order_url)}}}catch(e){}},15000);
}else location.assign(d.order_url);
}catch(e){message(e.message);if(e instanceof TypeError){finished=true;message('Conexão interrompida. Verifique o pagamento antes de tentar novamente.')}throw e}finally{busy=false;controls()}}
pix.onclick=async()=>{selected='pix';pix.setAttribute('aria-pressed','true');card.setAttribute('aria-pressed','false');pixInfo.hidden=false;brickStatus.hidden=true;await closeBrick();message('')};generate.onclick=async()=>{try{await closeBrick();await submit('pix')}catch(e){message(e.message)}};
async function render(){if(busy||finished||rendering)return;if(!validData())return;selected='card';pix.setAttribute('aria-pressed','false');card.setAttribute('aria-pressed','true');pixInfo.hidden=true;brickStatus.hidden=false;brickStatus.textContent='Carregando formulário seguro...';rendering=true;controls();message('');try{await closeBrick();if(!window.MercadoPago)throw Error('Cartão indisponível. Você pode pagar por Pix.');const mp=new MercadoPago(c.publicKey,{locale:'pt-BR'});brick=await mp.bricks().create('cardPayment','odtc-cardbrick',{initialization:{amount:amount(),payer:{email:email.value,identification:{type:'CPF',number:digits(cpf.value)}}},callbacks:{onReady:()=>{brickStatus.hidden=true},onError:()=>{brickStatus.hidden=true;message('Não foi possível carregar ou validar os dados do cartão. Tente novamente ou escolha Pix.')},onSubmit:(f,a)=>submit('card',{card_token:f.token||'',method:f.payment_method_id||'',payment_type:a?.paymentTypeId||'',installments:String(f.installments||1),doc_type:f.payer?.identification?.type||'',doc_number:f.payer?.identification?.number||''})}})}catch(e){brickStatus.hidden=true;message(e.message)}finally{rendering=false;controls()}}
card.onclick=render;addon.onchange=()=>{document.getElementById('odtc-total').textContent=amount()===104?'R$ 104,00':'R$ 67,00';document.getElementById('odtc-addonline').hidden=!addon.checked;if(selected==='card')render()};email.onchange=()=>{if(selected==='card')render()};
})();
</script>
HTML;
    foot();exit;
}
