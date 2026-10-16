<?php
declare(strict_types=1);
ini_set('display_errors','1');set_time_limit(240);
$home='/home/u899160275/domains/iokort.com';
$site=$home.'/public_html/media';
$priv=$home.'/.apis';
$zip=$priv.'/media_ui_v2_20261016.zip';
$stage=$priv.'/media_ui_v2_20261016_stage';
$backup=$priv.'/media_ui_v2_20261016_backup';
$source='https://d2ol7oe51mr4n9.cloudfront.net/user_3KUpBA6Sdv2wiQ3QyedHD5iKLX0/3fe891a5-80e0-4830-a360-6bd1f71306a0.zip';
$sha='86d5e27983cddafcf87a78b95d6adb23ca7b6df1edaa746df7a65a2950b4fcb1';
$files=['index.html','assets/media-v2.css','assets/media-v2.js','api/browse.php'];
function fail(string $reason,int $n=1):never{echo 'ERROR '.$reason."\n";exit($n);}
if(is_file($backup.'/SUCCESS')){echo "ALREADY_INSTALLED\n";exit;}
if(!is_dir($site)||!is_file($site.'/database/assets.sqlite')||!is_file($site.'/assets/media.js'))fail('Media backend missing');
if(!is_dir($priv))mkdir($priv,0750,true);
$part=$zip.'.part';
$fh=fopen($part,'wb');if(!$fh)fail('Cannot create private download');
$ch=curl_init($source);
curl_setopt_array($ch,[CURLOPT_FILE=>$fh,CURLOPT_FAILONERROR=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>120]);
$result=curl_exec($ch);$err=curl_error($ch);curl_close($ch);fclose($fh);
if(!$result)fail('Download failed '.$err,2);
if(filesize($part)!==10896||hash_file('sha256',$part)!==$sha)fail('Archive size/checksum mismatch',3);
rename($part,$zip);echo "ARCHIVE_VERIFIED\n";
$archive=new ZipArchive;
if($archive->open($zip)!==true)fail('Zip invalid',4);
if($archive->numFiles!==count($files))fail('Unexpected archive file count',5);
for($i=0;$i<$archive->numFiles;$i++)if(!in_array($archive->getNameIndex($i),$files,true))fail('Unexpected path in archive',6);
if(!is_dir($stage))mkdir($stage,0750,true);
if(!$archive->extractTo($stage))fail('Extract failed',7);$archive->close();
if(!is_dir($backup))mkdir($backup,0750,true);
foreach($files as $file){
 if(!is_file($stage.'/'.$file))fail('Missing '.$file,8);
 if(is_file($site.'/'.$file)&&!is_file($backup.'/'.$file)){
  @mkdir(dirname($backup.'/'.$file),0750,true);
  if(!copy($site.'/'.$file,$backup.'/'.$file))fail('Backup failed '.$file,9);
 }
}
foreach(['api/browse.php','assets/media-v2.css','assets/media-v2.js','index.html'] as $file){
 @mkdir(dirname($site.'/'.$file),0755,true);
 $tmp=$site.'/'.$file.'.deploytmp';
 if(!copy($stage.'/'.$file,$tmp)||!rename($tmp,$site.'/'.$file))fail('Write failed '.$file,10);
 chmod($site.'/'.$file,0644);
}
file_put_contents($backup.'/SUCCESS',gmdate('c'));
echo "MEDIA_V2_LIVE PUBLIC_NO_PIN 24_PER_PAGE ADMIN_CRUD ACTIONS_VISIBLE\n";
