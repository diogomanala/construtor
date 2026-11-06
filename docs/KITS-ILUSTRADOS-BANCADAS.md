# Bancadas ilustradas e pack de materiais | Odonto em Dia

**Entrega publicada em 06/11/2026.** Página autenticada: `?page=kits-bancada`. Dois conjuntos iniciais: `?page=kits-bancada&kit=profilaxia` e `?page=kits-bancada&kit=exodontia`.

## O que existe de fato
- Uma biblioteca de **54 arquivos PNG individuais** foi preparada como artefato da conversa (ZIP com manifesto e exemplos). **O ZIP completo não está hospedado no repositório/site**, e não deve ser anunciado como disponível dentro da plataforma.
- **16 itens ilustrados estão integrados ao site**, em duas bancadas editáveis (6 de profilaxia, 10 de extração). Na web, as ilustrações usam um atlas AVIF de 672×672 px, **9.623 bytes**: `assets/illustrations/odonto-bancadas-16-v1.avif`. O uso do atlas evita 16 requisições separadas.
- Registro das imagens no Git: blob SHA `03db440756568f99a8c1da8d8cb10904e58b7f25`. A ordem da grade de 4×4 está documentada em `kit-bancadas.php::od_kit_definitions()`.
- Há um link na biblioteca e outro no menu Documentos. A interface permite selecionar/remover itens, adicionar observações, salvar por clínica e imprimir com nome/logo da unidade.

## Regras de funcionamento
- `kit-bancadas.php`: define conjuntos, autenticação/isolamento via `clinic()`, validação de IDs de itens, tabela SQLite `od_kit_visual_preferences` por `clinic_id + kit_key`, contagem de revisões da seleção, interface de impressão.
- `kit-bancadas.css`: cards responsivos e estilo de impressão, atlas local.
- `kit-bancadas.js`: ações selecionar todos/limpar, contagem e atualização de observações para impressão.
- `index.php`: roteamento `kits-bancada`, ação POST `kit_visual_save` com CSRF já checado globalmente, estilos/scripts, menu.
- `guided-ux.php`: descoberta discreta na biblioteca documental.
- As listas são **exemplos ilustrativos, não listas obrigatórias para procedimentos**, nem POPs clínicos aprovados. Os 30 modelos existentes continuam separados. Não copiar os layouts nem as imagens da concorrência.

## Limitações e validação
- As imagens são representações autorais geradas por IA com preparação individual. A exatidão da forma e do nome dos instrumentos requer conferência profissional; algumas imagens precisam de revisão de fidelidade/recorte antes de serem apresentadas como materiais didáticos definitivos.
- Cor e símbolos de resíduos, medicamentos e outros produtos não podem ser usados para prescrever processos nem representar regularidade sanitária.
- **Deploy Git Hostinger confirmado**, banco SQLite preservado, arquivo visual verificado por hash. **Testes de login/navegador, PHP CLI e gravação por duas clínicas não executados**, pendentes.
- Caso seja necessário promover um kit a documento clínico próprio, preparar conteúdo e validar com RT habilitado, registrar norma/aplicabilidade e versionar separadamente antes de publicar como POP.

## Próximas ações
1. Testar os dois kits autenticado em desktop/390px/320px e impressão.
2. Salvar, reabrir, alterar e verificar isolamento de duas clínicas; testar POST inválido e CSRF.
3. Revisão de instrumentais e ilustrações com RT; corrigir eventuais correspondências.
4. Melhorar recortes/qualidade dos PNGs individuais de maior destaque antes de aumentar o catálogo incorporado.
