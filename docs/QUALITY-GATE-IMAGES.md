# Padrão de qualidade visual dos documentos | Odonto em Dia
Data: 06/11/2026

## Regra vinculante
**Se a imagem não for realmente nítida, fiel ao instrumento e recortada com transparência convincente, não usar.** Evitar fundos brancos embutidos, halo, recortes retangulares, renderização 3D artificial ou ilustração ampliada acima da resolução real.

### Auditoria dos assets existentes
- Os 54 PNGs do pack inicial (320×320 px) têm canal alpha binário (transparente/opaco), mas os objetos preservam regiões brancas retangulares e fundos artificiais dentro da área opaca. Em teste sobre verde escuro, os retângulos ficaram visíveis. **0 de 54 aprovados para exibição na documentação**.
- O atlas 3D de capas possui resolução insuficiente para o tamanho usado na capa. As seis ilustrações de capa foram **desativadas** até substituição por arquivos realmente adequados. Não ampliar sprites pequenos.
- O atlas de 54 materiais e os conjuntos de bancada continuam no repositório para eventual revisão, mas não podem ser acessados pela interface nem incluídos em novas prévias/documentos.
- Nenhum documento técnico ou histórico foi excluído. Seleções antigas permanecem persistidas, porém não são renderizadas; novas versões não devem incluir imagem reprovação.
- Recomendações na biblioteca usam **ícones SVG vetoriais consistentes**, não fotos PNG de baixa qualidade.

### Critérios antes de reativar um asset
1. Arquivo original de alta definição, preferencialmente PNG/WebP com transparência real e bordas suaves. **Sem fundo branco embutido**. Testar sobre branco, verde escuro e cinza.
2. Instrumento e legenda identificados corretamente por revisão profissional quando a imagem estiver dentro de documentação clínica. Não inventar detalhes de instrumentos, produtos ou marcas.
3. Resolução original adequada ao tamanho exibido: a imagem deve ter pelo menos 2× o tamanho CSS alvo. Em capa de ~300 px, procurar fonte com pelo menos 600 px úteis, sem upscaling artificial.
4. Sem recorte truncado, halo, sombras quadradas ou fundo resultante de crop automático. Imagem individual de cada item, não recorte tosco de um mosaico.
5. Licença/autoria/documentação rastreáveis, ou arte original criada para o produto.
6. Verificar visualmente no editor (desktop, 390 px, 320 px), na impressão e em múltiplas paletas das clínicas antes de publicar.
7. Ativar somente os assets que passarem individualmente. Um pack não é aprovado em bloco. Se nenhum passar, usar ícone SVG e layout tipográfico.

### Implementação da suspensão
- `visual-library.php`: `od_visual_current()` retorna `null`; `od_visual_cover_markup()` retorna string vazia; `od_visual_card_markup()` retorna ícone SVG por categoria. O banco de snapshots históricos não é apagado.
- `index.php`: links para acervo e bancadas removidos do menu; rotas antigas redirecionam de modo explícito à biblioteca; POST antigo de seleção redireciona sem salvar. CSS/JS do acervo desativados.
- `guided-ux.php`: callout do acervo removido.
- `document-engine.php`: caixa para selecionar imagens e prévia ilustrada removidas, mantendo o corpo técnico e o editor guiado.
- `material-library.php`: sugestões automáticas e prévia desativadas, novas versões não exibem imagens do pack rejeitado.
- As futuras substituições exigem QA real em sessão autenticada antes de reabilitar galeria, bancadas ou imagens de capas.

**Não confundir aprovação visual do arquivo com aprovação técnica ou sanitária do documento.**
