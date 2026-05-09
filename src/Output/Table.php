<?php
namespace PhpCliToolkit\Output;
class Table {
    public function __construct(private array $headers, private array $rows) {}

    public function toString(): string {
        $colWidths = array_map('strlen', $this->headers);
        foreach ($this->rows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $colWidths[$i] = max($colWidths[$i] ?? 0, strlen((string) $cell));
            }
        }

        $separator = '+' . implode('+', array_map(fn($w) => str_repeat('-', $w + 2), $colWidths)) . '+';
        $out = $separator . "\n";

        $headerLine = '|';
        foreach ($this->headers as $i => $h) {
            $headerLine .= ' ' . str_pad($h, $colWidths[$i]) . ' |';
        }
        $out .= $headerLine . "\n";
        $out .= $separator . "\n";

        foreach ($this->rows as $row) {
            $line = '|';
            foreach ($this->headers as $i => $_) {
                $line .= ' ' . str_pad((string) ($row[$i] ?? ''), $colWidths[$i]) . ' |';
            }
            $out .= $line . "\n";
        }

        $out .= $separator . "\n";
        return $out;
    }

    public function render(): void {
        echo $this->toString();
    }
}
