<?php
namespace furyfire\chip8;

class Stack
{
    protected $stack = array();
    protected $stackPointer;

    const STACK_SIZE = 12;

    public function __construct()
    {
        $this->stackPointer = 0;
    }

    public function push($value)
    {
        if (Helpers::validateAddress($value) and $this->stackPointer < self::STACK_SIZE) {
            $this->stack[$this->stackPointer] = $value;
            $this->stackPointer++;

            return;
        }

        throw new \Exception("Stack is full or invalid push - sp: ".$this->stackPointer." pc: ".$pc);
    }

    public function pop()
    {
        if ($this->stackPointer) {
            return $this->stack[--$this->stackPointer];
        }
        throw new \Exception("Stack is already empty");
    }
}
