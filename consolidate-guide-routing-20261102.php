<?php
// CIC editorial consolidation: dry-run preflight, backups and atomic per-file writes.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$backup=dirname($root).'/cic-adsense-backup-20261102/guide-consolidation';
$ops=[["guia.php",[["JGRiU2x1Zz1zdG9yYWdlU2x1Zygkc2x1Zyk7","Ly8gQ0lDX0VESVRPUklBTF9DT05TT0xJREFUSU9OXzIwMjYxMTAyOiBjYWRhIGFzc3VudG8gdGVtIHVtYSB1bmljYSBVUkwgcHJpbmNpcGFsLgokbWVyZ2VkR3VpZGVzPVsnY29tby1kaXZpZGlyLWltYWdlbS1wYXJhLWluc3RhZ3JhbS1lbS1wYXJ0ZXMtaWd1YWlzJz0+J2ZlcnJhbWVudGEtb25saW5lLXBhcmEtY29ydGFyLWltYWdlbnMtZGUtY2Fycm9zc2VsLW5vLWluc3RhZ3JhbScsJ2NvbW8tZmF0aWFyLXVtYS1pbWFnZW0tcGFyYS1pbnN0YWdyYW0tc2VtLXBlcmRlci1xdWFsaWRhZGUnPT4nY29tby1ldml0YXItcGVyZGEtZGUtcXVhbGlkYWRlLW5vLWNhcnJvc3NlbC1pbnN0YWdyYW0nXTsKaWYoaXNzZXQoJG1lcmdlZEd1aWRlc1skc2x1Z10pKXsKIGhlYWRlcignTG9jYXRpb246IGh0dHBzOi8vY29ydGFyaW1hZ2Vuc2NhcnJvc3NlbC5vbmxpbmUvZ3VpYXMvJy4kbWVyZ2VkR3VpZGVzWyRzbHVnXS4nLycsdHJ1ZSwzMDEpOwogZXhpdDsKfQokZGJTbHVnPXN0b3JhZ2VTbHVnKCRzbHVnKTs="],["RlJPTSBwb3N0cyBXSEVSRSBpZDw+PyBBTkQgc2x1ZyBJUyBOT1QgTlVMTCBBTkQgc2x1Zzw+JycgT1JERVIgQlkgY3JlYXRlZF9hdCBERVNDIExJTUlUIDM=","RlJPTSBwb3N0cyBXSEVSRSBpZDw+PyBBTkQgc2x1ZyBJUyBOT1QgTlVMTCBBTkQgc2x1Zzw+JycgQU5EIHNsdWcgTk9UIElOICgnY29tby1kaXZpZGlyLWltYWdlbS1wYXJhLWluc3RhZ3JhbS1lbS1wYXJ0ZXMtaWd1YWlzJywnY29tby1mYXRpYXItdW1hLWltYWdlbS1wYXJhLWluc3RhZ3JhbS1zZW0tcGVyZGVyLXF1YWxpZGFkZScpIE9SREVSIEJZIGNyZWF0ZWRfYXQgREVTQyBMSU1JVCAz"]]],
["guias.php",[["d2hpbGUoJHEmJiRyb3c9JHEtPmZldGNoX2Fzc29jKCkpewo=","d2hpbGUoJHEmJiRyb3c9JHEtPmZldGNoX2Fzc29jKCkpewogIC8vIENJQ19FRElUT1JJQUxfQ09OU09MSURBVElPTl8yMDI2MTEwMjogbWFudGVyIGFydGlnb3MgYW50aWdvcyBhcGVuYXMgY29tbyByZWRpcmVjaW9uYW1lbnRvLgogIGlmKGluX2FycmF5KCRyb3dbJ3NsdWcnXSxbJ2NvbW8tZGl2aWRpci1pbWFnZW0tcGFyYS1pbnN0YWdyYW0tZW0tcGFydGVzLWlndWFpcycsJ2NvbW8tZmF0aWFyLXVtYS1pbWFnZW0tcGFyYS1pbnN0YWdyYW0tc2VtLXBlcmRlci1xdWFsaWRhZGUnXSx0cnVlKSljb250aW51ZTsK"],["ICBpZihzdHJwb3MoJHNsdWcsJ3RhbWFuaG8tY2Fycm9zc2VsJykhPT1mYWxzZSlyZXR1cm4gJ01lZGlkYXMgZSBwcm9wb3LDp8O1ZXMnOw==","ICBpZihzdHJwb3MoJHNsdWcsJ2NvbW8tZmF6ZXItY2Fycm9zc2VsLW5vLWVkaXRzJykhPT1mYWxzZSlyZXR1cm4gJ0VkaXRvciBFZGl0cyc7CiAgaWYoc3RycG9zKCRzbHVnLCdjYXJyb3NzZWwtc3BsaXR0ZXInKSE9PWZhbHNlKXJldHVybiAnUHJlY2lzw6NvIGRvIGNvcnRlJzsKICBpZihzdHJwb3MoJHNsdWcsJ2NvbW8tY29ydGFyLWJhbm5lcicpIT09ZmFsc2UpcmV0dXJuICdCYW5uZXJzIGUgZm9ybWF0b3MnOwogIGlmKHN0cnBvcygkc2x1ZywnY29tby1kaXZpZGlyLXVtYS1pbWFnZW0nKSE9PWZhbHNlKXJldHVybiAnR3JhZGUgZSBtb3NhaWNvcyc7CiAgaWYoc3RycG9zKCRzbHVnLCd0YW1hbmhvLWNhcnJvc3NlbCcpIT09ZmFsc2UpcmV0dXJuICdNZWRpZGFzIGUgcHJvcG9yw6fDtWVzJzs="],["ICBpZihzdHJwb3MoJHNsdWcsJ2ZlcnJhbWVudGEtb25saW5lJykhPT1mYWxzZSlyZXR1cm4gJ1Bhc3NvIGEgcGFzc28nOw==","ICBpZihzdHJwb3MoJHNsdWcsJ2ZlcnJhbWVudGEtb25saW5lJykhPT1mYWxzZSlyZXR1cm4gJ0NvcnRhciAzLCA1LCA3IGUgMTAgc2xpZGVzJzs="]]],
["sitemap.php",[["d2hpbGUoJHJvdz0kcmVzdWx0LT5mZXRjaF9hc3NvYygpKSRwb3N0c1tdPSRyb3c7","d2hpbGUoJHJvdz0kcmVzdWx0LT5mZXRjaF9hc3NvYygpKXsKICAgICAgLy8gQ0lDX0VESVRPUklBTF9DT05TT0xJREFUSU9OXzIwMjYxMTAyOiBVUkxzIGFudGlnYXMgZmF6ZW0gMzAxIHBhcmEgZ3VpYXMgY29uc29saWRhZG9zLgogICAgICBpZihpbl9hcnJheSgkcm93WydzbHVnJ10sWydjb21vLWRpdmlkaXItaW1hZ2VtLXBhcmEtaW5zdGFncmFtLWVtLXBhcnRlcy1pZ3VhaXMnLCdjb21vLWZhdGlhci11bWEtaW1hZ2VtLXBhcmEtaW5zdGFncmFtLXNlbS1wZXJkZXItcXVhbGlkYWRlJ10sdHJ1ZSkpY29udGludWU7CiAgICAgICRwb3N0c1tdPSRyb3c7CiAgICB9"],["ICAnZmVycmFtZW50YS1vbmxpbmUtcGFyYS1jb3J0YXItaW1hZ2Vucy1kZS1jYXJyb3NzZWwtbm8taW5zdGFncmFtJz0+JzIwMjYtMTEtMDIn","ICAnZmVycmFtZW50YS1vbmxpbmUtcGFyYS1jb3J0YXItaW1hZ2Vucy1kZS1jYXJyb3NzZWwtbm8taW5zdGFncmFtJz0+JzIwMjYtMTEtMDInLAogICdjb21vLWV2aXRhci1wZXJkYS1kZS1xdWFsaWRhZGUtbm8tY2Fycm9zc2VsLWluc3RhZ3JhbSc9PicyMDI2LTExLTAyJw=="]]],
["post.php",[["JHB1YmxpY1NsdWcgPSAoJHNsdWcgPT09ICdtZWxob3ItZmVycmFtZW50YS1wYXJhLWNvcnRhci1pbWFnZW5zLXByYS1jYXJyb2xzc2VsLW5vLWluc3RhZ3JhbScpDQogICAgICAgID8gJ21lbGhvci1mZXJyYW1lbnRhLXBhcmEtY29ydGFyLWltYWdlbnMtcHJhLWNhcnJvc3NlbC1uby1pbnN0YWdyYW0nIDogJHNsdWc7","Ly8gQ0lDX0VESVRPUklBTF9DT05TT0xJREFUSU9OXzIwMjYxMTAyOiByZWRpcmVjaW9uYW1lbnRvIGRpcmV0bywgc2VtIHNhbHRvcyBleHRyYXMuDQogICAgJGFsaWFzZXM9Wydjb21vLWRpdmlkaXItaW1hZ2VtLXBhcmEtaW5zdGFncmFtLWVtLXBhcnRlcy1pZ3VhaXMnPT4nZmVycmFtZW50YS1vbmxpbmUtcGFyYS1jb3J0YXItaW1hZ2Vucy1kZS1jYXJyb3NzZWwtbm8taW5zdGFncmFtJywgJ2NvbW8tZmF0aWFyLXVtYS1pbWFnZW0tcGFyYS1pbnN0YWdyYW0tc2VtLXBlcmRlci1xdWFsaWRhZGUnPT4nY29tby1ldml0YXItcGVyZGEtZGUtcXVhbGlkYWRlLW5vLWNhcnJvc3NlbC1pbnN0YWdyYW0nLCAnbWVsaG9yLWZlcnJhbWVudGEtcGFyYS1jb3J0YXItaW1hZ2Vucy1wcmEtY2Fycm9sc3NlbC1uby1pbnN0YWdyYW0nPT4nbWVsaG9yLWZlcnJhbWVudGEtcGFyYS1jb3J0YXItaW1hZ2Vucy1wcmEtY2Fycm9zc2VsLW5vLWluc3RhZ3JhbSddOw0KICAgICRwdWJsaWNTbHVnID0gJGFsaWFzZXNbJHNsdWddID8/ICRzbHVnOw=="]]]];
$plan=[];
foreach($ops as $entry){
 $f=$entry[0];$p=$root.'/'.$f;$src=@file_get_contents($p);
 if($src===false){echo "READ_ERR ".$f."\n";exit(1);}
 if(strpos($src,'CIC_EDITORIAL_CONSOLIDATION_20261102')!==false){echo "ALREADY ".$f."\n";continue;}
 foreach($entry[1] as $pair){
  $a=base64_decode($pair[0],true);$b=base64_decode($pair[1],true);
  if($a===false||$b===false||substr_count($src,$a)!==1){echo "ANCHOR_ERR ".$f."\n";exit(1);}
  $src=str_replace($a,$b,$src);
 }
 try{token_get_all($src,TOKEN_PARSE);}catch(Throwable $e){echo "SYNTAX_ERR ".$f."\n";exit(1);}
 $plan[$f]=$src;
}
if(!$plan){echo "NO_CHANGES\n";exit;}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERR\n";exit(1);}
foreach($plan as $f=>$src){
 if(!file_exists($backup.'/'.$f)&&!copy($root.'/'.$f,$backup.'/'.$f)){echo "BACKUP_ERR ".$f."\n";exit(1);}
}
$done=[];
foreach($plan as $f=>$src){
 if(file_put_contents($root.'/'.$f,$src,LOCK_EX)===false){
   foreach($done as $old){copy($backup.'/'.$old,$root.'/'.$old);}
   echo "WRITE_ERR ".$f." ROLLED_BACK\n";exit(1);
 }
 $done[]=$f;echo "UPDATED ".$f."\n";
}
echo "GUIDE_CONSOLIDATION_DONE\n";
