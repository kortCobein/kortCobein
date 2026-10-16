/* ioKort Media: acciones completas sin reinstalar la UI heredada */
(() => {
  'use strict';
  const gallery = document.getElementById('files');
  const dialog = document.getElementById('editDialog');
  if (!gallery || !dialog || typeof api !== 'function' || typeof state === 'undefined') return;

  const makeButton = (caption, callback, style = '') => {
    const el = document.createElement('button');
    el.type = 'button';
    el.textContent = caption;
    el.className = style;
    el.addEventListener('click', async () => {
      if (el.disabled) return;
      el.disabled = true;
      try { await callback(); }
      catch (e) { toast(e.message || 'No se pudo realizar la acción'); }
      finally { el.disabled = false; }
    });
    return el;
  };

  const change = async (name, uuid, body = {}) => {
    await api(name, { method: 'POST', body: { uuid, ...body } });
    await refresh();
  };
  const auth = () => state.mode === 'admin' && state.authenticated;
  const download = uuid => {
    if (!/^[0-9a-f-]{36}$/i.test(uuid)) return;
    window.open('api/download.php?id=' + encodeURIComponent(uuid), '_blank', 'noopener,noreferrer');
  };

  function augmentCards() {
    for (const card of gallery.querySelectorAll('.media-file-card:not([data-extended])')) {
      const id = card.dataset.uuid;
      if (!id) continue;
      card.dataset.extended = '1';
      const details = card.querySelector('.details');
      if (!details) continue;
      const extras = document.createElement('div');
      extras.className = 'media-extra-actions';
      const active = card.dataset.trashed !== 'true';
      const publicAccess = card.dataset.public === 'true';
      const listed = card.dataset.listed === 'true';
      const favorite = card.dataset.favorite === 'true';
      const available = card.dataset.available === 'true';
      if (!auth()) {
        if (active && publicAccess && available) extras.append(makeButton('Descargar', () => download(id)));
      } else {
        extras.append(makeButton('Descargar', () => download(id)));
        if (active) {
          extras.append(makeButton(favorite ? 'Quitar favorito ★' : 'Favorito ☆', () => change('toggle-favorite',id,{value:!favorite})));
          extras.append(makeButton(listed ? 'Ocultar del catálogo' : 'Publicar en catálogo', () => change('toggle-public',id,{value:!listed})));
          extras.append(makeButton(publicAccess ? 'Restringir enlace' : 'Habilitar enlace', () => change('toggle-visibility',id,{value:!publicAccess})));
          extras.append(makeButton('Variantes y detalles', async () => edit(id)));
          extras.append(makeButton('Papelera', async () => {
            if (confirm('¿Enviar este recurso a la papelera?')) await change('trash-file',id);
          }, 'danger'));
        } else {
          extras.append(makeButton('Restaurar', () => change('restore-file',id)));
          extras.append(makeButton('Eliminar definitivamente', async () => {
            if (confirm('Se eliminará definitivamente el recurso y sus archivos. ¿Continuar?')) await change('delete-file',id);
          },'danger'));
        }
      }
      details.append(extras);
    }
  }

  const watch = new MutationObserver(augmentCards);
  watch.observe(gallery,{childList:true});
  augmentCards();

  const fields = dialog.querySelector('.edit-fields');
  if (fields) {
    const section=document.createElement('section');
    section.className='media-variants';
    section.id='mediaVariants';
    section.innerHTML='<h3>Variantes y conversiones</h3><div id="mediaVariantsBody">Elige un recurso para consultar sus variantes.</div>';
    fields.append(section);
  }
  let inspected='';
  const editWatcher=new MutationObserver(() => {
    if (!dialog.open || !state.current) return;
    inspected=state.current;
    loadVariants(inspected);
  });
  editWatcher.observe(dialog,{attributes:true,attributeFilter:['open']});

  async function loadVariants(uuid) {
    const pane=document.getElementById('mediaVariantsBody');
    if (!pane || !uuid || !auth()) return;
    pane.replaceChildren(document.createTextNode('Cargando variantes…'));
    try {
      const data=await api('file-details',{query:{id:uuid}});
      if(!dialog.open || state.current!==uuid) return;
      pane.replaceChildren();
      const file=data.file || {};
      const variants=data.variants || [];
      const title=document.createElement('p');
      title.className='media-help';
      title.textContent=variants.length+' variantes vinculadas a este recurso.';
      pane.append(title);
      for(const variant of variants) {
        const row=document.createElement('div');
        row.className='media-variant-row';
        const text=document.createElement('span');
        text.textContent=(variant.display_name || 'Variante')+' · '+(variant.output_width||'')+'×'+(variant.output_height||'');
        row.append(text);
        row.append(makeButton('Abrir', () => window.open(variant.public_url,'_blank','noopener,noreferrer')));
        row.append(makeButton('Eliminar variante', async () => {
          if(!confirm('¿Eliminar esta variante?')) return;
          await api('delete-variant',{method:'POST',body:{uuid:variant.uuid}});
          toast('Variante eliminada');
          await loadVariants(uuid); await refresh();
        },'danger'));
        pane.append(row);
      }
      if (file.parent_file_id || !['image/png','image/jpeg','image/webp','image/gif'].includes(file.mime_type)) {
        const note=document.createElement('p');note.className='media-help';
        note.textContent='La creación de variantes circulares solo se permite sobre una imagen original PNG, JPG, WebP o GIF.';
        pane.append(note); return;
      }
      const form=document.createElement('form');
      form.className='media-variant-form';
      form.innerHTML='<label>Resolución<select name="size"><option value="128">128 × 128</option><option value="256" selected>256 × 256</option><option value="512">512 × 512</option><option value="1024">1024 × 1024</option></select></label><label>Zoom<input name="zoom" type="number" min="1" max="8" step=".1" value="1"></label><label>Centro horizontal<input name="crop_x" type="range" min="-1" max="1" step=".1" value="0"></label><label>Centro vertical<input name="crop_y" type="range" min="-1" max="1" step=".1" value="0"></label>';
      const create=document.createElement('button');create.type='submit';create.className='small primary';create.textContent='Crear variante circular';
      form.append(create);
      form.addEventListener('submit',async event => {
        event.preventDefault();create.disabled=true;create.textContent='Procesando…';
        try {
          await api('process-variant',{method:'POST',body:{
            parent_uuid:uuid,output_width:Number(form.elements.size.value),
            output_height:Number(form.elements.size.value),output_format:file.mime_type==='image/gif'?'gif':'png',
            zoom:Number(form.elements.zoom.value),
            crop_x:Number(form.elements.crop_x.value),crop_y:Number(form.elements.crop_y.value)
          }});
          toast('Variante creada');
          await loadVariants(uuid);await refresh();
        } catch(err){toast(err.message);}
        finally{create.disabled=false;create.textContent='Crear variante circular';}
      });
      pane.append(form);
    } catch(error){pane.textContent='No se pudieron cargar las variantes: '+error.message;}
  }

  const toolbar=document.querySelector('.intro-actions');
  const albumsButton=document.getElementById('manageAlbums');
  if(toolbar && albumsButton) {
    const settingsButton=makeButton('Configuración', async () => openSettings(),'small');
    settingsButton.id='mediaSettingsButton';
    settingsButton.classList.add('hidden');
    toolbar.insertBefore(settingsButton,albumsButton);
    const panel=document.createElement('dialog');
    panel.className='media-dialog';
    panel.id='mediaSettingsDialog';
    panel.innerHTML='<div class="dialog-shell"><header class="dialog-header"><h2>Configuración del gestor</h2><button type="button" class="small" id="closeMediaSettings">✕</button></header><div class="dialog-scroll"><form id="mediaSettingsForm" class="media-settings-form"><label>Nombre del gestor<input name="app_name" maxlength="80"></label><label>Retención de papelera (días)<input name="trash_retention_days" type="number" min="1" max="3650"></label><label>Duración de sesión (minutos)<input name="session_timeout_minutes" type="number" min="5" max="1440"></label><label><input name="count_accesses" type="checkbox"> Registrar accesos</label><label><input name="auto_delete_trash" type="checkbox"> Vaciar la papelera automáticamente al vencer el plazo</label><button type="submit" class="small primary">Guardar configuración</button></form></div></div>';
    document.body.append(panel);
    panel.querySelector('#closeMediaSettings').addEventListener('click',()=>panel.close());
    panel.querySelector('#mediaSettingsForm').addEventListener('submit',async event=>{
      event.preventDefault();const form=event.currentTarget;const button=form.querySelector('[type=submit]');
      button.disabled=true;
      try{
        await api('settings',{method:'POST',body:{
          app_name:form.elements.app_name.value,
          trash_retention_days:Number(form.elements.trash_retention_days.value),
          session_timeout_minutes:Number(form.elements.session_timeout_minutes.value),
          count_accesses:form.elements.count_accesses.checked,
          auto_delete_trash:form.elements.auto_delete_trash.checked
        }});
        toast('Configuración guardada');panel.close();
      }catch(e){toast(e.message)}finally{button.disabled=false}
    });
    async function openSettings(){
      if(!auth())return;
      const config=await api('settings');const form=panel.querySelector('form');
      for(const name of ['app_name','trash_retention_days','session_timeout_minutes'])form.elements[name].value=config[name]??'';
      for(const name of ['count_accesses','auto_delete_trash'])form.elements[name].checked=!!config[name];
      panel.showModal();
    }
    const modeCheck=new MutationObserver(()=>{
      settingsButton.classList.toggle('hidden',!auth()||document.getElementById('library').classList.contains('hidden'));
    });
    modeCheck.observe(document.getElementById('library'),{attributes:true,attributeFilter:['class']});
    modeCheck.observe(document.getElementById('profileLabel'),{childList:true});
  }
})();
