<?php
/** Autoinspeção 360: módulo complementar separado, 45 itens, não certificação. */
function od_360_items():array{
    $s=@file_get_contents(__DIR__.'/data/verificacoes45.json');$r=$s?json_decode($s,true):null;return is_array($r)?$r:[];
}
function od_360_schema():PDO{
    $p=db();$p->exec('CREATE TABLE IF NOT EXISTS od_inspection360(clinic_id INTEGER NOT NULL, code TEXT NOT NULL, status TEXT NOT NULL DEFAULT \'nao_avaliado\', evidence TEXT DEFAULT \'\', owner TEXT DEFAULT \'\', action TEXT DEFAULT \'\', updated_at TEXT DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(clinic_id,code))');return $p;
}
function od_360_allowed():bool{
    if(!user())return false;
    // A única conta piloto tem acesso de avaliação; nunca use essa exceção em uma conta comercial.
    if(od_is_pilot((int)user()))return true;
    $e=od_mp_entitlement((int)user());return $e && (int)$e['addon']===1;
}
function od_360_save():never{
    if(!od_360_allowed() || !($clinic=clinic())){http_response_code(403);exit('Módulo não incluído no seu plano.');}
    $code=(string)($_POST['code']??'');$index=filter_var(substr($code,1),FILTER_VALIDATE_INT);
    if(!preg_match('/^B\d{2}$/',$code)||!$index||!isset(od_360_items()[$index-1])){http_response_code(400);exit('Verificação desconhecida.');}
    $state=(string)($_POST['status']??'nao_avaliado');if(!in_array($state,['nao_avaliado','pendente','em_andamento','verificado','nao_aplicavel'],true))$state='nao_avaliado';
    od_360_schema()->prepare('INSERT INTO od_inspection360(clinic_id,code,status,evidence,owner,action) VALUES(?,?,?,?,?,?) ON CONFLICT(clinic_id,code) DO UPDATE SET status=excluded.status,evidence=excluded.evidence,owner=excluded.owner,action=excluded.action,updated_at=CURRENT_TIMESTAMP')->execute([$clinic['id'],$code,$state,mb_substr(trim((string)($_POST['evidence']??'')),0,500),mb_substr(trim((string)($_POST['owner']??'')),0,100),mb_substr(trim((string)($_POST['action_note']??'')),0,500)]);
    flash('Verificação registrada.');redirect('?page=auto360#'.$code);
}
function od_360_page():never{
    if(!user()){redirect('?page=entrar');}
    if(!od_360_allowed()){h('Autoinspeção 360');echo '<p class="lead">Este módulo complementar não está incluído no kit principal. Ele é oferecido de forma opcional no checkout.</p><a class="btn" href="?page=checkout">Conhecer o adicional de R$ 37</a>';foot();exit;}
    $clinic=clinic();if(!$clinic){redirect('?page=diagnostico');}
    $items=od_360_items();$q=od_360_schema()->prepare('SELECT * FROM od_inspection360 WHERE clinic_id=?');$q->execute([$clinic['id']]);$saved=[];foreach($q as $v)$saved[$v['code']]=$v;
    $counts=['pendente'=>0,'em_andamento'=>0,'verificado'=>0,'nao_aplicavel'=>0];foreach($saved as $s){if(isset($counts[$s['status']]))$counts[$s['status']]++;}
    h('Autoinspeção 360');echo '<p class="lead">45 verificações para acompanhamento interno, baseadas em referências normativas. Não substitui inspeção oficial, parecer técnico ou avaliação do RT.</p><div class="stats"><div class="stat"><strong>45</strong><span>Itens de verificação</span></div><div class="stat"><strong>'.(int)$counts['verificado'].'</strong><span>Verificados internamente</span></div><div class="stat"><strong>'.((int)$counts['pendente']+(int)$counts['em_andamento']).'</strong><span>Pendências abertas</span></div></div><div class="warn">Verificado é uma marcação feita pelo usuário, não comprovação de conformidade sanitária. Consulte o roteiro oficial de inspeção e avalie a aplicabilidade de cada requisito.</div>';
    $prev='';foreach($items as $i=>$item){$area=$item['area'];if($area!==$prev){if($prev!=='')echo '</section>';echo '<section class="doc-category"><div class="doc-category-title"><h2>'.esc($area).'</h2></div>';$prev=$area;}$code='B'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT);$s=$saved[$code]??[];
    echo '<article id="'.$code.'" class="card task" style="margin-bottom:13px"><div class="tasktop"><div><small>'.esc($code).' · Prioridade '.esc($item['prioridade']).' · '.esc($item['fundamento']).'</small><h3>'.esc($item['pergunta']).'</h3></div></div><form method="post">'.token().'<input type="hidden" name="act" value="auto360_save"><input type="hidden" name="code" value="'.$code.'"><div class="two"><label>Situação<select name="status">';foreach(['nao_avaliado'=>'Não avaliado','pendente'=>'Pendente','em_andamento'=>'Em andamento','verificado'=>'Verificado internamente','nao_aplicavel'=>'Não aplicável (justificar)'] as $k=>$v)echo '<option value="'.$k.'" '.(($s['status']??'nao_avaliado')===$k?'selected':'').'>'.$v.'</option>';echo '</select></label><label>Responsável<input name="owner" maxlength="100" value="'.esc($s['owner']??'').'"></label></div><label>Evidência ou justificativa<input name="evidence" maxlength="500" value="'.esc($s['evidence']??'').'"></label><label>Ação para corrigir ou completar<input name="action_note" maxlength="500" value="'.esc($s['action']??'').'"></label><button class="small">Salvar verificação</button></form></article>';
    }if($prev!=='')echo '</section>';foot();exit;
}