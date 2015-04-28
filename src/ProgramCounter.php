<?php

namespace furyfire\chip8;

class ProgramCounter
{
    protected $pc;

    /**
     * Intialize a new ProgramCounter starting at address 0x200
     */
    public function __construct()
    {
        $this->pc = 0x200;
    }

    /*
     * Step the program counter 1 instructions  (wrapping around at overflow)
     */
    public function step()
    {
        $this->pc += 2;
        $this->pc %= 0x1000;
    }

    /**
     * Skips the next Instruction (for conditionals)
     */
    public function skip()
    {
        $this->step();
        $this->step();
    }

    /**
     * Sets the program counter to a specific memory address
     *
     * @param int $address Memory address
     * @return void
     * @throws \InvalidArgumentException
     */
    public function jumpTo($address)
    {
        if(Helpers::validateAddress($address)) {
            $this->pc = $address;

            return;
        }

        throw new \InvalidArgumentException("Invalid address");
    }

    public function get()
    {
        return $this->pc;
    }
}
