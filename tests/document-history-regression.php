<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {http_response_code(404);exit;}
if (!function_exists('mb_strlen')) {function mb_strlen(string $s):int{return strlen($s);}}
/** Tests document versioning and clinic scoping with a fake PDO. No real DB or browser. */
class RedirectStop extends RuntimeException {}
class DocumentFakeDb extends PDO {
 public array $docs=[], $hist=[], $details=[], $fieldHistory=[]; public bool $transaction=false;
 public function __construct() {}
 public function exec(string $statement): int|false{return 0;}
 public function beginTransaction():bool{$this->transaction=true;return true;}
 public function inTransaction():bool{return $this->transaction;}
 public function commit():bool{$this->transaction=false;return true;}
 public function rollBack():bool{$this->transaction=false;return true;}
 public function prepare(string $sql,array $options=[]):PDOStatement|false{return new DocumentStmt($this,$sql);}
}
class DocumentStmt extends PDOStatement {
 private array $row=[];
 public function __construct(private DocumentFakeDb $db,private string $sql) {}
 public function execute(?array $params=null):bool{
   $p=$params??[];
   if (str_starts_with($this->sql,'SELECT version FROM od_documents')){
     $this->requireTenant();$this->row=$this->db->docs[$p[0]][$p[1]]??[];
   } elseif(str_starts_with($this->sql,'INSERT INTO od_documents')){
     [$clinic,$id,$body,$version,$stage]=$p;$this->db->docs[$clinic][$id]=['version'=>$version,'content_html'=>$body,'stage'=>$stage];
   } elseif(str_starts_with($this->sql,'INSERT INTO od_document_history')){
     [$clinic,$id,$version,$body,$stage]=$p;$this->db->hist[$clinic][$id][$version]=['version'=>$version,'content_html'=>$body,'stage'=>$stage];
   } elseif(str_starts_with($this->sql,'INSERT INTO od_document_details')){
     [$clinic,$id,$json]=$p;$this->db->details[$clinic][$id]=['fields_json'=>$json];
   } elseif(str_starts_with($this->sql,'INSERT INTO od_document_fields_history')){
     [$clinic,$id,$version,$json]=$p;$this->db->fieldHistory[$clinic][$id][$version]=['fields_json'=>$json];
   } elseif(str_starts_with($this->sql,'SELECT fields_json FROM od_document_details')){
     $this->requireTenant();$this->row=$this->db->details[$p[0]][$p[1]]??[];
   } elseif(str_starts_with($this->sql,'SELECT version,stage,content_html FROM od_document_history')){
     if(!str_contains($this->sql,'clinic_id=? AND template_id=? AND version=?'))throw new RuntimeException('History tenant guard missing');
     $this->row=$this->db->hist[$p[0]][$p[1]][$p[2]]??[];
   } elseif(str_starts_with($this->sql,'SELECT fields_json FROM od_document_fields_history')){
     if(!str_contains($this->sql,'clinic_id=? AND template_id=? AND version=?'))throw new RuntimeException('Field history tenant guard missing');
     $this->row=$this->db->fieldHistory[$p[0]][$p[1]][$p[2]]??[];
   } else {throw new RuntimeException('Unexpected SQL: '.substr($this->sql,0,110));}
   return true;
 }
 private function requireTenant():void {if(!str_contains($this->sql,'clinic_id=? AND template_id=?'))throw new RuntimeException('Tenant filter missing');}
 public function fetchColumn(int $column=0):mixed {if(!$this->row)return false;return array_values($this->row)[$column]??false;}
 public function fetch(int $mode=PDO::FETCH_DEFAULT,int $cursorOrientation=PDO::FETCH_ORI_NEXT,int $cursorOffset=0):mixed{return $this->row?:false;}
}
$GLOBALS['db']=new DocumentFakeDb();$GLOBALS['uid']=101;$GLOBALS['cid']=11;$GLOBALS['flash']='';
function db():PDO{return $GLOBALS['db'];}
function od_editor_schema():PDO{return db();}
function od_editor_doc(int $id,int $cid):?array{return $GLOBALS['db']->docs[$cid][$id]??null;}
function od_safe_document(string $html):string{return trim($html);} // sanitizer intentionally not covered in this suite
function template_by_id(int $id):?array{return $id===3?['id'=>3,'title'=>'Manual de boas práticas','category'=>'SDBPF','html'=>'<h2>Modelo de referência</h2>']:null;}
function user():?int{return $GLOBALS['uid'];}
function clinic():?array{return user()?['id'=>$GLOBALS['cid'],'user_id'=>user(),'name'=>'Clínica fictícia']:null;}
function flash(string $m):void{$GLOBALS['flash']=$m;}
function redirect(string $url='?'):never{throw new RedirectStop($url);}
function check(bool $ok,string $m):void{if(!$ok){fwrite(STDERR,"FAIL: $m\n");exit(1);}echo "PASS: $m\n";}
require dirname(__DIR__).'/document-engine.php';
function save(int $version,string $label):void {
 $_POST=['id'=>'3','body_html'=>'<h2>Manual de boas práticas</h2><p>'.str_repeat($label,8).'</p>','stage'=>'rascunho',
  'g'=>['setor'=>'Clínica fictícia '.$version,'executor'=>'RT','frequencia'=>'Anual','escopo'=>'Real','governanca'=>'Equipe','evidencias'=>'Ficha','capacitacao'=>'Treino']];
 try{odx_post();}catch(RedirectStop $e){check($e->getMessage()==='?page=editar&id=3','saving redirects to same document');}
}
save(1,'Primeira versão ');
check(($GLOBALS['db']->docs[11][3]['version']??0)===1,'first version stored for clinic A');
save(2,'Segunda versão ');
check(($GLOBALS['db']->docs[11][3]['version']??0)===2,'version number increments');
check(count($GLOBALS['db']->hist[11][3]??[])===2,'prior document version remains intact');
check(count($GLOBALS['db']->fieldHistory[11][3]??[])===2,'field snapshots saved per version');
check(json_decode($GLOBALS['db']->fieldHistory[11][3][1]['fields_json'],true)['setor']==='Clínica fictícia 1','first fields are retained after second save');
$GLOBALS['uid']=202;$GLOBALS['cid']=22;
check(odx_get_fields(22,3)===[],'clinic B receives no fields from A');
save(1,'Outro cliente ');
check(($GLOBALS['db']->docs[22][3]['version']??0)===1,'B gets independent version counter');
check(count($GLOBALS['db']->docs)===2,'document storage segregates clinics');
$q=$GLOBALS['db']->prepare('SELECT version,stage,content_html FROM od_document_history WHERE clinic_id=? AND template_id=? AND version=?');
$q->execute([22,3,2]);check($q->fetch()===false,'B cannot load A version 2');
$q->execute([11,3,2]);check(($q->fetch()['version']??0)===2,'A version 2 still available');
echo "PASS: simulated document history and tenant checks completed\n";