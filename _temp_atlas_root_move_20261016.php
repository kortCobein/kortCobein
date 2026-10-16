<?php
declare(strict_types=1);
$root='/home/u899160275/domains/iokort.com/public_html';
$from=$root.'/others/atlas';
$to=$root.'/atlas';
$backup='/home/u899160275/domains/iokort.com/.apis/atlas_move_20261016';
if(is_dir($to)){echo 'TARGET_ALREADY_EXISTS;ABORT';exit(10);}
if(!is_dir($from)||!is_file($from.'/index.html')||!is_file($from.'/assets/index-unified.js')){echo 'ATLAS_SOURCE_MISSING;ABORT';exit(11);}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo 'BACKUP_FAILED';exit(12);}
if(!copy($root.'/index.html',$backup.'/root_index.html')){echo 'BACKUP_ROOT_FAILED';exit(13);}
if(!rename($from,$to)){echo 'MOVE_FAILED';exit(14);}
if(!mkdir($from,0755,true)){echo 'REDIRECT_FOLDER_FAILED';exit(15);}
$rules="RewriteEngine On\nRewriteRule ^legacy/atlas\\.html$ /atlas/ [R=301,L,NE]\nRewriteRule ^(.*)$ /atlas/\$1 [R=301,L,NE]\n";
file_put_contents($from.'/.htaccess',$rules);
$legacy=$to.'/legacy/atlas.html';
if(is_file($legacy)){ $html=file_get_contents($legacy);$html=str_replace('/others/atlas/','/atlas/',$html);file_put_contents($legacy,$html); }
$rootFile=$root.'/index.html';
$html=file_get_contents($rootFile);
$new=str_replace(['/others/atlas/','others/atlas/'],['/atlas/','atlas/'],$html);
if($new!==$html)file_put_contents($rootFile,$new);
echo 'ATLAS_MOVED:/atlas/;ONE_PUBLIC_APPLICATION;OLD_ROUTE_REDIRECTED';
