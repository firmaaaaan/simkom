@once('select-searchable-js')
<style>
.select-searchable .ss-option:hover { background: #f8f9fa; }
.select-searchable .ss-tag { cursor: default; }
</style>
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var roots = document.querySelectorAll('.select-searchable');
        if (!roots.length) return;
        var specField = document.getElementById('spesifikasi');

        function rebuildSpec() {
            if (!specField) return;
            var groups = {};
            roots.forEach(function (root) {
                var group = root.dataset.group || root.dataset.name;
                var names = [];
                root.querySelectorAll('input.ss-checkbox:checked').forEach(function (cb) {
                    var label = cb.closest('.ss-option').querySelector('label');
                    names.push(label ? label.textContent.trim() : cb.value);
                });
                if (names.length) groups[group] = names;
            });
            var parts = [];
            ['Hardware', 'Software'].forEach(function (g) {
                if (groups[g]) parts.push(g + ': ' + groups[g].join(', '));
            });
            specField.value = parts.join(' | ');
        }

        roots.forEach(function (root) {
            var list = root.querySelector('.ss-list');
            var inp = root.querySelector('input.ss-input');
            var tags = root.querySelector('.ss-tags');
            var cbs = root.querySelectorAll('input.ss-checkbox');
            var native = root.querySelector('select.ss-native');

            function showList() { list.style.display = 'block'; }
            function hideList() { list.style.display = 'none'; }

            function syncNative() {
                var selected = [];
                cbs.forEach(function (cb) { if (cb.checked) selected.push(cb.value); });
                var opts = native.querySelectorAll('option');
                opts.forEach(function (o) { o.selected = selected.indexOf(o.value) > -1; });
            }

            function renderTags() {
                tags.innerHTML = '';
                var any = false;
                cbs.forEach(function (cb) {
                    if (!cb.checked) return;
                    any = true;
                    var label = cb.closest('.ss-option').querySelector('label');
                    var text = label ? label.textContent.trim() : cb.value;
                    var span = document.createElement('span');
                    span.className = 'badge bg-label-info ss-tag';
                    span.textContent = text + ' ';
                    var rm = document.createElement('i');
                    rm.className = 'bx bx-x';
                    rm.style.cursor = 'pointer';
                    rm.title = 'Hapus';
                    rm.addEventListener('click', function (e) {
                        e.stopPropagation();
                        cb.checked = false;
                        cb.dispatchEvent(new Event('change'));
                    });
                    span.appendChild(rm);
                    tags.appendChild(span);
                });
                if (!any) tags.innerHTML = '<small class="text-muted fst-italic">Tidak ada yang dipilih</small>';
            }

            function filterOptions() {
                var q = (inp.value || '').toLowerCase();
                cbs.forEach(function (cb) {
                    var opt = cb.closest('.ss-option');
                    if (!opt) return;
                    var txt = opt.textContent.toLowerCase();
                    opt.style.display = txt.indexOf(q) > -1 ? '' : 'none';
                });
            }

            inp.addEventListener('input', function () {
                filterOptions();
                showList();
            });

            inp.addEventListener('focus', function () {
                filterOptions();
                showList();
            });

            inp.addEventListener('blur', function (e) {
                var rel = e.relatedTarget;
                if (rel && root.contains(rel)) {
                    // focus moving within component (e.g. checkbox/label): keep open
                    setTimeout(function () {
                        if (root.contains(document.activeElement)) { showList(); }
                        else { hideList(); }
                    }, 80);
                } else {
                    setTimeout(hideList, 80);
                }
            });

            cbs.forEach(function (cb) {
                cb.addEventListener('change', function () {
                    syncNative();
                    renderTags();
                    rebuildSpec();
                });
                // keep the list open while interacting with options
                cb.closest('.ss-option').addEventListener('mousedown', function (e) {
                    e.preventDefault();
                });
            });

            // click outside this component -> close
            document.addEventListener('click', function (e) {
                if (!root.contains(e.target)) { hideList(); }
            });

            syncNative();
            renderTags();
        });

        rebuildSpec();
    });
})();
</script>
@endonce
