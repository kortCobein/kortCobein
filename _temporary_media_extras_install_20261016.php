<?php
declare(strict_types=1);
ini_set('display_errors','1');set_time_limit(180);
$base='/home/u899160275/domains/iokort.com';
$site=$base.'/public_html/media';
$priv=$base.'/.apis/media_extras_backup_20261016';
$src='https://raw.githubusercontent.com/kortCobein/kortCobein/8369f44fc3b1be1c88e4b6061c80788c4cbf4d38/_temporary_media_extras_20261016.js';
if(is_file($priv.'/INSTALLED')){echo "EXTRAS_ALREADY_INSTALLED\n";exit;}
function stop(string $reason):never{echo "ERROR ".$reason."\n";exit(1);}
if(!is_file($site.'/index.html')||!is_file($site.'/assets/media-v2.js')||!is_file($site.'/api/browse.php'))stop('Modern Media v2 not installed');
$fh=fopen('/tmp/iokort_media_extras_16.js','wb');if(!$fh)stop('Cannot stage JS');
$ch=curl_init($src);curl_setopt_array($ch,[CURLOPT_FILE=>$fh,CURLOPT_FAILONERROR=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>60]);
$ok=curl_exec($ch);$err=curl_error($ch);curl_close($ch);fclose($fh);
if(!$ok)stop('Download failed '.$err);
$extra=file_get_contents('/tmp/iokort_media_extras_16.js');
if(strlen($extra)<8000||!str_contains($extra,'media-extra-actions')||!str_contains($extra,'toggle-visibility'))stop('Bad JS stage');
$original=file_get_contents($site.'/assets/media-v2.js');
$needle="const a=element('article','file media-file-card');";
$replace="const a=element('article','file media-file-card');a.dataset.uuid=f.uuid;a.dataset.public=String(f.public);a.dataset.listed=String(f.listed);a.dataset.favorite=String(f.favorite);a.dataset.trashed=String(f.trashed);a.dataset.available=String(f.available);";
if(substr_count($original,$needle)!==1)stop('Card marker missing or duplicate');
$patched=str_replace($needle,$replace,$original);
$html=file_get_contents($site.'/index.html');
if(!str_contains($html,'assets/media-v2.js?v=2'))stop('Modern HTML version mismatch');
$html=str_replace('</head>','<link rel="stylesheet" href="assets/media-extras.css?v=20261016e"></head>',$html);
$html=str_replace('assets/media-v2.js?v=2','assets/media-v2.js?v=20261016e',$html);
$html=str_replace('</body>','<script defer src="assets/media-extras.js?v=20261016e"></script></body>',$html);
$css=<<<'CSS'
/* Fixed preview region, permanently accessible actions, adaptable card layout */
.media-file-card{display:flex!important;flex-direction:column;min-height:295px;overflow:hidden}
.media-file-card>.preview{height:145px!important;min-height:145px;max-height:145px;flex:0 0 145px!important;overflow:hidden;isolation:isolate}
.media-file-card>.preview img{display:block!important;width:100%!important;height:100%!important;max-height:100%!important;max-width:100%!important;object-fit:contain!important;position:static}
.media-file-card>.details{flex:1 1 auto;display:flex;flex-direction:column;min-width:0;overflow:visible}
.media-card-actions{margin-top:auto!important;display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:5px;width:100%;flex:0 0 auto}
.media-card-actions button{width:100%;flex:none;min-width:0;min-height:36px;white-space:normal;overflow-wrap:break-word;padding:7px 3px;line-height:1.15}
.media-extra-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));width:100%;margin-top:7px;gap:5px;flex-shrink:0}
.media-extra-actions button{border:1px solid #36516a;border-radius:8px;color:#d9ebf5;background:#203146;padding:8px 4px;min-width:0;min-height:36px;cursor:pointer;font-size:10.5px;line-height:1.25;text-align:center;white-space:normal;overflow-wrap:anywhere}
.media-extra-actions button:disabled{opacity:.45;cursor:wait}
.media-extra-actions button:hover{border-color:#43c2ad;background:#24465a}
.media-extra-actions button.danger{border-color:#864255}
.media-variants{border-top:1px solid #3c516d;margin-top:12px;padding-top:12px;min-width:0}
.media-variants h3{font-size:15px;margin:2px 0 10px}
.media-variants .media-help{font-size:12px;line-height:1.45}
.media-variant-row{display:flex;align-items:center;flex-wrap:wrap;gap:6px;padding:7px 0;border-bottom:1px solid #34435a}
.media-variant-row span{flex:1 1 170px;min-width:0;font-size:12px;overflow-wrap:anywhere}
.media-variant-row button{font-size:11px;padding:7px}
.media-variant-form,.media-settings-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px}
.media-variant-form label,.media-settings-form label{display:grid;gap:4px;min-width:0;font-size:12px;color:#b4c8dd}
.media-variant-form input,.media-variant-form select,.media-settings-form input{min-width:0;width:100%;background:#142138;border:1px solid #38516e;color:#e5f2ff;border-radius:8px;padding:8px}
.media-settings-form label:has([type=checkbox]){display:flex;align-items:center}
.media-settings-form input[type=checkbox]{width:auto}
.media-variant-form>button,.media-settings-form>button{grid-column:1/-1}
.media-dialog .dialog-shell{display:flex;flex-direction:column;max-height:min(94dvh,900px);min-height:0}
.media-dialog .dialog-scroll{min-height:0;overflow-y:auto;overscroll-behavior:contain}
.media-dialog .dialog-footer{flex:0 0 auto;position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:6px}
.media-dialog .dialog-footer button{flex:0 1 auto}
.media-dialog .editor-preview{min-width:0;min-height:0;overflow:hidden}
.media-dialog .editor-preview img{width:100%;height:min(32dvh,290px);max-width:100%;max-height:290px;object-fit:contain}
@media(max-width:700px){
 .media-file-card>.preview{height:108px!important;min-height:108px;max-height:108px;flex-basis:108px!important}
 .media-file-card>.details{padding:9px}
 .media-card-actions{grid-template-columns:repeat(2,minmax(0,1fr))}
 .media-card-actions button{font-size:10px}
 .media-extra-actions{grid-template-columns:repeat(2,minmax(0,1fr))}
 .media-extra-actions button{font-size:10px;padding:7px 3px}
 .media-dialog{width:100vw;max-width:100vw;max-height:96dvh}
 .media-dialog .dialog-scroll{max-height:65dvh;overflow-y:auto}
 .media-dialog .dialog-footer{padding:8px;gap:5px;justify-content:space-around}
 .media-dialog .dialog-footer button{flex:1 1 auto;min-height:38px}
 .media-settings-form,.media-variant-form{grid-template-columns:minmax(0,1fr)}
}
@media(max-width:360px){.media-file-card>.details{padding:6px}.media-extra-actions button{font-size:9px}.media-card-actions button{font-size:9px}}
CSS;
$secure=<<<'PHP'
<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/bootstrap/app.php';
pah_bootstrap_request(true);
pah_require_method('POST');
pah_require_auth();
pah_require_csrf();
$data=pah_read_json();
$uuid=(string)($data['uuid']??'');
$value=array_key_exists('value',$data)?(bool)$data['value']:null;
pah_ok(pah_toggle_file($uuid,'is_public',$value),'Visibilidad del enlace actualizada.');
PHP;
if(!is_dir($priv)&&!mkdir($priv,0750,true))stop('Cannot create backup directory');
foreach(['index.html','assets/media-v2.js'] as $file)if(!copy($site.'/'.$file,$priv.'/'.str_replace('/','_',$file)))stop('Backup failed '.$file);
foreach([
 'assets/media-extras.js'=>$extra,
 'assets/media-extras.css'=>$css,
 'api/toggle-visibility.php'=>$secure,
 'assets/media-v2.js'=>$patched,
 'index.html'=>$html
] as $file=>$data){
 $path=$site.'/'.$file;$temp=$path.'.tmp_20261016e';
 if(file_put_contents($temp,$data)===false||!rename($temp,$path))stop('Install failed '.$file);
 @chmod($path,0644);
}
file_put_contents($priv.'/INSTALLED',gmdate('c'));
echo "MEDIA_EXTRAS_INSTALLED;PUBLIC_PROFILE_RETAINED;FULL_ACTIONS_VISIBLE;BACKUP_OK\n";
