<?php
namespace furyfire\chip8;

class Stack
{

    protected $stack = array();
    protected $stack_pointer;
    
    public function __construct()
    {
        
        $this->stack_pointer = 0;
    }
    
    public function push($pc) {
        if(is_int($pc) and $pc >= 0 and $pc < 0x1000 and $this->stack_pointer < 128) {
            $this->stack[$this->stack_pointer] = $pc;
            $this->stack_pointer++;
            return;
        }        
        
        throw new Exception("Stack is full or invalid push - sp: ".$this->stack_pointer . " pc: ".$pc);
    }
    
    public function pop() {
        if($this->stack_pointer) {
           return $this->stack[--$this->stack_pointer];
        }
        throw new Exception("Stack is already empty");

    }
}
