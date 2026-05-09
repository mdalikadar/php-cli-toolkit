<?php
namespace PhpCliToolkit\Tests\Output;

use PHPUnit\Framework\TestCase;
use PhpCliToolkit\Output\Cell;
use PhpCliToolkit\Output\Grid;

class GridTest extends TestCase {
    private function stripAnsi(string $text): string {
        return preg_replace('/\033\[[0-9;]*m/', '', $text);
    }

    public function testThreeEqualColumnsRenderSideBySide(): void {
        $grid = (new Grid([20, 20, 20], gap: 1))
            ->cell('Alpha')
            ->cell('Beta')
            ->cell('Gamma');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertCount(1, $lines);
        $this->assertStringContainsString('Alpha', $lines[0]);
        $this->assertStringContainsString('Beta',  $lines[0]);
        $this->assertStringContainsString('Gamma', $lines[0]);
    }

    public function testExplicitColumnWidthsUsedDirectly(): void {
        $grid = (new Grid([10, 30], gap: 0))
            ->cell('A')
            ->cell('B');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertCount(1, $lines);
        $this->assertEquals(40, strlen($this->stripAnsi($lines[0])));
    }

    public function testSingleColumnUsesFullWidth(): void {
        $grid = (new Grid([40], gap: 0))
            ->cell('Hello world');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        foreach ($lines as $line) {
            $this->assertEquals(40, strlen($this->stripAnsi($line)));
        }
    }

    public function testShortCellPaddedWhenNeighbourWraps(): void {
        $grid = (new Grid([10, 10], gap: 0))
            ->cell('short')
            ->cell('this is a long text that will wrap');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertGreaterThan(1, count($lines));
        foreach ($lines as $line) {
            $this->assertEquals(20, strlen($this->stripAnsi($line)));
        }
    }

    public function testCellShorthandAddsCellToGrid(): void {
        $grid = (new Grid([10, 10], gap: 0))
            ->cell('Foo')
            ->cell('Bar');

        $out = $grid->toString();
        $this->assertStringContainsString('Foo', $out);
        $this->assertStringContainsString('Bar', $out);
    }

    public function testAddCellObjectWorks(): void {
        $grid = (new Grid([10, 10], gap: 0))
            ->add(new Cell('One'))
            ->add(new Cell('Two'));

        $out = $grid->toString();
        $this->assertStringContainsString('One', $out);
        $this->assertStringContainsString('Two', $out);
    }

    public function testPaddingTopAddsBlankLines(): void {
        $grid = (new Grid([20], gap: 0))
            ->add(new Cell('Text', [], paddingTop: 2));

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertCount(3, $lines);
        $this->assertEquals(str_repeat(' ', 20), $this->stripAnsi($lines[0]));
        $this->assertEquals(str_repeat(' ', 20), $this->stripAnsi($lines[1]));
        $this->assertStringContainsString('Text', $this->stripAnsi($lines[2]));
    }

    public function testPaddingBottomAddsBlankLines(): void {
        $grid = (new Grid([20], gap: 0))
            ->add(new Cell('Text', [], paddingBottom: 2));

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertCount(3, $lines);
        $this->assertStringContainsString('Text', $this->stripAnsi($lines[0]));
        $this->assertEquals(str_repeat(' ', 20), $this->stripAnsi($lines[1]));
        $this->assertEquals(str_repeat(' ', 20), $this->stripAnsi($lines[2]));
    }

    public function testPaddingLeftAndRightReduceInnerWidth(): void {
        $grid = (new Grid([10], gap: 0))
            ->add(new Cell('Hi', [], paddingLeft: 2, paddingRight: 2));

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $plain = $this->stripAnsi($lines[0]);
        $this->assertEquals(10, strlen($plain));
        $this->assertStringStartsWith('  ', $plain);
        $this->assertStringEndsWith('  ', $plain);
    }

    public function testGapInsertsSpacesBetweenColumns(): void {
        $grid = (new Grid([5, 5], gap: 3))
            ->cell('A')
            ->cell('B');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertEquals(13, strlen($this->stripAnsi($lines[0])));
    }

    public function testRenderProducesSameOutputAsToString(): void {
        $grid = (new Grid([15, 15], gap: 1))
            ->cell('Left')
            ->cell('Right');

        ob_start();
        $grid->render();
        $rendered = ob_get_clean();

        $this->assertSame($grid->toString(), $rendered);
    }

    // public function testIntColumnsEqualDivisionWithRemainder(): void {
    //     $grid = (new Grid(3, gap: 0))
    //         ->cell('A')->cell('B')->cell('C');

    //     $lines = explode("\n", rtrim($grid->toString(), "\n"));
    //     $this->assertCount(1, $lines);

    //     $plain = $this->stripAnsi($lines[0]);
    //     $this->assertEquals(120, strlen($plain));

    //     $third = (int) ceil(80 / 3);
    //     $this->assertGreaterThanOrEqual(80 / 3, $third);
    // }

    public function testMultipleRowsFromMoreCellsThanColumns(): void {
        $grid = (new Grid([10, 10], gap: 0))
            ->cell('Row1Col1')
            ->cell('Row1Col2')
            ->cell('Row2Col1')
            ->cell('Row2Col2');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('Row1Col1', $lines[0]);
        $this->assertStringContainsString('Row2Col1', $lines[1]);
    }

    public function testLastChunkPaddedWithEmptyCells(): void {
        $grid = (new Grid([10, 10], gap: 0))
            ->cell('Only')
            ->cell('One')
            ->cell('Three');

        $lines = explode("\n", rtrim($grid->toString(), "\n"));
        $this->assertCount(2, $lines);
        foreach ($lines as $line) {
            $this->assertEquals(20, strlen($this->stripAnsi($line)));
        }
    }
}
