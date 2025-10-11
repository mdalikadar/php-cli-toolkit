<?php

namespace PhpCliToolkit\Arguments;
use ArrayAccess;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
class ArgContainer implements ArrayAccess, IteratorAggregate {
    protected ArrayIterator $storage;

    public function __construct() {
        $this->storage = new ArrayIterator();
    }
    public function getIterator() : Traversable {
        return $this->storage;
    }

    public function offsetExists(mixed $offset) : bool {
        return isset($this->storage[$offset]);
    }

    public function offsetGet(mixed $offset) : mixed {
        return $this->storage[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value) : void {
        $this->storage[$offset] = is_array($value) ? new ArrayIterator($value) : $value;
    }

    public function offsetUnset(mixed $offset) : void {
        unset($this->storage[$offset]);
    }
}