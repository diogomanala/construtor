# DESIGN | Odonto em Dia

## Contexto do produto
Plataforma odontológica brasileira de organização documental, utilizada por clínicas e seus responsáveis técnicos. A área autenticada é uma ferramenta de trabalho, não uma segunda landing. Prioridade: clareza na navegação, leitura de minutas, salvamento de versões e registro de ocorrências. Documentos permanecem minutas até revisão formal profissional.

## Identidade existente e aprovada
- Marca da plataforma: **odonto** (tinta escura) + **em dia** (verde), com legenda **GESTÃO DOCUMENTAL**.
- Ícone único e original: `assets/favicon.svg` (símbolo de dente e visto). Reutilizar exatamente esse arquivo na landing e na área de membros. Não gerar versões do logo com caracteres, emojis ou formas inventadas.
- Tipografia: `Inter, system-ui, -apple-system, Segoe UI, sans-serif`. Títulos densos e precisos, navegação discreta, etiquetas legíveis.
- Registro visual: ambiente clínico contemporâneo; branco, verdes contidos, cinza esverdeado e contrastes sem efeitos decorativos desnecessários.

## Cores em uso
| Papel | Cor | Fonte canônica |
| --- | --- | --- |
| Fundo da área de membros | `#f6f8f7` | `style.css` |
| Texto principal | `#142f34` | `style.css` |
| Marca e ações | `#0b8175` | `style.css` |
| Identidade da landing | `#117c70` | `sales.css` |
| Texto da assinatura do logo | `#102e33` | `member-shell.css` |
| Texto auxiliar | `#687f81` | `style.css` |
| Divisórias | `#e1e9e6` | `style.css` |
| Superfície | `#ffffff` | superfícies existentes |

O CSS existente é a fonte de verdade em runtime (modelo B da frontend-design-premium). Este documento descreve as decisões, não gera tokens. Alterações estruturais devem conciliar o CSS e este arquivo no mesmo conjunto de mudanças. Unificar aliases gradualmente, não durante correções urgentes.

## Composição e responsividade
- Cabeçalho: `index.php::h()` gera a navegação única. `member-shell.css` estiliza. Não criar um menu independente por tela.
- Cabeçalho da área autenticada: mínimo de 78 px no desktop e 68 px em telas até 520 px. Conteúdo centrado, largura máxima de 1240 px; marca à esquerda, acesso e navegação à direita.
- Tela acima de 920 px: quatro destinos de uso frequente visíveis e menu expansível com todas as rotas.
- Até 920 px: manter marca visível e um único menu expansível nativo. O painel flutua sobre o conteúdo, sem expandir nem cortar o cabeçalho.
- Conteúdo geral: `main` limita a 1120 px em `style.css`; biblioteca/editor podem chegar a 1440 px nos layouts específicos de `document-engine.css`. Não uniformizar à força.
- Cards e documentos: cantos suaves; sombras pequenas; fundo predominantemente branco; linhas separadoras funcionais. Nenhuma informação obrigatória deve depender só da cor.
- Segurança visual: o status “minuta / aguardando revisão” não pode parecer aprovação técnica.

