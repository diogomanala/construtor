# Acervo visual odontológico | 54 itens | 06/11/2026

## Entregável publicado
- Os 54 PNGs individuais originais permanecem no pack ZIP produzido na conversa. A plataforma usa um único atlas AVIF de alta qualidade, **2880×1920 pixels**, organizado em grade **9×6** com **54 imagens de 320×320**; tamanho 130.545 bytes. Caminho: `assets/illustrations/odonto-materiais-54-v1.avif`; Git blob SHA `ce5d5c388728b76819ec528aa2e9a0111b05ba4d`.
- Manifesto estruturado em `data/materials-visual-catalog.json`, 54 IDs únicos, oito categorias, índice 0–53, todos `review: pendente`.
- Galeria privada `?page=acervo-visual`, com pesquisa e filtros. Para escolher os materiais de uma minuta, entrar em `?page=acervo-visual&id=11`, por exemplo.
- `material-library.php/css/js` oferecem busca, preview, seleção até 18 imagens, presets por documento, atalhos e estilos para impressão.
- As duas bancadas ilustrativas de profilaxia e exodontia passaram a usar a mesma fonte de imagens HD, no lugar do atlas anterior de 16 miniaturas.
- O editor `document-engine.php` mostra um quadro ilustrativo opcional, acessível pela barra lateral, e registra seleção por versão. Nenhuma imagem altera o conteúdo HTML do POP.

## Aplicabilidade inicial sem automatizar fatos
- Sugestões visuais por tipo de documento: 6 (EPI), 8–12 (processamento/esterilização, com diferenças por etapa), 14 e 24 (resíduos), 21, 25 e 28 (gestão/manutenção de equipamentos). Outros modelos são limpos por padrão, mas permitem inserir manualmente um quadro visual se fizer sentido.
- Radiologia só aparece nos exemplos de equipamentos se a unidade declarou que realiza o serviço.
- Imagens de recipientes de resíduos não determinam padronização de cores, grupos ou procedimentos de descarte. Segregação depende de normas e PGRSS da unidade.
- Conjuntos clínicos de profilaxia/exodontia são quadros auxiliares, **não POPs clínicos certificados**.

## Segurança e histórico
- POST `material_selection_save` usa CSRF global e sessão; valida template permitido, IDs exclusivamente do catálogo e limite de 18 itens.
- `od_material_drafts(clinic_id,template_id)` preserva a escolha em preparação, separada por clínica. `od_material_history(clinic_id,template_id,version)` grava a seleção na transação de salvamento da minuta.
- Versões históricas exibem exclusivamente o snapshot existente. Versões anteriores à implantação, sem snapshot, não recebem quadros novos retroativamente. Atualizações de seleção na prévia exigem nova versão salva para integrar o histórico.
- O arquivo é servido pelo próprio site, sem dependência de APIs externas ou chamadas ao Canva.

## Verificação executada e limites
- Checagem estática: 54 IDs, oito categorias, sprite/arquivo Git validado pelo SHA, rotas, proteção multi-clínica e CSS.
- Hostinger Git deploy confirmado em 06/11/2026; arquivos no servidor verificados pela listagem. SQLite de produção permaneceu fora do Git, sem sobrescrita.
- **Pendente:** QA em navegador autenticado (mobile, desktop, impressão), teste real de POST entre duas clínicas, testes PHP CLI e revisão profissional da precisão das imagens/legendas.
- As ilustrações de IA podem ter detalhes imprecisos, especialmente instrumentais metálicos; revisar antes de usar como apoio técnico definitivo. O atlas não constitui orientação clínica nem certificação sanitária.

## Manutenção
- Para atualizar uma arte, criar nova versão `v2` do pack e ajustar manifest. Nunca sobrescrever silenciosamente um pack usado em versões históricas sem estratégia de compatibilidade.
- Teste de cobertura em `tests/material-library-coverage.php` (executável em CI/CLI, ainda sem prova de execução em produção).
