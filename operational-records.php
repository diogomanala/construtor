<?php
/** Odonto em Dia / registros operacionais. Minutas administrativas, sem certificação automatizada. */
declare(strict_types=1);

function odx_ops_config(): array {
    return [
        'maintenance' => [
            'slug' => 'registros-manutencao',
            'name' => 'Manutenção de equipamentos',
            'label' => 'REGISTROS DE MANUTENÇÃO',
            'intro' => 'Registre intervenções e acompanhe históricos por equipamento. A periodicidade e os critérios técnicos devem seguir a rotina validada da clínica.',
            'subject' => 'Equipamento / identificação interna',
            'actor' => 'Executante / prestador responsável',
            'source_id' => 21,
            'fields' => [
                'service_type' => ['label' => 'Natureza da intervenção', 'type' => 'select', 'required' => true, 'choices' => ['preventiva' => 'Preventiva', 'corretiva' => 'Corretiva', 'verificacao' => 'Verificação', 'calibracao' => 'Calibração']],
                'provider' => ['label' => 'Empresa ou área prestadora', 'type' => 'text', 'max' => 130],
                'service_note' => ['label' => 'Atividade realizada / evidência de serviço', 'type' => 'textarea', 'max' => 1300],
                'next_date' => ['label' => 'Próxima data prevista (se definida)', 'type' => 'date'],
            ],
        ],
        'epi' => [
            'slug' => 'registros-epi',
            'name' => 'Entrega e orientação sobre EPI',
            'label' => 'REGISTROS DE EPI',
            'intro' => 'Registre a entrega e as orientações comunicadas à equipe, conforme as rotinas da clínica. Este controle não substitui recibo assinado nem avaliação de treinamento.',
            'subject' => 'EPI / identificação do item',
            'actor' => 'Colaborador / identificação interna',
            'source_id' => 6,
            'fields' => [
                'quantity' => ['label' => 'Quantidade entregue', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 1000],
                'item_detail' => ['label' => 'Tamanho, lote ou especificação', 'type' => 'text', 'max' => 180],
                'delivered_by' => ['label' => 'Responsável pela entrega', 'type' => 'text', 'max' => 120],
                'orientation_note' => ['label' => 'Orientação ou observações sobre a entrega', 'type' => 'textarea', 'max' => 1200],
            ],
        ],
    ];
}

function odx_ops_date_valid(string $date): bool {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $d !== false && $d->format('Y-m-d') === $date;
}

/** Validação pura para QA sem banco ou sessão. */
function odx_ops_validate(string $kind, array $request): array {
    $config = odx_ops_config()[$kind] ?? null;
    if (!$config) return ['ok' => false, 'error' => 'Tipo de registro não reconhecido.'];
    $row = [];
    foreach (['event_date', 'subject', 'actor', 'notes', 'status'] as $key) {
        $value = $request[$key] ?? '';
        if (!is_string($value)) return ['ok' => false, 'error' => 'Campo inválido.'];
        $row[$key] = trim($value);
    }
    if (!odx_ops_date_valid($row['event_date'])) return ['ok' => false, 'error' => 'Informe uma data válida.'];
    if ($row['subject'] === '' || $row['actor'] === '' || mb_strlen($row['subject']) > 160 || mb_strlen($row['actor']) > 120) {
        return ['ok' => false, 'error' => 'Informe o item e o responsável, dentro dos limites indicados.'];
    }
    if (mb_strlen($row['notes']) > 1300) return ['ok' => false, 'error' => 'Observação muito longa.'];
    if (!in_array($row['status'], ['registrado', 'pendente', 'ocorrencia'], true)) return ['ok' => false, 'error' => 'Situação inválida.'];
    $details = [];
    foreach ($config['fields'] as $name => $field) {
        $value = $request[$name] ?? '';
        if (!is_string($value)) return ['ok' => false, 'error' => 'Campo de detalhe inválido.'];
        $value = trim($value);
        if (!empty($field['required']) && $value === '') return ['ok' => false, 'error' => 'Preencha o campo: '.$field['label'].'.'];
        if ($field['type'] === 'select' && !isset($field['choices'][$value])) return ['ok' => false, 'error' => 'Opção inválida em '.$field['label'].'.'];
        if ($field['type'] === 'integer' && (!ctype_digit($value) || (int)$value < $field['min'] || (int)$value > $field['max'])) {
            return ['ok' => false, 'error' => 'Quantidade inválida.'];
        }
        if ($field['type'] === 'date' && $value !== '' && !odx_ops_date_valid($value)) return ['ok' => false, 'error' => 'Próxima data inválida.'];
        if (isset($field['max']) && $field['type'] !== 'integer' && mb_strlen($value) > $field['max']) {
            return ['ok' => false, 'error' => 'Texto acima do limite em '.$field['label'].'.'];
        }
        $details[$name] = $value;
    }
    $row['details'] = $details;
    return ['ok' => true, 'data' => $row];
}

