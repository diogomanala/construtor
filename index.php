<?php
declare(strict_types=1);
session_start(['cookie_httponly'=>true, 'cookie_samesite'=>'Lax', 'use_strict_mode'=>true]);
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');
$errors=[]; $ok=false;
function e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function c($s,$max):string{return mb_substr(trim(preg_replace('/[\x00-\x1F\x7F]/u',' ',(string)$s)??''),0,$max);}
function allow_hit($id,$max,$seconds):bool {
  $dir=sys_get_temp_dir().'/cic_partnership_limits';
  if(!is_dir($dir) && !@mkdir($dir,0700,true) && !is_dir($dir)) return false;
  $f=@fopen($dir.'/'.hash('sha256',$id).'.json','c+');if(!$f)return false;
  if(!flock($f,LOCK_EX)){fclose($f);return false;}
  $list=json_decode(stream_get_contents($f)?:'[]',true); $now=time();
  $list=array_values(array_filter(is_array($list)?$list:[],fn($t)=>is_numeric($t)&&(int)$t>$now-$seconds));
  $allowed=count($list)<$max;if($allowed)$list[]=$now;
  ftruncate($f,0);rewind($f);fwrite($f,json_encode($list));fflush($f);flock($f,LOCK_UN);fclose($f);return $allowed;
}
$name=$email=$company=$subject=$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=c($_POST['name']??'',100);$email=c($_POST['email']??'',150);
  $company=c($_POST['company']??'',150);$subject=c($_POST['subject']??'',70);
  $message=trim((string)($_POST['message']??''));
  $token=(string)($_POST['token']??'');
  $origin=(string)($_SERVER['HTTP_ORIGIN']??'');
  $host=(string)($_SERVER['HTTP_HOST']??'');
  $expected=(string)($_SESSION['ptoken']??'');
  if($origin && parse_url($origin,PHP_URL_HOST)!==explode(':',$host)[0])$errors[]='Origem inválida.';
  if(!$expected||!hash_equals($expected,$token)||time()-(int)($_SESSION['pstart']??0)<4||time()-(int)($_SESSION['pstart']??0)>3600||trim((string)($_POST['company_url_verify']??''))!=='')$errors[]='Validação expirada. Atualize a página e tente novamente.';
  if((string)($_POST['challenge']??'')!==(string)($_SESSION['panswer']??''))$errors[]='Resposta de segurança incorreta.';
  if(mb_strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($message)<30||mb_strlen($message)>2500)$errors[]='Informe nome, e-mail válido e uma proposta de 30 a 2.500 caracteres.';
  if(!in_array($subject,['Publicidade','Parceria','Outro'],true))$errors[]='Escolha o assunto.';
  if(!$errors&&!allow_hit('ip:'.($_SERVER['REMOTE_ADDR']??''),2,3600))$errors[]='Limite de envios atingido. Tente mais tarde.';
  if(!$errors&&!allow_hit('global',40,3600))$errors[]='Muitas mensagens no momento. Tente mais tarde.';
  if(!$errors){
    $to=hex2bin('746974656377656240676d61696c2e636f6d');
    $body="Nova proposta de anúncios e parcerias\n\nNome: $name\nE-mail: $email\nEmpresa/site: $company\nAssunto: $subject\n\nProposta:\n".str_replace(["\r","\0"],'',$message)."\n";
    $headers=['From: Cortar Imagens Carrossel <no-reply@cortarimagenscarrossel.online>','Reply-To: '.$email,'Content-Type: text/plain; charset=UTF-8','MIME-Version: 1.0'];
    $ok=@mail($to,'[Carrossel] Nova proposta: '.$subject,$body,implode("\r\n",$headers));
    if(!$ok)$errors[]='Não foi possível enviar agora. Tente mais tarde.';
  }
  unset($_SESSION['ptoken'],$_SESSION['pstart'],$_SESSION['panswer']);
}
if(!$ok){$_SESSION['ptoken']=bin2hex(random_bytes(20));$_SESSION['pstart']=time();$a=random_int(2,8);$b=random_int(2,8);$_SESSION['panswer']=$a+$b;}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,follow"><title>Anuncie ou seja parceiro | Cortar Imagens Carrossel</title>
<style>*{box-sizing:border-box}body{margin:0;background:#f7f9fc;color:#17233b;font-family:system-ui,-apple-system,Segoe UI,Arial,sans-serif;line-height:1.5}header{background:#fff;border-bottom:1px solid #e5eaf2;padding:20px max(20px,calc((100vw - 630px)/2))}header a{color:#2052ba;text-decoration:none;font-weight:600;font-size:14px}.card{max-width:630px;margin:36px auto 60px;padding:32px;background:#fff;border:1px solid #e4e9f1;border-radius:18px;box-shadow:0 8px 30px #18294309}h1{font-size:clamp(25px,5vw,34px);letter-spacing:-.035em;margin:0 0 10px}p{color:#5f6e82;margin:0 0 27px}label{display:block;font-weight:600;font-size:13px;margin:0 0 7px}input,select,textarea{display:block;width:100%;margin:0 0 19px;border:1px solid #cbd4e2;border-radius:10px;padding:13px 14px;background:#fff;color:#17233b;font:inherit;outline:0}input:focus,select:focus,textarea:focus{border-color:#2563eb;box-shadow:0 0 0 3px #e1edff}textarea{min-height:145px;resize:vertical}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.grid>*{min-width:0}button{display:block;width:100%;background:#1d58d6;color:white;font:inherit;font-weight:700;border:0;border-radius:10px;padding:14px;cursor:pointer}button:hover{background:#184cba}.alert{padding:13px 15px;border-radius:10px;margin:0 0 20px}.bad{background:#fff0f0;color:#9b1b1b}.good{background:#effdf5;color:#17633b}.hidden{position:absolute;left:-99999px;width:1px;height:1px;overflow:hidden}.small{font-size:12px;margin:0 0 16px}footer{text-align:center;color:#718096;font-size:12px;padding-bottom:30px}@media(max-width:690px){.card{margin:20px 14px 45px;padding:23px}.grid{display:block}header{padding:17px 20px}}</style></head>
<body><header><a href="/">← Voltar ao Cortar Imagens Carrossel</a></header><main class="card"><h1>Anúncios e parcerias</h1><p>Quer anunciar sua marca ou propor uma parceria? Conte o que você tem em mente. Analisamos as propostas relacionadas ao público da ferramenta.</p>
<?php if($ok): ?><div class="alert good" role="status">Proposta enviada. Obrigado! Vamos avaliar sua mensagem.</div><a href="/">Voltar ao site</a>
<?php else: ?>
<?php if($errors): ?><div class="alert bad" role="alert"><?=e(implode(' ',$errors))?></div><?php endif;?>
<form method="post" action="/parcerias/index.php" accept-charset="UTF-8"><input type="hidden" name="token" value="<?=e($_SESSION['ptoken'])?>"><div class="hidden" aria-hidden="true"><label for="company_url_verify">Deixe vazio</label><input id="company_url_verify" name="company_url_verify" tabindex="-1" autocomplete="off"></div>
<div class="grid"><div><label for="name">Seu nome *</label><input id="name" name="name" required maxlength="100" autocomplete="name" value="<?=e($name)?>"></div><div><label for="email">Seu e-mail *</label><input id="email" name="email" required type="email" maxlength="150" autocomplete="email" value="<?=e($email)?>"></div></div>
<label for="company">Empresa ou site</label><input id="company" name="company" maxlength="150" placeholder="Nome da marca ou endereço do site" value="<?=e($company)?>">
<label for="subject">Assunto *</label><select id="subject" name="subject" required><option value="">Selecione</option><?php foreach(['Publicidade','Parceria','Outro'] as $option):?><option value="<?=e($option)?>" <?=$subject===$option?'selected':''?>><?=e($option)?></option><?php endforeach;?></select>
<label for="message">Sua proposta *</label><textarea id="message" name="message" required minlength="30" maxlength="2500" placeholder="Explique o que você gostaria de divulgar ou propor."><?=e($message)?></textarea>
<label for="challenge">Verificação: quanto é <?=$a?> + <?=$b?>? *</label><input id="challenge" name="challenge" type="number" min="0" max="30" inputmode="numeric" required><p class="small">Os dados informados serão utilizados apenas para responder à proposta.</p><button type="submit">Enviar proposta</button></form>
<?php endif;?></main><footer>© <?=date('Y')?> Cortar Imagens Carrossel Online</footer></body></html>