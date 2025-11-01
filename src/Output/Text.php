<?php
namespace PhpCliToolkit\Output;
use PhpCliToolkit\Exceptions\CliException;
class Text {
    public const TOP    = 0;
    public const BOTTOM = 1;
    public const LEFT   = 2;
    public const RIGHT  = 3;
    public const ALL    = 4;
    protected int    $charLength;
    protected int    $terminalWidth = 80;
    protected array  $lines = [];
    protected array  $styles = [];
    protected array  $spaces = [
        [0, ' ', []],
        [0, ' ', []],
        [0, ' ', []],
        [0, ' ', []],
    ];
    protected array  $edgeSpaces = [
        [0, ' ', []],
        [0, ' ', []],
        [0, ' ', []],
        [0, ' ', []],
    ];

    public function __construct(protected string $text) {
        $this->charLength = strlen($this->text);
        $this->updateTerminalWidth();
    }

    public function space(int $position, int $space,  array $style, string $char = ' ') : static {
        $this->_space('spaces', $position, $space, $style, $char);
        return $this;
    }

    public function edgeSpace(int $position, int $space,  array $style, string $char = ' ') : static {
        $this->_space('edgeSpaces', $position, $space, $style, $char);
        return $this;
    }

    protected function _space(string $prop, int $position, int $space,  array $style, string $char = ' ') : static {
        if (!in_array($prop, ['spaces', 'edgeSpaces'], true)) {
            throw new CliException(sprintf('Invalid Property Name: %s', $prop));
        }

        if (!in_array($position, range(0, 4), true)) {
            throw new CliException(sprintf('Invalid Position: %d', $position));
        }

        if ($position === 4) {
            foreach(range(0, 3) as $i) {
                $this->{$prop}[$i] = [$space, $char, $style];
            }
        }
        else {
            $this->{$prop}[$position] = [$space, $char, $style];
        }
        return $this;
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
        foreach(($eTop[0] > 0 ? range(1,$eTop[0]) : []) as $lt) {
            $this->lines[] = (
                $this->apply_style(
                    $eTop[2],
                    str_repeat($eTop[1], $this->terminalWidth )
                )
            );
        }

        foreach(($top[0] > 0 ? range(1,$top[0]) : []) as $lt) {
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
            $bg = [];
            if(!Styles\BgColors::containsBg($this->styles)) {
                foreach(['left','right','top','bottom'] as $direction) {
                    if(isset($$direction[2]) && Styles\BgColors::containsBg($$direction[2], $bg)) {
                        // print_r($bg);
                        $this->styles[] = current($bg);
                    }
                }
            }
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

        foreach(($bottom[0] > 0 ? range(1,$bottom[0]) : []) as $lt) {
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

        foreach(($eBottom[0] > 0 ? range(1,$eBottom[0]) : []) as $lt) {
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
