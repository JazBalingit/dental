/* ────────────────────────────────────────────────────────────────
   Odontogram controller — one instance per .odontogram block.

   Editable mode (staff, Patient Records):
     click a tooth → editor panel → pick condition / surfaces / notes
     → Apply writes to state → hidden teeth[<n>][...] inputs are rebuilt
     for the OdontogramController@save endpoint.

   Read-only mode (patient, User Appointments — data-readonly="1"):
     paints the saved chart and lists every charted tooth. No editing.

   Config travels in <script type="application/json"> tags inside the
   block: .odontogram-colors, .odontogram-condlabels, .odontogram-seed.
   ──────────────────────────────────────────────────────────────── */
(function () {
  var SURFACES = ['mesial', 'distal', 'occlusal', 'buccal', 'lingual', 'incisal'];

  function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }
  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }
  function readJson(root, sel, fallback) {
    try {
      var el = root.querySelector(sel);
      var v = JSON.parse((el && el.textContent) || 'null');
      return v == null ? fallback : v;
    } catch (e) { return fallback; }
  }

  function initOdontogram(root) {
    var readonly = root.dataset.readonly === '1';
    var COLORS = readJson(root, '.odontogram-colors', {});
    var LABELS = readJson(root, '.odontogram-condlabels', {});

    var state = {};
    var seed = readJson(root, '.odontogram-seed', {});
    if (seed && !Array.isArray(seed)) {
      Object.keys(seed).forEach(function (t) {
        state[t] = {
          condition: seed[t].condition,
          surfaces: Array.isArray(seed[t].surfaces) ? seed[t].surfaces.slice() : [],
          description: seed[t].description || ''
        };
      });
    }

    var teeth = Array.prototype.slice.call(root.querySelectorAll('.odontogram-tooth'));
    var summary = root.querySelector('.odontogram-summary');
    var selected = null;

    function chartedCount() {
      return Object.keys(state).filter(function (t) { return state[t] && state[t].condition; }).length;
    }

    function paint() {
      teeth.forEach(function (btn) {
        var t = btn.dataset.tooth;
        var shape = btn.querySelector('.odontogram-tooth-shape');
        var flag = btn.querySelector('.odontogram-tooth-flag');
        var entry = state[t];
        btn.classList.toggle('is-selected', !readonly && t === selected);
        if (entry && entry.condition) {
          btn.classList.add('is-charted');
          btn.classList.toggle('is-missing', entry.condition === 'missing');
          shape.style.fill = COLORS[entry.condition] || '';
          var extra = (entry.surfaces && entry.surfaces.length) || (entry.description && entry.description.trim());
          if (flag) flag.hidden = !extra;
        } else {
          btn.classList.remove('is-charted', 'is-missing');
          shape.style.fill = '';
          if (flag) flag.hidden = true;
        }
      });
      if (summary) {
        var n = chartedCount();
        summary.textContent = n
          ? (n + (n === 1 ? ' tooth charted' : ' teeth charted'))
          : (readonly ? 'No teeth were charted on this visit' : 'No teeth charted yet');
      }
    }

    /* ---------- read-only ---------- */
    if (readonly) {
      var list = root.querySelector('.odontogram-detail-list');
      var empty = root.querySelector('.odontogram-detail-empty');
      var order = teeth.map(function (b) { return b.dataset.tooth; });
      var charted = order.filter(function (t) { return state[t] && state[t].condition; });

      if (list) {
        list.innerHTML = '';
        charted.forEach(function (t) {
          var e = state[t];
          var item = document.createElement('div');
          item.className = 'odontogram-detail-item';
          item.style.borderLeftColor = COLORS[e.condition] || '#cbd5e1';
          var surf = (e.surfaces && e.surfaces.length)
            ? ' · ' + e.surfaces.map(cap).join(', ')
            : '';
          item.innerHTML =
            '<div class="odontogram-detail-tooth">Tooth ' + t + '</div>' +
            '<div class="odontogram-detail-body">' +
              '<span class="odontogram-detail-cond">' + esc(LABELS[e.condition] || e.condition) + '</span>' +
              '<span class="odontogram-detail-surfaces">' + surf + '</span>' +
              (e.description && e.description.trim() ? '<br>' + esc(e.description) : '') +
            '</div>';
          list.appendChild(item);
        });
      }
      if (empty) empty.hidden = charted.length > 0;
      paint();
      return;
    }

    /* ---------- editable ---------- */
    var editor = root.querySelector('.odontogram-editor');
    var editorTooth = editor.querySelector('.odontogram-editor-tooth');
    var conditionBtns = Array.prototype.slice.call(editor.querySelectorAll('.odontogram-condition'));
    var surfaceInputs = Array.prototype.slice.call(editor.querySelectorAll('.odontogram-surface input'));
    var descInput = editor.querySelector('.odontogram-desc');
    var inputsWrap = root.querySelector('.odontogram-inputs');
    var draftCondition = null;

    function rebuildInputs() {
      inputsWrap.innerHTML = '';
      function add(name, value) {
        var i = document.createElement('input');
        i.type = 'hidden';
        i.name = name;
        i.value = value;
        inputsWrap.appendChild(i);
      }
      Object.keys(state).forEach(function (t) {
        var entry = state[t];
        if (!entry || !entry.condition) return;
        add('teeth[' + t + '][tooth]', t);
        add('teeth[' + t + '][condition]', entry.condition);
        (entry.surfaces || []).forEach(function (s) {
          if (SURFACES.indexOf(s) !== -1) add('teeth[' + t + '][surfaces][]', s);
        });
        add('teeth[' + t + '][description]', entry.description || '');
      });
    }

    function render() {
      paint();
      rebuildInputs();
    }

    function openEditor(t) {
      selected = t;
      var entry = state[t] || { condition: null, surfaces: [], description: '' };
      draftCondition = entry.condition || null;
      editorTooth.textContent = t;
      conditionBtns.forEach(function (b) {
        b.classList.toggle('is-active', b.dataset.condition === draftCondition);
      });
      surfaceInputs.forEach(function (i) {
        i.checked = (entry.surfaces || []).indexOf(i.value) !== -1;
      });
      descInput.value = entry.description || '';
      editor.hidden = false;
      render();
      editor.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function closeEditor() {
      selected = null;
      draftCondition = null;
      editor.hidden = true;
      render();
    }

    teeth.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var t = btn.dataset.tooth;
        if (selected === t && !editor.hidden) { closeEditor(); return; }
        openEditor(t);
      });
    });

    conditionBtns.forEach(function (b) {
      b.addEventListener('click', function () {
        draftCondition = (draftCondition === b.dataset.condition) ? null : b.dataset.condition;
        conditionBtns.forEach(function (x) {
          x.classList.toggle('is-active', x.dataset.condition === draftCondition);
        });
      });
    });

    editor.querySelector('.odontogram-editor-close').addEventListener('click', closeEditor);

    editor.querySelector('.odontogram-apply').addEventListener('click', function () {
      if (!selected) return;
      if (!draftCondition) {
        delete state[selected];
      } else {
        state[selected] = {
          condition: draftCondition,
          surfaces: surfaceInputs.filter(function (i) { return i.checked; }).map(function (i) { return i.value; }),
          description: descInput.value.trim()
        };
      }
      closeEditor();
    });

    editor.querySelector('.odontogram-clear').addEventListener('click', function () {
      if (selected) delete state[selected];
      closeEditor();
    });

    render();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.odontogram').forEach(initOdontogram);
  });
})();
