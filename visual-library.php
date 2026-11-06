<?php
declare(strict_types=1);
/**
 * Biblioteca visual editorial 3D do Odonto em Dia.
 * Ilustrações decorativas, sem representar procedimentos técnicos,
 * símbolos normativos oficiais ou certificação sanitária.
 */
function od_visual_catalog(): array {
    return [
        3  => ['key'=>'manual',       'title'=>'Manual de Boas Práticas',          'alt'=>'Documentos e organização clínica'],
        24 => ['key'=>'residuos',     'title'=>'PGRSS',                           'alt'=>'Composição conceitual de gerenciamento de resíduos'],
        11 => ['key'=>'esterilizacao','title'=>'POP de Esterilização',           'alt'=>'Ilustração conceitual de autoclave odontológica'],
        7  => ['key'=>'limpeza',      'title'=>'POP de Limpeza',                 'alt'=>'Conjunto ilustrativo de itens de limpeza'],
        6  => ['key'=>'epi',          'title'=>'POP de EPI',                     'alt'=>'Conjunto ilustrativo de equipamentos de proteção'],
        22 => ['key'=>'seguranca',    'title'=>'Plano de Segurança do Paciente',  'alt'=>'Checklist conceitual de segurança do paciente'],
    ];
}
function od_visual_current(int $templateId): ?array {
    return od_visual_catalog()[$templateId] ?? null;
}
function od_visual_schema(): PDO {
    $db=db();
    $db->exec("CREATE TABLE IF NOT EXISTS od_document_visual_history (
        clinic_id INTEGER NOT NULL,
        template_id INTEGER NOT NULL,
        version INTEGER NOT NULL,
        asset_key TEXT NOT NULL DEFAULT '',
        pack_version TEXT NOT NULL DEFAULT 'none',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (clinic_id,template_id,version)
    )");
    return $db;
}
function od_visual_snapshot(PDO $db,int $clinicId,int $templateId,int $version): void {
    $spec=od_visual_current($templateId);
    // Não altera versões anteriores: a ilustração é congelada por nova versão.
    $q=$db->prepare("INSERT INTO od_document_visual_history(clinic_id,template_id,version,asset_key,pack_version)
        VALUES(?,?,?,?,?)");
    $q->execute([$clinicId,$templateId,$version,$spec['key']??'', $spec?'dental-3d-v1':'none']);
}
function od_visual_historical(int $clinicId,int $templateId,int $version): ?array {
    $q=od_visual_schema()->prepare('SELECT asset_key,pack_version FROM od_document_visual_history WHERE clinic_id=? AND template_id=? AND version=?');
    $q->execute([$clinicId,$templateId,$version]);
    $row=$q->fetch();
    if(!$row || $row['pack_version']!=='dental-3d-v1')return null;
    $spec=od_visual_current($templateId);
    return $spec && $spec['key']===$row['asset_key'] ? $spec : null;
}
function od_visual_cover_markup(?array $spec): string {
    if(!$spec)return '';
    // A imagem não substitui orientação técnica; por isso é ignorada por leitores de tela.
    return '<div class="odx-cover-art" aria-hidden="true"><span class="odx-visual-3d odx-visual-3d--'.esc($spec['key']).'"></span></div>';
}
function od_visual_card_markup(int $templateId): string {
    $spec=od_visual_current($templateId);
    return $spec
        ? '<span class="od-ui-recommend-symbol od-ui-recommend-symbol--3d" aria-hidden="true"><span class="odx-visual-3d odx-visual-3d--'.esc($spec['key']).'"></span></span>'
        : '<span class="od-ui-recommend-symbol">'.od_ui_icon('file').'</span>';
}
