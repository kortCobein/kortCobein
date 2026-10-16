<?php
declare(strict_types=1);
ini_set('display_errors','1');error_reporting(E_ALL);
$root='/home/u899160275/domains/iokort.com';
$public=$root.'/public_html/atlas';$stage=$root.'/public_html/atlas-preview';
$backup=$root.'/.apis/atlas_real_preview_backup_20261016';
$manifest=$backup.'/SUCCESS';
$files=['index.html','assets/index-preview-fix.js','assets/live-preview.css','assets/preview-frame.js','preview-frame.html'];
if(is_file($manifest)){echo 'ALREADY_INSTALLED';exit;}
foreach(array_slice($files,1) as $f){if(!is_file($stage.'/'.$f)){echo 'STAGE_MISSING:'.$f;exit(2);}}
$jsx=file_get_contents($stage.'/assets/index-preview-fix.js');
if(!str_contains($jsx,"className:'atlas-legacy-component-sample atlas-live-preview'")){echo 'PATCH_MISSING';exit(3);}
if(!str_contains($jsx,'const[C,st]=Xt.useState("components")')){echo 'EXPECTED_STAGE_ROUTE_MISSING';exit(4);}
$jsx=str_replace('const[C,st]=Xt.useState("components")','const[C,st]=Xt.useState("home")',$jsx);
if(!str_contains($jsx,'const[C,st]=Xt.useState("home")')){echo 'ROUTE_RESET_FAILED';exit(5);}
$html=file_get_contents($public.'/index.html');
if(!str_contains($html,'index-unified.js')){echo 'PROD_SCRIPT_NOT_FOUND';exit(6);}
if(!str_contains($html,'live-preview.css'))$html=str_replace('</head>','<link rel="stylesheet" href="./assets/live-preview.css?v=20261016_2"></head>',$html);
$html=preg_replace('~index-unified\\.js(?:\\?[^"\']*)?~','index-preview-fix.js?v=20261016_2',$html,1);
if(!str_contains($html,'index-preview-fix.js')){echo 'INDEX_PATCH_FAILED';exit(7);}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo 'BACKUP_CREATE_FAILED';exit(8);}
if(!is_file($backup.'/index.html')&&!copy($public.'/index.html',$backup.'/index.html')){echo 'INDEX_BACKUP_FAILED';exit(9);}
if(!is_file($backup.'/index-unified.js')&&!copy($public.'/assets/index-unified.js',$backup.'/index-unified.js')){echo 'JS_BACKUP_FAILED';exit(10);}
foreach(['assets/live-preview.css','assets/preview-frame.js','preview-frame.html'] as $f){
 $tmp=$public.'/'.$f.'.new';
 if(!copy($stage.'/'.$f,$tmp)||!rename($tmp,$public.'/'.$f)){echo 'ASSET_FAILED '.$f;exit(11);}
 @chmod($public.'/'.$f,0644);
}
file_put_contents($public.'/assets/index-preview-fix.js',$jsx);
@chmod($public.'/assets/index-preview-fix.js',0644);
$tmp=$public.'/index.html.new';
if(file_put_contents($tmp,$html)===false||!rename($tmp,$public.'/index.html')){echo 'INDEX_REPLACE_FAILED';exit(12);}
file_put_contents($manifest,gmdate('c'));
echo 'ATLAS_PRODUCTION_REAL_PREVIEWS_OK;ORIGINAL_BACKED_UP;40_LAZY_AT_A_TIME';
