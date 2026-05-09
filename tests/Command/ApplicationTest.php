<?php
namespace PhpCliToolkit\Tests\Command;
use PhpCliToolkit\Command\Application;
use PhpCliToolkit\Command\Command;
use PHPUnit\Framework\TestCase;

class GreetCommand extends Command {
    public string $received = '';

    public function name(): string { return 'greet'; }
    public function description(): string { return 'Greets someone'; }

    protected function setup(): void {
        $this->parser->registerArg('who');
    }

    public function handle(): int {
        $this->received = (string) $this->parser->getArg('who');
        return 0;
    }
}

class RequiredCommand extends Command {
    public function name(): string { return 'req'; }
    public function description(): string { return 'Has required arg'; }

    protected function setup(): void {
        $this->parser->registerArg('name', null, true);
    }

    public function handle(): int { return 0; }
}

class ApplicationTest extends TestCase {
    public function testRegisterStoresCommand(): void {
        $app = new Application('App');
        $cmd = new GreetCommand();
        $result = $app->register($cmd);
        $this->assertSame($app, $result);
    }

    public function testDispatchHelpReturnsZeroAndPrintsName(): void {
        $app = new Application('MyApp', '3.0.0');
        ob_start();
        $code = $app->dispatch(['help']);
        $output = ob_get_clean();
        $this->assertEquals(0, $code);
        $this->assertStringContainsString('MyApp v3.0.0', $output);
    }

    public function testDispatchNoCommandPrintsHelp(): void {
        $app = new Application('MyApp', '1.0.0');
        ob_start();
        $code = $app->dispatch([]);
        $output = ob_get_clean();
        $this->assertEquals(0, $code);
        $this->assertStringContainsString('MyApp v1.0.0', $output);
    }

    public function testDispatchUnknownCommandReturnsOne(): void {
        $app = new Application('MyApp');
        ob_start();
        $code = $app->dispatch(['unknown-cmd']);
        $output = ob_get_clean();
        $this->assertEquals(1, $code);
        $this->assertStringContainsString("Command 'unknown-cmd' not found.", $output);
    }

    public function testDispatchDelegatesToCorrectCommand(): void {
        $cmd = new GreetCommand();
        $app = new Application('MyApp');
        $app->register($cmd);
        $code = $app->dispatch(['greet', 'World']);
        $this->assertEquals(0, $code);
        $this->assertEquals('World', $cmd->received);
    }

    public function testDispatchCatchesValidationExceptionAndReturnsOne(): void {
        $cmd = new RequiredCommand();
        $app = new Application('MyApp');
        $app->register($cmd);
        ob_start();
        $code = $app->dispatch(['req']);
        $output = ob_get_clean();
        $this->assertEquals(1, $code);
        $this->assertStringContainsString("Argument 'name' is required.", $output);
    }

    public function testDispatchHelpListsRegisteredCommands(): void {
        $app = new Application('MyApp');
        $app->register(new GreetCommand());
        ob_start();
        $app->dispatch(['help']);
        $output = ob_get_clean();
        $this->assertStringContainsString('greet', $output);
        $this->assertStringContainsString('Greets someone', $output);
    }
}
