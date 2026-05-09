<?php
namespace PhpCliToolkit\Output;

class Cell {
    public function __construct(
        private string $content,
        private array  $style         = [],
        private int    $paddingTop    = 0,
        private int    $paddingRight  = 0,
        private int    $paddingBottom = 0,
        private int    $paddingLeft   = 0,
        private array  $paddingStyle  = [],
    ) {}

    public function lines(int $width): array {
        $innerWidth = max(1, $width - $this->paddingLeft - $this->paddingRight);
        $left  = str_repeat(' ', $this->paddingLeft);
        $right = str_repeat(' ', $this->paddingRight);

        $wrapped = wordwrap($this->content, $innerWidth, "\n", true);
        $contentLines = $wrapped === '' ? [''] : explode("\n", $wrapped);

        $result = [];

        for ($i = 0; $i < $this->paddingTop; $i++) {
            $result[] = $this->applyStyle($this->paddingStyle, str_repeat(' ', $width));
        }

        foreach ($contentLines as $line) {
            $padded = $left . str_pad($line, $innerWidth) . $right;
            $result[] = $this->applyStyle($this->style, $padded);
        }

        for ($i = 0; $i < $this->paddingBottom; $i++) {
            $result[] = $this->applyStyle($this->paddingStyle, str_repeat(' ', $width));
        }

        return $result;
    }

    public function emptyLine(int $width): string {
        return $this->applyStyle($this->style, str_repeat(' ', $width));
    }

    private function applyStyle(array $style, string $text): string {
        if (empty($style)) {
            return $text;
        }
        return "\033[" . implode(';', $style) . 'm' . $text . "\033[0m";
    }
}
