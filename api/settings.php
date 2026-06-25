<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../lib/Scanner.php';

$settingsPath = __DIR__ . '/../data/settings.json';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['validate'])) {
        $checkPath = realpath($_GET['validate']);
        $valid = $checkPath !== false && is_dir($checkPath) && is_file($checkPath . '\ROADMAP.md');
        echo json_encode(['valid' => $valid, 'resolved' => $checkPath], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $settings = loadSettings();
    echo json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $settings = loadSettings();

    if (isset($input['add_path'])) {
        $path = realpath($input['add_path']);
        if ($path === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Path does not exist'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $rmPath = $path . '\ROADMAP.md';
        if (!is_file($rmPath)) {
            http_response_code(400);
            echo json_encode(['error' => 'No ROADMAP.md found at this path'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $normalized = str_replace('/', '\\', $path);
        if (!in_array($normalized, $settings['added_paths'])) {
            $settings['added_paths'][] = $normalized;
        }
    }

    if (isset($input['hide_path'])) {
        $normalized = str_replace('/', '\\', realpath($input['hide_path']));
        if ($normalized && !in_array($normalized, $settings['hidden_paths'])) {
            $settings['hidden_paths'][] = $normalized;
        }
    }

    if (isset($input['unhide_path'])) {
        $normalized = str_replace('/', '\\', realpath($input['unhide_path']));
        if ($normalized) {
            $settings['hidden_paths'] = array_values(array_filter(
                $settings['hidden_paths'],
                fn($p) => $p !== $normalized
            ));
        }
    }

    if (isset($input['unhide_all'])) {
        $settings['hidden_paths'] = [];
    }

    saveSettings($settings);
    echo json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);

function loadSettings(): array
{
    global $settingsPath;
    if (!is_file($settingsPath)) {
        return ['added_paths' => [], 'hidden_paths' => []];
    }
    $content = file_get_contents($settingsPath);
    $data = json_decode($content, true);
    return is_array($data) ? $data : ['added_paths' => [], 'hidden_paths' => []];
}

function saveSettings(array $settings): void
{
    global $settingsPath;
    $dir = dirname($settingsPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
