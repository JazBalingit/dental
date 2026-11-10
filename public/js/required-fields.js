/*
 * Required-field markers — every form in the system, automatically.
 *
 * Loaded on every page from partials/flash-toasts (same as
 * responsive-tables.js). For each visible-label field that has the HTML
 * `required` attribute it:
 *
 *   1. adds a red asterisk to the field's label:
 *        <label>Last name <span class="req-star">*</span></label>
 *   2. adds ONE legend per form / modal body, just above the first
 *      required field:
 *        "Fields marked with * are required."
 *
 * So the rule for new forms is simply: put `required` on the input (which
 * also gets you the browser's own "please fill out this field" check).
 * Fields that are required only conditionally (a script toggles
 * `required`) gain/lose their asterisk as it changes — a MutationObserver
 * watches the attribute and newly added markup.
 *
 * Label lookup, in order: the field's own <label for> / wrapping label;
 * for a radio group, the label above the whole group; otherwise the
 * nearest label/.form-label/.section-label just before the field (or
 * before its wrapper, e.g. .input-icon / .input-group) inside the same
 * column. A field with no label it can find gets no asterisk.
 *
 * Opt-outs: data-no-required-star on a field (or any ancestor) skips it;
 * data-no-required-legend on a form / modal skips just the legend.
 * Opt-in without browser validation: data-required="1" marks a field as
 * required for display only. A label that already has a hand-written red
 * "*" (e.g. <span class="text-danger">*</span>) is reused, not doubled.
 */
