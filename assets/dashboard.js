const AUTO_INTERVAL = 900000; // 15 min
const COUNTDOWN_STEP = 1000;

let projects = [];
let hiddenPaths = [];
let autoRefreshId = null;
let autoEnabled = true;
let countdownInterval = null;
let remainingSecs = 900;
let refreshInProgress = false;

document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('project-list');
    const detailEl = document.getElementById('project-detail');
    const refreshBtn = document.getElementById('refresh-btn');
    const autoToggle = document.getElementById('auto-toggle');
    const addBtn = document.getElementById('add-project-btn');
    const showHiddenBtn = document.getElementById('show-hidden-btn');

    loadProjects();

    refreshBtn.addEventListener('click', manualRefresh);
    autoToggle.addEventListener('change', toggleAutoRefresh);

    addBtn.addEventListener('click', openAddModal);
    document.getElementById('browse-btn').addEventListener('click', browseFolder);
    document.getElementById('add-cancel-btn').addEventListener('click', closeAddModal);
    document.getElementById('modal-backdrop').addEventListener('click', closeAddModal);
    document.getElementById('add-confirm-btn').addEventListener('click', confirmAdd);
    document.getElementById('add-path-input').addEventListener('keydown', e => { if (e.key === 'Enter') confirmAdd(); });
    showHiddenBtn.addEventListener('click', toggleShowHidden);

    startAutoRefresh();
    startCountdown();

    async function loadProjects() {
        try {
            const res = await fetch('api/projects.php');
            if (!res.ok) throw new Error('HTTP ' + res.status);
            projects = await res.json();
            const settings = await fetchSettings();
            hiddenPaths = settings.hidden_paths || [];
            renderProjectList();
            updateStatus();
        } catch (err) {
            listEl.innerHTML = '<li class="error">Error: ' + err.message + '</li>';
        }
    }

    async function fetchSettings() {
        try {
            const res = await fetch('api/settings.php');
            return await res.json();
        } catch {
            return { added_paths: [], hidden_paths: [] };
        }
    }

    async function fetchRoadmap(path) {
        const res = await fetch('api/roadmap.php?project=' + encodeURIComponent(path));
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    }

    function renderProjectList() {
        const visible = projects.filter(p => !isHidden(p.path));
        const hidden = projects.filter(p => isHidden(p.path));
        const isShowingHidden = showHiddenBtn.dataset.showing === 'true';

        listEl.innerHTML = '';

        if (visible.length === 0 && hidden.length === 0) {
            listEl.innerHTML = '<li class="loading">No se detectaron proyectos con ROADMAP.md</li>';
            return;
        }

        visible.forEach((proj, i) => {
            const li = createProjectItem(proj, false);
            listEl.appendChild(li);
        });

        if (isShowingHidden) {
            hidden.forEach(proj => {
                const li = createProjectItem(proj, true);
                listEl.appendChild(li);
            });
        }

        const hs = document.getElementById('hidden-section');
        if (hidden.length > 0) {
            hs.style.display = 'block';
            showHiddenBtn.textContent = (isShowingHidden ? 'Ocultar' : 'Mostrar') + ' ocultos (' + hidden.length + ')';
        } else {
            hs.style.display = 'none';
        }
    }

    function createProjectItem(proj, hidden) {
        const li = document.createElement('li');
        li.className = hidden ? 'hidden-item' : '';
        const s = proj.stats || {};
        let badgesHtml = '';
        if (proj.bugs && proj.bugs.total) badgesHtml += '<span class="badge badge-bugs">' + proj.bugs.total + ' bugs</span>';
        if (proj.format) badgesHtml += '<span class="badge badge-format">' + esc(proj.format) + '</span>';

        var nameText = esc(proj.name);
        if (proj.version) nameText += ' <span class="version-inline">' + esc(proj.version) + '</span>';
        li.innerHTML = '<span class="project-name">' + nameText + '</span>'
            + '<span class="project-meta">' + (s.completionPercent || 0) + '% · '
            + (s.done || 0) + '/' + (s.total || 0) + '</span>'
            + (badgesHtml ? '<span class="project-badges">' + badgesHtml + '</span>' : '')
            + '<button class="project-remove" title="Ocultar proyecto">✕</button>';

        li.addEventListener('click', e => {
            if (e.target.classList.contains('project-remove')) return;
            selectProject(proj, li);
        });
        li.querySelector('.project-remove').addEventListener('click', e => {
            e.stopPropagation();
            hideProject(proj);
        });
        return li;
    }

    function isHidden(path) {
        return hiddenPaths.includes(path);
    }

    async function hideProject(proj) {
        await fetch('api/settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ hide_path: proj.path })
        });
        hiddenPaths.push(proj.path);
        renderProjectList();
    }

    function toggleShowHidden() {
        const btn = document.getElementById('show-hidden-btn');
        btn.dataset.showing = btn.dataset.showing === 'true' ? 'false' : 'true';
        renderProjectList();
    }

    async function selectProject(proj, activeEl) {
        document.querySelectorAll('.project-list li').forEach(el => el.classList.remove('active'));
        if (activeEl) activeEl.classList.add('active');
        document.getElementById('project-detail').innerHTML = '<p class="loading">Cargando roadmap...</p>';
        try {
            const data = await fetchRoadmap(proj.path);
            renderProjectHeader(proj);
            renderRoadmap(data);
        } catch (err) {
            document.getElementById('project-detail').innerHTML = '<p class="error">Error: ' + err.message + '</p>';
        }
    }

    function renderProjectHeader(proj) {
        const headerEl = document.getElementById('project-header');
        let html = '<h2>' + esc(proj.name) + '</h2><div class="header-badges">';
        if (proj.version) html += '<span class="badge badge-version">' + esc(proj.version) + '</span>';
        if (proj.diligencia) html += '<span class="badge badge-diligencia">Diligencia ' + esc(proj.diligencia) + '</span>';
        if (proj.stack) html += '<span class="badge badge-stack">' + esc(proj.stack) + '</span>';
        if (proj.adrs) html += '<span class="badge badge-adrs">' + proj.adrs.total + ' ADRs</span>';
        if (proj.bugs && proj.bugs.total) html += '<span class="badge badge-bugs">' + proj.bugs.total + ' bugs</span>';
        if (proj.format) html += '<span class="badge badge-format">' + esc(proj.format) + '</span>';
        html += '</div>';
        headerEl.innerHTML = html;
    }

    function renderRoadmap(data) {
        const s = data.stats || {};
        const h = s.completionPercent || 0;
        let html = '<div class="project-stats">';
        html += statCard('Total', s.total || 0);
        html += statCard('Completado', s.done || 0, '#2ecc71');
        html += statCard('En progreso', s.inProgress || 0, '#f39c12');
        html += statCard('Pendiente', s.pending || 0, '#e74c3c');
        if (s.blocked) html += statCard('Bloqueado', s.blocked, '#8e44ad');
        html += statCard('Avance', h + '%', '#e94560', h);
        html += '</div>';

        if (data.format === 'extended') {
            html += renderExtendedSections(data);
        } else {
            html += renderStandardPhases(data);
        }
        document.getElementById('project-detail').innerHTML = html;
    }

    function renderStandardPhases(data) {
        let html = '';
        if ((data.now || []).length > 0) {
            html += '<h3 class="phase-header phase-now">Ahora (Now)</h3>' + taskTable(data.now, true);
        }
        if ((data.next || []).length > 0) {
            html += '<h3 class="phase-header phase-next">Siguiente (Next)</h3>' + taskTable(data.next, true);
        }
        if ((data.later || []).length > 0) {
            html += '<h3 class="phase-header phase-later">Futuro (Later)</h3>' + taskTable(data.later, true);
        }
        if ((data.done || []).length > 0) {
            const validDone = data.done.filter(d => d.item && d.item !== '—');
            if (validDone.length > 0) {
                html += '<h3 class="phase-header phase-done">Completado</h3>' + doneTable(validDone);
            }
        }
        return html;
    }

    function renderExtendedSections(data) {
        let html = '';
        const sections = data.sections || [];
        if (data.now && data.now.length > 0) {
            html += '<h3 class="phase-header phase-now">Resumen — En progreso</h3>' + compactList(data.now);
        }
        sections.forEach((sec, i) => {
            html += '<details class="section-details" ' + (i === 0 ? 'open' : '') + '>';
            html += '<summary class="section-summary">';
            html += '<span class="section-name">' + esc(sec.name) + '</span>';
            html += '<span class="section-meta">' + (sec.itemCount || 0) + ' items · ' + sec.progress + '%</span>';
            html += '<div class="section-bar"><div class="section-bar-fill" style="width:' + sec.progress + '%"></div></div>';
            html += '</summary>';
            if (sec.items && sec.items.length > 0) {
                html += '<div class="section-items">';
                sec.items.forEach(item => {
                    const cls = item.status && item.status.label === 'Completado' ? 'item-done'
                        : item.status && item.status.label === 'En progreso' ? 'item-progress' : 'item-pending';
                    html += '<div class="section-item ' + cls + '">';
                    html += '<span class="item-dot"></span>';
                    html += '<span class="item-text">' + esc(item.item || '(item)') + '</span>';
                    if (item.status) html += '<span class="item-status">' + esc(item.status.label) + '</span>';
                    html += '</div>';
                });
                html += '</div>';
            } else {
                html += '<div class="section-empty">Sin items detectados</div>';
            }
            html += '</details>';
        });
        const validDone = (data.done || []).filter(d => d.item && d.item !== '—');
        if (validDone.length > 0) {
            html += '<h3 class="phase-header phase-done">Completado</h3>' + doneTable(validDone);
        }
        return html;
    }

    function compactList(items) {
        let html = '<div class="compact-list">';
        items.forEach(item => {
            const label = (item.status && item.status.label) || 'Pendiente';
            const cls = label === 'Completado' ? 'item-done' : label === 'En progreso' ? 'item-progress' : 'item-pending';
            html += '<div class="compact-item ' + cls + '">';
            html += '<span class="item-dot"></span>';
            html += '<span class="item-text">' + esc(item.item) + '</span>';
            html += '<span class="item-status">' + label + '</span></div>';
        });
        html += '</div>';
        return html;
    }

    function statCard(label, value, color, bar) {
        const c = color || '#999';
        const b = bar !== undefined ? bar : 0;
        return '<div class="stat-card"><div class="stat-value" style="color:' + c + '">' + value + '</div>'
            + '<div class="stat-label">' + label + '</div>'
            + (b > 0 ? '<div class="stat-bar"><div class="stat-bar-fill" style="width:' + b + '%;background:' + c + '"></div></div>' : '')
            + '</div>';
    }

    function taskTable(items) {
        let html = '<table class="roadmap-table"><thead><tr>'
            + '<th>ID</th><th>Item</th><th>Prioridad</th><th>Estado</th>'
            + '</tr></thead><tbody>';
        items.forEach(item => {
            const priClass = 'badge-' + (item.priority || 'p3').toLowerCase();
            const sl = (item.status && item.status.label) || 'Pendiente';
            const sc = 'status-dot status-' + (sl === 'Completado' ? 'completado' : sl === 'En progreso' ? 'progreso' : sl === 'Bloqueado' ? 'bloqueado' : 'pendiente');
            html += '<tr><td>' + esc(item.id || '') + '</td><td>' + esc(item.item) + '</td>'
                + '<td>' + (item.priority ? '<span class="badge ' + priClass + '">' + esc(item.priority) + '</span>' : '') + '</td>'
                + '<td><span class="' + sc + '"></span>' + sl + '</td></tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    function doneTable(items) {
        let html = '<table class="roadmap-table"><thead><tr><th>Item</th><th>Instancia</th></tr></thead><tbody>';
        items.forEach(item => {
            html += '<tr><td>' + esc(item.item) + '</td><td>' + esc(item.instance || '—') + '</td></tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    function updateStatus() {
        const visible = projects.filter(p => !isHidden(p.path)).length;
        const hidden = projects.filter(p => isHidden(p.path)).length;
        const line = visible + ' proyectos visibles' + (hidden > 0 ? ' · ' + hidden + ' ocultos' : '');
        document.getElementById('status-line').textContent = line;
    }

    // Refresh logic
    async function manualRefresh() {
        if (refreshInProgress) return;
        refreshInProgress = true;
        const btn = document.getElementById('refresh-btn');
        btn.classList.add('spinning');
        try {
            await loadProjects();
            resetCountdown();
        } finally {
            btn.classList.remove('spinning');
            refreshInProgress = false;
        }
    }

    function startAutoRefresh() {
        if (autoRefreshId) clearInterval(autoRefreshId);
        autoRefreshId = setInterval(async () => {
            if (autoEnabled && !refreshInProgress) {
                await manualRefresh();
            }
        }, AUTO_INTERVAL);
    }

    function toggleAutoRefresh() {
        autoEnabled = document.getElementById('auto-toggle').checked;
        if (autoEnabled) {
            startAutoRefresh();
            startCountdown();
        } else {
            if (autoRefreshId) { clearInterval(autoRefreshId); autoRefreshId = null; }
            if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
            document.getElementById('countdown').textContent = 'OFF';
        }
    }

    function startCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);
        remainingSecs = Math.floor(AUTO_INTERVAL / 1000);
        countdownInterval = setInterval(() => {
            remainingSecs--;
            if (remainingSecs <= 0) remainingSecs = Math.floor(AUTO_INTERVAL / 1000);
            const m = String(Math.floor(remainingSecs / 60)).padStart(2, '0');
            const s = String(remainingSecs % 60).padStart(2, '0');
            document.getElementById('countdown').textContent = m + ':' + s;
        }, COUNTDOWN_STEP);
    }

    function resetCountdown() {
        remainingSecs = Math.floor(AUTO_INTERVAL / 1000);
    }

    // Add project modal
    document.getElementById('add-path-input').addEventListener('input', function () {
        debounceValidate(this.value);
    });

    function debounceValidate(path) {
        if (window._validateTimer) clearTimeout(window._validateTimer);
        window._validateTimer = setTimeout(() => validatePath(path), 400);
    }

    async function validatePath(path) {
        const icon = document.getElementById('path-validation');
        const input = document.getElementById('add-path-input');
        if (!path || path.length < 5) {
            icon.textContent = '';
            input.classList.remove('valid', 'invalid');
            return;
        }
        try {
            const res = await fetch('api/settings.php?validate=' + encodeURIComponent(path));
            const data = await res.json();
            if (data.valid) {
                icon.textContent = '✅';
                input.classList.add('valid');
                input.classList.remove('invalid');
            } else {
                icon.textContent = '❌';
                input.classList.add('invalid');
                input.classList.remove('valid');
            }
        } catch {
            icon.textContent = '?';
        }
    }

    async function browseFolder() {
        const btn = document.getElementById('browse-btn');
        const errorEl = document.getElementById('add-path-error');
        errorEl.style.display = 'none';
        btn.disabled = true;
        btn.textContent = '⏳';

        try {
            const ctrl = new AbortController();
            const timeout = setTimeout(() => ctrl.abort(), 60000);
            const res = await fetch('api/pick-folder.php', { signal: ctrl.signal });
            clearTimeout(timeout);
            const data = await res.json();

            if (data.path) {
                document.getElementById('add-path-input').value = data.path;
                validatePath(data.path);
            } else {
                if (data.error) {
                    errorEl.textContent = data.error;
                    errorEl.style.display = 'block';
                }
            }
        } catch (err) {
            if (err.name === 'AbortError') {
                errorEl.textContent = 'El diálogo tardó demasiado. Probá escribiendo la ruta manualmente.';
            } else {
                errorEl.textContent = 'Error al abrir el selector de carpetas';
            }
            errorEl.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = '📂';
        }
    }

    async function openAddModal() {
        document.getElementById('add-modal').style.display = 'flex';
        document.getElementById('add-path-input').value = '';
        document.getElementById('add-path-error').style.display = 'none';
        document.getElementById('path-validation').textContent = '';
        document.getElementById('add-path-input').classList.remove('valid', 'invalid');
        document.getElementById('add-path-input').focus();

        try {
            const text = await navigator.clipboard.readText();
            if (/^[a-zA-Z]:[\\\/]/.test(text.trim())) {
                document.getElementById('add-path-input').value = text.trim();
                validatePath(text.trim());
            }
        } catch (e) { /* clipboard not available */ }
    }

    function closeAddModal() {
        document.getElementById('add-modal').style.display = 'none';
    }

    async function confirmAdd() {
        const input = document.getElementById('add-path-input');
        const errorEl = document.getElementById('add-path-error');
        const path = input.value.trim();
        if (!path) {
            errorEl.textContent = 'Ingresá una ruta';
            errorEl.style.display = 'block';
            return;
        }
        errorEl.style.display = 'none';
        try {
            const res = await fetch('api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ add_path: path })
            });
            if (!res.ok) {
                const errData = await res.json();
                errorEl.textContent = errData.error || 'Error al agregar';
                errorEl.style.display = 'block';
                return;
            }
            closeAddModal();
            showToast('✅ Proyecto agregado: ' + path.split('\\').pop());
            await manualRefresh();
        } catch (err) {
            errorEl.textContent = 'Error de conexión: ' + err.message;
            errorEl.style.display = 'block';
        }
    }

    function showToast(msg) {
        const t = document.createElement('div');
        t.className = 'toast';
        t.textContent = msg;
        document.body.appendChild(t);
        requestAnimationFrame(() => { t.classList.add('toast-show'); });
        setTimeout(() => {
            t.classList.remove('toast-show');
            setTimeout(() => t.remove(), 400);
        }, 3000);
    }

    function esc(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }
});
