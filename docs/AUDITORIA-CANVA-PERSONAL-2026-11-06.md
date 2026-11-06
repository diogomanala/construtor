# Auditoria comparativa | Odonto em Dia x materiais Canva Personal
Data: 2026-11-06
Escopo: análise comercial, visual e de estrutura. NÃO é validação sanitária ou jurídica.
Fonte: designs da conexão Canva "personal" (link_id de autenticação distinto da equipe Up Media) e fonte do SaaS no branch `odonto-em-dia-hostinger`.

## Amostras auditadas no Canva Personal
| Canva ID | Design | Páginas | Diagnóstico de uso |
|---|---|---:|---|
| DAHaI7yaLjI | POPs Odontologia Parte 1 | 18 | POPs e orientações por procedimentos clínicos; escopo complementar, não substituir protocolo do RT |
| DAHaI1tH-IU | POP Odontologia | 38 | POPs com executante, área, objetivo, instruções e assinatura; revisar técnica antes de qualquer incorporação |
| DAHaIvjdoZc | Cuidados pós-procedimento | 28 | Orientações ao paciente por procedimento; possível produto de papelaria clínica em separado |
| DAHaIr8yJ2M | Parte 1 Termos Papelaria | 91 | Termos de consentimento por tratamento, versão de aprovação do profissional e revisão jurídica |
| DAHaIj4f84o | Fichas Papelaria | 8 | Formulários com campos de anamnese, odontograma, diagnósticos e assinaturas; cuidado com LGPD |
| DAHaImlJaks | Consentimento dados | 4 | LGPD, requer revisão jurídica e mapeamento das bases legais; nem todo tratamento depende de consentimento |
| DAHaInpz_b8 | Documentos Papelaria | 12 | PGRSS com identificação, classes de resíduos, contratadas, responsabilidades e rotinas |
| DAHaIqpIyWA | Manual de Boas Práticas | 10 | Identificação, objetivos, responsabilidades, seções e terminação de ciência |
| DAHaIj-pOsI | Monitorização da Esterilização | 5 | Fichas de cargas/autoclave, identificadores e indicadores; alto valor para uso recorrente |
| DAHaIuVlz6E | Orçamento | 2 | Papelaria comercial, não incluir no kit sanitário por confusão de escopo |
| DAHaIgLPQ3Q | Cartão Digital | 8 | Marketing, fora do kit RDC |

Total das nove primeiras referências normativas/assistenciais: 214 páginas. Com orçamento e cartão: 224 páginas. Não assumir que todas são corretas, licenciadas para revenda ou prontas para usar.

## Produto atual no GitHub
- `data/templates.json`: 30 itens, 103.678 caracteres de HTML/JSON no repositório. 18 POPs, 7 planos, 2 SDBPF, 2 formulários e 1 guia.
- Nenhum dos 30 modelos usa `img`, `svg` ou `figure`.
- Plano PGRSS (id 24) é um roteiro de preenchimento, não um PGRSS concluído.
- Formulário de registros (id 29) agrega cerca de 11 tipos de fichas numa única tela; não estão separados em ferramentas operacionais por ocorrência.
- POP de esterilização (id 11) explica controle de ciclos, porém não oferece na mesma experiência uma ficha preenchível e exportável específica para cada carga.
- `editor.php`: exportação DOCX percorre tags HTML h2/h3/h4/p/li/tr e converte cada linha de tabela em parágrafo. Não gera `w:tbl`, não incorpora imagens, e não preserva fidelidade gráfica do documento. Prioridade crítica pré-vendas.

## Decisões
1. Posicionar claramente o produto como **Kit de Documentação RDC + Plataforma de Edição e Acompanhamento**, não como apenas pasta de downloads nem software de gestão clínica.
2. Preservar base normativa e linguagem de minuta; não prometer aprovação ou certificado.
3. Criar um **design system documental** independente do visual publicitário da landing: A4, capa, identificação do serviço, código, versão, vigência, RT, capítulos, tabelas reais, assinaturas e rodapé com número de página e status.
4. Criar, quando úteis, diagramas simples e originais a partir de procedimentos validados: fluxos de material, identificação de áreas, sequência de processamento, decisão de não conformidade. Não reproduzir imagens/textos do concorrente sem licença.
5. Primeiro corrigir exportador DOCX para preservar tabelas reais, listas, negrito, cabeçalhos e margens; depois permitir imagens vinculadas e imagens autorais no PDF/Word.
6. Separar **fichas de registro vivas** da redação dos POPs. Registro de esterilização, limpeza, EPIs, treinamento, manutenção e água devem ser preenchíveis por ocorrência, pesquisáveis, com exportação e impressão.
7. Diferenciar obrigatório conforme estrutura da clínica de opcionais; não incorporar automaticamente os 91 págs. de termos de consentimento e orientações pós-procedimento ao kit RDC.
8. Toda adaptação clínica/regulatória deve passar por revisão de RT; consentimentos e LGPD exigem revisão jurídica competente.

## Ordem recomendada
P0: corrigir exportação DOCX preservando tabela, estrutura e listas; comparar amostra final em Word e PDF.
P0: revisar POP esterilização, PGRSS e Manual SDBPF com RT e organizar ficha de monitoramento preenchível.
P1: layout documental premium (A4, metadados, tabela, sumário, fluxos ilustrativos quando clinicamente adequados).
P1: biblioteca com miniaturas reais, categorias e estados de documento; editor com modos documento e registro.
P2: se houver direitos e validação, ofertar papelaria clínica como produto complementar separado; não ampliar o kit principal indiscriminadamente.

## Verificações de qualidade antes da venda
- Verificar conteúdo, prazos, referências e adequação regional pelo RT.
- Conferir cada documento no navegador, exportado DOCX e PDF, inclusive tabela, cabeçalho, assinatura e quebra de página.
- Testar se histórico e versões são acessíveis apenas à clínica correta.
- Conferir filtros, status e exportação de fichas preenchidas.
- Não utilizar artefatos do Canva Personal de terceiros diretamente em revenda sem garantia de direitos.
