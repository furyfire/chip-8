<?php

namespace furyfire\chip8;

abstract class CHIP8Instructions
{

    protected static $opcodes = array(
        0x0 => 'i0xxx',
        0x1 => 'i1NNN',
        0x2 => 'i2NNN',
        0x3 => 'i3XNN',
        0x4 => 'i4XNN',
        0x5 => 'i5XY0',
        0x6 => 'i6XNN',
        0x7 => 'i7XNN',
        0x8 => 'i8XYx',
        0x9 => 'i9XY0',
        0xa => 'iANNN',
        0xb => 'iBNNN',
        0xc => 'iCXNN',
        0xd => 'iDXYN',
        0xe => 'iEXxx',
        0xf => 'iFXxx',
    );

    /**
     * 0___ Sub opcode.
     *
     * @param Instruction $instruction
     */
    protected function i0xxx(Instruction $instruction)
    {
        switch ($instruction->getNNN()) {
            case 0x000:
                throw new Exception("Terminated");
                break;
            case 0x0E0:
                $this->screen->clearScreen();
                $this->pc->step();
                break;
            case 0x0EE:
                $this->pc->jumpTo($this->stack->pop());
                break;
            default:
                $this->pc->jumpTo($instruction->getNNN());
                $this->pc->step();
        }
    }

    /**
     * 1NNN Jumps to address NNN.
     *
     * @param Instruction $instruction
     */
    protected function i1NNN(Instruction $instruction)
    {
        $this->pc->jumpTo($instruction->getNNN());
    }

    /**
     * 2NNN Calls subroutine at NNN.
     *
     * @param Instruction $instruction
     */
    protected function i2NNN(Instruction $instruction)
    {
        $this->pc->step();
        $this->stack->push($this->pc->get());
        $this->pc->jumpTo($instruction->getNNN());
    }

