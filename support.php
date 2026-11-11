<?php
declare(strict_types=1);

// Suporte autenticado da área de membros. Nunca incluir dados de pacientes.
function od_support_categories():array {
    return [
        'acesso'=>'Acesso à minha conta',
        'documentos'=>'Documentos e edição',
        'uso'=>'Como usar a plataforma',
        'pagamento'=>'Pagamento e compra',
        'erro'=>'Erro ou problema técnico',
        'outro'=>'Outro assunto',
    ];
}

function od_support_db():PDO {
    $p=db();
    $p->exec("CREATE TABLE IF NOT EXISTS od_support_tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        clinic_id INTEGER NOT NULL,
        email TEXT NOT NULL,
        clinic_name TEXT NOT NULL,
        category TEXT NOT NULL,
        subject TEXT NOT NULL,
        message TEXT NOT NULL,
        email_status TEXT NOT NULL DEFAULT 'pending',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $p->exec('CREATE INDEX IF NOT EXISTS od_support_by_user ON od_support_tickets(user_id,created_at)');
    return $p;
}

function od_support_post():never {
    $uid=(int)(user()??0);
    $clinic=clinic();
    if($uid<1 || !$clinic){http_response_code(403);exit('Acesso necessário.');}

    $category=$_POST['category']??null;
    $subject=$_POST['subject']??null;
    $message=$_POST['message']??null;
    $website=$_POST['website']??'';
    if(!is_string($category)||!is_string($subject)||!is_string($message)||!is_string($website)){
        flash('Preencha os campos corretamente.');
        redirect('?page=suporte');
    }
    // Campo antispam invisível para usuários reais.
    if($website!==''){redirect('?page=suporte');}
    $categories=od_support_categories();
    $subject=trim(preg_replace('/[\r\n]+/u',' ',$subject)??'');
    $message=trim(str_replace(["\r\n","\r"],"\n",$message));
    if(!isset($categories[$category])||mb_strlen($subject)<5||mb_strlen($subject)>120||
       mb_strlen($message)<20||mb_strlen($message)>3000){
        flash('Escolha um assunto, escreva um título de 5 a 120 caracteres e detalhe o pedido em 20 a 3.000 caracteres.');
        redirect('?page=suporte');
    }

    $db=od_support_db();
    $limit=$db->prepare("SELECT COUNT(*) FROM od_support_tickets WHERE user_id=? AND created_at >= datetime('now','-1 hour')");
    $limit->execute([$uid]);
    if((int)$limit->fetchColumn()>=3){
        flash('Você já abriu três solicitações na última hora. Para urgências, use o e-mail de contato direto.');
        redirect('?page=suporte');
    }

    $q=$db->prepare('SELECT email FROM users WHERE id=?');
    $q->execute([$uid]);
    $email=(string)$q->fetchColumn();
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){http_response_code(422);exit('E-mail de cadastro inválido.');}
    $clinicName=mb_substr((string)$clinic['name'],0,180);

    $insert=$db->prepare('INSERT INTO od_support_tickets(user_id,clinic_id,email,clinic_name,category,subject,message) VALUES(?,?,?,?,?,?,?)');
    $insert->execute([$uid,(int)$clinic['id'],$email,$clinicName,$category,$subject,$message]);
    $id=(int)$db->lastInsertId();

    $body="NOVO CHAMADO - ODONTO EM DIA\r\n"
        ."Protocolo: #".$id."\r\n"
        ."Categoria: ".$categories[$category]."\r\n"
        ."Assunto: ".$subject."\r\n"
        ."Clínica: ".$clinicName."\r\n"
        ."Usuário ID: ".$uid."\r\n"
        ."E-mail de resposta: ".$email."\r\n"
        ."Enviado em: ".date('d/m/Y H:i:s')."\r\n"
        ."\r\nMENSAGEM:\r\n".$message."\r\n\r\n"
        ."Responda diretamente a este e-mail para atender a solicitação.\r\n";
    $headers=[
        'MIME-Version'=>'1.0',
        'Content-Type'=>'text/plain; charset=UTF-8',
        'From'=>'Odonto em Dia <notificacoes@odontonegocios.com>',
        'Reply-To'=>$email,
    ];
    $emailSubject=mb_encode_mimeheader('[Odonto em Dia] Suporte #'.$id.' - '.$categories[$category], 'UTF-8', 'B', "\r\n");
    $queued=false;
    try {
        $queued=mail('titecweb@gmail.com',$emailSubject,$body,$headers);
    } catch(Throwable $e) {
        error_log('[Odonto suporte] Falha na tentativa de envio do chamado '.$id);
    }
    $db->prepare('UPDATE od_support_tickets SET email_status=? WHERE id=?')->execute([$queued?'queued':'failed',$id]);
    flash($queued
        ?'Solicitação #'.$id.' registrada. O envio do e-mail de suporte foi iniciado.'
        :'Solicitação #'.$id.' registrada, mas o envio do e-mail falhou. Use o contato direto exibido nesta página.');
    redirect('?page=suporte');
}

