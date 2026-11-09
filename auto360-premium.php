<?php
declare(strict_types=1);
/**
 * Autoinspeção 360 | Acompanhamento interno guiado da clínica.
 * Não certifica conformidade, não substitui RT, inspeção ou parecer.
 * O módulo utiliza exclusivamente os 45 itens da matriz existente.
 */

function od360_status_options(): array {
    return [
        'nao_avaliado' => 'Não avaliado',
        'pendente' => 'Pendente',
        'em_andamento' => 'Em andamento',
        'verificado' => 'Conferido internamente',
        'nao_aplicavel' => 'Não aplicável',
    ];
}

function od360_saved(int $clinicId): array {
    $db = od_360_schema();
    $q = $db->prepare('SELECT code,status,evidence,owner,action,updated_at FROM od_inspection360 WHERE clinic_id=?');
    $q->execute([$clinicId]);
    $saved = [];
    foreach ($q as $record) {
        if (preg_match('/^B(?:0[1-9]|[1-3][0-9]|4[0-5])$/', (string)$record['code'])) {
            $saved[$record['code']] = $record;
        }
    }
    return $saved;
}

function od360_status(array $row): string {
    $status = (string)($row['status'] ?? 'nao_avaliado');
    return array_key_exists($status, od360_status_options()) ? $status : 'nao_avaliado';
}

function od360_code(int $index): string {
    return 'B'.str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
}

function od360_info(array $items, array $saved): array {
    $count = ['total'=>count($items),'avaliados'=>0,'nao_avaliado'=>0,'pendente'=>0,'em_andamento'=>0,'verificado'=>0,'nao_aplicavel'=>0];
    $areas = [];
    $next = null;
    foreach ($items as $index => $item) {
        $code = od360_code($index);
        $status = od360_status($saved[$code] ?? []);
        $area = (string)$item['area'];
        if (!isset($areas[$area])) $areas[$area] = ['total'=>0,'avaliados'=>0,'atencao'=>0];
        $areas[$area]['total']++;
        $count[$status]++;
        if ($status !== 'nao_avaliado') {
            $count['avaliados']++;
            $areas[$area]['avaliados']++;
        } elseif ($next === null) {
            $next = $code;
        }
        if (in_array($status, ['pendente','em_andamento'], true)) $areas[$area]['atencao']++;
    }
    return ['counts'=>$count,'areas'=>$areas,'next'=>$next,'percent'=>$count['total'] ? (int)round($count['avaliados']*100/$count['total']) : 0];
}

function od360_protect(): array {
    if (!user()) redirect('?page=entrar');
    if (!od_360_allowed()) {
        h('Autoinspeção 360', false);
        echo '<div class="od360-locked"><span class="od360-kicker">MÓDULO COMPLEMENTAR</span><h1>Autoinspeção 360</h1><p>Este acompanhamento de 45 verificações é um adicional opcional. Ele não está incluído no kit principal.</p><a class="od360-button" href="?page=checkout">Ver opções de compra</a></div>';
        foot();exit;
    }
    $clinic = clinic();
    if (!$clinic) redirect('?page=diagnostico');
    return $clinic;
}

