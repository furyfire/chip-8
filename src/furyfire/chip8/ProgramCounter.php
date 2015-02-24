<?php

namespace furyfire\chip8;

class ProgramCounter
{
    protected $pc;
    
    public function __construct() {
        $this->pc = 0x200;
    }
    
    public function step() {
        $this->pc += 2;
        if($this->pc >= 0x1000) 
            $this->pc = 0x00;
    }
    
    public function skip() {
        $this->step();
        $this->step();
    }
    
    public function jumpTo($address) {
        if(is_int($address) and $address >= 0 and $address < 0x1000) {
            $this->pc = $address;
            return;
        }
        
        throw new Exception("Invalid address");
    }
    
    public function get() {
        return $this->pc;
    }
}
