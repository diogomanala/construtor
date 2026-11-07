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