function od360_save(): never {
    if (!user() || !od_360_allowed() || !($clinic = clinic())) {
        http_response_code(403);exit('Módulo não incluído no seu plano.');
    }
    $code = (string)($_POST['code'] ?? '');
    $items = od_360_items();
    if (!preg_match('/^B(?:0[1-9]|[1-3][0-9]|4[0-5])$/', $code)) {
        http_response_code(400);exit('Verificação desconhecida.');
    }
    $index = (int)substr($code, 1);
    if ($index < 1 || $index > count($items)) {
        http_response_code(400);exit('Verificação não encontrada.');
    }
    $status = (string)($_POST['status'] ?? 'nao_avaliado');
    if (!array_key_exists($status, od360_status_options())) {
        http_response_code(400);exit('Situação inválida.');
    }
    $evidence = mb_substr(trim((string)($_POST['evidence'] ?? '')), 0, 500);
    $owner = mb_substr(trim((string)($_POST['owner'] ?? '')), 0, 100);
    $action = mb_substr(trim((string)($_POST['action_note'] ?? '')), 0, 500);
    if ($status === 'nao_aplicavel' && $evidence === '') {
        flash('Explique por que este requisito não se aplica à clínica.');
        redirect('?page=auto360&abrir='.$code.'#'.$code);
    }
    if ($status === 'verificado' && $evidence === '') {
        flash('Para marcar como conferido internamente, informe o registro ou a evidência observada.');
        redirect('?page=auto360&abrir='.$code.'#'.$code);
    }
    $db = od_360_schema();
    $q = $db->prepare('INSERT INTO od_inspection360(clinic_id,code,status,evidence,owner,action,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP) ON CONFLICT(clinic_id,code) DO UPDATE SET status=excluded.status,evidence=excluded.evidence,owner=excluded.owner,action=excluded.action,updated_at=CURRENT_TIMESTAMP');
    $q->execute([(int)$clinic['id'],$code,$status,$evidence,$owner,$action]);
    $next = $code;
    if (isset($_POST['advance'])) {
        $all = od360_saved((int)$clinic['id']);
        for ($i = $index; $i < count($items); $i++) {
            $candidate = od360_code($i);
            if (od360_status($all[$candidate] ?? []) === 'nao_avaliado') {$next = $candidate;break;}
        }
        if ($next === $code) {
            for ($i = 0; $i < $index - 1; $i++) {
                $candidate = od360_code($i);
                if (od360_status($all[$candidate] ?? []) === 'nao_avaliado') {$next = $candidate;break;}
            }
        }
    }
    flash('Verificação salva. Seu progresso foi atualizado.');
    redirect('?page=auto360&abrir='.$next.'#'.$next);
}

