<?php
namespace PhpCliToolkit\Input;
class Prompt {
    public static function ask(string $question, ?string $default = null): string {
        $hint = $default !== null ? " [{$default}]" : '';
        echo $question . $hint . ': ';
        $input = trim((string) fgets(STDIN));
        return $input !== '' ? $input : (string) $default;
    }

    public static function confirm(string $question, bool $default = false): bool {
        $hint = $default ? '[Y/n]' : '[y/N]';
        echo $question . ' ' . $hint . ': ';
        $input = strtolower(trim((string) fgets(STDIN)));
        if ($input === '') return $default;
        return in_array($input, ['y', 'yes'], true);
    }

    public static function secret(string $question): string {
        echo $question . ': ';
        if (strpos(PHP_OS, 'WIN') !== false) {
            return trim((string) fgets(STDIN));
        }
        shell_exec('stty -echo');
        $input = trim((string) fgets(STDIN));
        shell_exec('stty echo');
        echo "\n";
        return $input;
    }

    public static function select(string $question, array $choices): string {
        echo $question . "\n";
        foreach (array_values($choices) as $i => $choice) {
            echo '  [' . ($i + 1) . '] ' . $choice . "\n";
        }
        echo 'Choice: ';
        $input = trim((string) fgets(STDIN));
        $index = (int) $input - 1;
        $values = array_values($choices);
        if (!isset($values[$index])) {
            echo "Invalid selection. Please try again.\n";
            return static::select($question, $choices);
        }
        return $values[$index];
    }
}
