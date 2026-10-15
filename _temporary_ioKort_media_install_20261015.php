<?php
error_reporting(E_ALL);ini_set('display_errors','1');set_time_limit(0);
$root='/home/u899160275/domains/iokort.com';
$vid=$root.'/public_html/error_pages/assets/videos';
$zipfile=$root.'/.apis/.original_errors_media_20261015.zip';
$log=$root.'/public_html/error_pages/assets/install_status.txt';
$names=json_decode('{"h-401.mp4":"192779-893446888_medium.mp4","h-403.mp4":"48420-453832153_medium.mp4","h-404.mp4":"170655-843752693_medium.mp4","h-410.mp4":"359071_medium.mp4","h-500.mp4":"317899_medium.mp4","h-503.mp4":"143859-784164176_medium.mp4","v-403.mp4":"311497_medium.mp4","v-404.mp4":"362312_medium.mp4","v-500.mp4":"377093_medium.mp4"}',true);
$src='https://d2ol7oe51mr4n9.cloudfront.net/user_3KUpBA6Sdv2wiQ3QyedHD5iKLX0/4b53454d-f935-43c9-a7ec-6f4ece773f00.zip';
function status($s){global $log; file_put_contents($log,date('c')." ".$s."\n",FILE_APPEND);echo $s."\n";}
if(!is_dir($vid))mkdir($vid,0755,true);
status('START PHP '.PHP_VERSION.' ZIP:'.(class_exists('ZipArchive')?'yes':'no').' CURL:'.(function_exists('curl_init')?'yes':'no').' URLFOPEN:'.ini_get('allow_url_fopen'));
if(!is_file($zipfile)||filesize($zipfile)<80000000){
 $temp=$zipfile.'.download';
 $out=fopen($temp,'wb');if(!$out){status('ERROR open temp');exit(11);}
 if(function_exists('curl_init')){
  $ch=curl_init($src);
  curl_setopt_array($ch,[CURLOPT_FILE=>$out,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>30,CURLOPT_TIMEOUT=>270,CURLOPT_USERAGENT=>'ioKortMediaInstall/1.0',CURLOPT_FAILONERROR=>true]);
  $ok=curl_exec($ch);$err=curl_error($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
  fclose($out);
  if(!$ok){status('ERROR download '.$code.' '.$err);exit(12);}
 }else{
  $in=@fopen($src,'rb');if(!$in){fclose($out);status('ERROR remote fopen');exit(13);}
  stream_copy_to_stream($in,$out);fclose($in);fclose($out);
 }
 clearstatcache(true,$temp);
 status('DOWNLOADED_BYTES '.filesize($temp));
 if(filesize($temp)<80000000){status('ERROR undersized');exit(14);}
 rename($temp,$zipfile);
}
if(!class_exists('ZipArchive')){status('ERROR no ZipArchive');exit(15);}
$za=new ZipArchive();if($za->open($zipfile)!==true){status('ERROR invalid zip');exit(16);}
foreach($names as $dest=>$origin){
 $path=$vid.'/'.$dest;
 if(is_file($path)&&filesize($path)>100000){status('KEEP '.$dest);continue;}
 $in=$za->getStream($origin);
 if(!$in){status('ERROR missing entry '.$origin);exit(17);}
 $temp=$path.'.temp';$out=fopen($temp,'wb');
 if(!$out){fclose($in);status('ERROR cannot create '.$temp);exit(18);}
 $copied=stream_copy_to_stream($in,$out);fclose($in);fclose($out);
 if($copied<100000){status('ERROR empty '.$dest);exit(19);}
 rename($temp,$path);chmod($path,0644);status('INSTALLED '.$dest.' '.$copied);
}
$za->close();
$ff=trim((string)@shell_exec('command -v ffmpeg 2>/dev/null'));
status('FFMPEG '.($ff?:'NOT_AVAILABLE'));
if($ff){
 $crops=['v-401.mp4'=>'h-401.mp4','v-410.mp4'=>'h-410.mp4','v-503.mp4'=>'h-503.mp4'];
 foreach($crops as $dst=>$src){
  $path=$vid.'/'.$dst;$tmp=$path.'.tmp.mp4';
  if(is_file($path)&&filesize($path)>30000){status('CROP_KEEP '.$dst);continue;}
  $filter='scale=540:960:force_original_aspect_ratio=increase,crop=540:960';
  $cmd=escapeshellarg($ff).' -y -nostdin -hide_banner -loglevel error -i '.escapeshellarg($vid.'/'.$src).' -t 9 -an -vf '.escapeshellarg($filter).' -c:v libx264 -preset ultrafast -crf 33 -pix_fmt yuv420p -movflags +faststart '.escapeshellarg($tmp).' 2>&1';
  $lines=[];$exit=1;@exec($cmd,$lines,$exit);
  if($exit===0&&is_file($tmp)&&filesize($tmp)>30000){rename($tmp,$path);chmod($path,0644);status('CROP_INSTALLED '.$dst.' '.filesize($path));}
  else{status('CROP_UNAVAILABLE '.$dst.' '.implode(' ',array_slice($lines,-1)));@unlink($tmp);}
 }
}

$js="(()=>{const v=document.querySelector('[data-error-video]');if(!v)return;const code=v.dataset.code;const mobile=matchMedia('(max-width:700px), (orientation:portrait)').matches;const b='/error_pages/assets/videos/';const first=b+(mobile?'v-':'h-')+code+'.mp4';const fallback=b+'h-'+code+'.mp4';let swapped=false;v.addEventListener('error',()=>{if(swapped)return;swapped=true;v.src=fallback;v.load();v.play().catch(()=>{});});v.src=first;v.load();v.play().catch(()=>{});})();";
file_put_contents($root.'/public_html/error_pages/assets/js/error.js',$js);
status('COMPLETE all nine originals installed');
