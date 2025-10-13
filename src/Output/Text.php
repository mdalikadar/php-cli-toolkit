<?php
namespace PhpCliToolkit\Output;
class Text {
    public const RESET = 0;
    protected int    $charLength;
    protected int    $terminalWidth = 80;
    protected array  $lines = [];
    protected array  $styles = [];
    protected array  $spaces = [
        [10, ' ', [42]],
        [10, ' ', [42]],
        [10, ' ', [42]],
        [10, ' ', [42]],
    ];
    protected array  $edgeSpaces = [
        [0, ' ', []],
        [0, ' ', []],
        [0, ' ', []],
        [0, ' ', []],
    ];

    public function __construct(protected string $text) {
        $this->charLength = strlen($this->text);
    }

    public function style(array $styles) : static {
        $this->styles = $styles;
        return $this;
    }

    public function apply_spaces() : static {
        [$top, $bottom, $left, $right]     = $this->spaces;
        [$eTop, $eBottom, $eLeft, $eRight] = $this->edgeSpaces;
        sort($this->styles);
        $lineWidth = $this->terminalWidth - $eLeft[0] - $eRight[0] - $left[0] - $right[0];
        $lines = explode(
            "\n", 
            wordwrap(
                str_pad($this->text, $lineWidth), 
                $lineWidth, 
                "\n", 
                true
            )
        );
        foreach(range(1,$eTop[0]) as $lt) {
            $this->lines[] = $this->apply_style(
                $eTop[2],
                str_repeat($eTop[1], $this->terminalWidth)
            );
        }

        foreach(range(1,$top[0]) as $lt) {
            $this->lines[] = $this->apply_style(
                $top[2],
                str_repeat($top[1], $this->terminalWidth)
            );
        }

        foreach($lines as $line) {
            $this->lines[] = (
                $this->apply_style(
                    $eLeft[2],
                    str_repeat($eLeft[1], $eLeft[0])
                ).
                $this->apply_style(
                    $left[2],
                    str_repeat($left[1], $left[0])
                ).
                $this->apply_style(
                    $this->styles,
                    $line
                ).
                $this->apply_style(
                    $right[2],
                    str_repeat($right[1], $right[0])
                ).
                $this->apply_style(
                    $eRight[2],
                    str_repeat($eRight[1], $eRight[0])
                )
            );
        }

        foreach(range(1,$bottom[0]) as $lt) {
            $this->lines[] = $this->apply_style(
                $bottom[2],
                str_repeat($bottom[1], $this->terminalWidth)
            );
        }

        foreach(range(1,$eBottom[0]) as $lt) {
            $this->lines[] = $this->apply_style(
                $eBottom[2],
                str_repeat($eBottom[1], $this->terminalWidth)
            );
        }
        return $this;
    }

    public function apply_style(array $styles, string $text) : string {
        return (
            (!empty($this->styles) ? "\033[".implode(';', $styles)."m" : '').
            $text.
            "\033[0m"
        );
    }

    public function write() : void {
        $this->apply_spaces();
        foreach($this->lines as $line) {
            fwrite(STDOUT, $line."\n");
        }
    }
}