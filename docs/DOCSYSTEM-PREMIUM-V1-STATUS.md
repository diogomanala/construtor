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
