<?php
namespace PhpCliToolkit\Tests\Arguments;
use PhpCliToolkit\Arguments\OptContainer;
use PHPUnit\Framework\TestCase;

class OptContainerTest extends TestCase {
    public function testBoundToAliasResolvedByOffsetGet(): void {
        $c = new OptContainer();
        $c['verbose'] = ['value' => null];
        $c->boundTo('verbose', 'v');
        $c['verbose']['value'] = true;
        $this->assertTrue($c['v']['value']);
    }

    public function testOffsetExistsWithAlias(): void {
        $c = new OptContainer();
        $c['verbose'] = ['value' => null];
        $c->boundTo('verbose', 'v');
        $this->assertTrue(isset($c['v']));
        $this->assertFalse(isset($c['x']));
    }

    public function testOffsetExistsFalseForUnknownAlias(): void {
        $c = new OptContainer();
        $this->assertFalse(isset($c['unknown']));
    }

    public function testOffsetUnsetRemovesBoundEntry(): void {
        $c = new OptContainer();
        $c['verbose'] = ['value' => null];
        $c->boundTo('verbose', 'v');
        unset($c['verbose']);
        $this->assertFalse(isset($c['verbose']));
        $this->assertFalse(isset($c['v']));
    }

    public function testAliasSetValueModifiesPrimary(): void {
        $c = new OptContainer();
        $c['verbose'] = ['value' => null];
        $c->boundTo('verbose', 'v');
        $c['v']['value'] = true;
        $this->assertTrue($c['verbose']['value']);
    }
}
