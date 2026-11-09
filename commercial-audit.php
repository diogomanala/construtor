<?php
declare(strict_types=1);
/** Auditoria comercial somente do proprietário. Não expõe segredos nem PII. */
function od_commercial_audit_page(): never {
  if(!user()){redirect('?page=entrar');}
  if((int)user()!==1 || !od_is_pilot((int)user())){http_response_code(403);exit('Acesso restrito.');}
  $cfg=od_mp_config();
  $mp=od_mp_ready();
  $secretConfigured=!empty($cfg['webhook_secret']);
  $tokenConfigured=!empty($cfg['token']);
  unset($cfg);
  $legal=is_file(__DIR__.'/site-legal.php');
  $support=(string)(getenv('OD_SUPPORT_EMAIL')?:'');
  $hasSupport=(bool)filter_var($support,FILTER_VALIDATE_EMAIL);
  $count=['paid'=>0,'paid_activated'=>0,'pending'=>0];
  try {
    $db=od_orders_db();
    foreach($db->query('SELECT status,COUNT(*) AS qty,SUM(CASE WHEN activated_user_id IS NOT NULL THEN 1 ELSE 0 END) AS active_count FROM od_orders GROUP BY status') as $row){
      if($row['status']==='paid'){$count['paid']=(int)$row['qty'];$count['paid_activated']=(int)$row['active_count'];}
      elseif($row['status']==='pending')$count['pending']=(int)$row['qty'];
    }
  }catch(Throwable $e){error_log('[Odonto] Auditoria comercial: não foi possível consultar a contagem de pedidos.');}
  h('Auditoria comercial',false);
  echo '<div class="od-launch"><a href="?page=painel">← Voltar ao painel</a><span class="od-launch-kicker">ACESSO RESTRITO AO PROPRIETÁRIO</span><h1>Conferência antes dos anúncios</h1><p>Este painel mostra a configuração do site sem revelar tokens nem dados pessoais. Um item verde não substitui uma compra de teste.</p><div class="od-launch-grid">';
  $entries=[
   ['Checkout Mercado Pago', $mp?'Configurado':'Indisponível', $mp, $mp?'Token e requisitos locais detectados. Ainda precisa de uma compra real de teste.':'Verifique chaves privadas do Mercado Pago e o requisito de configuração do webhook.'],
   ['Credenciais do pagamento', $tokenConfigured?'Token encontrado':'Sem token', $tokenConfigured, 'Apenas presença. Não valida permissões nem saldo de conta.'],
   ['Segredo do webhook', $secretConfigured?'Configurado':'Não configurado', $secretConfigured, 'O código atual só utiliza consulta por ID de pedido; o segredo está no critério de ativação do checkout.'],
   ['Termos e privacidade', $legal?'Páginas criadas':'Páginas ausentes', $legal, 'Verifique as páginas e preencha dados comerciais reais antes de anunciar.'],
   ['Contato comercial', $hasSupport?'E-mail definido':'E-mail ainda não definido', $hasSupport, 'Configure OD_SUPPORT_EMAIL e confirme que a caixa de atendimento existe.'],
   ['Pixel de compras Meta', 'Não implementado', false, 'Defina o pixel correto do Odonto em Dia e só depois configure PageView, InitiateCheckout e Purchase.'],
   ['Teste de compra + ativação', $count['paid_activated']>0?'Existem acessos pagos ativados':'Sem ativação paga confirmada', $count['paid_activated']>0,'Contagem no banco não substitui testar o e-mail, login e o adicional com uma nova compra.']
  ];
  foreach($entries as [$title,$status,$good,$detail])echo '<article class="od-launch-card '.($good?'is-ready':'is-pending').'"><span>'.($good?'✓':'!').'</span><h2>'.esc($title).'</h2><strong>'.esc($status).'</strong><p>'.esc($detail).'</p></article>';
  echo '</div><div class="od-launch-bottom"><h2>Próximo teste necessário</h2><p>Abra o checkout em uma janela anônima, use um e-mail próprio, conclua uma compra real autorizada e confirme a ativação da conta e a entrega da Autoinspeção 360. Não faça pagamento falso e não use dados de pacientes nos testes.</p><a href="?page=checkout" class="btn">Abrir checkout</a><p>Pedidos pagos: '.(int)$count['paid'].' · com acesso ativado: '.(int)$count['paid_activated'].' · pendentes: '.(int)$count['pending'].' (resumo administrativo)</p></div></div>';
  echo '<style>.od-launch{max-width:1050px;margin:10px auto 60px}.od-launch>a{font-size:13px;color:#167765}.od-launch-kicker{display:block;margin:28px 0 8px;color:#418d68;font-size:10px;letter-spacing:1.6px;font-weight:800}.od-launch h1{font-size:clamp(29px,4vw,43px);margin:0 0 13px}.od-launch>p{color:#688075;font-size:14px;line-height:1.7;max-width:750px}.od-launch-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:24px 0}.od-launch-card{background:#fff;padding:22px;border:1px solid #dbe8df;border-radius:15px}.od-launch-card>span{display:inline-grid;width:29px;height:29px;place-items:center;border-radius:50%;background:#eef4e8;color:#538346;font-weight:850}.od-launch-card.is-pending>span{background:#fff2e8;color:#af7032}.od-launch-card h2{font-size:16px;margin:13px 0 7px}.od-launch-card strong{font-size:12px;color:#317654}.od-launch-card.is-pending strong{color:#996533}.od-launch-card p{font-size:12px;line-height:1.65;color:#6d8177;margin:11px 0 0}.od-launch-bottom{background:#edf7f0;padding:24px;border-radius:16px}.od-launch-bottom h2{font-size:20px}.od-launch-bottom p{font-size:13px;line-height:1.7;color:#506d5c}@media(max-width:620px){.od-launch-grid{grid-template-columns:1fr}.od-launch-card{padding:18px}}</style>';
  foot();exit;
}
