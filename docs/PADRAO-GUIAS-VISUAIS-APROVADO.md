# Padrão visual aprovado: guias de materiais por documento

**Decisão registrada em 07/11/2026.** O usuário aprovou a composição visual e a experiência do [POP 08 de esterilização](https://odontonegocios.com/rdc/?page=editar&id=11#odx-esteril-visual), incluindo os **sete materiais isolados aprovados** exibidos ao clicar em números da bancada. Esse é o padrão para outros documentos em que imagens realmente ajudam na identificação dos objetos.

## Padrão obrigatório
1. Cena principal clara, com os objetos posicionados em uma bancada ou contexto realista e identificados por marcadores numerados discretos. Sem foto decorativa que atrapalhe o objeto.
2. Ao clicar no número ou na legenda, o painel ao lado troca **somente** a imagem do item, o nome, a categoria, a finalidade resumida e um ponto de conferência. **Nunca ampliar um recorte da cena.**
3. A imagem selecionada é arquivo **individual transparente**, conferido, nitidamente reconhecível, com sombra muito leve e sem quadrado branco/halo artificial; usar `object-fit:contain`. Padrão de referência: WebP 810×810 gerado a partir de PNG transparente aprovado, sem esticar além da resolução.
4. Grid de legenda numerada acessível, foco claro, clique por teclado, responsivo no celular, impressão limpa. Capa e conteúdo técnico do documento permanecem intactos. Guia é material auxiliar, não prova de regularidade ou procedimento aprovado.
5. Uma cena e um conjunto de imagens só serão habilitados depois que **todos** os itens daquele guia passarem pela aprovação visual, identificação técnica e QA. Não promover guias incompletos.
6. Não reutilizar o pack antigo de 54 PNGs rejeitados, os sprites borrados nem o anterior quadro SVG; tampouco copiar artes do Canva da concorrência.

## Onde o padrão será aplicado
| Grupo | Documentos existentes | Objetos planejados | Status |
|---|---|---|---|
| EPIs e equipe | POP 03 (#6) | Máscara, luvas, óculos, protetor facial, gorro e avental | Aguardando imagens |
| Resíduos | POP 11 (#14), PGRSS (#24) | Coletor perfurocortante, recipiente identificado, saco apropriado, contentor de transporte | Aguardando imagens |
| Equipamentos | POP 18 (#21), PGTS (#25), Plano de Manutenção (#28) | Conjunto odontológico, compressor, sistema de sucção, aparelho de raios X intraoral | Aguardando imagens |

Os componentes, a cena e a seleção são **referências visuais**, não listagens normativas de itens obrigatórios. Para resíduos, evitar códigos de cores e classificações genéricas não validados para a atividade e as regras locais; para raios X, é necessária revisão das exigências aplicáveis.

## Implementação segura já preparada
- `data/illustrated-guides.json`: manifesto com grupos, modelos de destino, nomes, campos de orientação, asset da cena e asset de cada objeto; os novos grupos têm `status: "aguardando_assets_visuais"`.
- `illustrated-guides.php`: componente único que desenha o padrão da cena com hotspots, painel com imagem isolada, legenda e texto. Só libera o grupo com `status: "aprovado_visualmente"`, caminhos locais válidos e todos os objetos completos, sem imagens ausentes. Nunca altera arquivos ou históricos das minutas.
- `illustrated-guides.js`: interação reutilizável para seleção, texto, imagem isolada, foco e fallback.
- `document-engine.php`: ponto de integração contextual por ID; retorna vazio para grupos ainda não aprovados. Documentos anteriores não ganham imagens retroativas.
- `index.php`: carrega o módulo e o JS; usa os estilos do padrão atual em `steril-inline.css?v=3`.
- A implementação aprovada do POP 08 **não foi modificada**; permanece com `steril-inline.php`, `steril-inline.js` e `steril-inline.css`.

## Critérios de entrega por grupo
- [ ] Uma cena principal original, legível, em resolução alta, sem objetos fictícios ou incorretamente rotulados
- [ ] Imagens transparentes **individuais** de cada item, conferidas sobre fundo claro e verde-escuro
- [ ] A legenda identifica exatamente o item representado e o RT confirma finalidade/aplicabilidade
- [ ] Hotspots têm posição verificada com a cena real (não estimar porcentagens)
- [ ] Visual desktop, 390px, 320px; clique, foco, teclado, fallback de imagem e A4
- [ ] Verificação em sessão real e deploy com caminhos/arquivos confirmados antes de anunciar publicação
- [ ] Habilitação de cada grupo por manifesto só depois das checagens e aprovação visual

**Estado:** padrão aprovado, estrutura reutilizável preparada, três grupos planejados. **As cenas e os objetos novos ainda não foram aprovados nem publicados na UI.** Não anunciar os demais como concluídos até os assets e o teste real estarem prontos.

## Primeiro lote de EPI preparado, 07/11/2026
- **Seis objetos originais transparentes**, 810 × 810: `mascara`, `luvas`, `oculos`, `protetor_facial`, `gorro`, `avental`; diretório `assets/illustrations/epi-detail/*-v1.webp`. Cada objeto foi testado sobre fundo verde-escuro e claro; metadados e arquivos versionados no GitHub.
- **Cena contextual 1014 × 488** em `assets/illustrations/epi-scene-v1.avif`. É um recorte de cena gerada para o projeto com seis itens e números 1 a 6 já incorporados à foto. Os hotspots são botões transparentes posicionados sobre esses círculos; não adicionar um segundo conjunto de números.
- Mapeamento no manifesto `data/illustrated-guides.json`, modelos `[6]`, com `scene`, `asset` e coordenadas `point` por item. Campo `status: aguardando_aprovacao_visual` **impede a publicação ao cliente** via gate no PHP, mesmo com imagens existentes no servidor.
- Uma montagem de revisão visual da página foi gerada localmente para conferência do usuário; é uma prévia estática, não teste E2E autenticado. O texto da máscara distingue máscara cirúrgica de respirador, sem alegar equivalência de proteção.
- **Pendências:** aprovação visual explícita do conjunto; checar hotspots na página real, mobile e impressão, legibilidade dos itens claros, nomenclatura e indicação pelo RT. Só depois habilitar no POP 03. Evitar ultrapassar a qualidade visual do POP 08 aprovado.

## Resíduos: assets finalizados, prévia pronta e publicação bloqueada (07/11/2026)

- Cena fotográfica original 1672×941 sem texto, classificação de resíduos, símbolos ou selos embutidos: `assets/illustrations/residuos-scene-v1.avif` (~83 KB).
- Quatro imagens individuais com canal alpha, recortadas com transparência real e padronizadas para 810×810: `residuos-detail/perfurocortante-v1.webp` (~60 KB), `lixeira_tampa-v1.webp` (~44 KB), `saco_residuo-v1.webp` (~66 KB), `contentor_transporte-v1.webp` (~60 KB). Cada arquivo foi inspecionado sobre fundo claro e verde-escuro; hashes dos blobs no GitHub conferidos.
- `data/illustrated-guides.json`: quatro itens com nome, descrição, orientação e coordenadas verificadas na cena; modelos elegíveis **POP 11 (14)** e **PGRSS (24)**. As imagens foram selecionadas apenas como referências genéricas, não estabelecem classificação, cor ou equipamento obrigatório.
- Novo suporte a cenas sem números desenhados: `scene.numbers_in_image:false`; `illustrated-guides.php` cria o número como elemento HTML de botão; `steril-inline.css` estiliza sobre o objeto com foco acessível. Proporção da cena passa a ser extraída de dimensões reais no manifesto, impedindo cortes. A cena EPI, que já contém números embutidos, não recebe número duplicado.
- **Status do guia permanece `aguardando_aprovacao_visual`** até aprovação específica pelo usuário; a função `od_illustrated_ready` impede que o novo bloco seja exibido na área dos clientes, mesmo com os assets publicados no servidor.
- Verificações estáticas de código/manifesto/arquivos: **18/18** e correspondência SHA **5/5**. Ainda **não houve** teste de navegador autenticado, clique de hotspots nem impressão real. O conteúdo técnico exige revisão RT, e a aplicabilidade das normas locais deve ser considerada junto à RDC Anvisa 222/2018.
- Prévia estática e controle de transparência gerados nesta sessão: `/mnt/data/odonto-residuos-v1/preview_residuos_pop11.png` e `/mnt/data/odonto-residuos-v1/auditoria_transparencia_residuos.jpg`. Esses caminhos são artefatos da conversa, não caminhos permanentes do GitHub.

## Publicação concluída: Resíduos e Equipamentos (07/11/2026)

O usuário aprovou explicitamente o lote de Resíduos e, em seguida, os quatro objetos de Equipamentos e a cena. O manifesto `data/illustrated-guides.json` foi atualizado para `status: aprovado_visualmente` em **todos os três grupos**, preservando a aprovação anterior de EPI.

- **Resíduos:** guia para POP 11 (14) e PGRSS (24), com cena `assets/illustrations/residuos-scene-v1.avif` (1672×941) e quatro objetos em `residuos-detail/*-v1.webp`. No material, não fixar classificação, cor ou identificação obrigatória de resíduos; o PGRSS concreto e a revisão do RT prevalecem.
- **Equipamentos:** guia para POP 18 (21), PGTS (25) e Plano de Manutenção (28). Cena original `assets/illustrations/equipamentos-scene-v1.avif`, 1672×941 e aproximadamente 83 KB. Quatro imagens isoladas e aprovadas, **810×810, com transparência alfa**, em `assets/illustrations/equipamentos-detail/`: `cadeira-v1.webp` (145 KB), `compressor-v1.webp` (75 KB), `sugador-v1.webp` (68 KB), `raio_x-v1.webp` (74 KB). Materiais representados na cena são compatíveis com os quatro arquivos de detalhe. **Não pressupor que todas as clínicas possuam aparelho radiológico**.
- **UI:** hotspots numerados com HTML nas cenas de Resíduos e Equipamentos, sem números embutidos na fotografia. A função `od_illustrated_guide_html()` insere o quadro dentro de cada documento elegível, sem criar outra página de navegação ou modificar os documentos históricos. `illustrated-guides.js` troca a imagem isolada e o texto do painel. `steril-inline.css?v=5` mantém os números também no quadro impresso; a implementação do POP 08 permanece intacta.
- **Verificação:** 25/25 checagens estáticas de estrutura, segurança de caminho e estado; PNGs originais inspecionados em fundo claro e escuro, convertidos para WebP com alfa; SHA dos cinco blobs novos verificado. Deploy Git Hostinger `is_success:true` e listagem de `rdc/assets/illustrations/equipamentos-detail` comprovam quatro arquivos e cena no servidor. SQLite de produção fora do versionamento preservado (155.648 bytes).
- **Atenção:** o usuário aprovou **visualmente** as imagens. Não houve teste E2E autenticado de clique, impressão real ou de ambas clínicas, nem avaliação clínica das ilustrações por RT. Não fazer alegação de certificação ou conformidade validada. Páginas para conferência: `?page=editar&id=14#odx-visual-guide-residuos`, `?page=editar&id=24#odx-visual-guide-residuos`, `?page=editar&id=21#odx-visual-guide-equipamentos`, `?page=editar&id=25#odx-visual-guide-equipamentos` e `?page=editar&id=28#odx-visual-guide-equipamentos`.

Este checkpoint **substitui o status anterior de “Resíduos em revisão” e “Equipamentos aguardando imagens”**, mantendo o histórico de decisões anteriores.
