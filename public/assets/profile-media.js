// Image preview only; validation, decoding and authorization remain on the server.
(() => {
  document.querySelectorAll('[data-image-editor]').forEach(editor => {
    const input=editor.querySelector('input[type=file]'),remove=editor.querySelector('[name=remove_image]');
    const preview=editor.querySelector('[data-image-preview]'),empty=editor.querySelector('[data-image-placeholder]');
    const status=editor.querySelector('[data-image-status]');
    const original=preview.getAttribute('src') || '';
    let objectUrl;
    const show=src => {
      if(objectUrl) {URL.revokeObjectURL(objectUrl);objectUrl=null;}
      if(src) preview.src=src; else preview.removeAttribute('src');
      preview.hidden=!src;empty.hidden=Boolean(src);
    };
    input.addEventListener('change',()=>{
      const file=input.files[0];input.setCustomValidity('');status.textContent='';
      if(!file) {show(remove?.checked?editor.dataset.fallback:original);return;}
      if(file.size>2*1024*1024 || !/\.(jpe?g|png)$/i.test(file.name) || !['image/jpeg','image/png'].includes(file.type)) {
        input.setCustomValidity(editor.dataset.invalidMessage);status.textContent=editor.dataset.invalidMessage;show(original);return;
      }
      if(remove) remove.checked=false;
      show('');objectUrl=URL.createObjectURL(file);preview.src=objectUrl;preview.hidden=false;empty.hidden=true;
    });
    remove?.addEventListener('change',()=>{
      input.value='';input.setCustomValidity('');status.textContent='';
      show(remove.checked?editor.dataset.fallback:original);
    });
  });
})();
