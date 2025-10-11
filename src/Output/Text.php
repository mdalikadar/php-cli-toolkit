<?php
namespace PhpCliToolkit\Output;
class Text {
    public const RESET = 0;
    protected int    $charLength;
    protected array  $styles = [];

    public function __construct(protected string $text) {
        $this->charLength = strlen($this->text);
    }

    public function style(array $styles) : static {
        $this->styles = $styles;
        return $this;
    }

    public function write() : void {
        sort($this->styles);
        $styleStr = '';
        if(!empty($this->styles))
            $styleStr = "\033[".implode(';', $this->styles)."m";
        fwrite(STDOUT, $styleStr.$this->text);
    }
}