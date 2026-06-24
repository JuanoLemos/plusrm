<?php

class RoadmapParser
{
    public function parse(string $filePath): ?array
    {
        if (!is_file($filePath)) {
            return null;
        }
        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            return null;
        }
        $lines = explode("\n", $content);
        $projectName = $this->extractProjectName($lines);
        $format = $this->detectFormat($lines);

        if ($format === 'extended') {
            return $this->parseExtended($lines, $projectName);
        }
        return $this->parseStandard($lines, $projectName);
    }

    private function detectFormat(array $lines): string
    {
        $joined = '';
        foreach (array_slice($lines, 0, 80) as $line) {
            $joined .= $line . "\n";
        }
        $lower = mb_strtolower($joined);

        $hasStandard = (
            preg_match('/^##\s*(ahora|siguiente|futuro|completado)/ium', $joined) ||
            preg_match('/^##\s*(now|next|later|done|completed)/ium', $joined)
        );

        $hasExtended = (
            preg_match('/^##\s*t[eé]cnico/ium', $joined) ||
            preg_match('/^##\s*ui\b/ium', $joined) ||
            preg_match('/^##\s*ux\b/ium', $joined)
        );

        if ($hasExtended && !$hasStandard) {
            return 'extended';
        }
        return 'standard';
    }

    private function extractProjectName(array $lines): string
    {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^#\s*ROADMAP\s*(?:[:=—–-]|\p{Pd})?\s*(.+)$/iu', $trimmed, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/^#\s+(.+)$/', $trimmed, $m)) {
                return trim($m[1]);
            }
        }
        return 'Unknown';
    }

    private function parseStandard(array $lines, string $projectName): array
    {
        $sections = ['now' => [], 'next' => [], 'later' => [], 'done' => []];
        $currentSection = null;
        $phase = 'idle';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^##\s+(.+)$/', $trimmed, $m)) {
                $currentSection = $this->normalizeSection($m[1]);
                $phase = 'idle';
                continue;
            }
            if ($currentSection === null || !isset($sections[$currentSection])) {
                continue;
            }
            if (!str_starts_with($trimmed, '|')) {
                if ($phase === 'data' && $trimmed === '') {
                    $phase = 'idle';
                }
                continue;
            }
            if (preg_match('/^\|?\s*-{2,}\s*\|/', $trimmed)) {
                $phase = 'data';
                continue;
            }
            if ($phase === 'idle' || $phase === 'header_seen') {
                $phase = 'header_seen';
                continue;
            }
            if ($phase === 'data') {
                if ($currentSection === 'done') {
                    $item = $this->parseDoneRow($trimmed);
                } else {
                    $item = $this->parseTaskRow($trimmed);
                }
                if ($item !== null) {
                    $sections[$currentSection][] = $item;
                }
            }
        }

        $stats = $this->computeStats($sections);
        return [
            'projectName' => $projectName,
            'format' => 'standard',
            'now' => $sections['now'],
            'next' => $sections['next'],
            'later' => $sections['later'],
            'done' => $sections['done'],
            'sections' => [],
            'stats' => $stats,
        ];
    }

    private function parseExtended(array $lines, string $projectName): array
    {
        $bigSections = $this->extractBigSections($lines);
        $resumen = $this->tryParseResumenGeneral($lines);

        $sectionData = [];
        $globalNow = [];
        $globalNext = [];
        $globalLater = [];
        $globalDone = [];

        if ($resumen) {
            foreach ($resumen as $area => $phaseMap) {
                foreach ($phaseMap as $phase => $items) {
                    $label = $this->normalizeSection($phase);
                    foreach ($items as $item) {
                        $entry = ['id' => '', 'item' => $item, 'priority' => '', 'status' => ['label' => $label === 'done' ? 'Completado' : ($label === 'now' ? 'En progreso' : 'Pendiente'), 'progress' => $label === 'done' ? 100 : ($label === 'now' ? 50 : 0), 'raw' => $item]];
                        if (in_array($label, ['now', 'next', 'later', 'done'])) {
                            ${'global' . ucfirst($label)}[] = $entry;
                        }
                    }
                }
            }
        }

        foreach ($bigSections as $sectionName => $sectionLines) {
            $items = $this->scanSectionItems($sectionLines);
            $subCount = count($items);
            $doneSub = count(array_filter($items, fn($i) => ($i['status']['label'] ?? '') === 'Completado'));
            $sectionData[] = [
                'name' => $sectionName,
                'items' => $items,
                'itemCount' => $subCount,
                'doneCount' => $doneSub,
                'progress' => $subCount > 0 ? round(($doneSub / $subCount) * 100) : 0,
            ];
        }

        $sections = ['now' => $globalNow, 'next' => $globalNext, 'later' => $globalLater, 'done' => $globalDone];
        $stats = $this->computeStats($sections);

        return [
            'projectName' => $projectName,
            'format' => 'extended',
            'now' => $globalNow,
            'next' => $globalNext,
            'later' => $globalLater,
            'done' => $globalDone,
            'sections' => $sectionData,
            'stats' => $stats,
        ];
    }

    private function extractBigSections(array $lines): array
    {
        $sections = [];
        $currentName = null;
        $currentLines = [];
        $sectionKeywords = ['técnico', 'tecnico', 'ui', 'ux'];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^##\s+(.+)$/', $trimmed, $m)) {
                $header = mb_strtolower(trim($m[1]));
                $isSection = false;
                foreach ($sectionKeywords as $kw) {
                    if ($header === $kw || str_starts_with($header, $kw)) {
                        $isSection = true;
                        break;
                    }
                }
                if ($isSection) {
                    if ($currentName !== null && count($currentLines) > 0) {
                        $sections[$currentName] = $currentLines;
                    }
                    $currentName = trim($m[1]);
                    $currentLines = [];
                    continue;
                }
            }
            if ($currentName !== null) {
                $currentLines[] = $trimmed;
            }
        }
        if ($currentName !== null && count($currentLines) > 0) {
            $sections[$currentName] = $currentLines;
        }
        return $sections;
    }

    private function tryParseResumenGeneral(array $lines): ?array
    {
        $inResumen = false;
        $inTable = false;
        $headers = [];
        $rows = [];

        foreach ($lines as $line) {
            $t = trim($line);
            if (preg_match('/^##\s*resumen\s+general/iu', $t)) {
                $inResumen = true;
                continue;
            }
            if (!$inResumen) {
                continue;
            }
            if (preg_match('/^\|?\s*-{2,}\s*\|/', $t)) {
                if (!$inTable) {
                    $inTable = true;
                }
                continue;
            }
            if (!str_starts_with($t, '|')) {
                if ($inTable && $t === '') {
                    break;
                }
                continue;
            }
            $parts = explode('|', $t);
            $parts = array_map(fn($p) => trim($p), $parts);
            $parts = array_values(array_filter($parts, fn($p) => $p !== ''));

            if (count($headers) === 0) {
                $headers = $parts;
                continue;
            }
            if (count($parts) >= 2) {
                $rows[] = $parts;
            }
        }

        if (count($rows) === 0) {
            return null;
        }

        $result = [];
        foreach ($rows as $row) {
            $phase = $row[0] ?? '';
            $phase = preg_replace('/[🟢🟡🔴🔵✅✔]/u', '', $phase);
            $phase = trim($phase);
            for ($i = 1; $i < count($row); $i++) {
                $area = $headers[$i] ?? "Area$i";
                if (!isset($result[$area])) {
                    $result[$area] = [];
                }
                $items = $row[$i] ?? '';
                $items = trim($items);
                if ($items === '' || $items === '—' || $items === '-') {
                    continue;
                }
                $parsed = preg_split('/[,;]\s*/', $items);
                $parsed = array_filter($parsed, fn($p) => trim($p) !== '');
                $result[$area][$phase] = array_values($parsed);
            }
        }
        return $result;
    }

    private function scanSectionItems(array $lines): array
    {
        $items = [];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '' || preg_match('/^-{3,}$/', $t)) {
                continue;
            }
            if (preg_match('/^\|?\s*-{2,}\s*\|/', $t)) {
                continue;
            }
            if (preg_match('/^#+\s+(.+)$/', $t, $m)) {
                $header = trim($m[1]);
                $status = $this->detectInlineStatus($header);
                $clean = preg_replace('/[🟢🟡🔴🔵✅✔⏸🔄❌⚪]/u', '', $header);
                $clean = trim($clean);
                $items[] = ['id' => '', 'item' => $clean, 'priority' => '', 'status' => $status, 'dependsOn' => null];
                continue;
            }
            if (preg_match('/^[-*]\s+(.+)$/', $t, $m)) {
                $text = trim($m[1]);
                $status = $this->detectInlineStatus($text);
                $clean = preg_replace('/[🟢🟡🔴🔵✅✔⏸🔄❌⚪]/u', '', $text);
                $clean = trim($clean);
                $items[] = ['id' => '', 'item' => $clean, 'priority' => '', 'status' => $status, 'dependsOn' => null];
                continue;
            }
            if (str_starts_with($t, '|')) {
                $parts = explode('|', $t);
                $parts = array_map('trim', $parts);
                $parts = array_values(array_filter($parts, fn($p) => $p !== ''));
                $statusText = '';
                $itemText = '';
                foreach ($parts as $part) {
                    $status = $this->detectInlineStatus($part);
                    if ($status['progress'] > 0) {
                        $statusText = $part;
                        $itemText = $parts[0] ?? '';
                        break;
                    }
                }
                if ($statusText !== '' && $itemText !== '') {
                    $clean = preg_replace('/[🟢🟡🔴🔵✅✔⏸🔄❌⚪]/u', '', $itemText);
                    $items[] = ['id' => '', 'item' => trim($clean), 'priority' => '', 'status' => $this->detectInlineStatus($statusText), 'dependsOn' => null];
                }
            }
        }
        return $items;
    }

    private function detectInlineStatus(string $text): array
    {
        $label = 'Pendiente';
        $progress = 0;
        if (preg_match('/[🟢✅✔]/u', $text)) {
            $label = 'Completado';
            $progress = 100;
        } elseif (preg_match('/[🟡🟠🔄⏳]/u', $text)) {
            $label = 'En progreso';
            $progress = 50;
        } elseif (preg_match('/[🔴⏸❌⚠]/u', $text)) {
            $label = 'Bloqueado';
            $progress = 5;
        } elseif (preg_match('/[🔵⚪]/u', $text)) {
            $label = 'Pendiente';
            $progress = 0;
        }
        $lower = mb_strtolower($text);
        if (str_contains($lower, 'activo')) {
            $label = 'En progreso';
            $progress = 50;
        } elseif (str_contains($lower, 'pausado') || str_contains($lower, 'bloqueado')) {
            $label = 'Bloqueado';
            $progress = 5;
        }
        return ['label' => $label, 'progress' => $progress, 'raw' => $text];
    }

    private function normalizeSection(string $header): string
    {
        $header = strtolower(trim($header));
        if (str_contains($header, 'ahora') || str_contains($header, 'now')) {
            return 'now';
        }
        if (str_contains($header, 'siguiente') || str_contains($header, 'next')) {
            return 'next';
        }
        if (str_contains($header, 'futuro') || str_contains($header, 'later')) {
            return 'later';
        }
        if (str_contains($header, 'completado') || str_contains($header, 'done') || str_contains($header, 'completed')) {
            return 'done';
        }
        return 'unknown';
    }

    private function parseTaskRow(string $line): ?array
    {
        $parts = explode('|', $line);
        $parts = array_map('trim', $parts);
        $parts = array_values(array_filter($parts, fn($p) => $p !== ''));

        if (count($parts) < 4) {
            return null;
        }
        $id = $this->cleanCell($parts[0] ?? '');
        $item = $this->cleanCell($parts[1] ?? '');
        $priority = $this->cleanCell($parts[2] ?? 'P3');
        $status = $this->normalizeStatus($parts[3] ?? '');
        $dependsOn = $this->cleanCell($parts[4] ?? '');

        return [
            'id' => $id,
            'item' => $item,
            'priority' => $priority,
            'status' => $status,
            'dependsOn' => ($dependsOn === '—' || $dependsOn === '-' || $dependsOn === '') ? null : $dependsOn,
        ];
    }

    private function parseDoneRow(string $line): ?array
    {
        $parts = explode('|', $line);
        $parts = array_map('trim', $parts);
        $parts = array_values(array_filter($parts, fn($p) => $p !== ''));

        if (count($parts) < 2) {
            return null;
        }
        $item = $this->cleanCell($parts[0] ?? '');
        $instance = $this->cleanCell($parts[1] ?? '—');

        return [
            'item' => $item,
            'instance' => ($instance === '—' || $instance === '-') ? null : $instance,
        ];
    }

    private function cleanCell(string $cell): string
    {
        $cell = preg_replace('/^\|+|\|+$/', '', $cell);
        return trim($cell);
    }

    private function normalizeStatus(string $raw): array
    {
        $raw = trim($raw);
        $label = $raw;
        $progress = 0;

        if (preg_match('/[🟢✅✔]/u', $raw)) {
            $label = 'Completado';
            $progress = 100;
        } elseif (preg_match('/[🟡🟠🔄]/u', $raw)) {
            $label = 'En progreso';
            $progress = 50;
        } elseif (preg_match('/[🔴❌⏸]/u', $raw)) {
            $label = 'Pendiente';
            $progress = 0;
        } elseif (preg_match('/[🔵]/u', $raw)) {
            $label = 'Pendiente';
            $progress = 0;
        } else {
            $lower = mb_strtolower($raw);
            if (str_contains($lower, 'completado') || str_contains($lower, 'done') || str_contains($lower, 'listo')) {
                $label = 'Completado';
                $progress = 100;
            } elseif (str_contains($lower, 'progreso') || str_contains($lower, 'in progress') || str_contains($lower, 'wip')) {
                $label = 'En progreso';
                $progress = 50;
            } else {
                $label = 'Pendiente';
                $progress = 0;
            }
        }

        return ['label' => $label, 'progress' => $progress, 'raw' => $raw];
    }

    private function computeStats(array $sections): array
    {
        $total = 0;
        $done = 0;
        $inProgress = 0;
        $pending = 0;
        $blocked = 0;

        foreach (['now', 'next', 'later'] as $key) {
            foreach ($sections[$key] ?? [] as $item) {
                $total++;
                $status = $item['status']['label'] ?? 'Pendiente';
                if ($status === 'Completado') {
                    $done++;
                } elseif ($status === 'En progreso') {
                    $inProgress++;
                } elseif ($status === 'Bloqueado') {
                    $blocked++;
                } else {
                    $pending++;
                }
            }
        }

        foreach ($sections['done'] ?? [] as $item) {
            if ($item['item'] !== '—' && $item['item'] !== '') {
                $done++;
                $total++;
            }
        }

        $completionPercent = $total > 0 ? round(($done / $total) * 100) : 0;

        return [
            'total' => $total,
            'done' => $done,
            'inProgress' => $inProgress,
            'pending' => $pending,
            'blocked' => $blocked,
            'completionPercent' => $completionPercent,
        ];
    }
}