function od360_premium_page(): never {
    $clinic = od360_protect();
    $items = od_360_items();
    if (count($items) !== 45) {
        http_response_code(503);
        h('Autoinspeção 360', false);
        echo '<section class="od360-locked"><h1>Autoinspeção temporariamente indisponível</h1><p>O roteiro completo não pôde ser carregado. Nenhuma resposta foi alterada.</p></section>';
        foot();exit;
    }
    $saved = od360_saved((int)$clinic['id']);
    $info = od360_info($items,$saved);
    $c = $info['counts'];
    $areaNames = array_keys($info['areas']);
    $firstArea = $areaNames[0] ?? '';
    $nextCode = $info['next'];
    $startArea = $nextCode ? (string)$items[((int)substr($nextCode,1))-1]['area'] : $firstArea;
    h('Autoinspeção 360', false);
    echo '<div class="od360-page" data-od360-page>';
    echo '<header class="od360-hero"><div class="od360-hero-main"><span class="od360-kicker">MÓDULO COMPLEMENTAR · ACOMPANHAMENTO INTERNO</span><h1>Autoinspeção 360</h1><p>Confira sua clínica com calma, registre o que encontrou e saiba quais pontos ainda precisam de atenção. Uma verificação por vez.</p><div class="od360-hero-actions"><a href="?page=auto360-relatorio" class="od360-button od360-button-light" target="_blank" rel="noopener" data-od360-report>Preparar relatório A4</a><button type="button" class="od360-button od360-button-ghost" data-od-help-open>Como usar</button></div></div><div class="od360-hero-num"><strong>'.(int)$c['avaliados'].'<em>/'.(int)$c['total'].'</em></strong><span>itens avaliados</span></div></header>';
    echo '<div class="od360-note"><strong>Importante:</strong> este é um roteiro de acompanhamento interno baseado em referências normativas. Marcar “conferido” não comprova adequação sanitária. A aplicabilidade, a análise técnica e as correções devem ser conferidas com o responsável técnico.</div>';
    echo '<section class="od360-overview" aria-label="Progresso da autoinspeção" data-od360-progress><div class="od360-progress-head"><div><span class="od360-kicker">SEU ANDAMENTO</span><h2>'.($c['avaliados']===45?'Todas as verificações registradas':'Seu progresso, sem complicação.').'</h2><p>O percentual indica quantos itens receberam uma resposta. Não é uma nota de conformidade.</p></div><strong>'.(int)$info['percent'].'%</strong></div><div class="od360-track" role="progressbar" aria-label="Verificações com resposta" aria-valuenow="'.(int)$info['percent'].'" aria-valuemin="0" aria-valuemax="100"><span style="width:'.(int)$info['percent'].'%"></span></div><div class="od360-metrics"><div><b>'.(int)$c['nao_avaliado'].'</b><span>Por avaliar</span></div><div><b>'.((int)$c['pendente']+(int)$c['em_andamento']).'</b><span>Precisam de atenção</span></div><div><b>'.(int)$c['verificado'].'</b><span>Conferidos internamente</span></div><div><b>'.(int)$c['nao_aplicavel'].'</b><span>Não aplicáveis</span></div></div></section>';
    echo '<section class="od360-next" data-od360-next><div><span class="od360-kicker">'.($nextCode?'PRÓXIMO PASSO':'SEU ROTEIRO ESTÁ PREENCHIDO').'</span><h2>'.($nextCode?'Continue de onde parou':'Agora revise seu plano de ação').'</h2><p>'.($nextCode?'Comece pelos itens não avaliados. Você pode registrar uma resposta e avançar automaticamente para a próxima pergunta.':'Confira o relatório A4 e planeje a revisão dos pontos pendentes com o RT.').'</p></div><a class="od360-button" href="'.($nextCode?'#'.esc($nextCode):'?page=auto360-relatorio').'">'.($nextCode?'Continuar no item '.esc($nextCode):'Ver relatório A4').'</a></section>';
    echo '<section class="od360-areas" aria-label="Verificações por assunto"><div class="od360-section-heading"><span class="od360-kicker">ORGANIZADO POR ASSUNTO</span><h2>Escolha uma área</h2></div><div class="od360-area-chips">';
    foreach ($info['areas'] as $area => $values) {
        echo '<a href="#area-'.esc((string)array_search($area,$areaNames,true)+1).'" data-od360-area-link="'.esc($area).'"><strong>'.esc($area).'</strong><span>'.(int)$values['avaliados'].'/'.(int)$values['total'].'</span></a>';
    }
    echo '</div></section>';
    echo '<section class="od360-checks" data-od360-list><div class="od360-section-heading"><span class="od360-kicker">45 VERIFICAÇÕES</span><h2>Registre a situação de cada item</h2><p>Use a busca ou os filtros. As respostas só são gravadas quando você clica em salvar.</p></div><div class="od360-toolbar" data-od360-toolbar><label>Buscar por assunto ou palavra<input type="search" data-od360-search placeholder="Ex.: esterilização, licença, resíduos" autocomplete="off"></label><label>Mostrar<select data-od360-filter><option value="todos">Todos os itens</option><option value="nao_avaliado">Ainda não avaliados</option><option value="atencao">Pendentes e em andamento</option><option value="verificado">Conferidos internamente</option><option value="nao_aplicavel">Não aplicáveis</option></select></label></div><p class="od360-results" data-od360-results aria-live="polite">45 itens no roteiro</p><p class="od360-empty" data-od360-empty hidden>Nenhum item corresponde à busca ou ao filtro selecionado.</p>';
    $areaIndex = 0;$openedArea = false;
    foreach ($items as $index => $item) {
        $area = (string)$item['area'];
        if ($areaIndex === 0 || $area !== (string)($items[$index-1]['area'] ?? '')) {
            if ($areaIndex > 0) echo '</div></details>';
            $areaIndex++;
            $open = $area === $startArea || (!$openedArea && $nextCode===null && $area === $firstArea);
            if ($open) $openedArea = true;
            $values = $info['areas'][$area];
            echo '<details class="od360-group" id="area-'.$areaIndex.'" data-od360-group'.($open?' open':'').'><summary><span><small>ÁREA '.(int)$areaIndex.'</small><strong>'.esc($area).'</strong><em>'.(int)$values['avaliados'].' de '.(int)$values['total'].' avaliados</em></span><span class="od360-summary-count">'.(int)$values['atencao'].' com atenção</span></summary><div class="od360-group-body">';
        }
        $code=od360_code($index);$row=$saved[$code]??[];$status=od360_status($row);
        $priority=(string)$item['prioridade'];$isHigh=$priority==='Alta';
        $question=(string)$item['pergunta'];$foundation=(string)$item['fundamento'];
        echo '<article class="od360-card" id="'.$code.'" data-od360-card data-status="'.esc($status).'" data-query="'.esc(mb_strtolower($area.' '.$question.' '.$foundation.' '.$code)).'">';
        echo '<div class="od360-card-heading"><div class="od360-card-id"><span>'.$code.'</span><span class="'.($isHigh?'od360-priority-high':'od360-priority-mid').'">Prioridade '.esc(mb_strtolower($priority)).'</span></div><span class="od360-current" data-od360-current>'.esc(od360_status_options()[$status]).'</span></div><h3>'.esc($question).'</h3><p class="od360-source">Referência do roteiro: '.esc($foundation).'. Confirmar a aplicabilidade com o RT.</p>';
        echo '<form method="post" action="?page=auto360#'.$code.'" data-od360-form>'.token().'<input type="hidden" name="act" value="auto360_premium_save"><input type="hidden" name="code" value="'.$code.'"><div class="od360-form-grid"><label>Como está esta situação na clínica?<select name="status" required data-od360-status>';
        foreach (od360_status_options() as $value=>$label) echo '<option value="'.esc($value).'"'.($value===$status?' selected':'').'>'.esc($label).'</option>';
        echo '</select></label><label>Responsável pela conferência<input name="owner" maxlength="100" placeholder="Ex.: responsável técnico" value="'.esc((string)($row['owner']??'')).'"></label></div><label>Registro, evidência ou justificativa<textarea name="evidence" maxlength="500" rows="2" placeholder="Descreva o que foi verificado ou por que não se aplica" data-od360-evidence>'.esc((string)($row['evidence']??'')).'</textarea><small>Obrigatório quando marcar “Conferido internamente” ou “Não aplicável”.</small></label><label>Ação necessária ou próximo passo<textarea name="action_note" maxlength="500" rows="2" placeholder="Ex.: localizar comprovante, solicitar avaliação do RT">'.esc((string)($row['action']??'')).'</textarea></label><div class="od360-card-actions"><button type="submit" name="advance" value="1" class="od360-button">Salvar e ir para o próximo</button><button type="submit" class="od360-button od360-button-muted">Salvar sem avançar</button></div></form></article>';
    }
    if ($areaIndex > 0) echo '</div></details>';
    echo '</section><div class="od360-bottom"><div><h2>Próximo passo: montar seu relatório.</h2><p>O relatório reúne todas as 45 verificações, pendências e ações registradas. Você pode imprimir ou salvar em PDF e discutir com o responsável técnico.</p></div><a href="?page=auto360-relatorio" target="_blank" rel="noopener" class="od360-button">Abrir relatório A4</a></div></div>';
    foot();exit;
}

