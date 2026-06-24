<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../lib/Scanner.php';

$config = require __DIR__ . '/../config.php';

$scanner = new Scanner($config['scan_dirs'], $config['scan_depth'] ?? 1);
$projects = $scanner->scan();

$result = array_map(function ($p) {
    return [
        'name' => $p['name'],
        'path' => $p['path'],
        'version' => $p['version'],
        'diligencia' => $p['diligencia'],
        'stack' => $p['stack'],
        'adrs' => $p['adrs'],
        'bugs' => $p['bugs'],
        'incidents' => $p['incidents'],
        'stats' => $p['stats'],
        'format' => $p['format'],
        'docs' => $p['docs'],
    ];
}, $projects);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
