# Guias visuais complementares | Instrumentais, Exodontia, Materiais e Profilaxia
Data: 08/11/2026

## Decisão
O usuário aprovou visualmente quatro novas cenas de bancada e quatro conjuntos de cinco objetos isolados. A experiência visual segue o padrão já aprovado: cena contextual, hotspots, painel com imagem PNG/WebP transparente individual e legendas. As imagens são geradas por IA e **a aprovação é apenas estética**.

Os 30 modelos atuais da SDBPF não contêm quatro POPs clínicos específicos de exodontia/profilaxia. Por isso este lote entra como **galeria complementar**, sem ser inserido automaticamente em minutas da RDC, sem modificar histórico ou conteúdo clínico.

## Arquivos previstos (ainda não publicados no servidor)
- `assets/illustrations/instrumentais-scene-v1.webp` e 5 arquivos em `instrumentais-detail/`
- `assets/illustrations/exodontia-scene-v1.webp` e 5 arquivos em `exodontia-detail/`
- `assets/illustrations/materiais-scene-v1.webp` e 5 arquivos em `materiais-detail/`
- `assets/illustrations/profilaxia-scene-v1.webp` e 5 arquivos em `profilaxia-detail/`

Todas as cenas têm 1672×941 px; os 20 WebP de detalhe têm 810×810 px e canal alfa real. Os 24 arquivos estão no artefato ZIP gerado na conversa, acompanhado de `previsualizacao.html`, `guias_referencia.json`, `manifesto_arquivos.json` e prévia dos recortes.

## Código preparado
- Manifesto `data/illustrated-guides.json` passa a ter 4 grupos `reference_only: true`, com `document_ids: []`, preservando EPI, Resíduos, Equipamentos e o POP 08 independente.
- `illustrated-guides.php` disponibiliza `od_illustrated_guide_markup()` reutilizável sem duplicar a implementação.
- `reference-gallery.php` e `reference-gallery.css` definem a galeria autenticada `?page=acervo-visual`.
- `index.php` liga o menu e a rota, mas **o link só aparece se `od_reference_gallery_available()` encontrar pelo menos um grupo completo**. Sem assets, a rota mantém o redirecionamento seguro para Documentos.
- O gate `od_illustrated_ready()` exige imagem principal e todos os objetos presentes no servidor. Falta de 1 arquivo desativa aquele guia. Nenhum documento é regravado e não há migração de SQLite.

## Pendências antes de publicar na branch principal
1. Subir os 24 binários para o repositório GitHub, nos caminhos acima (não basta o pacote existir no ChatGPT).
2. Conferir os SHA/nomes e que os quatro guias passam no gate de prontidão.
3. Validar nomes e representação das peças com profissional habilitado, especialmente o recipiente do anestésico, a representação de periótomo e dispositivos de corte; **não usar como orientação clínica sem revisão**.
4. Validar clique em todos os 20 pontos, teclado, acessibilidade, 320/390 px, tela desktop e impressão A4 em sessão autenticada.
5. Só então integrar a branch `feat/odonto-galeria-visuais-20261108` na `odonto-em-dia-hostinger` e fazer deploy Hostinger. Preservar `rdc/data/odonto_em_dia.sqlite` não versionado.

## Estado
**Imagens criadas e empacotadas, código de integração na branch de desenvolvimento, NÃO PUBLICADO NA HOSTINGER.** Não classificar como teste E2E, catálogo clínico homologado ou entrega em produção.
