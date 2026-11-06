# DocSystem Premium v1 | Odonto em Dia
Data: 2026-11-06
Deploy: https://odontonegocios.com/rdc/ (branch odonto-em-dia-hostinger)
Prioridade comercial: entregável da plataforma, não PDFs/DOCX nem links para editar no Canva.

## Implementado
- `document-engine.php` : motor e biblioteca premium, prévia documental, campos guiados, 3 esquemas específicos, histórico por clínica e por versão, status de revisão técnica, ferramenta de registro de ciclos de esterilização.
- `document-engine.css`: identidade documental própria, capas, quadros de processos esquemáticos, dados da unidade, sumários, tabelas, formulários, biblioteca responsiva e painel de registro.
- `document-engine.js`: atualização da prévia em tempo real, progresso de campos, índice de seções, alternância de edição de texto, aviso de alterações não salvas, filtros da biblioteca.
- `index.php`: integra rotas `documentos`, `editar`, `meus-documentos`, `registros-esterilizacao`, além dos POSTs protegidos por CSRF.
- `landing.php`: oferta ajustada para entrega no próprio ambiente, sem dependência de DOCX/PDF.
- Mantém 30 modelos como biblioteca, com 3 pilotos destacados: id 3 Manual de Boas Práticas/SDBPF, id 11 POP Esterilização e id 24 PGRSS.
- Grava modelos editados em `od_documents`, conteúdo por versão em `od_document_history`, dados guiados atuais em `od_document_details`, dados por versão em `od_document_fields_history`.
- Registros de ciclos em `od_steril_monitoring`, isolados por clínica; resultados são inseridos pelo usuário, jamais validados automaticamente.

## O que NÃO foi feito
- Não há homologação ou certificação Anvisa/CRO.
- Os conteúdos clínicos originais não foram alterados ou considerados revisados por RT.
- Não há imagens clínicas próprias em todos os 30 documentos; os diagramas inseridos são mapas de organização não clínicos. Outros 27 modelos usam conteúdo-base legado com visual de apresentação premium.
- Não há aprovação digital/assinatura eletrônica do RT.
- Sem teste ponta a ponta autenticado no ambiente público, pois o agente não possui credenciais da conta piloto.
- Acesso de teste deve ser validado manualmente pelo proprietário nos caminhos `?page=documentos`, `?page=editar&id=3`, `?page=editar&id=24`, `?page=editar&id=11`, `?page=registros-esterilizacao`.

## QA realizada
- Sintaxe PHP de `document-engine.php` aprovada com `php -l` em ambiente local (PHP 8.4).
- JS aprovado com `node --check`.
- Especificações 3, 11 e 24 têm 7 campos guiados e quatro etapas visuais cada.
- Hostinger confirmou deploy Git e presença dos arquivos `document-engine.php/css/js`, `index.php` e `landing.php`; CDN purge aceita.
- Falta teste funcional autenticado no navegador público e conferência de renderização em vários dispositivos.

## Pendências antes da venda
1. Testar login, biblioteca, formulário, histórico, isolamento por clínica, estados de revisão, histórico de esterilização.
2. Conferência técnica do conteúdo de cada POP e plano com RT habilitado, inclusive anexos e normas locais; obter evidência de revisão.
3. Aprofundar figuras e diagramas originais pertinentes nos demais documentos, observando direitos de imagem e validação profissional.
4. Testar compra real em ambiente adequado e webhook do Mercado Pago, provisionamento, expiração de acesso e pedido cancelado.
5. Avaliar proteção de registros e retenção conforme obrigações legais; evitar dados pessoais de pacientes.

## Implementação adicional, 06/11/2026: controle de limpeza
- Nova rota autenticada: `?page=registros-limpeza`, no mesmo sistema PHP/SQLite (sem serviços pagos).
- Tabela `od_cleaning_records`, índice por clínica/data, inserção com CSRF na rota `cleaning_record`, data e horário explícitos, área, produto/diluição, responsável, status administrativo e observações.
- Histórico por clínica com filtro por data e situação; limite de 200 registros por listagem. Campos de texto validados, saída HTML escapada, consultas parametrizadas e identificação da clínica vinda exclusivamente da sessão.
- Acesso pelo menu, biblioteca e POP 04 de Limpeza de Ambientes (id 7). Não coleta dados de pacientes nem aprova automaticamente o processo.
- Arquivos alterados: `document-engine.php`, `document-engine.css`, `index.php`; deploy Git na Hostinger confirmado e presença física dos arquivos conferida, com solicitação de limpeza da CDN.
- **Verificação ainda pendente:** teste ponta a ponta em conta autenticada, persistência/recarregamento, isolamento entre clínicas, mobile e fluxo de erros. O deploy não equivale a QA final.

