<?php
declare(strict_types=1);
$root='/home/u899160275/domains/iokort.com/public_html/error_pages';
$save='/home/u899160275/domains/iokort.com/.apis/error_pages_previous_design_20261015';
$marker=$root.'/.minimal_installed_20261015';
if(file_exists($marker)){echo "ALREADY_MINIMAL\n";exit;}
$names=json_decode('{"401":"No autorizado","403":"Acceso denegado","404":"Página no encontrada","410":"Contenido eliminado","500":"Error del servidor","503":"Servicio no disponible"}',true);
if(!is_dir($save)&&!mkdir($save,0750,true)&&!is_dir($save))exit(10);
$style=<<<'CSS'
*{box-sizing:border-box}html,body{margin:0;min-height:100%;background:#020610;color:#fff;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}body{overflow:hidden}.error{position:relative;display:grid;place-items:center;min-height:100svh;padding:24px;text-align:center}.backdrop{position:absolute;inset:0;overflow:hidden;background:#020610}.backdrop video{width:100%;height:100%;object-fit:cover;filter:brightness(.54) saturate(.9)}.backdrop:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,4,15,.24),rgba(0,4,15,.50))}.content{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:0;width:100%;max-width:760px;text-shadow:0 4px 24px #0008}.code{font-size:clamp(112px,17vw,210px);line-height:.95;font-weight:850;letter-spacing:-.085em}.title{font-size:clamp(24px,3.5vw,42px);line-height:1.2;letter-spacing:-.035em;font-weight:650;margin:15px 0 36px}.back{display:inline-flex;align-items:center;justify-content:center;gap:9px;color:#fff;text-decoration:none;cursor:pointer;font:600 15px/1.1 inherit;border:1px solid #ffffff60;background:#080f239c;backdrop-filter:blur(16px);border-radius:999px;padding:15px 34px;min-width:152px;min-height:50px;transition:background .2s,border-color .2s}.back:hover{background:#ffffff28;border-color:#ffffffaa}.back:focus-visible{outline:2px solid #81c8ff;outline-offset:3px}@media(max-width:700px){.error{padding:18px}.code{font-size:clamp(106px,33vw,172px)}.title{font-size:clamp(24px,7vw,34px);margin:14px 0 32px}.back{min-height:52px}}@media(prefers-reduced-motion:reduce){.backdrop video{display:none}.backdrop{background:radial-gradient(ellipse at 50% 35%,#0a2552,#020610 72%)}}
CSS;
$items=['assets/css/error.css'=>$style];
foreach($names as $code=>$label){
  $c=htmlspecialchars((string)$code, ENT_QUOTES,'UTF-8');
  $t=htmlspecialchars((string)$label, ENT_QUOTES,'UTF-8');
  $items[$c.'.html']='<!doctype html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="noindex,nofollow"><title>'.$c.' — '.$t.' | ioKort</title><link rel="stylesheet" href="/error_pages/assets/css/error.css?v=2"></head><body><main class="error"><div class="backdrop" aria-hidden="true"><video data-error-video data-code="'.$c.'" autoplay muted loop playsinline preload="metadata" poster="/iokort_core/assets/fondo-poster.jpg"></video></div><div class="content"><div class="code">'.$c.'</div><h1 class="title">'.$t.'</h1><button class="back" type="button" onclick="history.length>1?history.back():location.assign(\'/\')">← Volver</button></div></main><script src="/error_pages/assets/js/error.js?v=2"></script></body></html>';
}
foreach($items as $name=>$content){
  $dest=$root.'/'.$name;
  $old=$save.'/'.$name;
  if(!is_dir(dirname($old)))mkdir(dirname($old),0750,true);
  if(file_exists($dest)&&!copy($dest,$old))exit(20);
  $tmp=$dest.'.new';
  if(file_put_contents($tmp,$content)===false||!rename($tmp,$dest))exit(21);
}
file_put_contents($marker,gmdate('c'));
echo "MINIMAL_DEPLOYED_7\n";
