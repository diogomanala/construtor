# UX-CONTRACT | Odonto em Dia

## Escopo
Comportamento compartilhado da área de membros PHP. Registrar decisões observáveis para manter a navegação consistente em painel, biblioteca, editor, histórico e registros.

## Navegação canônica
- Dono da estrutura: `index.php::h(string $page)`. Dono do visual da navegação: `member-shell.css`.
- A marca é a mesma da landing, usa `assets/favicon.svg`; o link leva ao painel com sessão ou à página inicial sem sessão.
- Todas as rotas continuam disponíveis: painel, biblioteca, meus documentos, meu mapa, perfil, esterilização, limpeza, manutenção, EPI, autoavaliação, Autoinspeção 360 e sair.
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

## Tarefa futura: identidade de clínica
A identidade visual da clínica será configurada por clínica (nome, imagem e paleta), com upload validado, prévia, persistência, isolamento e aplicação ao documento. A marca da plataforma permanece estável. [Backlog](https://app.notion.com/p/410739c88b0a81808084d79bc9320a1b).

## Fontes
- [Cérebro](https://app.notion.com/p/410739c88b0a81f8a436d82129ff205a)
- [QA funcional](https://github.com/diogomanala/construtor/blob/odonto-em-dia-hostinger/docs/QA-ACESSO-CLINICAS.md)
- [DESIGN.md](https://github.com/diogomanala/construtor/blob/odonto-em-dia-hostinger/DESIGN.md)
