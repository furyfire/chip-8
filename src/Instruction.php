<?php
namespace furyfire\chip8;

class Instruction
{
    /**
     * @var int Binary representation of the instruction
     */
    private $instruction;

    /**
     * Create a new single instruction object. This object is created whenever a new instruction is loaded from memory.
     * @link http://en.wikipedia.org/wiki/CHIP-8#Memory Wikipedia specifications
     * @param int $instruction Binary value of an Instruction
     * @return void
     * @throws Exception If the supplied value does not fall witin the ranges for a CHIP-8 instruction
     */
    public function __construct($instruction)
    {
        if (is_int($instruction) and $instruction >= 0 and $instruction <= 0xFFFF) {
            $this->instruction = $instruction;

            return;
        }
        throw new Exception("not a 16bit instruction");
    }

    /**
     * @return string Hexadecimal string representation of the current instruction
     */
    public function __toString()
    {
        return "0x".dechex($this->instruction);
    }

    /**
     * Return the MSB (Which is the Opcode of the instruction)
     * @return int
     */
    public function getOpcode()
    {
        return ($this->instruction & 0xF000) >> 12;
    }

    /**
     * Return the 12 LSB, refered in CHIp-8 specifications as NNN
     * @return int
     */
    public function getNNN()
    {
        return $this->instruction & 0x0FFF;
    }

    /**
     * Return the 8 LSB, refered in CHIp-8 specifications as NN
     * @return int
     */
    public function getNN()
    {
        return $this->instruction & 0x00FF;
    }

    /**
     * Return the 4 LSB, refered in CHIp-8 specifications as N
     * @return int
     */
    public function getN()
    {
        return $this->instruction & 0x000F;
    }

    /**
     * Return bit 9-12, refered in CHIp-8 specifications as X
     * @return int
     */
    public function getX()
    {
        return ($this->instruction & 0x0F00) >> 8;
    }
    /**
     * Return bit 5-8, refered in CHIp-8 specifications as X
     * @return int
     */
    public function getY()
    {
        return ($this->instruction & 0x00F0) >> 4;
    }

    /**
     * Get the current value of the current instruction
     * @return int
     */
    public function get() {
        return $this->instruction;
    }
}
