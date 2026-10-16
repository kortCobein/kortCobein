<?php
declare(strict_types=1);
ini_set('display_errors','1');
error_reporting(E_ALL);
set_time_limit(0);
$base='/home/u899160275/domains/iokort.com';
$public=$base.'/public_html';
$private=$base.'/.apis';
$stage=$private.'/stage_media_20261016';
$archive=$private.'/ioKort_Media_UNICO_Optimizado_full.zip';
$source='https://d2ol7oe51mr4n9.cloudfront.net/user_3KUpBA6Sdv2wiQ3QyedHD5iKLX0/7dd5494c-d092-4ed7-8b9d-a902475e8ea1.zip';
$expected='b04b70e1bd995bbb5f19489dba677027a4cf1b7defe4c7f2e7d942dfbfe56078';
function announce(string $msg):void {echo $msg."\n";flush();}
if (!is_dir($private)) mkdir($private,0750,true);
$lock=fopen($private.'/media_installer.lock','c');
if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) exit(0);
if (is_file($public.'/media/index.html') && is_file($public.'/media/api/page.php') && is_file($public.'/media/database/assets.sqlite')) {
 announce('ALREADY_INSTALLED'); exit(0);
}
if (!is_file($archive) || filesize($archive)!==89630568 || hash_file('sha256',$archive)!==$expected) {
 announce('DOWNLOADING_FULL_MEDIA');
 $out=fopen($archive.'.part','wb');
 if(!$out)exit(2);
 $ch=curl_init($source);
 curl_setopt_array($ch,[CURLOPT_FILE=>$out,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_FAILONERROR=>true,CURLOPT_CONNECTTIMEOUT=>30,CURLOPT_TIMEOUT=>480,CURLOPT_USERAGENT=>'ioKortMediaInstaller/2.0']);
 $good=curl_exec($ch); $err=curl_error($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);
 curl_close($ch);fclose($out);
 if(!$good){announce('DOWNLOAD_FAIL HTTP='.$http.' '.$err);exit(3);}
 if(filesize($archive.'.part')!==89630568 || hash_file('sha256',$archive.'.part')!==$expected){announce('BAD_CHECKSUM');exit(4);}
 rename($archive.'.part',$archive);
}
announce('ARCHIVE_SHA256_OK');
$zip=new ZipArchive();
if($zip->open($archive)!==true){announce('ZIP_OPEN_FAIL');exit(5);}
if($zip->numFiles!==344){announce('ZIP_ENTRIES_UNEXPECTED '.$zip->numFiles);exit(6);}
for($i=0;$i<$zip->numFiles;$i++){
 $name=$zip->getNameIndex($i);
 if(!is_string($name)||!str_starts_with($name,'media/')||str_contains($name,'..')||str_contains($name,"\\")||str_contains($name,"\0")){announce('INVALID_ZIP_ENTRY');exit(7);}
}
if(!is_dir($stage))mkdir($stage,0750,true);
if(!$zip->extractTo($stage)){announce('ZIP_EXTRACT_FAIL');exit(8);}
$zip->close();
if(!is_file($stage.'/media/index.html')||!is_file($stage.'/media/api/page.php')||!is_file($stage.'/media/database/assets.sqlite')){announce('MISSING_REQUIRED_COMPONENTS');exit(9);}
if(!file_exists($stage.'/media/storage/.htaccess'))file_put_contents($stage.'/media/storage/.htaccess',"Options -Indexes\nRequire all denied\n");
$iter=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage.'/media',FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach($iter as $file){@chmod($file->getPathname(),$file->isDir()?0755:0644);}
foreach(['storage','temp','thumbs','database'] as $dir){if(is_dir($stage.'/media/'.$dir))@chmod($stage.'/media/'.$dir,0755);}
if(is_dir($public.'/media')) {
 $old=$private.'/media_previous_before_20261016';
 if(file_exists($old)){announce('BACKUP_EXISTS_ABORT');exit(10);}
 $existing=array_values(array_diff(scandir($public.'/media'),['.','..']));
 if(count($existing)!==1||$existing[0]!=='ioKort_Media_UNICO_Optimizado.zip'){announce('MEDIA_DIRECTORY_CHANGED_ABORT');exit(11);}
 if(!rename($public.'/media',$old)){announce('BACKUP_RENAME_FAILED');exit(12);}
}
if(!rename($stage.'/media',$public.'/media')){announce('LIVE_RENAME_FAILED');exit(13);}
@unlink($archive);
announce('INSTALLED_344_FILES');
announce('PHP='.PHP_VERSION.' SQLITE='.(extension_loaded('pdo_sqlite')?'YES':'NO').' MBSTRING='.(extension_loaded('mbstring')?'YES':'NO').' GD='.(extension_loaded('gd')?'YES':'NO'));
