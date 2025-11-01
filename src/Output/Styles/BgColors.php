<?php
namespace PhpCliToolkit\Output\Styles;
class BgColors {
    public const BLACK   = 40;
    public const RED     = 41;
    public const GREEN   = 42;
    public const YELLOW  = 43;
    public const BLUE    = 44;
    public const MAGENTA = 45;
    public const CYAN    = 46;
    public const WHITE   = 47;

    public const BRIGHT_BLACK   = 100;
    public const BRIGHT_RED     = 101;
    public const BRIGHT_GREEN   = 102;
    public const BRIGHT_YELLOW  = 103;
    public const BRIGHT_BLUE    = 104;
    public const BRIGHT_MAGENTA = 105;
    public const BRIGHT_CYAN    = 106;
    public const BRIGHT_WHITE   = 107;

    public static function containsBg(array $styles, array &$bg = []) : bool {
        $bg = (array) array_intersect(
            array_merge(
                range(40, 47),
                range(100, 107)
            ),
            array_map('intval', $styles)
        );
        return count($bg) > 0;
    }
}