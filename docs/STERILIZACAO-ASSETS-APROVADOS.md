# Seleção de materiais da esterilização | imagens aprovadas
Data: 07/11/2026

## Decisão
O usuário aprovou **sete PNGs isolados** de instrumentos e materiais. Esses arquivos substituem exclusivamente a imagem recortada da bancada quando o usuário seleciona um objeto. A cena principal com sete números permanece como contexto. O quadro está integrado diretamente ao **POP 08, modelo 11**, em `?page=editar&id=11#odx-esteril-visual`.

## Assets versionados no GitHub
Arquivos derivados diretamente dos PNGs originais aprovados; redimensionados para **810 × 810 px**, exportados para **WebP com transparência real** a fim de reduzir a transferência sem parecer um recorte da cena.

| Chave | Imagem | Tamanho |
|---|---|---:|
| `autoclave` | `assets/illustrations/steril-detail/autoclave-v1.webp` | 57 KB |
| `seladora` | `assets/illustrations/steril-detail/seladora-v1.webp` | 55 KB |
| `embalagem` | `assets/illustrations/steril-detail/embalagem-v1.webp` | 74 KB |
| `quimico` | `assets/illustrations/steril-detail/quimico-v1.webp` | 47 KB |
| `biologico` | `assets/illustrations/steril-detail/biologico-v1.webp` | 36 KB |
| `bandeja` | `assets/illustrations/steril-detail/bandeja-v1.webp` | 47 KB |
| `cassete` | `assets/illustrations/steril-detail/cassete-v1.webp` | 45 KB |

Os originais PNG foram gerados para este projeto e aprovados visualmente pelo usuário; não copiar imagens da concorrência. **A aprovação é visual, não clínica.** A identificação e aplicação dos instrumentos continuam sujeitas à revisão do RT.

## Implementação real
- `steril-visual.php::od_steril_visual_asset()` fornece o caminho de imagem a partir de **sete chaves autorizadas**.
- `steril-inline.php` renderiza um `<img>` com a autoclave por padrão, além de `data-si-image` em cada hotspot.
- `steril-inline.js` seleciona o item e troca apenas `img.src`, nome, descrição e orientação. Inclui estados de carregamento e fallback para imagem indisponível.
- `steril-inline.css` exibe a imagem inteira em `object-fit: contain`, fundo clínico claro, sombra discreta e tamanhos para desktop/mobile.
- `index.php` carrega os arquivos ativos com versão `v=3` para invalidar cache.

**Armadilha anterior:** os arquivos `steril-visual.js/css` são legados do protótipo, mas a versão efetivamente carregada no POP é `steril-inline.js/css`. Alterar apenas os arquivos legados não produz efeito na tela do documento.

## Critérios de validação e limitações
- GitHub: 7/7 arquivos presentes com SHA de blob verificado. Transparência (canal alpha 0–255), tamanhos 810×810 e preview sobre claro/verde conferidos.
- Hostinger: deploy Git reportou `is_success:true`; 7/7 arquivos listados em `rdc/assets/illustrations/steril-detail`.
- Código: checagens estáticas de integração 9/9, compilação de JavaScript sem erro.
- **Ainda não executado:** teste no navegador em sessão autenticada, clique de cada hotspot, carregamento HTTP efetivo e impressão real, CSS em 320/390px.
- Selecionar o objeto muda **somente a exibição visual**, não registra a execução de um ciclo, nem altera o histórico regulatório das minutas.

Se um asset não carregar, manter o painel e o texto, apresentar "Imagem temporariamente indisponível", sem ampliar recortes da foto nem substituir por PNGs reprovados.
