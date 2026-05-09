<?php
namespace PhpCliToolkit\Tests\Arguments;
use PhpCliToolkit\Arguments\Parser;
use PhpCliToolkit\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase {
    public function testPositionalArgsInOrder(): void {
        $p = new Parser();
        $p->registerArg('name');
        $p->registerArg('age');
        $p->parse(['Alice', '30']);
        $this->assertEquals('Alice', $p->getArg('name'));
        $this->assertEquals('30', $p->getArg('age'));
    }

    public function testLongFlagSetsTrueWhenNoValue(): void {
        $p = new Parser();
        $p->registerOption('verbose');
        $p->parse(['--verbose']);
        $this->assertTrue($p->getOption('verbose'));
    }

    public function testLongOptionWithValue(): void {
        $p = new Parser();
        $p->registerOption('output');
        $p->parse(['--output=file.txt']);
        $this->assertEquals('file.txt', $p->getOption('output'));
    }

    public function testShortFlag(): void {
        $p = new Parser();
        $p->registerOption('f');
        $p->parse(['-f']);
        $this->assertTrue($p->getOption('f'));
    }

    public function testCombinedShortFlags(): void {
        $p = new Parser();
        $p->registerOption('a');
        $p->registerOption('b');
        $p->registerOption('c');
        $p->parse(['-abc']);
        $this->assertTrue($p->getOption('a'));
        $this->assertTrue($p->getOption('b'));
        $this->assertTrue($p->getOption('c'));
    }

    public function testOptionAliasLongForm(): void {
        $p = new Parser();
        $p->registerOption('verbose|v');
        $p->parse(['--verbose']);
        $this->assertTrue($p->getOption('verbose'));
    }

    public function testOptionAliasShortForm(): void {
        $p = new Parser();
        $p->registerOption('verbose|v');
        $p->parse(['-v']);
        $this->assertTrue($p->getOption('verbose'));
        $this->assertTrue($p->hasOption('verbose'));
    }

    public function testDefaultArgValueSurvivesWhenNotProvided(): void {
        $p = new Parser();
        $p->registerArg('name', null, false, 'world');
        $p->parse([]);
        $this->assertEquals('world', $p->getArg('name'));
    }

    public function testDefaultOptionValueSurvivesWhenNotProvided(): void {
        $p = new Parser();
        $p->registerOption('format', null, false, 'json');
        $p->parse([]);
        $this->assertEquals('json', $p->getOption('format'));
    }

    public function testGetArgsReturnsAllArgValues(): void {
        $p = new Parser();
        $p->registerArg('a');
        $p->registerArg('b');
        $p->parse(['foo', 'bar']);
        $this->assertEquals(['a' => 'foo', 'b' => 'bar'], $p->getArgs());
    }

    public function testGetOptionsReturnsAllOptionValues(): void {
        $p = new Parser();
        $p->registerOption('verbose');
        $p->registerOption('output');
        $p->parse(['--verbose', '--output=file.txt']);
        $opts = $p->getOptions();
        $this->assertTrue($opts['verbose']);
        $this->assertEquals('file.txt', $opts['output']);
    }

    public function testHasArgTrueWhenSet(): void {
        $p = new Parser();
        $p->registerArg('name');
        $p->parse(['Alice']);
        $this->assertTrue($p->hasArg('name'));
    }

    public function testHasArgFalseWhenNotSet(): void {
        $p = new Parser();
        $p->registerArg('name');
        $p->parse([]);
        $this->assertFalse($p->hasArg('name'));
    }

    public function testHasOptionTrueWhenSet(): void {
        $p = new Parser();
        $p->registerOption('verbose');
        $p->parse(['--verbose']);
        $this->assertTrue($p->hasOption('verbose'));
    }

    public function testHasOptionFalseWhenNotSet(): void {
        $p = new Parser();
        $p->registerOption('verbose');
        $p->parse([]);
        $this->assertFalse($p->hasOption('verbose'));
    }

    public function testValidationExceptionWhenRequiredArgMissing(): void {
        $p = new Parser();
        $p->registerArg('name', null, true);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Argument 'name' is required.");
        $p->parse([]);
    }

    public function testValidationExceptionWhenRequiredOptionMissing(): void {
        $p = new Parser();
        $p->registerOption('token', null, true);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Option '--token' is required.");
        $p->parse([]);
    }

    public function testMultipleValidationErrorsCollectedInOneException(): void {
        $p = new Parser();
        $p->registerArg('name', null, true);
        $p->registerArg('age', null, true);
        $p->registerOption('token', null, true);
        try {
            $p->parse([]);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertCount(3, $e->errors());
        }
    }

    public function testSynopsisReturnsCorrectStructure(): void {
        $p = new Parser();
        $p->registerArg('name', 'Your name', true, 'anonymous');
        $p->registerOption('verbose', 'Enable verbose mode');
        $synopsis = $p->synopsis();
        $this->assertArrayHasKey('arguments', $synopsis);
        $this->assertArrayHasKey('options', $synopsis);
        $this->assertEquals('Your name', $synopsis['arguments']['name']['description']);
        $this->assertTrue($synopsis['arguments']['name']['isRequired']);
        $this->assertEquals('anonymous', $synopsis['arguments']['name']['default']);
        $this->assertEquals('Enable verbose mode', $synopsis['options']['verbose']['description']);
    }

    public function testUnregisteredOptionsAndArgsAreIgnored(): void {
        $p = new Parser();
        $p->parse(['--unknown=x', 'ignored']);
        $this->assertEquals([], $p->getArgs());
        $this->assertEquals([], $p->getOptions());
    }
}
