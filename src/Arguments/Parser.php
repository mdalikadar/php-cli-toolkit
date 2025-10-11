<?php
namespace PhpCliToolkit\Arguments;
class Parser {
    protected OptContainer $options;
    protected ArgContainer $arguments;

    public function __construct() {
        $this->options   = new OptContainer;
        $this->arguments = new ArgContainer;
    }

    public function dump(){
        return [
            $this->options,
            $this->arguments,
        ];
    }

    public function registerArg(string $name, ?string $description = null, bool $isRequired = false) : void {
        $this->arguments[$name] = [
            'description' => $description,
            'isRequired' => $isRequired,
            'value' => null,
        ];
    }

    public function registerOption(string $name, ?string $description = null, bool $isRequired = false) : void {
        $nameArr = explode('|', $name);
        if (isset($nameArr[1])) {
            $this->options->boundTo(...$nameArr);
        }
        $this->options[$nameArr[0]] = [
            'description' => $description,
            'isRequired' => $isRequired,
            'value' => null,
        ];
    }

    public function args() : array {
        global $argv;
        $args = $argv;
        array_shift($args);
        return $args;
    }

    public function runParser() : void {
        foreach ($this->args() as $arg) {
            if (str_starts_with($arg, '--')) {
                $parts = explode('=', $arg);
                $option = preg_replace('|^--|', '', $parts[0]);
                if(!isset($this->options[$option])) continue;
                $this->options[$option]['value'] = count($parts) === 2 ? $parts[1] : true; 
            }
            else if (str_starts_with($arg, '-')) {
                $arg = preg_replace('|[^a-zA-Z0-9]|', '', $arg);
                if(empty($arg)) continue;
                $parts = str_split($arg);
                if(empty($parts)) continue;
                foreach($parts as $part) {
                    if(!isset($this->options[$part])) continue;
                    $this->options[$part]['value'] = true; 
                }
            }
            else {
                $key = $this->arguments->getIterator()->key();
                if(!is_null($key)) {
                    $this->arguments[$key]['value'] = $arg; 
                    $this->arguments->getIterator()->next();
                }
            }
        }
    }

    protected function validate() : void {
        foreach($this->arguments as $name => $arg) {
            if($arg['isRequired'] && empty($arg['value'])) {
                echo $name," is required.\n";
            }
        }
        foreach($this->options as $name => $arg) {
            if($arg['isRequired'] && empty($arg['value'])) {
                echo $name," is required.\n";
            }
        }
    }

    public function run() : void {
        $this->runParser();
        $this->validate();
    }
}