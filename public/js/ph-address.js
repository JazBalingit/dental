// Province → City/Municipality → Barangay dropdowns for every split address
// (inputs named [prefix]addr_province / addr_city / addr_barangay), filled
// from the PSGC API (https://psgc.gitlab.io/api — the PSA's official list).
//
// The original text inputs stay in the form, hidden, and keep their name and
// value — a dropdown is shown beside each one and copies its choice into it.
// So address-sync.js, the signup wizard, booking-forms.js ("same address"),
// profile-edit-toggle.js and the walk-in checks keep working untouched, and
// the address is still saved as "Street, Barangay, City, Province".
//
// If the API can't be reached the plain text inputs are left as they are, so
// a form never becomes unusable.
(function () {
    var API = 'https://psgc.gitlab.io/api';
    var NCR = { code: 'NCR', name: 'Metro Manila' };
    // The only two cities PSGC lists under no province — shown under the
    // province they sit in.
    var EXTRA_CITIES = {
        '150700000': [{ code: '099701000', name: 'City of Isabela' }],   // Basilan
        '153800000': [{ code: '129804000', name: 'City of Cotabato' }],  // Maguindanao
    };

    var pending = {};
    function getJson(path) {
        var key = 'psgc:' + path;
        try {
            var hit = sessionStorage.getItem(key);
            if (hit) return Promise.resolve(JSON.parse(hit));
        } catch (e) { /* storage blocked — just fetch */ }
        if (!pending[path]) {
            pending[path] = fetch(API + path).then(function (r) {
                if (!r.ok) throw new Error('PSGC ' + r.status);
                return r.json();
            }).then(function (data) {
                var slim = data.map(function (d) { return { code: d.code, name: d.name }; });
                try { sessionStorage.setItem(key, JSON.stringify(slim)); } catch (e) { /* full or blocked */ }
                return slim;
            });
            pending[path].catch(function () { delete pending[path]; });
        }
        return pending[path];
    }

    function byName(a, b) { return a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' }); }

    function provinces() {
        return getJson('/provinces/').then(function (list) { return list.concat([NCR]).sort(byName); });
    }
    function cities(provinceCode) {
        var path = provinceCode === NCR.code ? '/regions/130000000/cities-municipalities/' : '/provinces/' + provinceCode + '/cities-municipalities/';
        return getJson(path).then(function (list) { return list.concat(EXTRA_CITIES[provinceCode] || []).sort(byName); });
    }
    function barangays(cityCode) {
        return getJson('/cities-municipalities/' + cityCode + '/barangays/').then(function (list) { return list.slice().sort(byName); });
    }

    // Saved addresses were typed freely ("Imus City", "Cavite", "NCR"), so
    // match loosely: case, accents, "City of"/"City", punctuation.
    function norm(s) {
        var n = String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/\b(city|municipality|province) of\b/g, '').replace(/\bcity\b/g, '').replace(/[^a-z0-9]/g, '');
        if (n === 'ncr' || n === 'metromanila' || n === 'nationalcapitalregion') return 'metromanila';
        return n;
    }
    function find(list, value) {
        var v = norm(value);
        if (!v) return null;
        for (var i = 0; i < list.length; i++) if (norm(list[i].name) === v) return list[i];
        return null;
    }

    function makeSelect(input, placeholder) {
        var sel = document.createElement('select');
        input.classList.forEach(function (c) { if (c !== 'form-control' && c !== 'addr-part') sel.classList.add(c); });
        if (input.classList.contains('form-control')) sel.classList.add('form-select');
        sel.setAttribute('aria-label', placeholder);
        sel.dataset.placeholder = placeholder;
        if (input.required) { sel.required = true; input.required = false; input.dataset.phRequired = '1'; }
        input.style.display = 'none';
        input.setAttribute('tabindex', '-1');
        input.insertAdjacentElement('afterend', sel);
        // Other scripts disable/enable or flag the original input — mirror it.
        new MutationObserver(function () { syncState(input, sel); })
            .observe(input, { attributes: true, attributeFilter: ['disabled', 'class'] });
        syncState(input, sel);
        return sel;
    }

    function syncState(input, sel) {
        sel.disabled = input.disabled || sel.dataset.loading === '1';
        sel.classList.toggle('is-invalid', input.classList.contains('is-invalid'));
    }

    function fill(sel, input, list, selectedValue) {
        var match = find(list, selectedValue);
        sel.innerHTML = '';
        var ph = new Option(sel.dataset.placeholder, '');
        sel.appendChild(ph);
        list.forEach(function (item) {
            var o = new Option(item.name, item.name);
            o.dataset.code = item.code;
            sel.appendChild(o);
        });
        // Keep a saved value the list doesn't know, rather than losing it.
        if (!match && selectedValue) {
            var keep = new Option(selectedValue, selectedValue);
            sel.appendChild(keep);
            match = { name: selectedValue };
        }
        sel.value = match ? match.name : '';
        Array.prototype.forEach.call(sel.options, function (o) { o.defaultSelected = o.selected; });
        setInput(input, sel.value);
        return sel.selectedOptions[0] ? sel.selectedOptions[0].dataset.code : null;
    }

    function loading(sel, input, on) {
        sel.dataset.loading = on ? '1' : '';
        if (on) sel.innerHTML = '<option value="">Loading…</option>';
        syncState(input, sel);
    }

    // Fire input/change so address-sync.js and friends see the new value.
    function setInput(input, value) {
        if (input.value === value) return;
        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function revert(group) {
        group.parts.forEach(function (p) {
            if (!p || !p.sel) return;
            p.sel.remove();
            p.input.style.display = '';
            p.input.removeAttribute('tabindex');
            if (p.input.dataset.phRequired) p.input.required = true;
        });
    }

    function setup(group) {
        var prov = group.parts[0], city = group.parts[1], brgy = group.parts[2];

        function loadBarangays(cityCode, value) {
            if (!brgy) return Promise.resolve();
            if (!cityCode) { fill(brgy.sel, brgy.input, [], value); return Promise.resolve(); }
            loading(brgy.sel, brgy.input, true);
            return barangays(cityCode).then(function (list) {
                loading(brgy.sel, brgy.input, false);
                fill(brgy.sel, brgy.input, list, value);
            });
        }

        function loadCities(provCode, cityValue, brgyValue) {
            if (!provCode) {
                fill(city.sel, city.input, [], cityValue);
                return loadBarangays(null, brgyValue);
            }
            loading(city.sel, city.input, true);
            return cities(provCode).then(function (list) {
                loading(city.sel, city.input, false);
                var code = fill(city.sel, city.input, list, cityValue);
                return loadBarangays(code, brgyValue);
            });
        }

        function load() {
            var values = group.parts.map(function (p) { return p ? p.input.value : ''; });
            loading(prov.sel, prov.input, true);
            return provinces().then(function (list) {
                loading(prov.sel, prov.input, false);
                var code = fill(prov.sel, prov.input, list, values[0]);
                return loadCities(code, values[1], values[2]);
            });
        }

        prov.sel.addEventListener('change', function () {
            setInput(prov.input, prov.sel.value);
            setInput(city.input, '');
            if (brgy) setInput(brgy.input, '');
            var opt = prov.sel.selectedOptions[0];
            loadCities(opt ? opt.dataset.code : null, '', '').catch(function () { revert(group); });
        });
        city.sel.addEventListener('change', function () {
            setInput(city.input, city.sel.value);
            if (brgy) setInput(brgy.input, '');
            var opt = city.sel.selectedOptions[0];
            loadBarangays(opt ? opt.dataset.code : null, '').catch(function () { revert(group); });
        });
        if (brgy) brgy.sel.addEventListener('change', function () { setInput(brgy.input, brgy.sel.value); });

        // A form reset (e.g. Settings → Cancel) puts the hidden inputs back to
        // their saved values; rebuild the lists to match.
        if (group.form) group.form.addEventListener('reset', function () { setTimeout(function () { load().catch(function () { revert(group); }); }, 0); });

        return load();
    }

    // Dropdowns are picked top-down, so show the columns Province → City →
    // Barangay → Street (forms list them Street-first). Only the page order
    // changes — address-sync.js joins the saved address by field name.
    function reorder(inputs) {
        var row = inputs[0].closest('.row');
        if (!row) return;
        var cells = inputs.filter(Boolean).map(function (el) {
            var c = el;
            while (c.parentElement && c.parentElement !== row) c = c.parentElement;
            return c;
        });
        if (cells.some(function (c) { return c.parentElement !== row; })) return;
        var first = cells.slice().sort(function (a, b) {
            return a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1;
        })[0];
        var marker = document.createComment('address');
        row.insertBefore(marker, first);
        cells.forEach(function (c) { row.insertBefore(c, marker); });
        marker.remove();
    }

    function init(root) {
        (root || document).querySelectorAll('input[name$="addr_province"]:not([data-ph-bound])').forEach(function (provInput) {
            var prefix = provInput.name.slice(0, -'addr_province'.length);
            var scope = provInput.form || document;
            var cityInput = scope.querySelector('input[name="' + prefix + 'addr_city"]');
            if (!cityInput) return;
            var brgyInput = scope.querySelector('input[name="' + prefix + 'addr_barangay"]');
            var streetInput = scope.querySelector('input[name="' + prefix + 'addr_street"]');
            provInput.dataset.phBound = '1';

            // Only swap the inputs in once the province list has loaded, so an
            // unreachable API leaves the text inputs untouched.
            provinces().then(function () {
                var group = {
                    form: provInput.form,
                    parts: [
                        { input: provInput, sel: makeSelect(provInput, 'Select province') },
                        { input: cityInput, sel: makeSelect(cityInput, 'Select city / municipality') },
                        brgyInput ? { input: brgyInput, sel: makeSelect(brgyInput, 'Select barangay') } : null,
                    ],
                };
                reorder([provInput, cityInput, brgyInput, streetInput]);
                return setup(group).catch(function () { revert(group); });
            }).catch(function () { /* API down — keep typing */ });
        });
    }

    window.PhAddress = { init: init };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { init(); });
    else init();
})();
