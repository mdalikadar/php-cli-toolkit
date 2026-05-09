<?php
namespace PhpCliToolkit\Tests\Command;
use PhpCliToolkit\Command\Command;
use PhpCliToolkit\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class EchoNameCommand extends Command {
    public string $captured = '';

    public function name(): string { return 'echo-name'; }
    public function description(): string { return 'Echoes a name'; }

    protected function setup(): void {
        $this->parser->registerArg('name');
    }

    public function handle(): int {
        $this->captured = (string) $this->parser->getArg('name');
        return 0;
    }
}

class RequiredArgCommand extends Command {
    public function name(): string { return 'required'; }
    public function description(): string { return 'Requires an arg'; }

    protected function setup(): void {
        $this->parser->registerArg('target', null, true);
    }

    public function handle(): int {
        return 0;
    }
}

class ExitCodeCommand extends Command {
    public function name(): string { return 'exit-code'; }
    public function description(): string { return 'Returns code 42'; }
    public function handle(): int { return 42; }
}

class CommandTest extends TestCase {
    public function testExecuteParsesArgAndHandleReceivesIt(): void {
        $cmd = new EchoNameCommand();
        $result = $cmd->execute(['Alice']);
        $this->assertEquals(0, $result);
        $this->assertEquals('Alice', $cmd->captured);
    }

    public function testExecuteThrowsValidationExceptionWhenRequiredArgMissing(): void {
        $cmd = new RequiredArgCommand();
        $this->expectException(ValidationException::class);
        $cmd->execute([]);
    }

    public function testHandleReturnValuePropagatedFromExecute(): void {
        $cmd = new ExitCodeCommand();
        $result = $cmd->execute([]);
        $this->assertEquals(42, $result);
    }
}
