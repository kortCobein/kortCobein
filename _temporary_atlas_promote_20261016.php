<?php
declare(strict_types=1);
$public='/home/u899160275/domains/iokort.com/public_html/others';
$dest=$public.'/atlas';$stage=$public.'/atlas-check';
$back='/home/u899160275/domains/iokort.com/.apis/atlas_backup_20261016';
$files=['index.html','legacy/atlas.html'];
if(!is_file($stage.'/assets/index-unified.js')||!is_file($stage.'/assets/legacy-data.js')||!is_file($stage.'/assets/unified.css'))exit('STAGING_MISSING');
if(!is_dir($back)&&!mkdir($back,0750,true))exit('BACKUP_DENIED');
foreach($files as $f){if(!is_file($dest.'/'.$f))exit('EXISTING_MISSING:'.$f);if(!is_file($back.'/'.$f)){@mkdir(dirname($back.'/'.$f),0750,true);if(!copy($dest.'/'.$f,$back.'/'.$f))exit('BACKUP_FAILED:'.$f);}}
foreach(['index.html','assets/index-unified.js','assets/legacy-data.js','assets/unified.css'] as $f){
$source=$stage.'/'.$f;$target=$dest.'/'.$f;
if(!is_file($source))exit('NO_SOURCE:'.$f);
$data=file_get_contents($source);
if($f==='assets/index-unified.js'){$data=str_replace('const[C,st]=Xt.useState("components")','const[C,st]=Xt.useState("home")',$data);}
if($f==='index.html'){$data=str_replace('?v=20261016b','?v=20261016live',$data);}
$tmp=$target.'.deploytmp';if(file_put_contents($tmp,$data)===false||!rename($tmp,$target))exit('WRITE_FAILED:'.$f);
}
$alias='<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="0;url=/others/atlas/"><link rel="canonical" href="/others/atlas/"><title>Atlas unificado</title></head><body><a href="/others/atlas/">Abrir Atlas</a></body></html>';
file_put_contents($dest.'/legacy/atlas.html',$alias);
echo "ATLAS_PROD_MERGED;BACKUP_OK;EXAMPLES_277";
