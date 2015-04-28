<?php

namespace furyfire\chip8;

class Helpers
{

    /**
     * Validates the supplied input as being a CHIP-8 address
     * @param mixed $address
     * @return boolean True on success otherwise false
     */
    static function validateAddress($address)
    {
        return (is_int($address) && $address >= 0 && $address < 0x1000);
    }

    /**
     * Validates the supplied input as being a single byte value
     * @param mixed $byte
     * @return boolean True on success otherwise false
     */
    static function validateByte($byte)
    {
        return (is_int($byte) && $byte >= 0 && $byte <= 0xFF);
    }

    /**
     * Validates the supplied input as being a pointer to a register 0-15
     * @param mixed $index
     * @return boolean True on success otherwise false
     */
    static function validateRegister($index)
    {
        return (is_int($index) && $index >= 0 && $index <= 15);
    }

    /**
     * Validates the supplied input as being a CHIP-8 instruction
     * @param mixed $instruction
     * @return boolean True on success otherwise false
     */
    static function validateInstruction($instruction)
    {
        return (is_int($instruction) and $instruction >= 0 and $instruction <= 0xFFFF);
    }

}
