<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../lib/RoadmapParser.php';
require_once __DIR__ . '/../lib/Scanner.php';

$config = require __DIR__ . '/../config.php';

$projectPath = $_GET['project'] ?? '';
if ($projectPath === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing project parameter'], JSON_UNESCAPED_UNICODE);
    exit;
}

$resolved = realpath($projectPath);
if ($resolved === false || !is_dir($resolved)) {
    http_response_code(404);
    echo json_encode(['error' => 'Project not found'], JSON_UNESCAPED_UNICODE);
    exit;
}

$scanner = new Scanner($config['scan_dirs'], $config['scan_depth'] ?? 1);
if (!$scanner->isPathAllowed($resolved) && !isCustomPath($resolved)) {
    http_response_code(403);
    echo json_encode(['error' => 'Path not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rmPath = $resolved . '\ROADMAP.md';
if (!is_file($rmPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'ROADMAP.md not found in project'], JSON_UNESCAPED_UNICODE);
    exit;
}

$parser = new RoadmapParser();
$data = $parser->parse($rmPath);

if ($data === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to parse ROADMAP.md'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data['path'] = $resolved;
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

function isCustomPath(string $resolved): bool
{
    $settingsPath = __DIR__ . '/../data/settings.json';
    if (!is_file($settingsPath)) {
        return false;
    }
    $content = file_get_contents($settingsPath);
    $settings = json_decode($content, true);
    if (!is_array($settings) || !isset($settings['added_paths'])) {
        return false;
    }
    $normalized = str_replace('/', '\\', $resolved);
    foreach ($settings['added_paths'] as $ap) {
        if (str_replace('/', '\\', $ap) === $normalized) {
            return true;
        }
    }
    return false;
}
