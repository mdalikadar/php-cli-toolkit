<?php
namespace PhpCliToolkit\Tests\Output;
use PhpCliToolkit\Output\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase {
    public function testSimpleTableContainsSeparatorHeaderAndRows(): void {
        $table = new Table(['Name', 'Age'], [['Alice', '30'], ['Bob', '25']]);
        $output = $table->toString();

        $this->assertStringContainsString('+-------+-----+', $output);
        $this->assertStringContainsString('| Name  | Age |', $output);
        $this->assertStringContainsString('| Alice | 30  |', $output);
        $this->assertStringContainsString('| Bob   | 25  |', $output);
    }

    public function testSingleColumnTable(): void {
        $table = new Table(['Fruit'], [['Apple'], ['Banana']]);
        $output = $table->toString();

        $this->assertStringContainsString('| Fruit  |', $output);
        $this->assertStringContainsString('| Apple  |', $output);
        $this->assertStringContainsString('| Banana |', $output);
    }

    public function testColumnWidthExpandsToLongestValue(): void {
        $table = new Table(['Col'], [['short'], ['a much longer value']]);
        $output = $table->toString();

        $this->assertStringContainsString('a much longer value', $output);
        $this->assertStringContainsString('short              ', $output);
    }

    public function testRenderEchoesOutput(): void {
        $table = new Table(['X'], [['1']]);
        $this->expectOutputString($table->toString());
        $table->render();
    }

    public function testEmptyRowsRendersSeparatorAndHeaderOnly(): void {
        $table = new Table(['ID', 'Name'], []);
        $output = $table->toString();

        $this->assertStringContainsString('| ID | Name |', $output);
        $lines = explode("\n", trim($output));
        $this->assertCount(4, $lines);
    }
}
