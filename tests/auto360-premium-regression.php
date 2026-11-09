<?php
declare(strict_types=1);
require dirname(__DIR__).'/auto360-premium.php';
function check(bool $condition, string $label): void {if (!$condition) throw new RuntimeException('FAILED: '.$label); echo "PASS ".$label."\n";}
$areas=['Legal','SDBPF','Equipe','Infraestrutura','Esterilização','Segurança','Resíduos','Tecnologias','Condicional'];
$items=[];
for($i=0;$i<45;$i++)$items[]=['area'=>$areas[intdiv($i,5)],'pergunta'=>'Teste '.($i+1),'fundamento'=>'Art. '.($i+1),'prioridade'=>'Alta'];
check(od360_code(0)==='B01'&&od360_code(8)==='B09'&&od360_code(9)==='B10'&&od360_code(44)==='B45','códigos B01 a B45');
check(count(od360_status_options())===5,'cinco estados explícitos');
$info=od360_info($items,[]);
check($info['percent']===0&&$info['counts']['nao_avaliado']===45&&$info['next']==='B01','sem respostas mostra zero e primeiro item');
$saved=['B01'=>['status'=>'verificado'],'B02'=>['status'=>'pendente'],'B03'=>['status'=>'nao_aplicavel'],'B04'=>['status'=>'em_andamento'],'B05'=>['status'=>'invalido']];
$info=od360_info($items,$saved);
check($info['counts']['avaliados']===4&&$info['counts']['nao_avaliado']===41,'contagem não confunde status inválido com resposta');
check($info['counts']['pendente']===1&&$info['counts']['em_andamento']===1,'pendências separadas de verificações');
check($info['areas']['Legal']['atencao']===2,'resumo por área');
check($info['next']==='B05','próximo não avaliado');
check($info['percent']===9,'percentual arredondado é preenchimento, não conformidade');
foreach(['B00','B46','B99','B1','B009','B01OR1=1'] as $code)check(!preg_match('/^B(?:0[1-9]|[1-3][0-9]|4[0-5])$/',$code),'rejeita código inválido '.$code);
foreach(range(1,45) as $i)check((bool)preg_match('/^B(?:0[1-9]|[1-3][0-9]|4[0-5])$/',sprintf('B%02d',$i)),'aceita B'.sprintf('%02d',$i));
