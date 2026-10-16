<?php
declare(strict_types=1);
$public='/home/u899160275/domains/iokort.com/public_html/media';
$backup='/home/u899160275/domains/iokort.com/.apis/media_ui_before_20261016_full_ui_fix_01';
$marker=$backup.'/COMPLETED';
if(is_file($marker)){echo 'ALREADY_APPLIED';exit;}
foreach(['index.html','advanced.html'] as $name){
 if(!is_file($public.'/'.$name)){echo 'MISSING '.$name;exit(11);}
}
if(!is_dir($backup)&&!mkdir($backup,0750,true)){echo 'BACKUP_FAILED';exit(12);}
foreach(['index.html','advanced.html'] as $name){if(!copy($public.'/'.$name,$backup.'/'.$name)){echo 'COPY_FAILED';exit(13);}}
$html=file_get_contents($public.'/advanced.html');
$html=str_replace('ioKort Media · Administración avanzada (legado)','ioKort Media · Biblioteca y administración',$html);
$html=str_replace('</head>','<link rel="stylesheet" href="./assets/media-responsive.css?v=20261016a"></head>',$html);
$tmp=$public.'/index.html.20261016tmp';
if(file_put_contents($tmp,$html)===false||!rename($tmp,$public.'/index.html')){echo 'INSTALL_INDEX_FAILED';exit(14);}
$css=<<<'CSS'
/* ioKort Media · acciones y previsualización responsivas */
html,body,#root{max-width:100%;min-width:0;overflow-x:hidden}
.app-shell,.workspace,.library-panel,.public-library,.public-grid,.file-grid,.file-card,.public-resource{min-width:0;max-width:100%}
.file-card__preview,.public-resource__preview,.file-row__preview{min-width:0;isolation:isolate}
.file-card__preview .preview-media,.public-resource__preview .preview-media,.file-row__preview .preview-media{display:block;width:100%;height:100%;max-width:100%;max-height:100%;object-fit:contain}
.file-card{display:flex;flex-direction:column;min-height:210px}
.file-card__meta{min-width:0;flex:0 0 auto}
.file-card>.file-actions{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;margin-top:auto;min-height:44px;gap:5px;overflow:visible}
.file-card .file-actions .compact-action{display:inline-flex;align-items:center;justify-content:center;min-height:34px}
.file-card .file-actions .compact-action:not(.compact-action--primary){display:inline-flex}
.file-card:has(.action-menu){z-index:55}
.file-card .action-menu{z-index:120;max-width:min(230px,85vw)}
.public-resource--grid{min-height:198px}
.public-resource__actions{min-height:42px;flex-wrap:wrap}
.public-resource__actions .compact-action{display:inline-flex}
.modal-backdrop{overflow-y:auto;overscroll-behavior:contain;padding:clamp(8px,2vw,22px);align-items:center}
.modal{max-width:calc(100vw - 16px);max-height:calc(100dvh - 16px);display:flex;flex-direction:column;overflow:hidden}
.modal__header{flex:0 0 auto;min-height:48px}
.modal__body{flex:1 1 auto;min-height:0;max-height:none;overflow-y:auto;overscroll-behavior:contain}
.modal__actions{position:relative;flex:0 0 auto;z-index:2;flex-wrap:wrap;min-height:58px;background:#111a2d;border-top:1px solid #ffffff2a}
.modal__actions button{flex-shrink:0}
.details-modal{width:min(100%,1000px)}
.details-layout{min-width:0}
.details-preview{min-height:0;min-width:0;max-width:100%}
.details-preview__media{min-width:0;min-height:160px;max-width:100%;height:min(43dvh,420px);max-height:420px;overflow:hidden}
.details-preview__media .preview-media,.details-preview__media img,.details-preview__media video{display:block;width:100%;height:100%;max-width:100%;max-height:100%;object-fit:contain}
.details-preview__media iframe,.pdf-preview{max-width:100%;max-height:45dvh}
.details-layout>*,.details-modal .form-grid,.details-modal input,.details-modal textarea{min-width:0;max-width:100%}
@media(max-width:680px){
 .file-grid,.public-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
 .file-card{min-height:195px}
 .file-card__preview{height:105px}
 .file-card .file-actions .compact-action{display:inline-flex;min-width:32px}
 .modal-backdrop{padding:0;align-items:end}
 .modal{max-width:100%;width:100%;max-height:96dvh;border-radius:18px 18px 0 0}
 .modal__body{padding:12px;max-height:none}
 .modal__actions{padding:max(10px,env(safe-area-inset-bottom)) 12px;gap:6px;justify-content:space-around}
 .modal__actions button{flex:1 1 auto;min-width:90px}
 .details-layout{display:grid;grid-template-columns:minmax(0,1fr);gap:12px}
 .details-preview__media{height:min(30dvh,240px);min-height:115px}
 .details-preview__media .preview-media{max-height:100%}
 .file-card>.file-actions{gap:4px;min-height:42px}
 .file-card .action-menu{right:0;bottom:calc(100% + 5px)}
}
@media(max-width:365px){.file-grid,.public-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:6px}.file-card>.file-actions{padding:0 4px 8px}}

CSS;
if(file_put_contents($public.'/assets/media-responsive.css',$css)===false){echo 'WRITE_CSS_FAILED';exit(15);}
$alias='<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="0;url=/media/"><link rel="canonical" href="/media/"><title>ioKort Media</title></head><body><a href="/media/">Abrir ioKort Media</a></body></html>';
file_put_contents($public.'/advanced.html',$alias);
file_put_contents($marker,gmdate('c'));
echo 'MEDIA_COMPLETE_UI_INSTALLED;NO_DATABASE_CHANGE';
