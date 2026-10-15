<?php
error_reporting(E_ALL);set_time_limit(0);
$base='/home/u899160275/domains/iokort.com';
$target=$base.'/public_html/error_pages/assets/videos';
$archive=$base.'/.apis/.original_errors_media_20261015.zip';
$lock=$base.'/.apis/.errors_media_install.lock';
$log=$base.'/.apis/errors_media_install.log';
$source='https://d2ol7oe51mr4n9.cloudfront.net/user_3KUpBA6Sdv2wiQ3QyedHD5iKLX0/4b53454d-f935-43c9-a7ec-6f4ece773f00.zip';
$names=json_decode('{"h-401.mp4":"192779-893446888_medium.mp4","h-403.mp4":"48420-453832153_medium.mp4","h-404.mp4":"170655-843752693_medium.mp4","h-410.mp4":"359071_medium.mp4","h-500.mp4":"317899_medium.mp4","h-503.mp4":"143859-784164176_medium.mp4","v-403.mp4":"311497_medium.mp4","v-404.mp4":"362312_medium.mp4","v-500.mp4":"377093_medium.mp4"}',true);
function logm($s){global $log;file_put_contents($log,gmdate('c')." ".$s."\n",FILE_APPEND);echo $s."\n";}
if(!is_dir(dirname($lock)))mkdir(dirname($lock),0750,true);
$fp=fopen($lock,'c');if(!$fp||!flock($fp,LOCK_EX|LOCK_NB))exit(0);
if(!is_dir($target))mkdir($target,0755,true);
if(!is_file($archive)||filesize($archive)<60000000){
 logm('Downloading original user archive');
 $curl=trim((string)shell_exec('command -v curl'));if(!$curl)$curl='/usr/bin/curl';
 $cmd=escapeshellarg($curl).' -fL --retry 2 --connect-timeout 30 --max-time 250 '.escapeshellarg($source).' -o '.escapeshellarg($archive);
 exec($cmd.' 2>&1',$out,$rc);
 if($rc!==0||!is_file($archive)||filesize($archive)<60000000){logm('DOWNLOAD_FAILED '.$rc.' '.implode(' ',array_slice($out,-3)));exit(3);}
 logm('Download complete '.filesize($archive));
}
$zipclass=class_exists('ZipArchive');$zip=null;
if($zipclass){$zip=new ZipArchive();if($zip->open($archive)!==true){logm('ZIP_BAD');exit(4);}}
foreach($names as $dest=>$entry){
 $path=$target.'/'.$dest;
 if(is_file($path)&&filesize($path)>100000){logm('KEEP '.$dest);continue;}
 $tmp=$path.'.installing';
 if($zipclass){
  $reader=$zip->getStream($entry);if(!$reader){logm('ENTRY_NOT_FOUND '.$entry);exit(5);}
  $writer=fopen($tmp,'wb');if(!$writer){logm('CANT_WRITE '.$dest);exit(6);}
  stream_copy_to_stream($reader,$writer);fclose($reader);fclose($writer);
 }else{
  $cmd='unzip -p '.escapeshellarg($archive).' '.escapeshellarg($entry).' > '.escapeshellarg($tmp);
  exec($cmd.' 2>&1',$out,$rc);if($rc!==0){logm('UNZIP_FAIL '.$entry);exit(7);}
 }
 if(filesize($tmp)<100000){logm('EMPTY '.$dest);exit(8);}
 rename($tmp,$path);chmod($path,0644);logm('INSTALLED '.$dest.' '.filesize($path));
}
if($zip)$zip->close();
$ff=trim((string)shell_exec('command -v ffmpeg'));
logm('FFMPEG_PATH '.($ff?:'UNAVAILABLE'));
$derive=['v-401.mp4'=>'h-401.mp4','v-410.mp4'=>'h-410.mp4','v-503.mp4'=>'h-503.mp4'];
if($ff&&is_executable($ff)){
 foreach($derive as $dst=>$src){
  if(is_file($target.'/'.$dst)&&filesize($target.'/'.$dst)>30000)continue;
  $tmp=$target.'/'.$dst.'.tmp.mp4';
  $cmd=escapeshellarg($ff).' -y -nostdin -hide_banner -loglevel error -i '.escapeshellarg($target.'/'.$src).
  ' -t 9 -an -vf '.escapeshellarg('scale=540:960:force_original_aspect_ratio=increase,crop=540:960').
  ' -c:v libx264 -preset ultrafast -crf 32 -pix_fmt yuv420p -movflags +faststart '.escapeshellarg($tmp).' 2>&1';
  exec($cmd,$err,$rc);
  if($rc===0&&is_file($tmp)&&filesize($tmp)>30000){rename($tmp,$target.'/'.$dst);chmod($target.'/'.$dst,0644);logm('CROPPED '.$dst);}
  else {logm('CROP_FAILED '.$dst.' '.implode(' ',array_slice($err,-2)));@unlink($tmp);}
 }
}
$js="(()=>{const v=document.querySelector('[data-error-video]');if(!v)return;const code=v.dataset.code;const mobile=matchMedia('(max-width:700px), (orientation:portrait)').matches;const base='/error_pages/assets/videos/';const preferred=base+(mobile?'v-':'h-')+code+'.mp4';const fallback=base+'h-'+code+'.mp4';let usedFallback=false;v.addEventListener('error',()=>{if(usedFallback)return;usedFallback=true;v.src=fallback;v.load();v.play().catch(()=>{});});v.src=preferred;v.load();v.play().catch(()=>{});})();";
file_put_contents($base.'/public_html/error_pages/assets/js/error.js',$js);
logm('READY');
