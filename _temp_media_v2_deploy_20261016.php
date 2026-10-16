<?php
declare(strict_types=1);
ini_set('display_errors','1');
set_time_limit(0);
$base='/home/u899160275/domains/iokort.com';
$live=$base.'/public_html/media';
$private=$base.'/.apis';
$zipFile=$private.'/media_modern_v2_patch_20261016.zip';
$stage=$private.'/media_modern_v2_stage_20261016';
$backup=$private.'/media_before_v2_20261016';
$source='https://d2ol7oe51mr4n9.cloudfront.net/user_3KUpBA6Sdv2wiQ3QyedHD5iKLX0/3fe891a5-80e0-4830-a360-6bd1f71306a0.zip';
$hash='86d5e27983cddafcf87a78b95d6adb23ca7b6df1edaa746df7a65a2950b4fcb1';
function report($s){echo $s."\n";}
if(is_file($backup.'/DEPLOYED')){report('ALREADY_DEPLOYED');exit;}
if(!is_file($live.'/index.html')||!is_file($live.'/database/assets.sqlite')||!is_file($live.'/assets/media.js')){report('MEDIA_INSTALLATION_NOT_READY');exit(1);}
if(!is_dir($private))mkdir($private,0750,true);
$fp=fopen($zipFile.'.part','wb');if(!$fp){report('PRIVATE_FILE_DENIED');exit(2);}
$ch=curl_init($source);curl_setopt_array($ch,[CURLOPT_FILE=>$fp,CURLOPT_FAILONERROR=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>25,CURLOPT_TIMEOUT=>60]);
$ok=curl_exec($ch);$err=curl_error($ch);curl_close($ch);fclose($fp);
if(!$ok || filesize($zipFile.'.part')!==10896 || hash_file('sha256',$zipFile.'.part')!==$hash){report('PATCH_DOWNLOAD_INVALID '.$err);exit(3);}
rename($zipFile.'.part',$zipFile);report('PATCH_CHECKSUM_OK');
$zip=new ZipArchive;if($zip->open($zipFile)!==true){report('PATCH_ZIP_INVALID');exit(4);}
$allowed=['index.html','api/browse.php','assets/media-v2.css','assets/media-v2.js'];
if($zip->numFiles!==count($allowed)){report('PATCH_COUNT_INVALID');exit(5);}
for($i=0;$i<$zip->numFiles;$i++)if(!in_array($zip->getNameIndex($i),$allowed,true)){report('UNEXPECTED_FILE');exit(6);}
if(!is_dir($stage))mkdir($stage,0750,true);
if(!$zip->extractTo($stage)){report('EXTRACT_FAIL');exit(7);}
$zip->close();
if(!is_dir($backup))mkdir($backup,0750,true);
foreach($allowed as $rel){
 $src=$stage.'/'.$rel;$dst=$live.'/'.$rel;
 if(!is_file($src)){report('MISSING_STAGED '.$rel);exit(8);}
 if(is_file($dst)){@mkdir(dirname($backup.'/'.$rel),0750,true);if(!is_file($backup.'/'.$rel)&&!copy($dst,$backup.'/'.$rel)){report('BACKUP_FAIL '.$rel);exit(9);}}
}
foreach(['api/browse.php','assets/media-v2.css','assets/media-v2.js','index.html'] as $rel){
 $dst=$live.'/'.$rel;$tmp=$dst.'.new_20261016';
 if(!copy($stage.'/'.$rel,$tmp)||!rename($tmp,$dst)){report('INSTALL_FAIL '.$rel);exit(10);}
 @chmod($dst,0644);
}
file_put_contents($backup.'/DEPLOYED',gmdate('c'));
report('MEDIA_MODERN_V2_DEPLOYED;24_PER_PAGE;PUBLIC_NO_PIN;ADMIN_TOOLS');