function od_support_page():never {
    $uid=(int)(user()??0);
    if($uid<1){http_response_code(403);exit('Faça login.');}
    $q=od_support_db()->prepare('SELECT id,category,subject,email_status,created_at FROM od_support_tickets WHERE user_id=? ORDER BY id DESC LIMIT 5');
    $q->execute([$uid]);
    $tickets=$q->fetchAll();
    h('Suporte',false);
    echo '<div class="od-support-page"><header class="od-support-head"><span class="od-support-eyebrow">CENTRAL DE AJUDA</span><h1>Como podemos ajudar?</h1><p>Tem alguma dúvida sobre o kit, os documentos ou o acesso? Envie uma mensagem e nossa equipe poderá responder pelo e-mail cadastrado.</p></header>';
    echo '<div class="od-support-grid"><section class="od-support-card"><div class="od-support-card-heading"><h2>Enviar solicitação</h2><p>Descreva o que aconteceu ou o que você precisa.</p></div>';
    echo '<form class="od-support-form" method="post" action="?page=suporte">'.token().'<input type="hidden" name="act" value="support_create"><div class="od-support-honeypot" aria-hidden="true"><label>Seu site<input name="website" tabindex="-1" autocomplete="off"></label></div>';
    echo '<label for="od-support-category">Sobre o que precisa de ajuda?</label><select id="od-support-category" name="category" required><option value="" selected disabled>Selecione um assunto</option>';
    foreach(od_support_categories() as $value=>$label)echo '<option value="'.esc($value).'">'.esc($label).'</option>';
    echo '</select><label for="od-support-subject">Título da solicitação</label><input id="od-support-subject" type="text" name="subject" required minlength="5" maxlength="120" placeholder="Ex.: Não consigo editar um POP">';
    echo '<label for="od-support-message">Conte o que aconteceu</label><textarea id="od-support-message" name="message" required minlength="20" maxlength="3000" rows="7" placeholder="Explique sua dúvida, informe em qual tela está e o que tentou fazer."></textarea>';
    echo '<p class="od-support-privacy">Não envie nomes, prontuários, fotos ou informações pessoais de pacientes.</p><button type="submit" class="od-support-submit">Enviar solicitação</button></form></section>';
    echo '<aside class="od-support-aside"><section class="od-support-aside-card"><span class="od-support-aside-kicker">CONTATO DIRETO</span><h2>Prefere enviar um e-mail?</h2><p>Você também pode falar com o suporte usando seu aplicativo de e-mail.</p><a href="mailto:titecweb@gmail.com?subject=Suporte%20Odonto%20em%20Dia" class="od-support-email">titecweb@gmail.com</a></section>';
    echo '<section class="od-support-aside-card od-support-tip"><h2>Para agilizar o atendimento</h2><p>Informe qual documento ou funcionalidade está usando e descreva o erro exibido. Evite compartilhar dados de pacientes.</p></section></aside></div>';
    if($tickets){
        echo '<section class="od-support-history"><h2>Suas últimas solicitações</h2><div class="od-support-history-list">';
        foreach($tickets as $t){
            $ok=$t['email_status']==='queued';
            echo '<div class="od-support-ticket"><span class="od-support-ticket-number">#'.(int)$t['id'].'</span><div><strong>'.esc($t['subject']).'</strong><small>'.esc($t['created_at']).'</small></div><span class="od-support-status '.($ok?'is-queued':'is-failed').'">'.($ok?'Envio iniciado':'Envio não confirmado').'</span></div>';
        }
        echo '</div></section>';
    }
    echo '</div>';
    foot();exit;
}
