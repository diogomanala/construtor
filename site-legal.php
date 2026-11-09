<?php
declare(strict_types=1);
/** Páginas públicas de informações comerciais e privacidade. */
function od_public_legal_page(string $page): never {
  $privacy=in_array($page,['privacidade','politica-privacidade'],true);
  if(!$privacy && $page!=='termos'){http_response_code(404);exit('Página não encontrada');}
  h($privacy?'Privacidade':'Termos',false);
  echo '<div class="od-legal-public"><a href="?page=inicio">Voltar ao Odonto em Dia</a><span>ODONTO EM DIA · INFORMAÇÕES AO COMPRADOR</span><h1>'.($privacy?'Política de privacidade':'Termos de uso e condições da oferta').'</h1><p class="od-legal-updated">Atualizado em 09/11/2026. Aplicável à plataforma odontonegocios.com/rdc/.</p>';
  if($privacy){
    $sections=[
      ['Dados que utilizamos','Na compra e no acesso tratamos e-mail, identificadores de conta, situação do pagamento e informações necessárias à autenticação. No ambiente da clínica, você pode informar nome da unidade, CNPJ, dados do responsável técnico, registros operacionais, tarefas, respostas às autoavaliações e versões de documentos. Não insira informações identificáveis de pacientes ou prontuários em campos livres.'],
      ['Finalidades','Usamos esses dados para prestar o serviço contratado, liberar e proteger o acesso, personalizar documentos, manter versões, permitir registros e acompanhamentos, responder solicitações, prevenir abuso e cumprir obrigações legais.'],
      ['Serviços necessários','O pagamento é concluído em ambiente do Mercado Pago, que aplica as próprias condições de privacidade. Os serviços de hospedagem e infraestrutura podem tratar dados necessários ao funcionamento. Não comercializamos dados pessoais dos compradores.'],
      ['Cookies e mensuração','Utilizamos recursos técnicos necessários para login, sessão e prevenção de fraudes. Ferramentas externas de anúncios ou medição, se ativadas, exigem informação adicional e adequação dos controles de privacidade antes de sua utilização.'],
      ['Armazenamento e segurança','Adotamos controles de acesso, autenticação e medidas de proteção dos registros. Guardamos informações enquanto necessárias para a prestação do serviço e para atender obrigações legais. Não há garantia de segurança absoluta em serviços online.'],
      ['Direitos do titular','Você pode solicitar acesso, correção, esclarecimentos e, quando cabível pela LGPD, exclusão ou outros direitos sobre seus dados. Solicitações devem ser encaminhadas ao canal de atendimento oficial do fornecedor, identificado no comprovante da contratação.']
    ];
  }else{
    $sections=[
      ['O que está incluído','O kit principal Odonto em Dia custa R$ 67 em pagamento único e inclui acesso online por 12 meses, biblioteca de 30 modelos de referência, editor interativo, versões salvas, acompanhamento, autoavaliação inicial de 15 pontos e montagem de pasta documental A4 para impressão.'],
      ['Complemento opcional','A Autoinspeção 360, se selecionada expressamente no checkout, custa R$ 37 adicionais e oferece 45 verificações internas guiadas, registros e relatório A4. Não é incluída automaticamente no preço principal. Não há renovação automática nesta oferta.'],
      ['Natureza dos documentos','Os documentos são minutas para adaptação à realidade da clínica e não substituem o responsável técnico. Não há emissão de certificados, alvarás, pareceres, licenças nem garantia de aprovação na fiscalização. Cabe ao responsável técnico avaliar, revisar, aprovar, assinar e implementar o que for aplicável.'],
      ['Pagamento e ativação','O checkout encaminha para o ambiente seguro do Mercado Pago. Após confirmação da transação, o comprador pode ativar o acesso pelo fluxo apresentado na página do pedido. A liberação depende da confirmação do pagamento.'],
      ['Desistência e atendimento','Compras online estão sujeitas ao direito de arrependimento previsto no art. 49 do Código de Defesa do Consumidor, incluindo o prazo legal de 7 dias. Para dúvidas, cancelamento e suporte, utilize o canal oficial informado no comprovante da contratação.'],
      ['Uso e responsabilidade','O usuário deve manter dados corretos, resguardar credenciais e não armazenar dados sensíveis de pacientes em formulários que não foram preparados para essa finalidade. Uma marcação como conferido internamente não constitui conformidade sanitária.'],
      ['Normas e atualizações','Os links de referência ajudam a consultar fontes oficiais. A plataforma não garante consulta automática em tempo real a toda a legislação. Normas podem mudar e cabe ao responsável técnico verificar a versão vigente e sua aplicabilidade.']
    ];
  }
  foreach($sections as [$title,$body])echo '<section><h2>'.esc($title).'</h2><p>'.esc($body).'</p></section>';
  echo '<nav class="od-legal-links"><a href="?page=checkout">Conferir compra</a><a href="?page='.($privacy?'termos':'politica-privacidade').'">'.($privacy?'Termos de uso':'Política de privacidade').'</a></nav></div>';
  echo '<style>.od-legal-public{max-width:810px;margin:0 auto 60px;padding:clamp(22px,4vw,48px);background:#fff;border:1px solid #dfe9e4;border-radius:18px}.od-legal-public>a{font-size:13px;color:#15806c;text-decoration:none}.od-legal-public>span{display:block;font-size:10px;letter-spacing:1.7px;font-weight:800;color:#29806c;margin-top:35px}.od-legal-public h1{font-size:clamp(28px,5vw,42px);letter-spacing:-1.5px;margin:12px 0 12px}.od-legal-updated{color:#668279;font-size:13px;margin:0 0 25px}.od-legal-public section{border-top:1px solid #e7efea;padding:22px 0}.od-legal-public section h2{font-size:18px;margin:0 0 11px;color:#235540}.od-legal-public section p{font-size:14px;line-height:1.75;color:#536e62;margin:0}.od-legal-links{display:flex;flex-wrap:wrap;gap:12px 22px;margin-top:22px}.od-legal-links a{font-size:13px;color:#1e755f;text-decoration:none}@media(max-width:540px){.od-legal-public{border-radius:11px;padding:22px 19px}}</style>';
  foot();exit;
}
