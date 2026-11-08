# Guias visuais complementares | Instrumentais, Exodontia, Materiais e Profilaxia
Data: 08/11/2026

## Decisão
O usuário aprovou visualmente quatro novas cenas de bancada e quatro conjuntos de cinco objetos isolados. A experiência visual segue o padrão já aprovado: cena contextual, hotspots, painel com imagem PNG/WebP transparente individual e legendas. As imagens são geradas por IA e **a aprovação é apenas estética**.

Os 30 modelos atuais da SDBPF não contêm quatro POPs clínicos específicos de exodontia/profilaxia. Por isso este lote entra como **galeria complementar**, sem ser inserido automaticamente em minutas da RDC, sem modificar histórico ou conteúdo clínico.

## Arquivos publicados na Hostinger
- `assets/illustrations/instrumentais-scene-v1.webp` e 5 arquivos em `instrumentais-detail/`
- `assets/illustrations/exodontia-scene-v1.webp` e 5 arquivos em `exodontia-detail/`
- `assets/illustrations/materiais-scene-v1.webp` e 5 arquivos em `materiais-detail/`
- `assets/illustrations/profilaxia-scene-v1.webp` e 5 arquivos em `profilaxia-detail/`

Todas as cenas têm 1672×941 px; os 20 WebP de detalhe têm 810×810 px e canal alfa real. Os 24 arquivos foram versionados no GitHub e confirmados no diretório `rdc/assets/illustrations` da Hostinger. Há também um ZIP de referência na conversa com `previsualizacao.html`, `guias_referencia.json`, `manifesto_arquivos.json` e prévia dos recortes.

## Código publicado
- Manifesto `data/illustrated-guides.json` passa a ter 4 grupos `reference_only: true`, com `document_ids: []`, preservando EPI, Resíduos, Equipamentos e o POP 08 independente.
- `illustrated-guides.php` disponibiliza `od_illustrated_guide_markup()` reutilizável sem duplicar a implementação.
- `reference-gallery.php` e `reference-gallery.css` definem a galeria autenticada `?page=acervo-visual`.
- `index.php` liga o menu e a rota, mas **o link só aparece se `od_reference_gallery_available()` encontrar pelo menos um grupo completo**. Sem assets, a rota mantém o redirecionamento seguro para Documentos.
- O gate `od_illustrated_ready()` exige imagem principal e todos os objetos presentes no servidor. Falta de 1 arquivo desativa aquele guia. Nenhum documento é regravado e não há migração de SQLite.

## Publicação e QA
- [x] 24 arquivos WebP enviados ao GitHub como blobs com SHA correspondente ao pacote original.
- [x] Branch `feat/odonto-galeria-visuais-20261108` integrada por fast-forward na `odonto-em-dia-hostinger` em [a114ffd](https://github.com/diogomanala/construtor/commit/a114ffd9406bbfc4cd62a49865f95451528ac4d3).
- [x] Hostinger retornou `is_success:true` no deploy Git do diretório `rdc`, com presença dos 24 WebP e dos arquivos `reference-gallery.php/css`.
- [x] 13 verificações estáticas da integração passaram; a prévia local apresentou 4 cenas, 20 objetos alfa, posições numéricas válidas e `node --check` aprovado.
- [x] Banco `rdc/data/odonto_em_dia.sqlite` preservado no deploy, ainda não rastreado pelo Git, com o mesmo tamanho anterior (155648 bytes). Tamanho igual não comprova integridade lógica.
- [ ] **Pendente:** QA autenticado real da galeria em desktop, 320/390 px, cliques, foco/teclado, carregamento dos detalhes e impressão A4.
- [ ] **Pendente:** revisão de nomenclatura e representações clínicas pelo RT, especialmente recipiente de anestésico, periótomo e dispositivos de corte. Imagens meramente ilustrativas; não usar como orientação clínica sem revisão.
- [ ] **Pendente:** conferência de duas clínicas e isolamento de dados, caso a QA da aplicação exija esse cenário.

## Estado
**Publicado na Hostinger e versionado no GitHub em 08/11/2026.** A galeria complementar pode ser acessada na rota autenticada `https://odontonegocios.com/rdc/?page=acervo-visual`. Não classificar como teste E2E, catálogo clínico homologado ou conteúdo técnico aprovado; QA real e revisão profissional ainda são pendências.