## Identidade visual da clínica (implementada em código)
- Configuração em `?page=identidade` com cinco paletas aprovadas: jade, navy, graphite, plum e clay, além de cor personalizada hexadecimal com contraste mínimo 4.5:1 contra branco.
- Cada clínica tem nome próprio e logo PNG/JPG/WebP validado pelo PHP com máximo 350 KB e 3000 px por dimensão. Imagem armazenada como BLOB privado no SQLite, não em diretório público. Endpoint autenticado: `?page=logo-clinica`.
- `clinic-branding.php` é dono do esquema, validações e tratamento de imagens; `clinic-branding.css` do visual e override do papel; `clinic-branding.js` da prévia instantânea. Não duplicar nem converter estes estilos em tema global.
- O DocSystem aplica cores e nome no cabeçalho/na capa da minuta. A marca Odonto em Dia (`assets/favicon.svg`) permanece global, não é substituída pela identidade da clínica.
- No salvamento de uma nova versão, `od_document_brand_history` congela nome/dados de cabeçalho, cor e logo. Ao trocar a identidade, as versões antigas sem snapshot são congeladas com os dados disponíveis naquele momento, pois alterações anteriores à implantação não são recuperáveis.
- Design e isolamento ainda requerem QA autenticado, especialmente imagens grandes, contraste, versões anteriores, clínicas distintas e 320px/390px.
- [Tarefa e critérios no Notion](https://app.notion.com/p/410739c88b0a81808084d79bc9320a1b).

## Checklist de revisão
1. Verificar a mesma marca em landing e área autenticada.
2. Testar menu com mouse, toque e teclado, inclusive 320/390/768/1024/1440 px, zoom e títulos longos.
3. Conferir que links levam às rotas existentes e mostram `aria-current` corretamente.
4. Verificar que menu não encobre controles com foco nem cria rolagem horizontal.
5. Testar biblioteca, editor, histórico, painel e registros com sessão real antes de declarar homologação visual.
6. Preservar estrutura do banco SQLite e contratos de pagamento, revisão RT e isolamento entre clínicas.

## Rastreabilidade
- [Repositório e branch](https://github.com/diogomanala/construtor/tree/odonto-em-dia-hostinger)
- [Cérebro do projeto](https://app.notion.com/p/410739c88b0a81f8a436d82129ff205a)
- Skills aplicadas em 06/11/2026: `frontend-design` e `frontend-design-premium`.

## Biblioteca visual 3D aprovada, 06/11/2026
- Seis ilustrações aprovadas pelo usuário foram reunidas em um atlas leve AVIF de 540×360 px, aprox. 12,7 KB: `assets/illustrations/odonto-3d-atlas.avif`.
- Mapeamento fixo no `visual-library.php`: 3 Manual SDBPF (`manual`), 24 PGRSS (`residuos`), 11 POP Esterilização (`esterilizacao`), 7 POP Limpeza (`limpeza`), 6 POP EPI (`epi`), 22 PSP (`seguranca`). O sprite 3×2 usa exatamente essa ordem e `visual-library.css` aplica a imagem à capa e aos cartões recomendados.
- São ilustrações conceituais/decorativas, **não** demonstrações corretas de procedimentos ou símbolos oficiais. Não usar a composição de resíduos para prescrever cores ou classificações; isso exige revisão do RT com a regra aplicável.
- As capas continuam legíveis: objeto no lado direito em telas maiores, acima do título em telas estreitas. Não colocar 3D no corpo da minuta, nos campos ou nas assinaturas.
- `od_document_visual_history` registra o pacote visual da versão no salvamento. Versões antigas sem fotografia visual permanecem sem essas ilustrações, evitando alterações retrospectivas silenciosas.
- QA ainda necessário: telas 320/390/768/1440px, contraste, impressão, performance da imagem, histórico, salvamento, isolamento de clínicas e verificação no navegador autenticado. Aprovação visual das artes não significa certificação dos textos.

## Protótipo de esterilização ilustrada, 07/11/2026
- Rota autenticada: `?page=ilustracao-esterilizacao`, acessível pelo POP 08 (modelo id 11) em `document-engine.php`. Não adicionar outra aba principal ao menu.
- Arte original **vetorial** em `steril-visual.php::od_steril_visual_svg()`, com objetos separados e nítidos em qualquer escala: autoclave, seladora, embalagem, indicador químico, indicador biológico, bandeja perfurada, cassete de instrumentais. A área ilustrada ocupa a maioria da tela, com painel de informações à direita; clicando no objeto ele é ampliado e apresenta função, verificação e seleção.
- Cores: neutros metálicos, verdes clínicos contidos e alto contraste. Sem arte PNG reprovada, fundo branco embutido ou render artificial. Paleta da clínica é respeitada no acento de apresentação, sem sobrescrever os gradientes próprios dos equipamentos.
- Responsividade: objetos em 3 colunas no desktop, destaque para a autoclave; 2 colunas até 640 px, com a autoclave em largura dupla. No celular, detalhes em `dialog` nativo com botão de fechar e Escape.
- Impressão: quadro e legenda numerada em A4, somente itens selecionados, logotipo e nome da clínica, aviso de revisão técnica. A impressão não pode depender de tooltips ou conteúdo oculto no navegador.
- Comportamento e dados: `steril-visual.js` orquestra clique/seleção/modal/legenda e `steril-visual.css` é dono dos estilos. Escolhas em SQLite por `clinic_id`, **separadas** dos ciclos reais, do documento e do histórico já salvo. Trata-se de referência visual, não da execução ou certificação do processamento.
- Estado: implementação e deploy sujeitos a QA de navegador autenticado, desktop/mobile e impressão. Exigir revisão técnica da descrição e da fidelidade dos objetos antes de tratar como material assistencial validado.

## Correção de UX da esterilização ilustrada, 07/11/2026
- A primeira tela independente de SVGs foi rejeitada: desenhos fracos, grade com quadrados azuis de checkbox (a regra global `input{display:block}` expunha checkboxes `hidden`) e entrada distante do POP. A tela antiga foi retirada da navegação funcional.
- Padrão substituto: **uma única fotografia editorial ilustrativa**, com sete itens numerados, integrada à prévia do **POP 08** (ID 11) imediatamente depois dos dados da clínica. Não é um novo menu/tela. Fotografia em `assets/illustrations/esterilizacao-bancada-fotografica-v1.avif`, 720×358 px, 12.882 bytes, SHA Git `ba2fe0e30aede6eb09ffe20dda95d10eec75be23`, gerada como arte original de apoio conceitual.
- Sete pontos transparentes de clique mapeiam os números já impressos na fotografia. Um painel compacto mostra nome, função e ponto de conferência; uma lista discreta dá acesso por teclado e reforça a legenda.
- Donos de código: `steril-inline.php` (marcações e dados), `steril-inline.css` (layout dentro do papel e impressão), `steril-inline.js` (foco/seleção). As artes antigas permanecem arquivadas, mas não são carregadas.
- **Qualidade**: exibir a imagem no máximo na escala original útil. Sem ampliar thumbnails, sobrepor checkboxes ou usar sprite antigo. Reprodução concebida como visualização e não representação fotográfica de equipamento real ou protocolo de esterilização.
- Históricos antigos de documento não recebem a peça visual nova; o quadro é independente de `body_html` e do banco de ciclos. Requisitos técnicos ainda sujeitos ao RT.

## Padrão oficial aprovado para guias ilustrados, 07/11/2026
O usuário aprovou visualmente o **quadro integrado ao POP 08**: foto contextual de bancada, itens numerados clicáveis, painel lateral que substitui a foto recortada por um **objeto isolado em PNG/WebP transparente premium** com nome/finalidade e o que conferir. É a referência para EPI (POP 03), resíduos (POP 11/PGRSS) e inventário de equipamentos (POP 18/PGTS/Plano de Manutenção). O novo componente `illustrated-guides.php` reutiliza esse contrato, definido em `docs/PADRAO-GUIAS-VISUAIS-APROVADO.md`. As novas seções ficam ocultas até fotos e objetos individuais serem aprovados; não publicar quadros incompletos nem alterar o guia já aprovado de esterilização.
