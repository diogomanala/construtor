<?php
/**
 * Odonto em Dia | configuração dos formulários guiados dos planos.
 * Os campos organizam dados informados pela clínica. Não prescrevem procedimentos,
 * não atestam conformidade e não substituem revisão de profissional habilitado.
 */
declare(strict_types=1);

function odx_plan_configs(): array {
    return [
        22 => [
            'fields' => [
                ['key'=>'psp_governanca','label'=>'Organização da segurança do paciente','hint'=>'Identifique os responsáveis e a forma de coordenação adotada no serviço.','type'=>'textarea'],
                ['key'=>'psp_riscos','label'=>'Riscos e situações a acompanhar','hint'=>'Descreva os riscos levantados pela equipe, conforme a realidade da clínica.','type'=>'textarea'],
                ['key'=>'psp_notificacao','label'=>'Registro e comunicação de incidentes','hint'=>'Informe o fluxo interno definido para relatar e acompanhar ocorrências.','type'=>'textarea'],
                ['key'=>'psp_acoes','label'=>'Acompanhamento das ações e revisões','hint'=>'Defina como responsáveis e prazos serão acompanhados e revisados.','type'=>'textarea'],
            ],
            'spec' => [
                'name'=>'Plano de Segurança do Paciente',
                'eyebrow'=>'Gestão de riscos e acompanhamento interno',
                'number'=>'PLANO / PSP',
                'flow'=>['Definir responsáveis','Mapear riscos','Registrar incidentes','Acompanhar ações'],
                'accent'=>'jade',
            ],
        ],
        23 => [
            'fields' => [
                ['key'=>'iras_escopo','label'=>'Serviços e atividades abrangidos','hint'=>'Liste os atendimentos e áreas efetivamente abrangidos pelo plano.','type'=>'textarea'],
                ['key'=>'iras_responsaveis','label'=>'Responsáveis e atribuições','hint'=>'Registre os responsáveis por executar, monitorar e revisar as rotinas.','type'=>'textarea'],
                ['key'=>'iras_evidencias','label'=>'Monitoramento e evidências','hint'=>'Informe quais registros são utilizados e onde são arquivados.','type'=>'textarea'],
                ['key'=>'iras_revisao','label'=>'Como são tratadas as não conformidades','hint'=>'Descreva o processo interno de registro, correção e revisão técnica.','type'=>'textarea'],
            ],
            'spec' => [
                'name'=>'Plano de Prevenção e Controle de Infecções',
                'eyebrow'=>'Rotinas, indicadores e evidências do serviço',
                'number'=>'PLANO / PP-IRAS',
                'flow'=>['Delimitar escopo','Definir responsabilidades','Reunir evidências','Revisar desvios'],
                'accent'=>'blue',
            ],
        ],
        25 => [
            'fields' => [
                ['key'=>'pgts_inventario','label'=>'Inventário de tecnologias abrangidas','hint'=>'Informe onde o inventário atualizado de equipamentos e tecnologias pode ser consultado.','type'=>'textarea'],
                ['key'=>'pgts_responsavel','label'=>'Responsáveis por cada etapa','hint'=>'Defina quem acompanha aquisição, utilização, conservação e desativação.','type'=>'textarea'],
                ['key'=>'pgts_manutencao','label'=>'Registros de serviços e verificações','hint'=>'Indique como são documentadas manutenções, inspeções e orientações de fabricante.','type'=>'textarea'],
                ['key'=>'pgts_eventos','label'=>'Ocorrências, retirada de uso e revisão','hint'=>'Descreva o encaminhamento interno quando uma tecnologia apresenta falhas.','type'=>'textarea'],
            ],
            'spec' => [
                'name'=>'Plano de Gerenciamento de Tecnologias',
                'eyebrow'=>'Inventário, uso, manutenção e rastreabilidade',
                'number'=>'PLANO / PGTS',
                'flow'=>['Inventariar','Atribuir responsáveis','Registrar serviços','Tratar ocorrências'],
                'accent'=>'amber',
            ],
        ],
        26 => [
            'fields' => [
                ['key'=>'agua_pontos','label'=>'Pontos e usos de água monitorados','hint'=>'Descreva os pontos de utilização identificados na unidade.','type'=>'textarea'],
                ['key'=>'agua_criterios','label'=>'Critérios e referências utilizados','hint'=>'Registre os parâmetros aplicáveis conforme a avaliação técnica e normas pertinentes.','type'=>'textarea'],
                ['key'=>'agua_rotina','label'=>'Amostragem, responsável e frequência','hint'=>'Indique o procedimento efetivamente contratado ou executado pela clínica.','type'=>'textarea'],
                ['key'=>'agua_acao','label'=>'Resultados, evidências e ações corretivas','hint'=>'Informe onde os resultados ficam guardados e como os desvios são acompanhados.','type'=>'textarea'],
            ],
            'spec' => [
                'name'=>'Plano de Monitoramento da Água',
                'eyebrow'=>'Pontos de uso, verificações e registros',
                'number'=>'PLANO / ÁGUA',
                'flow'=>['Mapear pontos','Definir critérios','Reunir resultados','Revisar ocorrências'],
                'accent'=>'blue',
            ],
        ],
        27 => [
            'fields' => [
                ['key'=>'edu_necessidades','label'=>'Necessidades de capacitação levantadas','hint'=>'Relacione atividades e lacunas apontadas pelos responsáveis da unidade.','type'=>'textarea'],
                ['key'=>'edu_programacao','label'=>'Cronograma de orientações e treinamentos','hint'=>'Registre temas, datas previstas, responsáveis e participantes previstos.','type'=>'textarea'],
                ['key'=>'edu_evidencias','label'=>'Evidências de participação e ciência','hint'=>'Defina onde serão mantidos materiais, listas e comprovantes aplicáveis.','type'=>'textarea'],
                ['key'=>'edu_acompanhamento','label'=>'Avaliação e atualização do plano','hint'=>'Descreva como ações realizadas e novas necessidades serão acompanhadas.','type'=>'textarea'],
            ],
            'spec' => [
                'name'=>'Plano Anual de Educação Permanente',
                'eyebrow'=>'Formação, registros e acompanhamento da equipe',
                'number'=>'PLANO / EDUCAÇÃO',
                'flow'=>['Levantar demandas','Programar ações','Registrar evidências','Revisar calendário'],
                'accent'=>'jade',
            ],
        ],
        28 => [
            'fields' => [
                ['key'=>'manut_inventario','label'=>'Equipamentos incluídos no cronograma','hint'=>'Identifique o inventário e os equipamentos que exigem acompanhamento.','type'=>'textarea'],
                ['key'=>'manut_programacao','label'=>'Programação e critérios de manutenção','hint'=>'Descreva periodicidade e recomendações técnicas aplicáveis a cada item.','type'=>'textarea'],
                ['key'=>'manut_prestadores','label'=>'Responsáveis e documentação dos serviços','hint'=>'Informe prestadores, responsáveis internos e guarda de relatórios ou comprovantes.','type'=>'textarea'],
                ['key'=>'manut_pendencias','label'=>'Acompanhamento de pendências e falhas','hint'=>'Defina o fluxo interno para registrar falhas, suspender uso quando necessário e revisar ações.','type'=>'textarea'],
            ],
            'spec' => [
                'name'=>'Plano Anual de Manutenção',
                'eyebrow'=>'Cronograma e acompanhamento de equipamentos',
                'number'=>'PLANO / MANUTENÇÃO',
                'flow'=>['Inventariar','Programar serviços','Registrar intervenções','Acompanhar pendências'],
                'accent'=>'amber',
            ],
        ],
    ];
}

