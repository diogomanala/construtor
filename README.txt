ODONTO EM DIA | ESTADO TÉCNICO | 06/11/2026
Projeto isolado: https://odontonegocios.com/rdc/
GitHub: diogomanala/construtor, branch odonto-em-dia-hostinger.
Nunca modificar main (outro projeto) nem os arquivos do site principal.

ENTREGÁVEL
- 30 modelos de referência (18 POPs, 7 planos, 2 SDBPF, 2 formulários, 1 guia).
- DocSystem Premium com biblioteca, preenchimento guiado, prévia visual, histórico e edição dentro da plataforma.
- 12 modelos com roteiro de campos específico: ids 3, 6, 7, 11, 21, 22, 23, 24, 25, 26, 27 e 28. Os demais usam esquema de campos geral.
- Registros operacionais: esterilização, limpeza de ambientes, manutenção e entrega de EPI.
- Não depender de download de ZIP, DOCX, PDF ou Canva para a experiência principal.
- Conteúdo técnico são MINUTAS de referência; NÃO existe aprovação automática ou certificação sanitária. Revisão pelo RT e jurídica quando pertinente permanece pendente.

ARQUITETURA
- PHP 8.2 na Hostinger. Dados locais em SQLite dentro de /rdc/data, protegidos por .htaccess.
- Arquivos-chave: index.php, document-engine.php, document-plan-config.php, document-engine.css/js, operational-records.php, payments.php e webhook.php.
- O cadastro piloto existente cria apenas a primeira conta proprietária com convite exclusivo e senha definida pelo usuário. Após cadastro, cria clínica fictícia, inicia a sessão e abre a biblioteca. NÃO há perfil admin com painel de usuários separado.
- Pagamento: Mercado Pago Orders + webhook; proprietário informou configurar chaves externas. Não consta QA de compra real e ativação confirmados no checkpoint.
- Credenciais e segredos permanecem fora do webroot, nunca no código ou no Notion.
- Diretório /rdc/data/ não pode ser sobrescrito ou apagado durante deploy.

QA
- Testes CLI locais: tests/ops-smoke.php, tests/tenant-regression.php, tests/document-history-regression.php e tests/plan-config-regression.php. Simulação NÃO equivale a E2E na produção.
- Testes de login com duas clínicas, persistência em produção, permissões, Mercado Pago e documentos precisam de validação autenticada.
- Detalhes e limitações: docs/DOCSYSTEM-PREMIUM-V1-STATUS.md e docs/QA-ACESSO-CLINICAS.md.
- Cérebro privado no Notion: https://app.notion.com/p/410739c88b0a81f8a436d82129ff205a

OBSERVAÇÃO
O preço anunciado é R$ 67, com acesso online de 12 meses; Autoinspeção 360 opcional R$ 37. Confirmar o estado comercial antes de mudar preços.
