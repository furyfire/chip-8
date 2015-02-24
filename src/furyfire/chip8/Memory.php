<?php
namespace furyfire\chip8;

class Memory
{
    protected $memory = array();
    protected static $sprites = [
        0xF0, 0x90, 0x90, 0x90, 0xF0, // 0
        0x20, 0x60, 0x20, 0x20, 0x70, // 1
        0xF0, 0x10, 0xF0, 0x80, 0xF0, // 2
        0xF0, 0x10, 0xF0, 0x10, 0xF0, // 3
        0x90, 0x90, 0xF0, 0x10, 0x10, // 4
        0xF0, 0x80, 0xF0, 0x10, 0xF0, // 5
        0xF0, 0x80, 0xF0, 0x90, 0xF0, // 6
        0xF0, 0x10, 0x20, 0x40, 0x40, // 7
        0xF0, 0x90, 0xF0, 0x90, 0xF0, // 8
        0xF0, 0x90, 0xF0, 0x10, 0xF0, // 9
        0xF0, 0x90, 0xF0, 0x90, 0x90, // A
        0xE0, 0x90, 0xE0, 0x90, 0xE0, // B
        0xF0, 0x80, 0x80, 0x80, 0xF0, // C
        0xE0, 0x90, 0x90, 0x90, 0xE0, // D
        0xF0, 0x80, 0xF0, 0x80, 0xF0, // E
        0xF0, 0x80, 0xF0, 0x80, 0x80
    ];

    public function __construct() {
        $this->renderSprites();
    }
    
    
    
    private function renderSprites() {

        for ($i = 0; $i < count(self::$sprites); $i++) {
            $this->memory[$i] = self::$sprites[$i];
        }
    }
    
    public function setMem($offset, $buffer) {
        for ($i = 0; $i < count($buffer); $i++) {
            $this->memory[$offset + $i] = $buffer[$i];
        }
    }
    
    public function getMemory($offset, $length) {
        return array_slice($this->memory, $offset, $length);
    }
    
    public function getByte($address) {
        
        return isset($this->memory[$address]) ? $this->memory[$address] : 0x00 ;
    }
    
    public function getOpcode(ProgramCounter $pc) {
        if(isset($this->memory[$pc->get()])  AND isset($this->memory[$pc->get() + 1])) {
            return $this->memory[$pc->get()] << 8 | $this->memory[$pc->get() + 1];
        } else {
             echo "Out of range: 0x".dechex($pc->get())."\n";
             
            return 0x0000;
        }
        
    }
}
