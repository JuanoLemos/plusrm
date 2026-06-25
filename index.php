<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>+RM — Dashboard de Roadmaps</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="header">
        <div class="header-row">
            <h1><span class="logo">+RM</span> Dashboard de Roadmaps</h1>
            <div class="header-controls">
                <span id="auto-label" class="auto-label">Auto 15min</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="auto-toggle" checked>
                    <span class="toggle-slider"></span>
                </label>
                <span id="countdown" class="countdown">15:00</span>
                <button id="refresh-btn" class="btn btn-refresh" title="Refrescar ahora">🔄</button>
            </div>
        </div>
        <p class="subtitle" id="status-line">Escaneando proyectos...</p>
    </header>
    <main class="main">
        <aside class="sidebar">
            <div class="sidebar-top">
                <h2>Proyectos</h2>
                <button id="add-project-btn" class="btn btn-add" title="Agregar proyecto manualmente">+</button>
            </div>
            <ul id="project-list" class="project-list">
                <li class="loading">Escaneando proyectos...</li>
            </ul>
            <div id="hidden-section" class="hidden-section" style="display:none">
                <button id="show-hidden-btn" class="btn-hidden-toggle">Mostrar ocultos (0)</button>
            </div>
        </aside>
        <section class="content">
            <div id="project-header" class="project-header"></div>
            <div id="project-detail" class="project-detail">
                <div class="empty-state">
                    <p>Seleccioná un proyecto de la lista para ver su roadmap.</p>
                </div>
            </div>
        </section>
    </main>

    <div id="add-modal" class="modal" style="display:none">
        <div class="modal-backdrop" id="modal-backdrop"></div>
        <div class="modal-content">
            <h3>Agregar proyecto</h3>
            <p>Ingresá la ruta completa al directorio del proyecto (debe contener ROADMAP.md):</p>
            <div class="input-row">
                <div class="input-wrap">
                    <input type="text" id="add-path-input" class="modal-input" placeholder="C:\Users\...\mi-proyecto" autocomplete="off">
                    <span id="path-validation" class="input-icon"></span>
                </div>
                <button id="browse-btn" class="btn btn-browse" type="button" title="Examinar carpeta">📂</button>
            </div>
            <p class="modal-hint">Copiá la ruta desde el Explorador (clic en la barra de direcciones → <kbd>Ctrl+C</kbd>) — se pega automáticamente al abrir.</p>
            <p id="add-path-error" class="modal-error" style="display:none"></p>
            <div class="modal-actions">
                <button id="add-cancel-btn" class="btn btn-secondary">Cancelar</button>
                <button id="add-confirm-btn" class="btn btn-primary">Agregar</button>
            </div>
        </div>
    </div>

    <script src="assets/dashboard.js"></script>
</body>
</html>
