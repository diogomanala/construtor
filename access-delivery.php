<?php
/** Entrega de acesso transacional. Tokens de uso unico, nunca persistidos em texto puro. */
declare(strict_types=1);
const OD_ACCESS_URL='https://odontonegocios.com/rdc/';
function od_access_db():PDO{
    $db=od_orders_db();
    $db->exec("CREATE TABLE IF NOT EXISTS od_access_tokens (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id TEXT NOT NULL,
      token_hash TEXT NOT NULL UNIQUE,
      expires_at TEXT NOT NULL,
      consumed_at TEXT,
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec('CREATE INDEX IF NOT EXISTS od_access_by_order ON od_access_tokens(order_id,created_at)');
    $db->exec("CREATE TABLE IF NOT EXISTS od_access_delivery (
      order_id TEXT PRIMARY KEY,
      attempts INTEGER NOT NULL DEFAULT 0,
      status TEXT NOT NULL DEFAULT 'pending',
      last_attempt_at TEXT,
      sent_at TEXT,
      last_error TEXT DEFAULT ''
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS od_access_preferences (
      user_id INTEGER PRIMARY KEY,
      temp_password INTEGER NOT NULL DEFAULT 0,
      updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}
function od_access_config():array{
    // Segredos devem ficar fora do document root ou nas variaveis do servidor.
    od_mp_config();
    $privateMail=dirname(__DIR__,2).'/odonto-mail-env.php';
    if(is_file($privateMail))require_once $privateMail;
    return [
      'resend_key'=>defined('OD_RESEND_API_KEY')?(string)OD_RESEND_API_KEY:(string)(getenv('OD_RESEND_API_KEY')?:''),
      'from'=>defined('OD_ACCESS_FROM')?(string)OD_ACCESS_FROM:(string)(getenv('OD_ACCESS_FROM')?:'contato@odontonegocios.com'),
      'mail_fallback'=>defined('OD_ACCESS_MAIL_FALLBACK')?(bool)OD_ACCESS_MAIL_FALLBACK:true
    ];
}
function od_access_send(string $recipient,string $subject,string $body):bool{
    $cfg=od_access_config();
    if(!filter_var($recipient,FILTER_VALIDATE_EMAIL))return false;
    if(strlen($cfg['resend_key'])>20 && function_exists('curl_init')){
        $payload=['from'=>'Odonto em Dia <acesso@notificacoes.odontonegocios.com>','to'=>[$recipient],
                  'subject'=>$subject,'text'=>$body];
        $ch=curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),
          CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$cfg['resend_key'],'Content-Type: application/json'],
          CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>true]);
        $response=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
        if($status>=200&&$status<300)return true;
        error_log('[Odonto] Resend delivery rejected (HTTP '.$status.')');
    }
    if(!$cfg['mail_fallback'])return false;
    $safeFrom=filter_var($cfg['from'],FILTER_VALIDATE_EMAIL)?$cfg['from']:'contato@odontonegocios.com';
    $headers=['MIME-Version'=>'1.0','Content-Type'=>'text/plain; charset=UTF-8',
        'From'=>'Odonto em Dia <'.$safeFrom.'>'];
    try{return @mail($recipient,mb_encode_mimeheader($subject,'UTF-8','B',"\r\n"),$body,$headers,'-f'.$safeFrom);}
    catch(Throwable $e){error_log('[Odonto] PHP mail unavailable');return false;}
}
/**
 * Reserva um unico envio por pedido. Reenvios somente apos 15 minutos, no maximo
 * cinco tentativas automaticas. Senha temporaria individual apenas para contas novas.
 */
