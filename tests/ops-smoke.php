<?php
if(!function_exists('mb_strlen')){function mb_strlen($s){return strlen($s);}}
require __DIR__.'/operational-records.php';
function verify($condition,$message){if(!$condition){fwrite(STDERR,"FAIL: $message\n");exit(1);}echo "PASS: $message\n";}
$m=['event_date'=>'2026-11-06','subject'=>'Autoclave A','actor'=>'Técnico B','notes'=>'Calibração concluída','status'=>'registrado','service_type'=>'calibracao','provider'=>'Laboratório X','service_note'=>'Relatório arquivado','next_date'=>'2027-05-06'];
verify(odx_ops_validate('maintenance',$m)['ok'],'maintenance accepts valid fields');
verify(!odx_ops_validate('maintenance',array_merge($m,['event_date'=>'2026-02-30']))['ok'],'reject impossible date');
verify(!odx_ops_validate('maintenance',array_merge($m,['status'=>'aprovado']))['ok'],'reject unverified status');
verify(!odx_ops_validate('maintenance',array_merge($m,['service_type'=>'invalid']))['ok'],'reject invalid type');
verify(!odx_ops_validate('maintenance',array_merge($m,['next_date'=>'bad']))['ok'],'reject invalid future date');
verify(!odx_ops_validate('maintenance',array_merge($m,['actor'=>str_repeat('A',121)]))['ok'],'reject oversized actor');
$e=['event_date'=>'2026-11-06','subject'=>'Luvas procedimento','actor'=>'Funcionário 42','notes'=>'','status'=>'pendente','quantity'=>'2','item_detail'=>'Tamanho M','delivered_by'=>'Almoxarifado','orientation_note'=>'Uso conforme procedimento interno'];
verify(odx_ops_validate('epi',$e)['ok'],'EPI accepts valid fields');
verify(!odx_ops_validate('epi',array_merge($e,['quantity'=>'0']))['ok'],'reject zero EPI quantity');
verify(!odx_ops_validate('epi',array_merge($e,['quantity'=>'1001']))['ok'],'reject excess EPI quantity');
verify(!odx_ops_validate('epi',array_merge($e,['quantity'=>'2; DROP TABLE users']))['ok'],'reject non numeric EPI');
verify(!odx_ops_validate('epi',array_merge($e,['actor'=>[]]))['ok'],'reject malformed actor input');
verify(!odx_ops_validate('nope',$e)['ok'],'reject unknown operation type');