<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
/** Integration-style regression for operational records; fake PDO, no production access. */
if (!function_exists('mb_strlen')) { function mb_strlen(string $value): int {return strlen($value);} }
class RedirectIntercept extends RuntimeException {}
class FakeDb extends PDO {
 public array $rows=[]; public array $prepared=[]; public array $ddl=[];
 public function __construct(){}
 public function exec(string $statement): int|false {$this->ddl[]=$statement;return 0;}
 public function prepare(string $query,array $options=[]): PDOStatement|false {
   $this->prepared[]=$query;return new FakeStmt($this,$query);
 }
}
class FakeStmt extends PDOStatement {
 private array $result=[];
 public function __construct(private FakeDb $db,private string $sql){}
 public function execute(?array $params=null): bool {
   $p=$params??[];
   if(str_contains($this->sql,'INSERT INTO od_ops_records')){
      if(count($p)!==8)throw new Exception('Malformed insert');
      $this->db->rows[]=['clinic_id'=>$p[0],'record_kind'=>$p[1],'event_date'=>$p[2],'subject'=>$p[3],'actor'=>$p[4],'status'=>$p[5],'notes'=>$p[6],'details_json'=>$p[7], 'created_at'=>'2026-11-06 09:00:00', 'id'=>count($this->db->rows)+1];
   } elseif(str_contains($this->sql,'FROM od_ops_records')) {
      if(!str_contains($this->sql,'clinic_id=? AND record_kind=?'))throw new Exception('Tenant filter missing');
      if(count($p)<2)throw new Exception('Tenant args missing');
      $res=array_values(array_filter($this->db->rows,function($x)use($p){
        if((int)$x['clinic_id']!==(int)$p[0] || $x['record_kind']!==$p[1])return false;
        if(count($p)>=3 && $p[2]!=$x['status'] && $p[2]!=$x['event_date'])return false;
        if(count($p)===4 && $p[3]!=$x['event_date'])return false;
        return true;
      }));
      usort($res,fn($a,$b)=>strcmp($b['event_date'],$a['event_date']));
      $this->result=array_slice($res,0,200);
   }
   return true;
 }
 public function fetchAll(int $mode=PDO::FETCH_DEFAULT,mixed ...$args): array {return $this->result;}
}
$GLOBALS['DB']=new FakeDb();
$GLOBALS['uid']=101;
$GLOBALS['clinicId']=11;
$GLOBALS['flash']=[];
function db():PDO{return $GLOBALS['DB'];}
function odx_schema():PDO{return db();}
function user():?int{return $GLOBALS['uid'];}
function clinic():?array{return user()?['id'=>$GLOBALS['clinicId'],'user_id'=>user(),'name'=>'Clínica fictícia']:null;}
function redirect(string $path='?'):never{throw new RedirectIntercept($path);}
function flash(string $message):void{$GLOBALS['flash'][]=$message;}
function runPost(array $body):string{
  $_POST=$body;try{odx_ops_post();}catch(RedirectIntercept $e){return $e->getMessage();}
  throw new RuntimeException('Expected exit/redirect');
}
function assertQA(bool $ok,string $message):void{if(!$ok){fwrite(STDERR,"FAIL: $message\n");exit(1);}echo "PASS: $message\n";}
require dirname(__DIR__).'/operational-records.php';
$goodM=['record_kind'=>'maintenance','event_date'=>'2026-11-06','subject'=>'Autoclave A','actor'=>'Responsável A','status'=>'registrado','notes'=>'Serviço cadastrado para teste','service_type'=>'preventiva','provider'=>'Prestador 1','service_note'=>'Laudo interno','next_date'=>'2026-12-15','clinic_id'=>'22'];
assertQA(runPost($goodM)==='?page=registros-manutencao','A creates maintenance record');
assertQA(count($GLOBALS['DB']->rows)===1 && $GLOBALS['DB']->rows[0]['clinic_id']===11,'client-supplied clinic_id ignored');
assertQA(count(odx_ops_query(db(),11,'maintenance'))===1,'A sees own maintenance record');
assertQA(count(odx_ops_query(db(),22,'maintenance'))===0,'B cannot read A maintenance');
$GLOBALS['uid']=202;$GLOBALS['clinicId']=22;
$goodE=['record_kind'=>'epi','event_date'=>'2026-11-05','subject'=>'Luvas','actor'=>'Colaborador B','status'=>'pendente','notes'=>'','quantity'=>'3','item_detail'=>'Tamanho P','delivered_by'=>'Almoxarifado B','orientation_note'=>'Uso apropriado','clinic_id'=>'11'];
assertQA(runPost($goodE)==='?page=registros-epi','B creates EPI record');
assertQA(count($GLOBALS['DB']->rows)===2 && $GLOBALS['DB']->rows[1]['clinic_id']===22,'EPI saved to B despite forged clinic id');
assertQA(count(odx_ops_query(db(),11,'epi'))===0,'A cannot read B EPI');
assertQA(count(odx_ops_query(db(),22,'epi'))===1,'B reads own EPI');
assertQA(count(odx_ops_query(db(),22,'epi','pendente','2026-11-05'))===1,'date/status query filters matches');
assertQA(count(odx_ops_query(db(),22,'epi','registrado','2026-11-05'))===0,'non-matching status filters');
assertQA(count(odx_ops_query(db(),22,'epi','pendente','2026-11-04'))===0,'non-matching date filters');
$before=count($GLOBALS['DB']->rows);
runPost(array_replace($goodE,['quantity'=>'0']));
runPost(array_replace($goodE,['status'=>'aprovado']));
runPost(array_replace($goodE,['event_date'=>'2026-02-30']));
assertQA(count($GLOBALS['DB']->rows)===$before,'invalid fields do not persist');
$GLOBALS['uid']=null;$GLOBALS['clinicId']=null;
assertQA(count($GLOBALS['DB']->rows)===$before,'fixture unchanged before unauthorized request');
assertQA(odx_ops_validate('epi',$goodE)['ok']===true,'pure validator still responds to test');
$main=dirname(__DIR__).'/index.php';
if(is_file($main)){
 $src=file_get_contents($main);
 $gate=strpos($src,"!od_is_pilot((int)user()) && !od_mp_entitlement((int)user())");
 $post=strpos($src,"if(\$act==='op_record')odx_ops_post();");
 assertQA($gate!==false&&$post!==false&&$gate<$post,'main POST entitlement guard before operational writes');
}
echo "PASS: operational isolation and validation checks completed (no real DB)\n";