function od_access_deliver(array $order,bool $manual=false):bool{
    if(($order['status']??'')!=='paid'||empty($order['id'])||!filter_var($order['buyer_email']??'',FILTER_VALIDATE_EMAIL))return false;
    $db=od_access_db();$id=(string)$order['id'];
    try{
        $db->exec('BEGIN IMMEDIATE');
        $db->prepare("INSERT OR IGNORE INTO od_access_delivery(order_id,status) VALUES(?,'pending')")->execute([$id]);
        $q=$db->prepare('SELECT * FROM od_access_delivery WHERE order_id=?');$q->execute([$id]);$delivery=$q->fetch();
        $last=$delivery['last_attempt_at']?strtotime($delivery['last_attempt_at'].' UTC'):0;
        $cooldown=$manual?300:900;
        if((($delivery['status']==='sent')&&!$manual)||($last && time()-$last<$cooldown)||
            (int)$delivery['attempts']>=($manual?8:5)){
            $db->commit();return $delivery['status']==='sent';
        }
        $db->prepare("UPDATE od_access_delivery SET attempts=attempts+1,status='processing',last_attempt_at=CURRENT_TIMESTAMP WHERE order_id=?")->execute([$id]);
        // Para compras novas, cria um acesso imediato com senha individual aleatoria.
        // Compradores existentes preservam suas senhas; recebem apenas link de acesso.
        $q=$db->prepare('SELECT id FROM users WHERE email=?');
        $q->execute([(string)$order['buyer_email']]);
        $existing=$q->fetchColumn();
        $temporaryPassword=null;
        if(!$existing){
            $alphabet='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            $temporaryPassword='';
            for($i=0;$i<16;$i++)$temporaryPassword.=$alphabet[random_int(0,strlen($alphabet)-1)];
            $db->prepare('INSERT INTO users(email,password) VALUES(?,?)')->execute([
                (string)$order['buyer_email'],password_hash($temporaryPassword,PASSWORD_DEFAULT)
            ]);
            $uid=(int)$db->lastInsertId();
            $db->prepare("INSERT INTO od_access_preferences(user_id,temp_password) VALUES(?,1)")->execute([$uid]);
        }else{$uid=(int)$existing;}
        $db->prepare("INSERT INTO od_entitlements(user_id,order_id,addon,status,expires_at)
            VALUES(?,?,?,'active',datetime('now','+365 days'))
            ON CONFLICT(user_id) DO UPDATE SET order_id=excluded.order_id,
            addon=MAX(od_entitlements.addon,excluded.addon),status='active',expires_at=excluded.expires_at")
            ->execute([$uid,$id,(int)$order['addon']]);
        $db->prepare('UPDATE od_orders SET activated_user_id=? WHERE id=?')->execute([$uid,$id]);
        $raw=bin2hex(random_bytes(32));$hash=hash('sha256',$raw);
        $db->prepare('DELETE FROM od_access_tokens WHERE order_id=? AND consumed_at IS NULL')->execute([$id]);
        $db->prepare("INSERT INTO od_access_tokens(order_id,token_hash,expires_at) VALUES(?,?,datetime('now','+48 hours'))")->execute([$id,$hash]);
        $db->commit();
        $link=OD_ACCESS_URL.'?page=ativar-acesso&token='.$raw;
        $email=(string)$order['buyer_email'];
        $subject='Seu acesso ao Odonto em Dia';
        $body="Olá!\r\n\r\nSeu pagamento do Odonto em Dia foi confirmado. Seu acesso já está liberado.\r\n\r\n"
             ."ACESSAR SEM DIGITAR SENHA:\r\n".$link."\r\n\r\n"
             ."Esse link é individual, válido por 48 horas e de uso único.\r\n";
        if($temporaryPassword!==null){
            $body.="Se preferir entrar com e-mail e senha:\r\n"
                  ."E-mail: ".$email."\r\n"
                  ."Senha provisória: ".$temporaryPassword."\r\n\r\n"
                  ."Depois de entrar, você pode trocar a senha em Minha conta.\r\n";
        }else{
            $body.="Sua senha já cadastrada continua válida. Caso não lembre, use o link de acesso acima.\r\n";
        }
        $body.="\r\nÁrea de membros: ".OD_ACCESS_URL."?page=entrar\r\n"
             ."Se não reconhece esta compra, entre em contato pelo suporte oficial.\r\n\r\nEquipe Odonto em Dia";
        $ok=od_access_send($email,$subject,$body);
        $db->prepare("UPDATE od_access_delivery SET status=?,sent_at=CASE WHEN ?=1 THEN CURRENT_TIMESTAMP ELSE sent_at END,last_error=? WHERE order_id=?")
            ->execute([$ok?'sent':'failed',$ok?1:0,$ok?'':'send_failed',$id]);
        return $ok;
    }catch(Throwable $e){
        if($db->inTransaction())$db->rollBack();
        error_log('[Odonto] Falha ao preparar entrega do acesso: '.get_class($e));
        return false;
    }
}
function od_access_token_order(string $token):?array{
    if(!preg_match('/^[a-f0-9]{64}$/D',$token))return null;
    $db=od_access_db();
    $s=$db->prepare("SELECT o.*,t.id AS access_token_id FROM od_access_tokens t JOIN od_orders o ON o.id=t.order_id
      WHERE t.token_hash=? AND t.consumed_at IS NULL AND t.expires_at>CURRENT_TIMESTAMP AND o.status='paid' LIMIT 1");
    $s->execute([hash('sha256',$token)]);
    return $s->fetch()?:null;
}
function od_access_activate():never{
    $token=(string)($_POST['access_token']??'');
    $order=od_access_token_order($token);
    if(!$order){flash('Link expirado ou já utilizado. Solicite um novo acesso na página de entrada.');redirect('?page=entrar');}
    $order=od_order_sync($order);
    if($order['status']!=='paid'){flash('O pagamento ainda não está confirmado.');redirect('?page=entrar');}
    $password=$_POST['password']??null;
    if(!is_string($password)||strlen($password)<12||strlen($password)>256){
        flash('Escolha uma senha de 12 a 256 caracteres.');redirect('?page=ativar-acesso&token='.$token);
    }
    $db=od_access_db();$db->exec('BEGIN IMMEDIATE');
    try{
        $s=$db->prepare("SELECT id FROM od_access_tokens WHERE token_hash=? AND order_id=? AND consumed_at IS NULL AND expires_at>CURRENT_TIMESTAMP");
        $s->execute([hash('sha256',$token),$order['id']]);
        if(!$s->fetchColumn()){ $db->rollBack();flash('Este link já foi usado.');redirect('?page=entrar');}
        $q=$db->prepare('SELECT id FROM users WHERE email=?');$q->execute([$order['buyer_email']]);$existing=$q->fetchColumn();
        if($existing){
            $uid=(int)$existing;
            $db->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),$uid]);
        }else{
            $db->prepare('INSERT INTO users(email,password) VALUES(?,?)')->execute([$order['buyer_email'],password_hash($password,PASSWORD_DEFAULT)]);
            $uid=(int)$db->lastInsertId();
        }
        $db->prepare("INSERT INTO od_entitlements(user_id,order_id,addon,status,expires_at)
          VALUES(?,?,?,'active',datetime('now','+365 days'))
          ON CONFLICT(user_id) DO UPDATE SET order_id=excluded.order_id,addon=MAX(od_entitlements.addon,excluded.addon),status='active',expires_at=excluded.expires_at")
          ->execute([$uid,$order['id'],(int)$order['addon']]);
        $db->prepare('UPDATE od_orders SET activated_user_id=? WHERE id=?')->execute([$uid,$order['id']]);
        $db->prepare("INSERT INTO od_access_preferences(user_id,temp_password) VALUES(?,0)
            ON CONFLICT(user_id) DO UPDATE SET temp_password=0,updated_at=CURRENT_TIMESTAMP")->execute([$uid]);
        $db->prepare('UPDATE od_access_tokens SET consumed_at=CURRENT_TIMESTAMP WHERE order_id=? AND consumed_at IS NULL')->execute([$order['id']]);
        $db->commit();
        session_regenerate_id(true);$_SESSION['uid']=$uid;
        flash('Acesso ativado. Bem-vindo ao Odonto em Dia!');
        $c=clinic();redirect($c?'?page=painel':'?page=diagnostico');
    }catch(Throwable $e){
        if($db->inTransaction())$db->rollBack();error_log('[Odonto] Falha na ativacao com token');
        flash('Não foi possível ativar seu acesso. Tente novamente.');redirect('?page=ativar-acesso&token='.$token);
    }
}
function od_access_page():never{
    $token=(string)($_GET['token']??'');
    $order=od_access_token_order($token);
    header('Referrer-Policy: no-referrer');
    h('Acessar Odonto em Dia',false);
    echo '<div class="card" style="max-width:540px;margin:35px auto;padding:26px">';
    if(!$order){
        echo '<h2>Link inválido ou expirado</h2><p>Você pode solicitar um novo link usando o e-mail da compra.</p><a class="btn" href="?page=recuperar-acesso">Recuperar acesso</a>';
    }else{
        echo '<h2>Seu acesso está liberado!</h2><p>Pagamento confirmado. Clique abaixo para entrar sem precisar digitar uma senha.</p>'
          .'<form method="post">'.token().'<input type="hidden" name="act" value="access_login">'
          .'<input type="hidden" name="access_token" value="'.esc($token).'"><button type="submit">Entrar na minha plataforma</button></form>'
          .'<p style="font-size:12px;color:#648071;margin-top:15px">Você poderá trocar sua senha provisória depois de entrar. Este link é individual e válido por 48 horas.</p>';
    }
    echo '</div>';foot();exit;
}
function od_access_login():never{
    $token=(string)($_POST['access_token']??'');
    $order=od_access_token_order($token);
    if(!$order){flash('O link expirou ou já foi usado. Solicite outro.');redirect('?page=recuperar-acesso');}
    $order=od_order_sync($order);
    if($order['status']!=='paid'){flash('O pagamento não está confirmado.');redirect('?page=entrar');}
    $db=od_access_db();$db->exec('BEGIN IMMEDIATE');
    try{
        $q=$db->prepare("SELECT id FROM od_access_tokens WHERE order_id=? AND token_hash=?
          AND consumed_at IS NULL AND expires_at>CURRENT_TIMESTAMP");
        $q->execute([$order['id'],hash('sha256',$token)]);
        if(!$q->fetchColumn()){ $db->rollBack();redirect('?page=recuperar-acesso');}
        $uid=(int)($order['activated_user_id']??0);
        if(!$uid){$db->rollBack();redirect('?page=recuperar-acesso');}
        $q=$db->prepare('SELECT 1 FROM od_entitlements WHERE user_id=? AND status=? AND expires_at>=CURRENT_TIMESTAMP');
        $q->execute([$uid,'active']);
        if(!$q->fetchColumn()){ $db->rollBack();redirect('?page=recuperar-acesso');}
        $db->prepare('UPDATE od_access_tokens SET consumed_at=CURRENT_TIMESTAMP WHERE id=?')->execute([(int)$order['access_token_id']]);
        $db->commit();
        session_regenerate_id(true);$_SESSION['uid']=$uid;
        flash('Bem-vindo! Seu acesso está ativo.'.(od_access_has_temp_password($uid)?' Você pode trocar a senha provisória em Minha senha.':''));
        redirect(clinic()?'?page=painel':'?page=diagnostico');
    }catch(Throwable $e){
        if($db->inTransaction())$db->rollBack();
        error_log('[Odonto] Login por link falhou');
        flash('Não foi possível acessar pelo link. Solicite um novo.');redirect('?page=recuperar-acesso');
    }
}
function od_access_resend():never{
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(filter_var($email,FILTER_VALIDATE_EMAIL)&&strlen($email)<=180){
        $db=od_access_db();$s=$db->prepare("SELECT * FROM od_orders WHERE buyer_email=? AND status='paid' ORDER BY created_at DESC LIMIT 1");
        $s->execute([$email]);$order=$s->fetch();
        if($order)od_access_deliver($order,true);
    }
    flash('Se houver uma compra confirmada vinculada a esse e-mail, enviaremos um novo link. Confira também a pasta de spam.');
    redirect('?page=recuperar-acesso');
}
function od_access_recovery_page():never{
    h('Recuperar acesso',false);
    echo '<div class="card" style="max-width:550px;margin:35px auto;padding:27px"><h2>Receber link de acesso</h2>'
        .'<p>Informe o e-mail utilizado na compra. Se o pagamento foi confirmado, enviaremos um link para entrar sem precisar digitar senha.</p>'
        .'<form method="post">'.token().'<input type="hidden" name="act" value="access_resend">'
        .'<label>E-mail da compra<input type="email" name="email" required autocomplete="email" maxlength="180"></label>'
        .'<button type="submit">Enviar link de acesso</button></form><p><a href="?page=entrar">Voltar ao login</a></p></div>';foot();exit;
}

function od_access_has_temp_password(int $userId):bool{
    $q=od_access_db()->prepare('SELECT temp_password FROM od_access_preferences WHERE user_id=?');
    $q->execute([$userId]);return (bool)$q->fetchColumn();
}
function od_access_change_password():never{
    $uid=(int)(user()??0);
    if(!$uid){http_response_code(401);exit('Faça login.');}
    $current=(string)($_POST['current_password']??'');
    $new=(string)($_POST['new_password']??'');
    $q=od_access_db()->prepare('SELECT password FROM users WHERE id=?');$q->execute([$uid]);
    $saved=$q->fetchColumn();
    if(!$saved || !password_verify($current,(string)$saved)){
        flash('A senha atual não confere.');redirect('?page=minha-senha');
    }
    if(strlen($new)<12||strlen($new)>256||hash_equals($current,$new)){
        flash('Escolha uma senha diferente da atual, com pelo menos 12 caracteres.');redirect('?page=minha-senha');
    }
    od_access_db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$uid]);
    od_access_db()->prepare("INSERT INTO od_access_preferences(user_id,temp_password) VALUES(?,0)
        ON CONFLICT(user_id) DO UPDATE SET temp_password=0,updated_at=CURRENT_TIMESTAMP")->execute([$uid]);
    flash('Senha alterada. Utilize a nova senha nos próximos acessos.');
    redirect('?page=painel');
}
function od_access_password_page():never{
    if(!user()){redirect('?page=entrar');}
    h('Alterar minha senha',false);
    echo '<div class="card" style="max-width:540px;margin:32px auto;padding:27px"><h2>Alterar minha senha</h2>'
       .'<p>Você pode trocar sua senha provisória por uma de sua preferência, sem perder os documentos.</p>'
       .'<form method="post">'.token().'<input type="hidden" name="act" value="access_change_password">'
       .'<label>Senha atual<input type="password" name="current_password" autocomplete="current-password" required></label>'
       .'<label>Nova senha (mínimo 12 caracteres)<input type="password" name="new_password" autocomplete="new-password" minlength="12" maxlength="256" required></label>'
       .'<button type="submit">Salvar nova senha</button></form>'
       .'<p><a href="?page=painel">Voltar ao painel</a></p></div>';
    foot();exit;
}
