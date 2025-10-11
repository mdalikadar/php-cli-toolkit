<?php

namespace PhpCliToolkit\Arguments;
class OptContainer extends ArgContainer {
    protected array $bounds;

    public function boundTo(mixed $offset, mixed $withOffset) : void {
        $this->bounds[$withOffset] = $offset;
    }

    public function offsetExists(mixed $offset) : bool {
        return parent::offsetExists($offset) || (isset($this->bounds[$offset]) ? isset($this->storage[$this->bounds[$offset]]) : false);
    }

    public function offsetGet(mixed $offset) : mixed {
        return parent::offsetGet($offset) ?? (isset($this->bounds[$offset]) ? $this->storage[$this->bounds[$offset]] : null);
    }

    public function offsetUnset(mixed $offset) : void {
        $flippedBounds = array_flip($this->bounds);
        if (array_key_exists($offset ,$flippedBounds)) {
            unset($this->bounds[$flippedBounds[$offset]]);
        }
        parent::offsetUnset($offset);
    }

    public function dump() : void {
        var_dump('dump', $this->storage, $this->bounds);
    }
}