    /**
     * 3XNN Skips the next instruction if VX equals NN.
     *
     * @param Instruction $instruction
     */
    protected function i3XNN(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) == $instruction->getNN()) {
            $this->pc->skip();
            return;
        }
        $this->pc->step();
    }

    /**
     * 4XNN Skips the next instruction if VX doesn't equal NN.
     *
     * @param Instruction $instruction
     */
    protected function i4XNN(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) != $instruction->getNN()) {
            $this->pc->skip();
            return;
        }
        $this->pc->step();
    }

    /**
     * 5XY0 Skips the next instruction if VX equals VY.
     *
     * @param Instruction $instruction
     */
    protected function i5XY0(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) == $this->registers->getV($instruction->getY())) {
            $this->pc->skip();
        }
        $this->pc->step();
    }

    /**
     * 6XNN Sets VX to NN.
     *
     * @param Instruction $instruction
     */
    protected function i6XNN(Instruction $instruction)
    {
        $this->registers->setV($instruction->getX(), $instruction->getNN());
        $this->pc->step();
    }

    /**
     * i7XNN Adds NN to VX.
     *
     * @param Instruction $instruction
     */
    protected function i7XNN(Instruction $instruction)
    {
        $value = $this->registers->getV($instruction->getX()) + $instruction->getNN();
        $value &= 0xFF; //Only keep 8 LSB
        $this->registers->setV($instruction->getX(), $value & 0xFF);
        $this->pc->step();
    }

    /**
     * i8XY_ Bit operations.
     *
     * 8XY0 Sets VX to the value of VY.
     * 8XY1 Sets VX to VX or VY.
     * 8XY2 Sets VX to VX and VY.
     * 8XY3 Sets VX to VX xor VY.
     * 8XY4 Adds VY to VX. VF is set to 1 when there's a carry, and to 0 when there isn't.
     * 8XY5 VY is subtracted from VX. VF is set to 0 when there's a borrow, and 1 when there isn't.
     * 8XY6 Shifts VX right by one. VF is set to the value of the least significant bit of VX before the shift.[2]
     * 8XY7 Sets VX to VY minus VX. VF is set to 0 when there's a borrow, and 1 when there isn't.
     * 8XYE Shifts VX left by one. VF is set to the value of the most significant bit of VX before the shift.[2]
     *
     * @param Instruction $instruction
     */
    protected function i8XYx(Instruction $instruction)
    {
        $x = $this->registers->getV($instruction->getX());
        $y = $this->registers->getV($instruction->getY());
        switch ($instruction->getN()) {
            case 0x0:
                $this->registers->setV($instruction->getX(), $y);
                break;
            case 0x1:
                $value = $x || $y;
                $this->registers->setV($instruction->getX(), $value);
                break;
            case 0x2:
                $value = $x && $y;
                $this->registers->setV($instruction->getX(), $value);
                break;
            case 0x3:
                $value = $x xor $y;
                $this->registers->setV($instruction->getX(), $value);
                break;
            case 0x4:
                $value = $x + $y;
                $value &= 0xFF;
                $this->registers->setV($instruction->getX(), $value);
                $this->registers->Flag($value > 255);
                break;
            case 0x5:
                $value = $x - $y;
                $value = ($value < 0) ? 255 - abs($value) : $value;
                $value &= 0xFF;
                $this->registers->setV($instruction->getX(), $value);
                $this->registers->Flag($x >= $y);
                break;
            case 0x6:
                $value = $y;
                $flag  = (bool) ($value & 0x01);
                $value >>= 1;
                $this->registers->setV($instruction->getX(), $value);
                $this->registers->Flag($flag);
                break;
            case 0x7:
                $value = $y - $x;
                $value = ($value < 0) ? 255 - abs($value) : $value;
                $value &= 0xFF;
                $this->registers->setV($instruction->getX(), $value);
                $this->registers->Flag($y >= $x);
                break;
            case 0xE:
                $value = $y;
                $flag  = (bool) ($value & 0x80);
                $value <<= 1;
                $this->registers->setV($instruction->getX(), $value);
                $this->registers->Flag($flag);
                break;
            default:
                $this->InvalidOpcode($instruction);
        }
        //
        $this->pc->step();
    }

    /**
     * i9XY0 Skips the next instruction if VX doesn't equal VY.
     *
     * @param Instruction $instruction
     */
    protected function i9XY0(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) != $this->registers->getV($instruction->getY())) {
            $this->pc->skip();
            return;
        }
        $this->pc->step();
    }

    /**
     * iANNN Sets I to the address NNN.
     *
     * @param Instruction $instruction
     */
    protected function iANNN(Instruction $instruction)
    {
        $this->registers->setI($instruction->getNNN());
        $this->pc->step();
    }

    /**
     * iBNNN Jumps to the address NNN plus V0.
     *
     * @param Instruction $instruction
     */
    protected function iBNNN(Instruction $instruction)
    {
        $this->pc->jumpTo($this->registers->getI() + $instruction->getNNN());
    }

    /**
     * iCXNN Sets VX to a random number, masked by NN.
     *
     * @param Instruction $instruction
     */
    protected function iCXNN(Instruction $instruction)
    {
        $rand = rand(0, 255) & ($instruction->getNN());
        $this->registers->setV($instruction->getX(), $rand);
        $this->pc->step();
    }

    /**
     * iDXYN Sprites stored in memory at location in index register (I), maximum 8bits wide. Wraps around the screen.
     * If when drawn, clears a pixel, register VF is set to 1 otherwise it is zero.
     * All drawing is XOR drawing (i.e. it toggles the screen pixels).
     *
     * @param Instruction $instruction
     */
    protected function iDXYN(Instruction $instruction)
    {
        $drawX        = $this->registers->getV($instruction->getX());
        $drawY        = $this->registers->getV($instruction->getY());
        $drawLen      = $instruction->getN();
        $pixelFlag = false;
        for ($j = 0; $j < $drawLen; $j++) {
            for ($i = 0; $i < 8; $i++) {
                $byte = $this->memory->getByte($this->registers->getI() + $j);
                if ($byte & (1 << (7 - $i))) {
                    $pixel = $this->screen->getPixel($drawX + $i, $drawY + $j);
                    $pixelFlag = ($pixel) ? true : $pixelFlag;
                    $this->screen->setPixel($drawX + $i, $drawY + $j, !$pixel);
                }
            }
        }
        $this->registers->Flag($pixelFlag);

        $this->pc->step();
    }

    /**
     * iEX__ Key handling.
     *
     * EX9E Skips the next instruction if the key stored in VX is pressed.
     * EXA1 Skips the next instruction if the key stored in VX isn't pressed.
     *
     * @param Instruction $instruction
     */
    protected function iEXxx(Instruction $instruction)
    {
        switch ($instruction->getNN()) {
            case 0x9E:
                break;
            case 0xA1:
                break;
            default:
                $this->InvalidOpcode($instruction);
        }

        $this->pc->step();
    }

    /**
     * iFX__ Subopcode handling.
     *
     * FX07 Sets VX to the value of the delay timer.
     * FX0A A key press is awaited, and then stored in VX.
     * FX15 Sets the delay timer to VX.
     * FX18 Sets the sound timer to VX.
     * FX1E Adds VX to I.[3]
     * FX29 Sets I to the location of the sprite for the character in VX.
     *      Characters 0-F (in hexadecimal) are represented by a 4x5 font.
     * FX33 Stores the Binary-coded decimal representation of VX, with the most significant of three digits at the
     *      address in I, the middle digit at I plus 1, and the least significant digit at I plus 2. (In other words,
     *      take the decimal representation of VX, place the hundreds digit in memory at location in I, the tens digit
     *      at location I+1, and the ones digit at location I+2.)
     * FX55 Stores V0 to VX in memory starting at address I.[4]
     * FX65 Fills V0 to VX with values from memory starting at address I.[4]
     *
     * @param Instruction $instruction
     */
    protected function iFXxx(Instruction $instruction)
    {
        $x = $this->registers->getV($instruction->getX());
        switch ($instruction->getNN()) {
            case 0x07:
                $this->registers->setV($instruction->getX(), $this->timer->getDelay());
                break;
                break;
            case 0x0A:
                $this->registers->setV($instruction->getX(), 5);
                break;
            case 0x15:
                $this->timer->setDelay($this->registers->getV($instruction->getX()));
                break;
            case 0x18:
                $this->timer->setSound($x);
                break;
            case 0x1E:
                $value = $this->registers->getI() + $x;
                $value &= 0xFFF;
                $this->registers->setI($value);
                break;
            case 0x29:
                $value = $x * 5;
                $this->registers->setI($value & 0x1FF);
                break;
            case 0x55:
                for ($i = 0; $i <= $instruction->getX(); $i++) {
                    $this->memory->setMem($this->registers->getI(), $this->registers->getV($i));
                    $this->registers->setI($this->registers->getI() + 1);
                }
                break;
            case 0x65:
                for ($i = 0; $i <= $instruction->getX(); $i++) {
                    $this->registers->setV($i, $this->memory->getByte($this->registers->getI()));
                    $this->registers->setI($this->registers->getI() + 1);
                }
                break;
            default:
                $this->InvalidOpcode($instruction);
        }
        $this->pc->step();
    }
}