(function () {
    'use strict';

    if (window.__requiredFields) return;
    window.__requiredFields = true;

    var FIELD_SEL = 'input[required]:not([type="hidden"]), select[required], textarea[required], [data-required="1"]';
    var LABEL_SEL = 'label, .form-label, .section-label';
    // Climbing out of the field stops at these — anything beyond belongs to
    // a different field.
    var STOP_SEL = 'form, .modal-body, .modal-content, .row, fieldset, body, [class^="col"], [class*=" col"]';

    var style = document.createElement('style');
    style.textContent =
        '.req-star{color:#dc2626 !important;font-weight:700;margin-left:.2em;}' +
        '.required-legend{display:block;font-size:.8rem;font-weight:500;color:#64748b;margin:0 0 .85rem;line-height:1.4;text-align:left;}' +
        '.required-legend .req-star{margin:0 .1em;font-size:.95rem;}' +
        '.modal .modal-body .required-legend{margin-top:.85rem;}' +
        '.modal .modal-body > .required-legend:first-child{margin-top:0;}';
    document.head.appendChild(style);

    // Hidden companions don't count as "another field": type=hidden, the
    // input ph-address.js hid, or an invisible helper like the password
    // eye's toggle checkbox (opacity 0, absolutely positioned).
    function isHidden(el) {
        if (el.type === 'hidden' || el.style.display === 'none') return true;
        var cs = getComputedStyle(el);
        return cs.display === 'none' || (cs.opacity === '0' && (cs.position === 'absolute' || parseFloat(cs.width) === 0));
    }

    function isControl(el) {
        return el.matches('input, select, textarea') && !isHidden(el);
    }

    // A sibling that is (or holds) another field → we've left our field.
    function isFieldish(el) {
        if (isControl(el)) return true;
        var inner = el.querySelectorAll('input, select, textarea');
        for (var i = 0; i < inner.length; i++) if (!isHidden(inner[i])) return true;
        return false;
    }

    // A label we may star for `target` (a field, or a radio group's box).
    function labelOk(label, target) {
        if (label.classList.contains('visually-hidden') || label.classList.contains('btn')) return false;
        var ctrl = label.control;
        if (ctrl && ctrl !== target && !isHidden(ctrl)) return false;
        // A label wrapping some other control (a radio "card") isn't a heading.
        var inner = label.querySelector('input:not([type="hidden"]), select, textarea');
        return !inner || inner === target;
    }

    function nearbyLabel(start) {
        var node = start;
        for (var depth = 0; depth < 4 && node; depth++) {
            var sib = node.previousElementSibling;
            while (sib) {
                if (sib.matches(LABEL_SEL)) return labelOk(sib, start) ? sib : null;
                if (isFieldish(sib)) return null;
                sib = sib.previousElementSibling;
            }
            node = node.parentElement;
            if (!node || node.matches(STOP_SEL)) break;
        }
        return null;
    }

    function groupLabel(el) {
        var scope = el.form || document;
        var name = window.CSS && CSS.escape ? CSS.escape(el.name) : el.name;
        var group = Array.prototype.slice.call(scope.querySelectorAll('input[type="radio"][name="' + name + '"]'));
        var box = el.parentElement;
        while (box && !group.every(function (r) { return box.contains(r); })) box = box.parentElement;
        if (!box) return null;
        if (box.matches('fieldset')) {
            var legend = box.querySelector(':scope > legend');
            if (legend) return legend;
        }
        // Heading label inside the same box, before the options.
        var kids = box.children;
        for (var i = 0; i < kids.length; i++) {
            if (kids[i].matches(LABEL_SEL) && labelOk(kids[i], box)) return kids[i];
            if (isFieldish(kids[i])) break;
        }
        return nearbyLabel(box);
    }

    function labelFor(el) {
        if (el.type === 'radio') return groupLabel(el);
        var own = el.labels ? Array.prototype.filter.call(el.labels, function (l) { return labelOk(l, el); }) : [];
        if (own.length) return own[0];
        // ph-address.js swaps a text input for a <select> placed right after
        // it — the label still points at the (now hidden) original.
        var prev = el.previousElementSibling;
        if (prev && prev.matches('input') && isHidden(prev) && prev.labels && prev.labels.length) return prev.labels[0];
        return nearbyLabel(el);
    }

    function legendHost(el) {
        return el.closest('.modal-body') || el.closest('form') || el.form ||
            el.closest('.card-soft, .section-card, .card, section');
    }

    function addStar(label) {
        if (label.querySelector('.req-star')) return;
        // Reuse a hand-written "*" (e.g. <span class="text-danger">*</span>).
        var kids = label.children;
        for (var i = 0; i < kids.length; i++) {
            if (kids[i].textContent.trim() === '*') { kids[i].classList.add('req-star'); return; }
        }
        // Text the user actually sees (a hidden "(optional)" hint doesn't count).
        var visible = label.cloneNode(true);
        visible.querySelectorAll('[hidden], .d-none').forEach(function (n) { n.remove(); });
        var text = visible.textContent;
        if (/\*\s*$/.test(text)) return;
        // "(optional)" next to a required field — leave it alone.
        if (/\(optional\)/i.test(text)) return;
        var star = document.createElement('span');
        star.className = 'req-star';
        star.setAttribute('data-auto', '1');
        star.setAttribute('aria-hidden', 'true');
        star.textContent = '*';
        label.appendChild(star);
    }

    function addLegend(host, firstField) {
        var legend = document.createElement('div');
        legend.className = 'required-legend';
        legend.setAttribute('data-auto', '1');
        legend.innerHTML = 'Fields marked with <span class="req-star">*</span> are required.';

        var display = getComputedStyle(host).display;
        var isRow = host.classList.contains('row');
        if (isRow) legend.classList.add('col-12');
        else if (display.indexOf('flex') !== -1) { legend.style.flexBasis = '100%'; legend.style.width = '100%'; }
        else if (display.indexOf('grid') !== -1) legend.style.gridColumn = '1 / -1';

        var before = null;
        if (!isRow && display.indexOf('flex') === -1 && display.indexOf('grid') === -1) {
            // Block host: sit right above the block holding the first
            // required field (after any intro text / headings).
            before = firstField;
            while (before && before.parentElement !== host) before = before.parentElement;
            // …and above that block's section heading, if it has one.
            while (before && before.previousElementSibling &&
                before.previousElementSibling.matches('.section-label, h1, h2, h3, h4, h5, h6')) {
                before = before.previousElementSibling;
            }
        }
        if (!before) {
            before = Array.prototype.find.call(host.children, function (c) {
                return !c.matches('input[type="hidden"], script, style, template');
            }) || null;
        }
        host.insertBefore(legend, before);
    }

    function refresh() {
        var starLabels = new Set();
        var hosts = new Map(); // host → first required field in it

        document.querySelectorAll(FIELD_SEL).forEach(function (el) {
            if (el.closest('[data-no-required-star]')) return;
            var label = labelFor(el);
            if (!label) return;
            starLabels.add(label);
            var host = legendHost(el);
            if (host && !host.closest('[data-no-required-legend]') && !hosts.has(host)) hosts.set(host, el);
        });

        // Drop stars / legends whose field is no longer required.
        document.querySelectorAll('.req-star[data-auto]').forEach(function (star) {
            if (star.closest('.required-legend')) return;
            if (!starLabels.has(star.parentElement)) star.remove();
        });
        document.querySelectorAll('.required-legend[data-auto]').forEach(function (legend) {
            if (!hosts.has(legend.parentElement)) legend.remove();
        });

        starLabels.forEach(addStar);
        hosts.forEach(function (firstField, host) {
            var hasLegend = Array.prototype.some.call(host.children, function (c) { return c.classList.contains('required-legend'); });
            if (!hasLegend) addLegend(host, firstField);
        });
    }

    var queued = null;
    function schedule() {
        if (queued) return;
        queued = setTimeout(function () { queued = null; refresh(); }, 60);
    }

    function start() {
        refresh();
        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var m = mutations[i];
                if (m.type === 'attributes') { schedule(); return; }
                // Ignore our own star / legend insertions.
                var nodes = Array.prototype.concat.call([], Array.prototype.slice.call(m.addedNodes), Array.prototype.slice.call(m.removedNodes));
                for (var j = 0; j < nodes.length; j++) {
                    var n = nodes[j];
                    if (n.nodeType === 1 && !n.matches('.req-star, .required-legend')) { schedule(); return; }
                }
            }
        }).observe(document.body, { subtree: true, childList: true, attributes: true, attributeFilter: ['required', 'data-required'] });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
