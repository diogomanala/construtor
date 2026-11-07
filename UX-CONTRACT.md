# UX-CONTRACT | Odonto em Dia

## Escopo
Comportamento compartilhado da área de membros PHP. Registrar decisões observáveis para manter a navegação consistente em painel, biblioteca, editor, histórico e registros.

## Navegação canônica
- Dono da estrutura: `index.php::h(string $page)`. Dono do visual da navegação: `member-shell.css`.
- A marca é a mesma da landing, usa `assets/favicon.svg`; o link leva ao painel com sessão ou à página inicial sem sessão.
- Todas as rotas continuam disponíveis: painel, biblioteca, meus documentos, meu mapa, perfil, esterilização, limpeza, manutenção, EPI, autoavaliação, Autoinspeção 360, identidade visual e sair.
- Desktop acima de 920 px: destinos principais em acesso rápido, menu `details/summary` nativo para recursos adicionais. Móvel: menu nativo como entrada única, sem navegação extensa ou escondida por overflow.
- A rota atual recebe `aria-current="page"`. Links são `a` sem handlers globais; `summary` tem interação nativa por toque/teclado. Menu abre sobre o conteúdo, sem alterar a altura da página, e sua rolagem é limitada.
- Sair é um link identificado e visualmente distinto, sem operação destrutiva disfarçada.
- Preservar semântica de foco e contraste. Não trocar menu por div clicável nem exigir JavaScript para navegação básica.

## Interações da aplicação
- Edição e criação são por conta/clínica autenticada; salvar depende de verificação de sessão, CSRF e direito ativo já implementados no backend.
- Histórico documental é uma visão de versões, não uma alteração silenciosa do conteúdo salvo.
- Registros operacionais devem permanecer vinculados à clínica e não gerar aprovação ou certificação automática.
- Campos e rótulos de revisão devem distinguir “rascunho” e “aguardando RT” de qualquer aprovação profissional.
- Estados de erro, menu, botão e formulário devem informar o que ocorreu, manter contexto e impedir perda de alterações sempre que houver suporte.

## Pendências de verificação
- Teste navegador autenticado: menu desktop/mobile, foco, travamento, clique em cada rota e ausência de rolagem horizontal.
- Teste com duas clínicas distintas, persistência e isolamento, versão documental e controle de acesso expirado.
- Verificação de compra e ativação Mercado Pago em fluxo controlado. Nenhum desses testes está aprovado apenas por um deploy.

## Identidade visual por clínica
- Rota autenticada: `?page=identidade`. Formulário POST `brand_save`, protegido pelo mesmo CSRF, sessão e regra de compra vigente que o editor.
- Atualizar nome, paleta predefinida, tom próprio escuro com contraste 4.5:1 e logo PNG/JPG/WebP de até 350 KB e 3000 px. Substituição/remoção são explícitas.
- Upload é inspecionado pelo PHP (MIME, conteúdo e tamanho), mantido apenas no SQLite e servido em `?page=logo-clinica` com sessão e escopo de clínica. Nunca usar SVG enviado pelo cliente nem mostrar acesso público aos logos.
- Prévia de marca instantânea no formulário, sem alterar a marca da navegação. Editor renderiza imagem e tema no cabeçalho/capa.
- Cada versão nova grava identidade com o documento em `od_document_brand_history`; ao mudar o tema são congeladas as versões legadas sem snapshot com os dados atuais disponíveis.
- Pendente: teste autenticado real de arquivos, isolamento multi-clínica, histórico e comportamento responsivo. [Tarefa no Notion](https://app.notion.com/p/410739c88b0a81808084d79bc9320a1b).

## Fontes
- [Cérebro](https://app.notion.com/p/410739c88b0a81f8a436d82129ff205a)
- [QA funcional](https://github.com/diogomanala/construtor/blob/odonto-em-dia-hostinger/docs/QA-ACESSO-CLINICAS.md)
- [DESIGN.md](https://github.com/diogomanala/construtor/blob/odonto-em-dia-hostinger/DESIGN.md)

## Quadro de esterilização ilustrado (protótipo de apoio)
- Entrada pelo editor do POP 08 ou pela URL autenticada `?page=ilustracao-esterilizacao`.
- Interação: botão do objeto seleciona-o, exibe SVG ampliado, nome, função e o que conferir. Botão de incluir/remover é independente de selecionar; não indica que um procedimento foi executado.
- Persistência opcional: `steril_visual_save` valida sessão, licença vigente e CSRF no fluxo do `index.php`; backend valida chaves permitidas e limite das observações e grava em `od_steril_visual_choices` por clínica. GET apenas lê dados da clínica autenticada.
- No móvel, `dialog.showModal()` abre detalhe, fecha pelo botão, Escape e fechamento nativo, com foco gerenciado pelo navegador. Sem JS, a documentação original continua acessível; o protótipo exige JS para os cliques.
- A4: ocultar botões, exibir somente objetos incluídos e legenda numerada, dados identificáveis da clínica e advertência de revisão técnica. Não inserir este quadro automaticamente no corpo de minutas/históricos anteriores.
- Estados de QA: 7 objetos, estados incluído/excluído, seleção vazia, nome longo, RT ausente, dois tenants, JS desativado, 320/390/768/1440 px, impressão, contrastes, foco e botão fechar. Nenhum resultado é “aprovado pela Anvisa”.
