document.addEventListener('DOMContentLoaded', async () => {
    const listEl = document.getElementById('project-list');
    const detailEl = document.getElementById('project-detail');

    const projects = await fetchProjects();
    renderProjectList(projects);
    if (projects.length > 0) {
        selectProject(projects[0]);
    }

    async function fetchProjects() {
        try {
            const res = await fetch('api/projects.php');
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return await res.json();
        } catch (err) {
            listEl.innerHTML = '<li class="error">Error: ' + err.message + '</li>';
            return [];
        }
    }

    async function fetchRoadmap(path) {
        const res = await fetch('api/roadmap.php?project=' + encodeURIComponent(path));
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    }

    function renderProjectList(projects) {
        if (projects.length === 0) {
            listEl.innerHTML = '<li class="loading">No se detectaron proyectos con ROADMAP.md</li>';
            return;
        }
        listEl.innerHTML = '';
        projects.forEach((proj, i) => {
            const li = document.createElement('li');
            li.dataset.index = i;
            const s = proj.stats || {};
            let badges = '';
            if (proj.version) badges += '<span class="badge badge-version">' + esc(proj.version) + '</span>';
            if (proj.bugs && proj.bugs.total) badges += '<span class="badge badge-bugs">' + proj.bugs.total + ' bugs</span>';
            if (proj.format) badges += '<span class="badge badge-format">' + esc(proj.format) + '</span>';
            li.innerHTML = '<span class="project-name">' + esc(proj.name) + '</span>'
                + '<span class="project-meta">' + s.completionPercent + '% · '
                + (s.done || 0) + '/' + (s.total || 0) + '</span>'
                + (badges ? '<span class="project-badges">' + badges + '</span>' : '');
            li.addEventListener('click', () => selectProject(proj, li));
            listEl.appendChild(li);
        });
    }

    async function selectProject(proj, activeEl) {
        document.querySelectorAll('.project-list li').forEach(el => el.classList.remove('active'));
        if (activeEl) activeEl.classList.add('active');
        detailEl.innerHTML = '<p class="loading">Cargando roadmap...</p>';
        try {
            const data = await fetchRoadmap(proj.path);
            renderProjectHeader(proj);
            renderRoadmap(data);
        } catch (err) {
            detailEl.innerHTML = '<p class="error">Error: ' + err.message + '</p>';
        }
    }

    function renderProjectHeader(proj) {
        const headerEl = document.getElementById('project-header') || (() => {
            const h = document.createElement('div');
            h.id = 'project-header';
            h.className = 'project-header';
            detailEl.parentNode.insertBefore(h, detailEl);
            return h;
        })();

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
        if (s.blocked) html += statCard('Bloqueado', s.blocked, '#e74c3c');
        html += statCard('Avance', h + '%', '#e94560', h);
        html += '</div>';

        if (data.format === 'extended') {
            html += renderExtendedSections(data);
        } else {
            html += renderStandardPhases(data);
        }

        detailEl.innerHTML = html;
    }

    function renderStandardPhases(data) {
        let html = '';
        const nowItems = data.now || [];
        const nextItems = data.next || [];
        const laterItems = data.later || [];
        const doneItems = data.done || [];

        if (nowItems.length > 0) {
            html += '<h3 class="phase-header phase-now">Ahora (Now)</h3>';
            html += taskTable(nowItems, true);
        }
        if (nextItems.length > 0) {
            html += '<h3 class="phase-header phase-next">Siguiente (Next)</h3>';
            html += taskTable(nextItems, true);
        }
        if (laterItems.length > 0) {
            html += '<h3 class="phase-header phase-later">Futuro (Later)</h3>';
            html += taskTable(laterItems, true);
        }
        if (doneItems.length > 0) {
            html += '<h3 class="phase-header phase-done">Completado</h3>';
            html += doneTable(doneItems);
        }
        return html;
    }

    function renderExtendedSections(data) {
        let html = '';
        const sections = data.sections || [];

        if (data.now && data.now.length > 0) {
            html += '<h3 class="phase-header phase-now">Resumen — En progreso</h3>';
            html += compactList(data.now);
        }

        sections.forEach((sec, i) => {
            const expanded = i === 0;
            html += '<details class="section-details" ' + (expanded ? 'open' : '') + '>';
            html += '<summary class="section-summary">';
            html += '<span class="section-name">' + esc(sec.name) + '</span>';
            html += '<span class="section-meta">';
            if (sec.itemCount) html += sec.itemCount + ' items · ';
            html += sec.progress + '% completado';
            html += '</span>';
            html += '<div class="section-bar"><div class="section-bar-fill" style="width:' + sec.progress + '%"></div></div>';
            html += '</summary>';
            if (sec.items && sec.items.length > 0) {
                html += '<div class="section-items">';
                sec.items.forEach(item => {
                    const cls = item.status && item.status.label === 'Completado' ? 'item-done'
                        : item.status && item.status.label === 'En progreso' ? 'item-progress'
                        : 'item-pending';
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

        if (data.done && data.done.length > 0) {
            html += '<h3 class="phase-header phase-done">Completado</h3>';
            html += doneTable(data.done);
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
            html += '<span class="item-status">' + label + '</span>';
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    function statCard(label, value, color, bar) {
        const c = color || '#999';
        const b = bar !== undefined ? bar : 0;
        return '<div class="stat-card">'
            + '<div class="stat-value" style="color:' + c + '">' + value + '</div>'
            + '<div class="stat-label">' + label + '</div>'
            + (b > 0 ? '<div class="stat-bar"><div class="stat-bar-fill" style="width:' + b + '%;background:' + c + '"></div></div>' : '')
            + '</div>';
    }

    function taskTable(items, showDepends) {
        let html = '<table class="roadmap-table"><thead><tr>'
            + '<th>ID</th><th>Item</th><th>Prioridad</th><th>Estado</th>'
            + (showDepends ? '<th>Depende</th>' : '')
            + '</tr></thead><tbody>';
        items.forEach(item => {
            const priClass = 'badge-' + (item.priority || 'p3').toLowerCase();
            const statusLabel = (item.status && item.status.label) || 'Pendiente';
            const statusClass = 'status-dot status-'
                + (statusLabel === 'Completado' ? 'completado'
                    : statusLabel === 'En progreso' ? 'progreso'
                    : statusLabel === 'Bloqueado' ? 'bloqueado'
                    : 'pendiente');
            html += '<tr>'
                + '<td>' + esc(item.id || '') + '</td>'
                + '<td>' + esc(item.item) + '</td>'
                + '<td>' + (item.priority ? '<span class="badge ' + priClass + '">' + esc(item.priority) + '</span>' : '') + '</td>'
                + '<td><span class="' + statusClass + '"></span>' + statusLabel + '</td>'
                + (showDepends ? '<td>' + esc(item.dependsOn || '—') + '</td>' : '')
                + '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    function doneTable(items) {
        let html = '<table class="roadmap-table"><thead><tr>'
            + '<th>Item</th><th>Instancia</th>'
            + '</tr></thead><tbody>';
        items.forEach(item => {
            html += '<tr>'
                + '<td>' + esc(item.item) + '</td>'
                + '<td>' + esc(item.instance || '—') + '</td>'
                + '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    function esc(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }
});
