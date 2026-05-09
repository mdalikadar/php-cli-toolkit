<?php
namespace PhpCliToolkit\Tests\Arguments;
use ArrayIterator;
use PhpCliToolkit\Arguments\ArgContainer;
use PHPUnit\Framework\TestCase;

class ArgContainerTest extends TestCase {
    public function testOffsetSetWrapsArrayAsArrayIterator(): void {
        $c = new ArgContainer();
        $c['key'] = ['foo' => 'bar'];
        $this->assertInstanceOf(ArrayIterator::class, $c['key']);
        $this->assertEquals('bar', $c['key']['foo']);
    }

    public function testOffsetGetReturnsNullForMissingKey(): void {
        $c = new ArgContainer();
        $this->assertNull($c['missing']);
    }

    public function testOffsetExists(): void {
        $c = new ArgContainer();
        $this->assertFalse(isset($c['name']));
        $c['name'] = ['value' => 'Alice'];
        $this->assertTrue(isset($c['name']));
    }

    public function testOffsetUnset(): void {
        $c = new ArgContainer();
        $c['name'] = ['value' => 'Alice'];
        unset($c['name']);
        $this->assertFalse(isset($c['name']));
    }

    public function testIteration(): void {
        $c = new ArgContainer();
        $c['a'] = ['value' => '1'];
        $c['b'] = ['value' => '2'];
        $keys = [];
        foreach ($c as $key => $value) {
            $keys[] = $key;
        }
        $this->assertEquals(['a', 'b'], $keys);
    }

    public function testRewindResetsIterator(): void {
        $c = new ArgContainer();
        $c['a'] = ['value' => '1'];
        $c['b'] = ['value' => '2'];
        $c->getIterator()->next();
        $this->assertEquals('b', $c->getIterator()->key());
        $c->rewind();
        $this->assertEquals('a', $c->getIterator()->key());
    }
}
