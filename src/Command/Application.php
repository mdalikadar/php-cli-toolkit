<?php
namespace PhpCliToolkit\Command;
use PhpCliToolkit\Exceptions\ValidationException;
class Application {
    private array $commands = [];

    public function __construct(private string $name, private string $version = '1.0.0') {}

    public function register(Command ...$commands): static {
        foreach ($commands as $command) {
            $this->commands[$command->name()] = $command;
        }
        return $this;
    }

    public function dispatch(array $args): int {
        $commandName = array_shift($args);
        if (!$commandName || in_array($commandName, ['help', '--help', '-h'])) {
            $this->printHelp();
            return 0;
        }
        if (!isset($this->commands[$commandName])) {
            echo "Command '{$commandName}' not found.\n";
            $this->printHelp();
            return 1;
        }
        try {
            return $this->commands[$commandName]->execute($args);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $err) {
                echo $err . "\n";
            }
            return 1;
        }
    }

    public function run(?array $argv = null): void {
        $args = $argv ?? $GLOBALS['argv'] ?? [];
        array_shift($args);
        exit($this->dispatch($args));
    }

    private function printHelp(): void {
        echo "{$this->name} v{$this->version}\n\nAvailable commands:\n";
        foreach ($this->commands as $name => $cmd) {
            echo "  {$name}\t{$cmd->description()}\n";
        }
    }
}
