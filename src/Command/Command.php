<?php
namespace PhpCliToolkit\Command;
use PhpCliToolkit\Arguments\Parser;
abstract class Command {
    protected Parser $parser;

    public function __construct() {
        $this->parser = new Parser();
        $this->setup();
    }

    abstract public function name(): string;

    abstract public function description(): string;

    abstract public function handle(): int;

    protected function setup(): void {}

    final public function execute(array $argv): int {
        $this->parser->parse($argv);
        return $this->handle();
    }
}
