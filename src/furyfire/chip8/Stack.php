<?php
namespace furyfire\chip8;

class Stack
{
    protected $stack = array();
    protected $stackPointer;

    public function __construct()
    {
        $this->stackPointer = 0;
    }

    public function push($pc)
    {
        if (is_int($pc) and $pc >= 0 and $pc < 0x1000 and $this->stackPointer < 128) {
            $this->stack[$this->stackPointer] = $pc;
            $this->stackPointer++;

            return;
        }

        throw new Exception("Stack is full or invalid push - sp: ".$this->stackPointer." pc: ".$pc);
    }

    public function pop()
    {
        if ($this->stackPointer) {
            return $this->stack[--$this->stackPointer];
        }
        throw new Exception("Stack is already empty");
    }
}
