<?php
namespace PhpCliToolkit\Output;
class ProgressBar {
    private int $current = 0;
    private float $startTime;

    public function __construct(private int $total, private int $width = 40) {
        $this->startTime = microtime(true);
    }

    public function advance(int $step = 1): void {
        $this->current = min($this->current + $step, $this->total);
        $this->render();
    }

    public function finish(): void {
        $this->current = $this->total;
        $this->render();
        fwrite(STDOUT, "\n");
    }

    private function render(): void {
        $pct    = $this->total > 0 ? $this->current / $this->total : 1;
        $filled = (int) round($pct * $this->width);
        $bar    = str_repeat('=', max(0, $filled - 1))
                . ($filled > 0 ? '>' : '')
                . str_repeat('-', $this->width - $filled);
        $elapsed = microtime(true) - $this->startTime;
        $eta     = $pct > 0 && $pct < 1 ? ($elapsed / $pct) - $elapsed : 0;
        fwrite(STDOUT, sprintf(
            "\r[%s] %d%% (%d/%d) ETA:%ds  ",
            $bar,
            (int) ($pct * 100),
            $this->current,
            $this->total,
            (int) $eta
        ));
    }
}
