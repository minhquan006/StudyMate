<?php
// Configuration only: no database connection, key or provider request.
if(PHP_SAPI!=='cli')exit;
$names=['GEMINI_MODEL','AI_MODEL'];$saved=[];
foreach($names as $name)$saved[$name]=getenv($name);
$checks=0;
try{
 foreach([
  ['', '', 'gemini-2.5-flash-lite'],
  ['', 'legacy-model', 'legacy-model'],
  ['current-model', '', 'current-model'],
  ['current-model', 'legacy-model', 'current-model'],
 ] as [$canonical,$legacy,$expected]){
  putenv('GEMINI_MODEL='.$canonical);putenv('AI_MODEL='.$legacy);
  foreach(['docker/config.php','config.example.php'] as $file){
   $config=require dirname(__DIR__).'/'.$file;
   if($config['ai_model']!==$expected)throw new RuntimeException('Wrong model resolution: '.$file);
   echo "PASS model config $file case ".(++$checks)."\n";
  }
 }
 echo "$checks model configuration checks passed. Real Gemini: NOT RUN.\n";
}finally{foreach($saved as $name=>$value)putenv($value===false?$name:$name.'='.$value);}
