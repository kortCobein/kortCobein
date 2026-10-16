<?php
declare(strict_types=1);
ini_set('display_errors','1');error_reporting(E_ALL);set_time_limit(180);
$root='/home/u899160275/domains/iokort.com/public_html';
$live=$root.'/atlas';$stage=$root.'/atlas-preview';
if(!is_file($live.'/assets/index-unified.js')||!is_file($live.'/assets/legacy-data.js')){echo "ATLAS_MISSING";exit(1);}
if(!is_dir($stage))mkdir($stage,0755,true);
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($live,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
foreach($it as $f){
  $dest=$stage.substr($f->getPathname(),strlen($live));
  if($f->isDir()){if(!is_dir($dest))mkdir($dest,0755,true);}
  elseif(!is_file($dest)){if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);copy($f->getPathname(),$dest);}
}
$js=file_get_contents($live.'/assets/index-unified.js');
$needle="s.jsx('button',{className:'atlas-legacy-component-sample',onClick:()=>popup(t.entry),title:'Personalizar componente',children:s.jsxs('span',{children:[s.jsx('b',{children:t.name}),s.jsx('small',{children:t.kind})]})})";
$replace="s.jsxs('div',{className:'atlas-legacy-component-sample atlas-live-preview',children:[s.jsx('iframe',{loading:'lazy',src:'./preview-frame.html?id='+encodeURIComponent(t.entry.id),sandbox:'allow-scripts',title:'Vista previa de '+t.name,tabIndex:-1,scrolling:'no'}),s.jsx('button',{type:'button',className:'atlas-live-preview-open',onClick:()=>popup(t.entry),title:'Personalizar '+t.name,'aria-label':'Personalizar '+t.name,children:'Personalizar'})]})";
if(substr_count($js,$needle)!==1){echo 'JS_NEEDLE_COUNT='.substr_count($js,$needle);exit(2);}
$js=str_replace($needle,$replace,$js);
$js=str_replace('const[C,st]=Xt.useState("home")','const[C,st]=Xt.useState("components")',$js,$found);
if(!$found){echo "NO_STAGE_ROUTE";exit(3);}
file_put_contents($stage.'/assets/index-preview-fix.js',$js);
$style=<<<'CSS'
/* Vista previa real · Atlas catalog */
.atlas-component-card .atlas-live-preview{position:relative;display:block;min-height:155px;height:172px;padding:0!important;background:#f6f8fd!important;color:#102033;overflow:hidden;cursor:default;border:0;width:100%}
.atlas-live-preview iframe{display:block;width:100%;height:100%;border:0;background:#f6f8fd;pointer-events:none}
.atlas-live-preview-open{position:absolute;inset:0;z-index:1;border:0;outline:0;background:transparent;cursor:pointer;color:transparent;font-size:0}
.atlas-live-preview-open:hover{background:rgba(0,157,129,.08);outline:2px solid #009d81;outline-offset:-2px}
.atlas-live-preview-open:focus-visible{outline:3px solid #00b897;outline-offset:-3px}
@media(max-width:600px){.atlas-component-card .atlas-live-preview{height:160px}}
CSS;
file_put_contents($stage.'/assets/live-preview.css',$style);
$frame=<<<'HTML'
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>html,body{width:100%;height:100%;margin:0;overflow:hidden;background:#f6f8fd}#sample{width:100%;height:100%;display:flex;align-items:center;justify-content:center;box-sizing:border-box;padding:12px}#sample>.bsx-item{max-height:100%;min-width:0;max-width:100%}.preview-unavailable{color:#59677c;font:12px system-ui;text-align:center}</style><script defer src="./assets/legacy-data.js"></script><script defer src="./assets/preview-frame.js"></script></head><body><div id="sample" aria-label="Ejemplo visual"></div></body></html>
HTML;
$framejs=<<<'JAVASCRIPT'
(() => {
  'use strict';
  const mount=document.getElementById('sample');
  const library=window.BSX_LIBRARY, vendor=window.BSX_VENDOR;
  const id=new URL(location.href).searchParams.get('id')||'';
  const item=library?.items?.find(entry=>entry.id===id);
  if(!item||!mount){ if(mount)mount.textContent='Vista previa no disponible';return;}
  try{
    const settings=library.defaults(item),instance='preview_'+id.replace(/[^a-zA-Z0-9_-]/g,'-');
    const css=[vendor?.css||'',library.cssBase||'',library.cssFor(item,settings,instance),'body{font-size:13px} #sample .bsx-item{width:100%} #sample img,#sample video{max-width:100%} #sample .btn{font-size:12px} #sample .navbar{padding:.4rem!important} #sample .bsx-sample-image{height:96px}'];
    const style=document.createElement('style');style.textContent=css.join('\n');document.head.appendChild(style);
    mount.innerHTML=library.htmlFor(item,settings,instance);
  }catch(e){mount.textContent='Vista previa no disponible';console.error('Atlas preview',id,e);}
})();
JAVASCRIPT;
file_put_contents($stage.'/preview-frame.html',$frame);
file_put_contents($stage.'/assets/preview-frame.js',$framejs);
$html=file_get_contents($live.'/index.html');
if(!str_contains($html,'index-unified.js')){echo 'INDEX_SCRIPT_NOT_FOUND';exit(4);}
$html=preg_replace('~index-unified\\.js(?:\\?[^"\']*)?~','index-preview-fix.js?v=preview_20261016b',$html,1);
$html=str_replace('</head>','<link rel="stylesheet" href="./assets/live-preview.css?v=1"></head>',$html);
file_put_contents($stage.'/index.html',$html);
echo 'STAGE_READY;REACT_CARD_PATCHED;263_PREVIEWS_LAZY';
