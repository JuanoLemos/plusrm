<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../lib/Scanner.php';

$config = require __DIR__ . '/../config.php';

$settings = loadSettings();
$extraPaths = $settings['added_paths'] ?? [];
$hiddenPaths = $settings['hidden_paths'] ?? [];

$scanner = new Scanner($config['scan_dirs'], $config['scan_depth'] ?? 1, $extraPaths);
$projects = $scanner->scan();

$result = array_map(function ($p) use ($hiddenPaths) {
    $hidden = in_array($p['path'], $hiddenPaths);
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
        'hidden' => $hidden,
    ];
}, $projects);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

function loadSettings(): array
{
    $path = __DIR__ . '/../data/settings.json';
    if (!is_file($path)) {
        return ['added_paths' => [], 'hidden_paths' => []];
    }
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : ['added_paths' => [], 'hidden_paths' => []];
}
