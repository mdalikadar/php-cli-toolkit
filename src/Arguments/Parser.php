<?php
namespace PhpCliToolkit\Arguments;
use PhpCliToolkit\Exceptions\ValidationException;
class Parser {
    protected OptContainer $options;
    protected ArgContainer $arguments;

    public function __construct() {
        $this->options   = new OptContainer;
        $this->arguments = new ArgContainer;
    }

    public function registerArg(string $name, ?string $description = null, bool $isRequired = false, mixed $default = null): void {
        $this->arguments[$name] = [
            'description' => $description,
            'isRequired'  => $isRequired,
            'default'     => $default,
            'value'       => $default,
        ];
    }

    public function registerOption(string $name, ?string $description = null, bool $isRequired = false, mixed $default = null): void {
        $nameArr = explode('|', $name);
        if (isset($nameArr[1])) {
            $this->options->boundTo(...$nameArr);
        }
        $this->options[$nameArr[0]] = [
            'description' => $description,
            'isRequired'  => $isRequired,
            'default'     => $default,
            'value'       => $default,
        ];
    }

    public function parse(array $argv): void {
        $this->arguments->rewind();
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $parts  = explode('=', $arg);
                $option = preg_replace('|^--|', '', $parts[0]);
                if (!isset($this->options[$option])) continue;
                $this->options[$option]['value'] = count($parts) === 2 ? $parts[1] : true;
            } elseif (str_starts_with($arg, '-')) {
                $arg = preg_replace('|[^a-zA-Z0-9]|', '', $arg);
                if (empty($arg)) continue;
                $parts = str_split($arg);
                if (empty($parts)) continue;
                foreach ($parts as $part) {
                    if (!isset($this->options[$part])) continue;
                    $this->options[$part]['value'] = true;
                }
            } else {
                $key = $this->arguments->getIterator()->key();
                if (!is_null($key)) {
                    $this->arguments[$key]['value'] = $arg;
                    $this->arguments->getIterator()->next();
                }
            }
        }
        $this->validate();
    }

    public function run(): void {
        $args = $GLOBALS['argv'] ?? [];
        array_shift($args);
        $this->parse($args);
    }

    public function getArg(string $name): mixed {
        return $this->arguments[$name]['value'] ?? null;
    }

    public function getOption(string $name): mixed {
        return $this->options[$name]['value'] ?? null;
    }

    public function hasArg(string $name): bool {
        return $this->getArg($name) !== null;
    }

    public function hasOption(string $name): bool {
        $value = $this->getOption($name);
        return $value !== null && $value !== false;
    }

    public function getArgs(): array {
        $result = [];
        foreach ($this->arguments as $name => $arg) {
            $result[$name] = $arg['value'];
        }
        return $result;
    }

    public function getOptions(): array {
        $result = [];
        foreach ($this->options as $name => $opt) {
            $result[$name] = $opt['value'];
        }
        return $result;
    }

    public function synopsis(): array {
        $args = [];
        foreach ($this->arguments as $name => $arg) {
            $args[$name] = [
                'description' => $arg['description'],
                'isRequired'  => $arg['isRequired'],
                'default'     => $arg['default'],
            ];
        }
        $opts = [];
        foreach ($this->options as $name => $opt) {
            $opts[$name] = [
                'description' => $opt['description'],
                'isRequired'  => $opt['isRequired'],
                'default'     => $opt['default'],
            ];
        }
        return ['arguments' => $args, 'options' => $opts];
    }

    protected function validate(): void {
        $errors = [];
        foreach ($this->arguments as $name => $arg) {
            if ($arg['isRequired'] && $arg['value'] === null) {
                $errors[] = "Argument '{$name}' is required.";
            }
        }
        foreach ($this->options as $name => $opt) {
            if ($opt['isRequired'] && $opt['value'] === null) {
                $errors[] = "Option '--{$name}' is required.";
            }
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
