<?php
namespace PhpCliToolkit\Exceptions;
class ValidationException extends CliException {
    private array $errorList;

    public function __construct(array $errors) {
        $this->errorList = $errors;
        parent::__construct(implode("\n", $errors));
    }

    public function errors(): array {
        return $this->errorList;
    }
}
