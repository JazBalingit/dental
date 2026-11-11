/*
 * Profile photo inputs (<input type="file" name="photo">) on every profile
 * form: patient Settings, staff My Profile, and the admin / super-admin
 * account modals.
 *
 *  - The picked photo is shrunk in the browser (max 640px, JPEG) before it
 *    is uploaded, so it's small enough to store in the database and loads
 *    fast everywhere it appears.
 *  - The nearest avatar <img> previews it right away.
 *  - An input marked data-photo-autosubmit sends its form as soon as the
 *    photo is ready (staff My Profile has no separate Save button for it).
 */
(function () {
  if (window.__profilePhotoInputs) return;
  window.__profilePhotoInputs = true;

  var MAX_SIDE = 640;
  var MAX_BYTES = 5 * 1024 * 1024;

  function shrink(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var scale = Math.min(1, MAX_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
        var canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        URL.revokeObjectURL(url);
        canvas.toBlob(function (blob) {
          if (!blob) return reject(new Error('compress'));
          var name = (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
          resolve(new File([blob], name, { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.85);
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('unreadable')); };
      img.src = url;
    });
  }

  // The avatar shown next to this input (searching a few levels up).
  function previewFor(input) {
    var el = input.parentElement;
    for (var i = 0; el && i < 5; i++, el = el.parentElement) {
      var img = el.querySelector('img');
      if (img) return img;
    }
    return null;
  }

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.name !== 'photo') return;
    var file = input.files && input.files[0];
    if (!file) return;

    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
      alert('Please choose a JPG, PNG or WebP photo.');
      input.value = '';
      return;
    }

    shrink(file).then(function (small) {
      var dt = new DataTransfer();
      dt.items.add(small);
      input.files = dt.files;
      return small;
    }, function () {
      // Couldn't shrink it in this browser — send the original if it's within the limit.
      if (file.size > MAX_BYTES) {
        alert('The photo is too large. Please choose an image up to 5MB only.');
        input.value = '';
        return null;
      }
      return file;
    }).then(function (ready) {
      if (!ready) return;
      var img = previewFor(input);
      if (img) img.src = URL.createObjectURL(ready);
      if (input.hasAttribute('data-photo-autosubmit') && input.form) {
        input.form.requestSubmit ? input.form.requestSubmit() : input.form.submit();
      }
    });
  });
})();
