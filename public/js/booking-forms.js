/*
 * Small helpers for the "who is this appointment for?" forms — the guest
 * booking page and the Patient Portal's "Book for Someone Else" (one form per
 * calendar day there, so everything is scoped to its own form).
 *
 *  [data-age-target="#id"]  birthdate input → fills the age box
 *  [data-digits]            keeps digits only (mobile numbers)
 *  [data-who]               a radio group; each radio has data-kind, and every
 *                           [data-show-for="kind1,kind2"] block in the same form
 *                           shows only for those kinds. Hidden blocks' fields
 *                           are disabled so they neither submit nor validate.
 *  .other-same-address      hides + disables the patient's own address fields
 *  [data-mh-step]           medical history: "still the same?" (mh_status
 *                           same|update) shows/enables [data-mh-form]; a
 *                           "Yes" answer reveals its [data-mh-followup] box.
 *                           A radio's data-kind may list several kinds
 *                           ("existing-dep p12") so a block can be per-person.
 */
(function () {
  function ageFrom(value) {
    if (!value) return '';
    var dob = new Date(value);
    if (isNaN(dob.getTime())) return '';
    var t = new Date();
    var age = t.getFullYear() - dob.getFullYear();
    var m = t.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < dob.getDate())) age--;
    return age >= 0 ? age + (age === 1 ? ' year' : ' years') : '';
  }

  function setEnabled(block, on) {
    block.hidden = !on;
    block.querySelectorAll('input, select, textarea').forEach(function (el) {
      el.disabled = !on;
    });
  }

  function syncSameAddress(form) {
    var box = form.querySelector('.other-same-address');
    var fields = form.querySelector('.other-address-fields');
    if (!box || !fields) return;
    var block = box.closest('[data-show-for]');
    var blockOn = !block || !block.hidden;
    setEnabled(fields, blockOn && !box.checked);
  }

  function syncWho(form) {
    var picked = form.querySelector('[data-who] input[type="radio"]:checked');
    var kinds = picked ? picked.dataset.kind.split(' ') : [];
    form.querySelectorAll('[data-show-for]').forEach(function (block) {
      var wanted = block.dataset.showFor.split(',');
      setEnabled(block, wanted.some(function (k) { return kinds.indexOf(k) !== -1; }));
    });
    // Prefill the booker fields from a chosen dependent's guardian on file.
    if (picked && picked.dataset.bookerName !== undefined) {
      var n = form.querySelector('[name="booker_name"]');
      var p = form.querySelector('[name="booker_phone"]');
      if (n && !n.value) n.value = picked.dataset.bookerName || '';
      if (p && !p.value) p.value = picked.dataset.bookerPhone || '';
    }
    form.querySelectorAll('[data-who] .who-option').forEach(function (opt) {
      var input = opt.querySelector('input[type="radio"]');
      opt.classList.toggle('is-picked', !!(input && input.checked));
    });
    syncSameAddress(form);
    syncMedical(form);
  }

  /* A field inside a hidden "who" block stays disabled whatever else says. */
  function inHiddenBlock(el) {
    var block = el.closest('[data-show-for]');
    return !!(block && block.hidden);
  }

  function syncMedical(form) {
    form.querySelectorAll('[data-mh-step]').forEach(function (step) {
      if (inHiddenBlock(step)) return;
      var picked = step.querySelector('[data-mh-toggle] input:checked');
      var formPart = step.querySelector('[data-mh-form]');
      if (formPart) {
        var on = !step.querySelector('[data-mh-toggle]') || (picked && picked.value === 'update');
        setEnabled(formPart, on);
      }
    });
    form.querySelectorAll('[data-mh-followup]').forEach(function (box) {
      var q = box.closest('[data-mh-q]');
      var yes = q && q.querySelector('input[value="yes"]:checked');
      var formPart = box.closest('[data-mh-form]');
      var allowed = !(formPart && formPart.hidden) && !inHiddenBlock(box);
      box.hidden = !yes;
      box.querySelectorAll('input').forEach(function (i) { i.disabled = !(yes && allowed); });
    });
  }

  document.addEventListener('input', function (e) {
    var el = e.target;
    if (el.matches && el.matches('[data-digits]')) {
      el.value = el.value.replace(/\D/g, '').slice(0, 11);
    }
    if (el.matches && el.matches('[data-age-target]')) {
      var target = document.querySelector(el.dataset.ageTarget);
      if (target) target.value = ageFrom(el.value);
    }
  });

  document.addEventListener('change', function (e) {
    var el = e.target;
    var form = el.closest && el.closest('form');
    if (!form) return;
    if (el.matches('[data-who] input[type="radio"]')) syncWho(form);
    if (el.matches('.other-same-address')) syncSameAddress(form);
    if (el.closest('[data-mh-step]')) syncMedical(form);
    if (el.matches('[data-age-target]')) {
      var target = document.querySelector(el.dataset.ageTarget);
      if (target) target.value = ageFrom(el.value);
    }
  });

  function init() {
    document.querySelectorAll('form').forEach(function (form) {
      if (form.querySelector('[data-who]')) syncWho(form);
      else { syncSameAddress(form); syncMedical(form); }
    });
    document.querySelectorAll('[data-age-target]').forEach(function (el) {
      var target = document.querySelector(el.dataset.ageTarget);
      if (target && el.value) target.value = ageFrom(el.value);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
