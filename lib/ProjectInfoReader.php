<?php

class ProjectInfoReader
{
    private string $projectPath;

    public function __construct(string $projectPath)
    {
        $this->projectPath = rtrim($projectPath, '\\/');
    }

    public function read(): array
    {
        return [
            'version' => $this->readVersion(),
            'diligencia' => $this->readDiligenciaVersion(),
            'stack' => $this->readStack(),
            'adrs' => $this->readAdrSummary(),
            'bugs' => $this->readBugs(),
            'incidents' => $this->readIncidents(),
            'docs' => $this->checkAvailableDocs(),
        ];
    }

    private function filePath(string $name): string
    {
        return $this->projectPath . '\\' . ltrim($name, '\\/');
    }

    private function checkAvailableDocs(): array
    {
        $files = ['ROADMAP.md', 'CHECKLIST.md', 'CHANGELOG.md', 'DILIGENCIA.md',
                  'doc/arch/ADR_SUMMARY.md', 'doc/arch/SISTEMA.md',
                  'doc/arch/bugs.md', 'doc/arch/incidentes.md',
                  'AGENTS.md', '.opencode/HARNESS.md'];
        $result = [];
        foreach ($files as $f) {
            $result[$f] = is_file($this->filePath($f));
        }
        return $result;
    }

    private function readVersion(): ?string
    {
        $path = $this->filePath('CHANGELOG.md');
        if (!is_file($path)) {
            return null;
        }
        $fh = fopen($path, 'r');
        if (!$fh) {
            return null;
        }
        $version = null;
        while (($line = fgets($fh)) !== false) {
            $t = trim($line);
            if (preg_match('/^##\s*\[?v?(\d+\.\d+(?:\.\d+)?)\]?\s*(?:-|–)?\s*(\d{4}-\d{2}-\d{2})?/', $t, $m)) {
                $version = 'v' . $m[1];
                if (!empty($m[2])) {
                    $version .= ' (' . $m[2] . ')';
                }
                break;
            }
        }
        fclose($fh);
        return $version;
    }

    private function readDiligenciaVersion(): ?string
    {
        $path = $this->filePath('DILIGENCIA.md');
        if (!is_file($path)) {
            return null;
        }
        $fh = fopen($path, 'r');
        if (!$fh) {
            return null;
        }
        $version = null;
        while (($line = fgets($fh)) !== false) {
            $t = trim($line);
            if (preg_match('/^#\s*Diligencia\s*v?(\d+\.\d+(?:\.\d+)?)/i', $t, $m)) {
                $version = 'v' . $m[1];
                break;
            }
        }
        fclose($fh);
        return $version;
    }

    private function readStack(): ?string
    {
        $paths = [
            $this->filePath('doc/arch/SISTEMA.md'),
            $this->filePath('doc/arch/ESTRUCTURA.md'),
            $this->filePath('ROADMAP.md'),
        ];
        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }
            $content = file_get_contents($path);
            if ($content === false) {
                continue;
            }
            $stackCandidates = [];
            $techs = ['Node.js', 'Express', 'React', 'Vue', 'Angular', 'PHP', 'Laravel',
                      'SQLite', 'MySQL', 'PostgreSQL', 'MongoDB', 'Docker', 'Python',
                      'Flask', 'Django', 'DeepSeek', 'Vite', 'Next.js', 'Nuxt'];
            foreach ($techs as $tech) {
                if (stripos($content, $tech) !== false) {
                    $stackCandidates[] = $tech;
                }
            }
            if (count($stackCandidates) > 0) {
                return implode(' + ', array_slice($stackCandidates, 0, 5));
            }
        }
        return null;
    }

    private function readAdrSummary(): ?array
    {
        $paths = [
            $this->filePath('doc/arch/ADR_SUMMARY.md'),
            $this->filePath('doc/arch/ADR.md'),
        ];
        $result = ['total' => 0, 'active' => 0];

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }
            $content = file_get_contents($path);
            if ($content === false) {
                continue;
            }

            if (preg_match('/\*\*Total ADRs\*\*\s*\|\s*(\d+)/i', $content, $m)) {
                $result['total'] = (int)$m[1];
            }
            if (preg_match('/\*\*Aceptados\*\*\s*\|\s*(\d+)/i', $content, $m)) {
                $result['active'] = (int)$m[1];
            }

            preg_match_all('/ADR-\d+/i', $content, $adrMatches);
            if (count($adrMatches[0]) > $result['total']) {
                $result['total'] = count($adrMatches[0]);
                $result['active'] = count($adrMatches[0]);
            }
        }
        return $result['total'] > 0 ? $result : null;
    }

    private function readBugs(): ?array
    {
        $path = $this->filePath('doc/arch/bugs.md');
        if (!is_file($path)) {
            return $this->readBugsFromRoot();
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $p1Count = preg_match_all('/P1[—–-]*/i', $content);
        $p2Count = preg_match_all('/P2[—–-]*/i', $content);
        $p3Count = preg_match_all('/P3[—–-]*/i', $content);

        preg_match_all('/###\s+B-\d+/', $content, $bugMatches);
        $total = count($bugMatches[0]);

        return [
            'total' => $total,
            'p1' => min($p1Count, $total),
            'p2' => min($p2Count, $total),
            'p3' => min($p3Count, $total),
        ];
    }

    private function readBugsFromRoot(): ?array
    {
        $path = $this->filePath('bugs.md');
        if (!is_file($path)) {
            return null;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }
        $validLines = preg_match_all('/###\s+(B-\d+|bug\s+\d+)/i', $content, $m);
        return ['total' => $validLines, 'p1' => 0, 'p2' => 0, 'p3' => 0];
    }

    private function readIncidents(): ?array
    {
        $path = $this->filePath('doc/arch/incidentes.md');
        if (!is_file($path)) {
            return null;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }
        preg_match_all('/###\s+I-\d+/', $content, $m);
        return ['total' => count($m[0])];
    }
}
