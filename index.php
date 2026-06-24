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
        <h1><span class="logo">+RM</span> Dashboard de Roadmaps</h1>
        <p class="subtitle">Proyectos detectados en directorios escaneados</p>
    </header>
    <main class="main">
        <aside class="sidebar">
            <h2>Proyectos</h2>
            <ul id="project-list" class="project-list">
                <li class="loading">Escaneando proyectos...</li>
            </ul>
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
    <script src="assets/dashboard.js"></script>
</body>
</html>
