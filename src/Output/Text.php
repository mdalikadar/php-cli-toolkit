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
        [2, ' ', [43]],
        [2, ' ', [43]],
        [2, ' ', [43]],
        [2, ' ', [43]],
    ];

    public function __construct(protected string $text) {
        $this->charLength = strlen($this->text);
        $this->updateTerminalWidth();
    }

    public function updateTerminalWidth() : void {
        if(strpos(PHP_OS, 'WIN') !== false) {
            preg_match(
                '|-{8,}\r?\n(?:.*?(\d+).*\r?\n.*?(\d+))|', 
                shell_exec('mode con'), 
                $match
            );
            if(!empty($w = intval($match[2]))){
                $this->terminalWidth = $w;
            }
        }
        else {
            if(!empty($w = intval(shell_exec('tput cols')))){
                $this->terminalWidth = $w;
            }
        }
    }

    public function style(array $styles) : static {
        $this->styles = $styles;
        return $this;
    }

    public function apply_spaces() : static {
        [$top, $bottom, $left, $right]     = $this->spaces;
        [$eTop, $eBottom, $eLeft, $eRight] = $this->edgeSpaces;
        sort($this->styles);
        $elc = $eLeft[0] + $eRight[0] + $left[0] + $right[0];
        $lineWidth = $this->terminalWidth - $elc;
        $lines = explode(
            "\n", 
            wordwrap(
                str_pad($this->text, $lineWidth), 
                $lineWidth, 
                "\n", 
                true
            )
        );
        // var_dump($this->terminalWidth , $elc,$lines, $lineWidth);exit;
        foreach(range(1,$eTop[0]) as $lt) {
            $this->lines[] = (
                $this->apply_style(
                    $eTop[2],
                    str_repeat($eTop[1], $this->terminalWidth )
                )
            );
        }

        foreach(range(1,$top[0]) as $lt) {
            $this->lines[] = (
                $this->add_spaces($eLeft).
                $this->add_spaces($left).
                $this->apply_style(
                    $top[2],
                    str_repeat($top[1], $lineWidth)
                ).
                $this->add_spaces($right).
                $this->add_spaces($eRight)
            );
        }

        foreach($lines as $line) {
            $this->lines[] = (
                $this->add_spaces($eLeft).
                $this->add_spaces($left).
                $this->apply_style(
                    $this->styles,
                    $line
                ).
                $this->add_spaces($right).
                $this->add_spaces($eRight)
            );
        }

        foreach(range(1,$bottom[0]) as $lt) {
            $this->lines[] = (
                $this->add_spaces($eLeft).
                $this->add_spaces($left).
                $this->apply_style(
                    $bottom[2],
                    str_repeat($bottom[1], $lineWidth)
                ).
                $this->add_spaces($right).
                $this->add_spaces($eRight)
            );
        }

        foreach(range(1,$eBottom[0]) as $lt) {
            $this->lines[] = (
                $this->apply_style(
                    $eBottom[2],
                    str_repeat($eBottom[1], $this->terminalWidth)
                )
            );
        }
        return $this;
    }

    public function add_spaces(array $space) : string {
        return $this->apply_style(
                    $space[2],
                    str_repeat($space[1], $space[0])
                );
    }

    public function apply_style(array $styles, string $text) : string {
        return (
            (!empty($styles) ? "\033[".implode(';', $styles)."m" : '').
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
