<?php

require_once __DIR__ . '/RoadmapParser.php';
require_once __DIR__ . '/ProjectInfoReader.php';

class Scanner
{
    private array $scanDirs;
    private int $depth;
    private array $extraPaths;

    public function __construct(array $scanDirs, int $depth = 1, array $extraPaths = [])
    {
        $this->scanDirs = $scanDirs;
        $this->depth = max(1, $depth);
        $this->extraPaths = $extraPaths;
    }

    public function scan(): array
    {
        $projects = [];

        foreach ($this->extraPaths as $ep) {
            $resolved = $this->resolvePath($ep);
            if ($resolved === null) {
                continue;
            }
            $this->detectProject($resolved, $projects, true);
            if (!in_array($resolved, $this->scanDirs)) {
                $entries = $this->getDirectories($resolved, 1, 0);
                foreach ($entries as $entry) {
                    $this->detectProject($entry, $projects, true);
                }
            }
        }

        foreach ($this->scanDirs as $dir) {
            $expanded = $this->resolvePath($dir);
            if (!$expanded || !is_dir($expanded)) {
                continue;
            }
            $entries = $this->getDirectories($expanded, $this->depth, 0);
            foreach ($entries as $entry) {
                $this->detectProject($entry, $projects, true);
            }
        }

        return $projects;
    }

    private function detectProject(string $dirPath, array &$projects, bool $skipDuplicates = false): void
    {
        $rmPath = $dirPath . '\ROADMAP.md';
        if (!is_file($rmPath)) {
            return;
        }
        if ($skipDuplicates) {
            foreach ($projects as $p) {
                if ($p['path'] === $dirPath) {
                    return;
                }
            }
        }

        $parser = new RoadmapParser();
        $roadmap = $parser->parse($rmPath);
        $name = $roadmap['projectName'] ?? basename($dirPath);

        $reader = new ProjectInfoReader($dirPath);
        $info = $reader->read();

        $projects[] = [
            'name' => $name,
            'path' => $dirPath,
            'roadmapPath' => $rmPath,
            'version' => $info['version'],
            'diligencia' => $info['diligencia'],
            'stack' => $info['stack'],
            'adrs' => $info['adrs'],
            'bugs' => $info['bugs'],
            'incidents' => $info['incidents'],
            'stats' => $roadmap['stats'] ?? [
                'total' => 0, 'done' => 0, 'inProgress' => 0, 'pending' => 0, 'blocked' => 0, 'completionPercent' => 0,
            ],
            'format' => $roadmap['format'] ?? 'standard',
            'docs' => $info['docs'],
        ];
    }

    public function isPathAllowed(string $path): bool
    {
        $real = realpath($path);
        if ($real === false) {
            return false;
        }
        foreach ($this->scanDirs as $allowed) {
            $allowedReal = realpath($this->resolvePath($allowed));
            if ($allowedReal !== false && str_starts_with($real, $allowedReal)) {
                return true;
            }
        }
        return false;
    }

    private function resolvePath(string $path): ?string
    {
        $path = str_replace('/', '\\', $path);
        $real = realpath($path);
        return $real !== false ? $real : null;
    }

    private function getDirectories(string $root, int $maxDepth, int $currentDepth): array
    {
        $result = [];
        if ($currentDepth > $maxDepth) {
            return $result;
        }
        $items = scandir($root);
        if ($items === false) {
            return $result;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $fullPath = $root . '\\' . $item;
            if (is_dir($fullPath)) {
                $result[] = $fullPath;
                if ($currentDepth < $maxDepth) {
                    $sub = $this->getDirectories($fullPath, $maxDepth, $currentDepth + 1);
                    $result = array_merge($result, $sub);
                }
            }
        }
        return $result;
    }
}