function odx_ops_schema(): PDO {
    $db = odx_schema();
    $db->exec("CREATE TABLE IF NOT EXISTS od_ops_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        clinic_id INTEGER NOT NULL,
        record_kind TEXT NOT NULL,
        event_date TEXT NOT NULL,
        subject TEXT NOT NULL,
        actor TEXT NOT NULL,
        status TEXT NOT NULL,
        notes TEXT NOT NULL DEFAULT '',
        details_json TEXT NOT NULL DEFAULT '{}',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec('CREATE INDEX IF NOT EXISTS od_ops_clinic_kind_date ON od_ops_records(clinic_id,record_kind,event_date DESC,id DESC)');
    return $db;
}

function odx_ops_post(): never {
    if (!user() || !($clinic = clinic())) {http_response_code(403);exit('Acesso necessário');}
    $kind = (string)($_POST['record_kind'] ?? '');
    $definition = odx_ops_config()[$kind] ?? null;
    if (!$definition) {http_response_code(400);exit('Registro não encontrado');}
    $validated = odx_ops_validate($kind, $_POST);
    if (!$validated['ok']) {flash($validated['error']);redirect('?page='.$definition['slug']);}
    $data = $validated['data'];
    try {
        $db = odx_ops_schema();
        $stmt = $db->prepare('INSERT INTO od_ops_records (clinic_id,record_kind,event_date,subject,actor,status,notes,details_json) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([
            (int)$clinic['id'], $kind, $data['event_date'], $data['subject'], $data['actor'], $data['status'], $data['notes'],
            json_encode($data['details'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
        flash('Registro salvo no histórico da clínica. A conferência técnica continua sob responsabilidade da equipe.');
    } catch (Throwable $e) {
        error_log('[Odonto] Falha ao salvar registro operacional');
        flash('Não foi possível salvar o registro.');
    }
    redirect('?page='.$definition['slug']);
}

function odx_ops_query(PDO $db, int $clinicId, string $kind, string $status='', string $date=''): array {
    if (!isset(odx_ops_config()[$kind])) return [];
    $sql = 'SELECT event_date,subject,actor,status,notes,details_json,created_at FROM od_ops_records WHERE clinic_id=? AND record_kind=?';
    $params = [$clinicId, $kind];
    if (in_array($status, ['registrado', 'pendente', 'ocorrencia'], true)) {$sql .= ' AND status=?';$params[] = $status;}
    if (odx_ops_date_valid($date)) {$sql .= ' AND event_date=?';$params[] = $date;}
    $sql .= ' ORDER BY event_date DESC,id DESC LIMIT 200';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function odx_ops_page(string $kind): never {
    $definition = odx_ops_config()[$kind] ?? null;
    if (!$definition) {http_response_code(404);exit('Ferramenta não encontrada');}
    if (!user() || !($clinic=clinic())) {http_response_code(403);exit('Acesso necessário');}
    $status = (string)($_GET['status'] ?? '');
    if (!in_array($status,['registrado','pendente','ocorrencia'],true)) $status='';
    $date = trim((string)($_GET['date'] ?? ''));
    if (!odx_ops_date_valid($date)) $date='';
    $rows = odx_ops_query(odx_ops_schema(),(int)$clinic['id'],$kind,$status,$date);
    h($definition['name']);
    echo '<div class="odx-topline"><a href="?page=editar&id='.(int)$definition['source_id'].'">← POP relacionado</a><span> / </span><strong>'.esc($definition['label']).'</strong></div>';
    echo '<div class="odx-page-title"><div><span class="odx-eyebrow">ROTINAS DA CLÍNICA / HISTÓRICO</span><h2>'.esc($definition['name']).'</h2><p>'.esc($definition['intro']).'</p></div><span class="odx-version">Ferramenta de registros</span></div>';
    echo '<div class="odx-register-layout"><section class="odx-register-form"><span class="odx-side-kicker">NOVO REGISTRO</span><h3>Dados da ocorrência</h3><form method="post">'.token().'<input type="hidden" name="act" value="op_record"><input type="hidden" name="record_kind" value="'.esc($kind).'">';
    echo '<div class="odx-register-grid"><label>Data<input type="date" name="event_date" required value="'.esc(date('Y-m-d')).'"></label><label>'.esc($definition['subject']).'<input type="text" name="subject" required maxlength="160" placeholder="Identificação interna"></label><label>'.esc($definition['actor']).'<input type="text" name="actor" required maxlength="120" placeholder="Nome ou identificação interna"></label></div>';
    foreach ($definition['fields'] as $name=>$field) {
        $required = !empty($field['required'])?' required':'';
        echo '<label>'.esc($field['label']);
        if ($field['type']==='textarea') echo '<textarea name="'.esc($name).'" rows="3" maxlength="'.(int)$field['max'].'"'.$required.'></textarea>';
        elseif ($field['type']==='select') {
            echo '<select name="'.esc($name).'"'.$required.'>';
            foreach ($field['choices'] as $value=>$label)echo '<option value="'.esc($value).'">'.esc($label).'</option>';
            echo '</select>';
        } else {
            $type = $field['type']==='integer'?'number':$field['type'];
            echo '<input type="'.esc($type).'" name="'.esc($name).'"'.(isset($field['max'])?' maxlength="'.(int)$field['max'].'"':'').($type==='number'?' min="1" max="1000"':'').$required.'>';
        }
        echo '</label>';
    }
    echo '<label>Situação do lançamento<select name="status"><option value="registrado">Execução registrada</option><option value="pendente">Aguardando conferência</option><option value="ocorrencia">Ocorrência para acompanhar</option></select></label>';
    echo '<label>Observações adicionais<textarea name="notes" maxlength="1300" rows="3" placeholder="Descreva apenas fatos observados e providências adotadas"></textarea></label>';
    echo '<button class="odx-save" type="submit">Salvar registro <span>↗</span></button></form><p class="odx-safe">Não inclua dados de pacientes. Os lançamentos são registros administrativos e não certificam execução ou adequação normativa.</p></section>';
    echo '<section class="odx-register-history"><div class="odx-register-title"><div><span>HISTÓRICO DA UNIDADE</span><h3>Registros lançados</h3></div><small>Até 200 resultados</small></div>';
    echo '<form method="get" class="odx-clean-filter"><input type="hidden" name="page" value="'.esc($definition['slug']).'"><label>Filtrar por data<input type="date" name="date" value="'.esc($date).'"></label><label>Situação<select name="status"><option value="">Todas</option>';
    foreach (['registrado'=>'Registrada','pendente'=>'Aguardando conferência','ocorrencia'=>'Ocorrência'] as $value=>$label)echo '<option value="'.esc($value).'"'.($value===$status?' selected':'').'>'.esc($label).'</option>';
    echo '</select></label><button type="submit">Filtrar</button><a href="?page='.esc($definition['slug']).'">Limpar</a></form>';
    if (!$rows) echo '<div class="odx-register-empty"><b>Nenhum registro encontrado.</b><p>Comece criando o primeiro registro ou altere os filtros.</p></div>';
    foreach ($rows as $row) {
        $details = json_decode((string)$row['details_json'],true);
        if (!is_array($details))$details=[];
        echo '<article class="odx-register-row"><div class="odx-register-row-head"><b>'.esc($row['event_date']).' · '.esc($row['subject']).'</b><span class="'.($row['status']==='ocorrencia'?'odx-cycle-intercorrencia':($row['status']==='pendente'?'odx-cycle-pendente':'odx-cycle-conferido')).'">'.esc(['registrado'=>'Registrada','pendente'=>'Aguardando conferência','ocorrencia'=>'Ocorrência'][$row['status']]??'Registrada').'</span></div>';
        echo '<div class="odx-register-row-meta"><span>Responsável: '.esc($row['actor']).'</span></div>';
        foreach ($definition['fields'] as $key=>$field) {
            $value=(string)($details[$key]??'');
            if ($value==='') continue;
            if ($field['type']==='select') $value=(string)($field['choices'][$value]??$value);
            echo '<p><strong>'.esc($field['label']).':</strong> '.nl2br(esc($value)).'</p>';
        }
        if ($row['notes']!=='') echo '<p><strong>Observações:</strong> '.nl2br(esc($row['notes'])).'</p>';
        echo '<small>Registrado em '.esc($row['created_at']).'</small></article>';
    }
    echo '<div class="odx-bottom-hint"><strong>Informação administrativa:</strong> o registro não representa aprovação do procedimento, de um equipamento ou de uma rotina de EPI. A revisão cabe ao responsável técnico.</div></section></div>';
    foot();exit;
}