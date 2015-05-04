<?php

namespace furyfire\chip8;

class Helpers
{

    /**
     * Validates the supplied input as being a CHIP-8 address
     * @param mixed $address
     * @return boolean True on success otherwise false
     */
    public static function validateAddress($address)
    {
        return (is_int($address) && $address >= 0 && $address < 0x1000);
    }

    /**
     * Validates the supplied input as being a single byte value
     * @param mixed $byte
     * @return boolean True on success otherwise false
     */
    public static function validateByte($byte)
    {
        return (is_int($byte) && $byte >= 0 && $byte <= 0xFF);
    }

    /**
     * Validates the supplied input as being a pointer to a register 0-15
     * @param mixed $index
     * @return boolean True on success otherwise false
     */
    public static function validateRegister($index)
    {
        return (is_int($index) && $index >= 0 && $index <= 15);
    }

    /**
     * Validates the supplied input as being a CHIP-8 instruction
     * @param mixed $instruction
     * @return boolean True on success otherwise false
     */
    public static function validateInstruction($instruction)
    {
        return (is_int($instruction) and $instruction >= 0 and $instruction <= 0xFFFF);
    }

    /**
     * Validates a key
     * $param mixed $keyindex
     * @return boolean True on success otherwise false
     */
    public static function validateKey($keyindex)
    {
        return (is_int($keyindex) and $keyindex >= 0 and $keyindex <= 15);
    }
}
