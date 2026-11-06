# Odonto em Dia | Plano de QA funcional e isolamento de clínicas
Atualizado em: 2026-11-06
Origem: Notion Cérebro | Odonto em Dia, P0 QA autenticado.
Branch: `odonto-em-dia-hostinger`.
Escopo: somente `odontonegocios.com/rdc/`. Preservar o site principal, dados reais, recebimentos e permissões.

## Preparação
- Este documento é um roteiro de **teste ainda não concluído**, não um relatório de aprovação de produção.
- Não divulgar senhas, Access Tokens, segredos ou dados de pacientes. Usar duas contas de TESTE independentes, cada uma vinculada a clínica fictícia e sem informações reais.
- Não forçar compra, inserir dados fictícios no ambiente de cobrança nem criar acesso não autorizado. Não rodar scripts de teste via web.
- Fazer backup do SQLite antes de qualquer intervenção que altere o banco; nunca sobrescrever o banco durante deploy.
- Confirmar acesso piloto ou direito de acesso válido nas duas contas. Cancelamento e expiração só em ambiente isolado controlado.

## Evidências já obtidas
- Hostinger: PHP 8.2.35, extensões `pdo_sqlite`, `sqlite3`, `mbstring`, `dom`, `curl` e `zip` reportadas como habilitadas/build-in.
- PHP CLI local: `tests/ops-smoke.php` e `tests/tenant-regression.php` (15 checagens de inserção e isolamento com PDO simulado) e `tests/document-history-regression.php` (13 assertivas de versão e isolamento em PDO simulado). Não são testes com SQLite real no servidor nem testes E2E.
- Hostinger confirmou deploy de `operational-records.php` e dos scripts de QA sob `tests/.htaccess` (protegidos contra acesso web).
- Os diretórios `data/` e `tests/` contêm arquivos `.htaccess` para negar acesso pela web; verificar efetivamente o bloqueio HTTP antes da venda.

## Casos P0 de aceite funcional
| Código | Cenário | Resultado esperado | Situação |
| --- | --- | --- | --- |
| QA-01 | Cliente A entra e cadastra clínica teste | Painel do próprio A, sem dados de B | Não executado no navegador |
| QA-02 | A abre biblioteca e seis POPs guiados (3,6,7,11,21,24) | Carregamento, visual, campos específicos; não exige Word/Canva | Não executado |
| QA-03 | A preenche e salva documento 3 duas vezes | Versões 1 e 2, conteúdo salvo e snapshot da versão 1 preservado | Simulado localmente, pendente E2E |
| QA-04 | B abre documento 3 | Não vê versões, nome da clínica ou conteúdo do A | Simulado localmente, pendente E2E |
| QA-05 | A registra limpeza e filtra por data/status | Histórico persistente após novo login | Não executado |
| QA-06 | A registra carga de esterilização, sem aprovação automática | Histórico mostra observações; não autoriza uso de carga | Não executado |
| QA-07 | A registra manutenção, B consulta histórico | Somente A vê manutenção de A | Simulado localmente, pendente E2E |
| QA-08 | B registra EPI, A consulta histórico | Somente B vê EPI de B | Simulado localmente, pendente E2E |
| QA-09 | Contas sem direito de acesso tentam URL e POST de salvamento | Acesso negado, banco não sofre alteração | Guardas no código, pendente E2E |
| QA-10 | Tentativa de baixar `/rdc/data/odonto_em_dia.sqlite` e `/rdc/tests/tenant-regression.php` | HTTP 403/404; nenhum conteúdo exposto | Pendente teste HTTP |
| QA-11 | Navegação móvel e desktop na biblioteca, editor e registros | Formulários completos, botões visíveis, sem overflow ou dados perdidos | Pendente |
| QA-12 | Compra via Mercado Pago, webhook, liberação e revogação após cancelamento | Estado de pagamento confirmado pelo servidor, sem ativação por retorno de URL | Pendente teste controlado |

## Como executar QA sem dados reais
1. Abra `https://odontonegocios.com/rdc/?page=entrar` em duas sessões isoladas, com **duas contas autorizadas de teste distintas**.
2. Use somente nomes fictícios de clínica e responsáveis. Não preencher pacientes nem dados reais de funcionário.
3. Na clínica A salve duas versões de um modelo e um registro em cada controle.
4. Na clínica B repita consultas, filtros e tentativa de acessar as versões. B não pode recuperar nada da A.
5. Verifique se A ainda encontra suas informações após sair/entrar novamente.
6. Tire capturas de tela **sem qualquer dado pessoal** ou anote erro e URL; não copie tokens, cookies, hashes nem o banco para o Notion.
7. Anote em cada caso: **Data / Conta de teste (A ou B) / Resultado / Evidência / Commit verificado / Aberto ou Resolvido**. Atualize este arquivo e a tarefa P0 no Notion.

## Critérios de bloqueio de lançamento
- Falta de teste autenticado impede chamar o isolamento de clínicas de "validado em produção".
- Documentos ainda são minutas; aprovação requer RT habilitado e evidência de revisão técnica.
- Configuração das chaves Mercado Pago informada pelo proprietário não comprova compra, webhook e provisionamento.
- Não confundir teste local com homologação final ou aprovação pela vigilância sanitária.