function od360_premium_report(): never {
    $clinic = od360_protect();
    $items = od_360_items();
    if (count($items) !== 45) {http_response_code(503);exit('Roteiro incompleto.');}
    $saved = od360_saved((int)$clinic['id']);
    $info = od360_info($items,$saved);
    $c = $info['counts'];$statuses = od360_status_options();
    $name = (string)($clinic['name']??'Clínica');
    $rt = trim((string)($clinic['rt_name']??''));
    $cro = trim((string)($clinic['rt_cro']??''));
    $date = date('d/m/Y');
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Relatório interno Autoinspeção 360 · '.esc($name).'</title><link rel="stylesheet" href="assets/auto360-premium.css?v=1"></head><body class="od360-print"><div class="od360-print-toolbar"><strong>Autoinspeção 360 · relatório interno</strong><div><button type="button" onclick="window.print()">Imprimir ou salvar PDF</button><a href="?page=auto360">Voltar à autoinspeção</a></div></div><main class="od360-report">';
    echo '<header class="od360-report-cover"><span class="od360-kicker">ODONTO EM DIA · DOCUMENTO INTERNO</span><h1>Relatório de Autoinspeção 360</h1><p>Roteiro de acompanhamento interno, sem certificação sanitária.</p><div class="od360-report-clinic"><div><small>CLÍNICA</small><strong>'.esc($name).'</strong></div><div><small>DATA DE EMISSÃO</small><strong>'.esc($date).'</strong></div><div><small>RESPONSÁVEL TÉCNICO INFORMADO</small><strong>'.esc($rt?:'Não informado').($cro?' · '.esc($cro):'').'</strong></div></div><div class="od360-report-metrics"><div><b>'.(int)$c['avaliados'].'/45</b><span>Respostas registradas</span></div><div><b>'.((int)$c['pendente']+(int)$c['em_andamento']).'</b><span>Em atenção</span></div><div><b>'.(int)$c['nao_avaliado'].'</b><span>Por avaliar</span></div></div><p class="od360-report-warning"><strong>ATENÇÃO</strong> Esta saída registra exclusivamente declarações do usuário. Não é um laudo, licença ou atestado de conformidade. A legislação aplicável, a existência de evidências e a correção das ações precisam de verificação profissional.</p></header>';
    echo '<section class="od360-report-section"><h2>1. Resumo por área</h2><table><thead><tr><th>Área</th><th>Respondidos</th><th>Em atenção</th></tr></thead><tbody>';
    foreach($info['areas'] as $area=>$values)echo '<tr><td>'.esc($area).'</td><td>'.(int)$values['avaliados'].' / '.(int)$values['total'].'</td><td>'.(int)$values['atencao'].'</td></tr>';
    echo '</tbody></table></section>';
    echo '<section class="od360-report-section"><h2>2. Plano de ação e pontos a avaliar</h2><p>Itens pendentes ou em andamento aparecem primeiro. Itens não avaliados também são listados para não serem confundidos com itens conferidos.</p><table><thead><tr><th>Código e verificação</th><th>Situação</th><th>Responsável / próxima ação</th></tr></thead><tbody>';
    $actionCount=0;
    foreach($items as $index=>$item){$code=od360_code($index);$row=$saved[$code]??[];$status=od360_status($row);if(!in_array($status,['pendente','em_andamento','nao_avaliado'],true))continue;$actionCount++;
        echo '<tr><td><strong>'.$code.' · '.esc((string)$item['pergunta']).'</strong><small>'.esc((string)$item['area']).'</small></td><td>'.esc($statuses[$status]).'</td><td><strong>'.esc(trim((string)($row['owner']??'')) ?: 'A definir').'</strong><small>'.esc(trim((string)($row['action']??'')) ?: 'Avaliar e registrar próximo passo').'</small></td></tr>';
    }
    if(!$actionCount)echo '<tr><td colspan="3">Nenhum item pendente, em andamento ou por avaliar foi informado. Conferir as evidências com o RT.</td></tr>';
    echo '</tbody></table></section>';
    echo '<section class="od360-report-section"><h2>3. Registro completo das 45 verificações</h2>';
    $prev='';foreach($items as $index=>$item){$area=(string)$item['area'];if($area!==$prev){if($prev!=='')echo '</tbody></table>';echo '<h3>'.esc($area).'</h3><table><thead><tr><th>Código e verificação</th><th>Estado declarado</th><th>Registro e próximo passo</th></tr></thead><tbody>';$prev=$area;}
        $code=od360_code($index);$row=$saved[$code]??[];$status=od360_status($row);
        $detail=trim((string)($row['evidence']??''));$action=trim((string)($row['action']??''));$owner=trim((string)($row['owner']??''));
        echo '<tr><td><strong>'.$code.' · '.esc((string)$item['pergunta']).'</strong><small>Prioridade '.esc((string)$item['prioridade']).' · Referência: '.esc((string)$item['fundamento']).'</small></td><td>'.esc($statuses[$status]).'</td><td>';
        if($detail!=='')echo '<span>'.nl2br(esc($detail)).'</span>';
        if($action!=='')echo '<small><strong>Próxima ação:</strong> '.nl2br(esc($action)).'</small>';
        if($owner!=='')echo '<small><strong>Responsável:</strong> '.esc($owner).'</small>';
        if($detail===''&&$action===''&&$owner==='')echo '<small>Sem informações adicionais registradas</small>';
        echo '</td></tr>';
    }
    if($prev!=='')echo '</tbody></table>';
    echo '</section><section class="od360-report-sign"><h2>4. Conferência profissional</h2><p>Registro para conferência do responsável técnico; não contém aprovação automática.</p><div><span>Responsável pela revisão: ___________________________________</span><span>Data: ____ / ____ / ______</span></div></section>';
    echo '<footer class="od360-report-footer">Odonto em Dia · material de apoio para acompanhamento interno · conferir normas vigentes e aplicabilidade com profissional habilitado.</footer></main></body></html>';
    exit;
}
