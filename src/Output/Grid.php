<?php
namespace PhpCliToolkit\Output;

class Grid {
    private array $cells = [];
    private int   $terminalWidth = 80;

    public function __construct(
        private int|array $columns,
        private int       $gap = 1,
    ) {
        $this->updateTerminalWidth();
    }

    public function add(Cell $cell): static {
        $this->cells[] = $cell;
        return $this;
    }

    public function cell(
        string $content,
        array  $style         = [],
        int    $paddingTop    = 0,
        int    $paddingRight  = 0,
        int    $paddingBottom = 0,
        int    $paddingLeft   = 0,
        array  $paddingStyle  = [],
    ): static {
        return $this->add(new Cell($content, $style, $paddingTop, $paddingRight, $paddingBottom, $paddingLeft, $paddingStyle));
    }

    public function render(): void {
        echo $this->toString();
    }

    public function toString(): string {
        $colWidths = $this->computeColumnWidths();
        $numCols   = count($colWidths);
        $chunks    = array_chunk($this->cells, $numCols);
        $out       = '';

        foreach ($chunks as $chunk) {
            while (count($chunk) < $numCols) {
                $chunk[] = new Cell('');
            }

            $cellLines = [];
            foreach ($chunk as $i => $cell) {
                $cellLines[$i] = $cell->lines($colWidths[$i]);
            }

            $maxLines = max(array_map('count', $cellLines));
            $gapStr   = str_repeat(' ', $this->gap);

            for ($l = 0; $l < $maxLines; $l++) {
                $parts = [];
                foreach ($chunk as $i => $cell) {
                    $parts[] = $cellLines[$i][$l] ?? $cell->emptyLine($colWidths[$i]);
                }
                $out .= implode($gapStr, $parts) . "\n";
            }
        }

        return $out;
    }

    private function computeColumnWidths(): array {
        if (is_array($this->columns)) {
            return $this->columns;
        }

        $n         = $this->columns;
        $totalGap  = $this->gap * ($n - 1);
        $available = $this->terminalWidth - $totalGap;
        $base      = (int) floor($available / $n);
        $remainder = $available - $base * $n;

        $widths = array_fill(0, $n, $base);
        $widths[$n - 1] += $remainder;

        return $widths;
    }

    private function updateTerminalWidth(): void {
        if (strpos(PHP_OS, 'WIN') !== false) {
            preg_match('|-{8,}\r?\n(?:.*?(\d+).*\r?\n.*?(\d+))|', shell_exec('mode con'), $match);
            if (!empty($w = intval($match[2] ?? 0))) {
                $this->terminalWidth = $w;
            }
        } else {
            if (!empty($w = intval(shell_exec('tput cols')))) {
                $this->terminalWidth = $w;
            }
        }
    }
}