## Cérebro permanente
- [Cérebro do projeto no Notion](https://app.notion.com/p/410739c88b0a81f8a436d82129ff205a)
- [Banco Execução e decisões](https://app.notion.com/p/9d000ffdd9654a69a058422911cb7d7a)
- [Agent Operating Guide](https://app.notion.com/p/40f739c88b0a81808ae5fc4c50cab231)
- Regra: GitHub permanece como fonte do código. Notion registra decisões, pendências, prioridades, checkpoints e link à versão; não salvar credenciais, logs sensíveis ou conversas completas.

## Checkpoint v1.2 (06/11/2026) — Registros de manutenção e EPI

### Implementado no branch `odonto-em-dia-hostinger`
- Novo módulo `operational-records.php`, com dois formulários online: `?page=registros-manutencao` e `?page=registros-epi`.
- Estrutura persistente `od_ops_records` (SQLite existente), com `clinic_id` sempre obtido do servidor, `record_kind`, data, responsável, situação administrativa, detalhes em JSON e registro temporal. Histórico limitado a 200 resultados com filtros por data e status; consultas com parâmetros.
- Validações positivas e negativas, sem status de "aprovado tecnicamente". Registro de EPI não equivale a comprovante assinado; manutenção não substitui relatórios/laudos do prestador.
- Novos campos guiados e mapas visuais específicos nos POPs 03 (id 6), 04 (id 7) e 18 (id 21). Total: 6 modelos com configuração individual (ids 3,6,7,11,21,24); outros 24 ainda genéricos.
- Links no menu principal, biblioteca e nos três respectivos POPs. CSS existente reutilizado.
- Proteção reforçada no `index.php`: POSTs de documentos, registros, atividades e autoavaliações exigem conta piloto autorizada ou direito de acesso pago ainda ativo, como no GET.
- Removida a informação desatualizada "checkout ainda não está ativo" do primeiro cadastro; isso não valida o checkout.

### Evidências verificadas
- `php -l` no novo módulo e script CLI de testes no PHP 8.4 local: sem erros de sintaxe.
- Testes locais positivos e negativos para manutenção e EPI (data, quantidade, tipo, status, limites e campos malformados) passaram.
- Consulta SQLite em base sintética confirmou separação por `clinic_id` e tipo de registro; **não é teste ponta a ponta na hospedagem**.
- Hostinger aceitou deploy Git e confirmou presença de `operational-records.php`, `index.php`, `document-engine.php`, e arquivos de teste protegidos em `tests/.htaccess`; cache CDN limpo.

### Pendências P0 sem evidência ainda
1. Entrar na aplicação em duas contas de clínica, gravar registros de limpeza/esterilização/manutenção/EPI, recarregar, testar filtros e ausência de acesso cruzado. Verificar também histórico de documentos e proteção de POST expirado.
2. Testar compra Mercado Pago válida, webhook, recuperação, expiração, pedido cancelado e direito de acesso.
3. Revisão clínica/regulatória por RT habilitado e, quando necessário, jurídica. Sem selo ou certificação automática.
4. Continuar aprimorando os demais 24 modelos de referência, priorizando os POPs com registro operacional. Não acrescentar arte clínica sem validação de conteúdo ou direitos.

## Checkpoint v1.3 (06/11/2026): regressão local e plano de QA P0
- Confirmadas pela Hostinger em modo somente leitura: PHP **8.2.35**, extensões **pdo_sqlite, sqlite3, mbstring, dom, curl e zip** habilitadas.
- `tests/tenant-regression.php`: exercício de registros de manutenção/EPI com PDO simulado, verificando gravação por clínica da sessão, tentativa de forjar `clinic_id`, filtros, rejeição de campos inválidos e ausência de leitura cruzada. Execução CLI local passou nos cenários cobertos.
- `tests/document-history-regression.php`: versão documental simulada por clínica, snapshots de campos, recuperação da versão anterior e impedimento de consulta cruzada. Execução CLI local passou nos cenários cobertos. Sanitização HTML e persistência SQLite real **não** são testadas nesse harness.
- Scripts em `tests/` aceitam somente CLI; pasta protegida por `.htaccess`. Nenhum teste usa credenciais reais, dados de pacientes, cobrança ou modifica o banco de produção.
- `docs/QA-ACESSO-CLINICAS.md`: matriz com **12 casos P0** para teste autenticado, separação entre clínicas, histórico, controles operacionais, permissões e Mercado Pago.
- Chamadas públicas de verificação por ferramentas externas não alcançaram o domínio; a confirmação funcional real permanece **pendente**.
- Estado esperado no Notion: **P0 QA em execução**, não "Verificado". Próximo passo: executar casos autenticados A/B e registrar resultados, depois testar o pagamento de forma controlada.

## Checkpoint v1.4 (06/11/2026): primeiro acesso do proprietário
- `index.php` commit `8fca787f`: cadastro piloto do primeiro usuário agora usa transação e verifica contagem de usuários dentro dela; exige convite exclusivo, e-mail válido e senha criada pelo proprietário (mínimo de 12 caracteres). Não contém senha fixa.
- Após criação atômica, o sistema inclui uma **clínica fictícia de demonstração** (sem pacientes, RT ou CNPJ) e estabelece a sessão do próprio navegador com `session_regenerate_id(true)`. Redireciona direto para a biblioteca, pronto para teste dos documentos.
- O primeiro usuário recebe acesso de proprietário no fluxo piloto existente (`od_is_pilot`); **não existe um módulo de papéis administrativos separado**.
- Hostinger confirmou deploy Git e arquivo index.php atualizado. A listagem de `rdc/data` não apresentava arquivo SQLite na consulta; isso sugere, sem provar, que o primeiro cadastro ainda não foi concluído.
- **Não confundir:** um deploy não cria uma sessão no navegador do cliente. O próprio proprietário precisa definir e-mail e senha pelo formulário de primeiro acesso. O agente não criou usuário real nem conhece a senha; login e persistência aguardam verificação do proprietário.
- Nenhuma credencial real, senha ou código de convite deve ser registrado em GitHub/Notion. O convite existente deve perder utilidade após o primeiro cadastro pelo bloqueio de usuários.

## Checkpoint v1.5 (06/11/2026): mais seis planos específicos dentro do editor

- Adicionado `document-plan-config.php` com formulários guiados e mapas de organização próprios dos modelos **22 (PSP), 23 (PP-IRAS), 25 (PGTS), 26 (Água), 27 (Educação Permanente) e 28 (Manutenção)**.
- `document-engine.php` integra os campos e a aparência dos seis planos à mesma biblioteca, à prévia e ao versionamento; vincula os planos 25 e 28 ao controle operacional de manutenção já existente.
- Retrocompatibilidade: os campos legados `aplicabilidade` e `evidencias` continuam visíveis/editáveis nos seis planos, caso um usuário os tenha preenchido antes da especialização. Documentos originais e versões permanecem armazenados, sem alteração do conteúdo clínico.
- Total atual **12 de 30** modelos com campos e mapa específicos: ids **3,6,7,11,21,22,23,24,25,26,27,28**. **18 ainda utilizam campos genéricos**, exigindo aprimoramento individual.
- Regressão local PHP: `php -l` da nova configuração aprovado; validação de chaves únicas, quatro campos específicos por plano, quatro etapas visuais e integrações testadas em PHP 8.4 local. Adicionado `tests/plan-config-regression.php`, somente CLI.
- Hostinger aceitou deploy do branch `odonto-em-dia-hostinger`, confirmou presença de `document-plan-config.php`, `document-engine.php`, README e teste, e aceitou limpeza da CDN.
- README atualizado para não orientar futuros agentes à entrega antiga de ZIP/Word.
- **Não significa QA funcional autenticado**. O arquivo SQLite de produção ainda não aparece na listagem do diretório `rdc/data/` nesta sessão; não há evidência de ativação real do primeiro proprietário. Não compartilhar convites ou senhas no GitHub/Notion.
- Revisão clínica/regulatória por RT habilitado não concluída. Campos são guiados de organização administrativa e as minutas continuam sem certificação.

## Checkpoint v1.6 (06/11/2026): cobertura guiada dos 30 modelos

- Criado `data/document-guides.json` com mapas editoriais e quatro perguntas específicas para os **18 modelos que ainda usavam campos genéricos**: ids `1,2,4,5,8,9,10,12,13,14,15,16,17,18,19,20,29,30`.
- `document-plan-config.php` agora fornece `odx_document_configs()`, mesclando os seis planos documentais anteriores e os 18 adicionais com validação de chaves/estrutura, fallback para conteúdo original se JSON ausente ou inválido.
- `document-engine.php` utiliza o mapa completo na biblioteca e no editor. Os seis modelos já especializados no próprio motor (ids `3,6,7,11,21,24`) permanecem intactos.
- **30/30 documentos têm campos guiados e mapas de quatro etapas de organização**. Os 24 migrados recentemente preservam os campos legados `aplicabilidade` e `evidencias`, além do HTML de origem. Nada foi sobrescrito no banco.
- Novo `tests/document-guide-coverage.php`: teste CLI em PHP local aprovou cobertura exata 1..30, ausência de colisões e quatro campos/etapas por configuração. Integração de 24 esquemas com os dados legados verificada em cópia local do renderizador. Não é teste em produção autenticada.
- GitHub: `data/document-guides.json`, `document-plan-config.php`, `document-engine.php`, teste e README; deploy Hostinger confirmado e presença de arquivos no servidor conferida; CDN limpa.
- **Não significa que o conteúdo técnico ou a diagramação de cada minuta foram validados**. Os fluxos são mapas administrativos, sem instruções clínicas inventadas. Ainda falta revisão documental, elementos gráficos e fichas específicas com RT habilitado.
- **P0 de QA permanece:** cadastro de primeira conta real não confirmado; `rdc/data/` ainda não mostrava SQLite na listagem de arquivos. Testar login, versões e isolamento entre duas clínicas no navegador com contas autorizadas; Mercado Pago sem teste completo.
