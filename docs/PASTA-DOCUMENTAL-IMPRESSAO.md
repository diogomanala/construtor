# Pasta documental pronta para impressão | Odonto em Dia
Data: 07/11/2026

## Decisão de produto
O cliente quer ter um **conjunto documental bem organizado em papel**, sem precisar exportar, unir ou diagramar 30 arquivos manualmente. A plataforma oferece seleção dos materiais existentes e **impressão A4 unificada** com capa da clínica, índice, separadores, texto dos documentos, quadros ilustrados aprovados, campos de assinatura e lista de anexos reais a reunir.

**Limite normativo:** a RDC Anvisa 1.002/2025 estabelece requisitos de documentação/SDBPF acessível e formalizada, mas **não presume obrigação universal de manter todos os modelos exclusivamente em papel**. Documentos devem refletir o perfil da clínica e a revisão e aprovação pertinentes. A plataforma não emite nem inventa licenças, PBA, contratos, laudos, registros de atividades ou certificados.

## Rota e descoberta
- Configurador autenticado: `?page=pasta-documental`
- Visualização para impressão: `?page=pasta-impressao&ids[]=3&ids[]=6...`, gerada pelo servidor com dados da clínica da sessão
- Entrada no menu Documentos, card na biblioteca e link na Trilha de primeiros passos.
- Não criar tela nova para editar documentos: a pasta consulta os documentos já personalizados e oferece botão para voltar ao editor original.

## Arquivos e responsabilidade
- `pasta-documental.php`: seleção, lista de documentos/situação por clínica, sanitização de IDs, renderização da pasta para impressão e quadros estáticos.
- `pasta-documental.css`: UI do configurador e projeto editorial A4, cores institucionais/acento personalizado, separadores e `@page` para paginação.
- `pasta-documental.js`: contagem de documentos e seletores "Sugestão inicial", "Apenas personalizados" e "Todo o acervo".
- `index.php`: rotas autenticadas e inclusão dos arquivos; `guided-ux.php`: acesso na biblioteca e na trilha.
- Dependências reutilizadas: `clinic()`, `od_brand_current()`, `odx_schema()`, `odx_get_fields()`, `odx_spec()`, `od_safe_document()`, `od_guided_prepare_template_html()`, `od_illustrated_guide_for_document()`, `od_steril_visual_items()`.

## Conteúdo gerado
1. Capa premium com nome, logotipo ou monograma, CNPJ, RT/CRO, local, data e aviso de documentos em preparação.
2. Índice dos modelos selecionados por tipo e situação de revisão.
3. Divisórias de seção opcionais: SDBPF, Planos, POPs, Formulários, Guia de uso.
4. Documento por documento, a última versão salva para aquela clínica; se nunca salvo, o modelo de referência é exibido com etiqueta de **não personalizado**. Informações do editor são apresentadas em bloco.
5. Quadros visuais estáticos nos documentos pertinentes (esterilização, EPI, resíduos, equipamentos), com cena e imagens isoladas legíveis e legenda numerada; só usa conjuntos aprovados no catálogo.
6. Área de revisão com espaço de assinatura manual de RT e responsável legal, sem assinatura presumida.
7. Lista opcional de anexos e evidências reais, a serem providenciados externamente e conferidos conforme o perfil do serviço.

## Segurança e não regressão
- Rota atrás de autenticação, acesso ativo e clínica selecionada.
- Consultas sempre limitadas por `clinic_id` da sessão, nunca ID de clínica vindo do GET.
- Documentos selecionados por `ids[]` validados como inteiros de 1 a 30 e contra o catálogo; máximo 30 itens.
- Conteúdo de minutas reutiliza `od_safe_document`. Nome e demais campos via `esc`.
- `noindex,nofollow`, `Cache-Control: private, no-store`; nenhuma publicação pública dos documentos.
- Nenhuma alteração ou novo registro em SQLite durante a impressão. Históricos, assinaturas reais e estados de aprovação não são alterados.
- As ilustrações são referências; imagens não são certificados sanitários.

## Verificação
- PHP CLI `php -l` do novo módulo passou.
- Teste de renderização com banco/sessão **simulados** cobriu configurador e saída A4, 4 documentos e visual ilustrado.
- JavaScript passou em `node --check`.
- Revisão estática de rotas, consultas, sanitização, integração e estilos.
- **Pendente:** teste visual real no navegador autenticado, ajustes de quebra de página em documentos longos, teste em impressoras físicas e impressão por duas clínicas diferentes. Não classificar como QA E2E concluído.

## Próximos avanços possíveis
- Ajustar folhas pré-formatadas de registros para rotinas que exigem preenchimento manual, sem gerar registros fictícios.
- Quando houver evidências reais conectadas ou enviadas, permitir anexá-las ao pacote por escolha explícita do usuário, com validação.
- Se o usuário desejar, acrescentar armazenamento de "edições da pasta" (seleção + data de emissão), mantendo controle de versões; não é requisito para a primeira entrega.
