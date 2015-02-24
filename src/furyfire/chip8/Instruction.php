<?php
namespace furyfire\chip8;

class Instruction
{

    private $instruction;

    public function __construct($instruction)
    {
        if (is_int($instruction) and $instruction >= 0 and $instruction <= 0xFFFF) {
            $this->instruction = $instruction;
            return;
        }
        throw new Exception("not a 16bit instruction");
    }
    
    public function __toString()
    {
        return "0x".dechex($this->instruction);
    }

    public function getOpcode() {
        return ($this->instruction & 0xF000) >> 12;
    }
    public function getNNN()
    {
        return $this->instruction & 0x0FFF;
    }

    public function getNN()
    {
        return $this->instruction & 0x00FF;
    }

    public function getN()
    {
        return $this->instruction & 0x000F;
    }

    public function getX()
    {
        return ($this->instruction & 0x0F00) >> 8;
    }

    public function getY()
    {
        return ($this->instruction & 0x00F0) >> 4;
    }

}