/**
 * Complements the plan schemas with the remaining documents. Data are only
 * presentation questions and organizational flow labels, never clinical orders.
 * Missing or invalid JSON falls back to the original generic document editor.
 */
function odx_document_configs(): array {
    static $cached = null;
    if (is_array($cached)) return $cached;
    $plans = odx_plan_configs();
    $path = __DIR__.'/data/document-guides.json';
    $raw = @file_get_contents($path);
    $items = is_string($raw) ? json_decode($raw, true) : null;
    $additional = [];
    if (is_array($items)) {
        foreach ($items as $id => $row) {
            $id=(int)$id;
            if ($id < 1 || $id > 30 || isset($plans[$id]) || !is_array($row)) continue;
            $fields=$row['fields']??null;
            $spec=$row['spec']??null;
            if (!is_array($fields) || count($fields)!==4 || !is_array($spec) || !is_array($spec['flow']??null) || count($spec['flow'])!==4) continue;
            $keys=[];
            foreach ($fields as $f) {
                if (!is_array($f) || !isset($f['key'],$f['label'],$f['hint'],$f['type']) || !is_string($f['key']) || !preg_match('/^[a-z][a-z0-9_]{2,40}$/',$f['key']) || !in_array($f['type'],['text','textarea'],true)) continue 2;
                $keys[]=$f['key'];
            }
            if (count(array_unique($keys))!==4) continue;
            if (!in_array($spec['accent']??'', ['jade','blue','amber'],true)) continue;
            if (!isset($spec['name'],$spec['eyebrow'],$spec['number'])) continue;
            $additional[$id]=$row;
        }
    }
    return $cached = array_replace($plans,$additional);
}