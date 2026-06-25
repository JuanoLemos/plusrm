<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$vbsPath = __DIR__ . '/../assets/browse.vbs';

if (!is_file($vbsPath)) {
    echo json_encode(['path' => null, 'error' => 'Selector no encontrado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$cmd = 'cscript //NoLogo "' . $vbsPath . '" 2>&1';

$output = [];
$returnCode = -1;
exec($cmd, $output, $returnCode);

$result = trim(implode("\n", $output));

if ($result !== '' && is_dir($result)) {
    echo json_encode(['path' => $result], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['path' => null], JSON_UNESCAPED_UNICODE);
}
