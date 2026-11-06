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
