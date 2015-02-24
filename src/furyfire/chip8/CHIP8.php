<?php
namespace furyfire\chip8;

class CHIP8
{

    /**
     * @var Registers
     */
    protected $registers;
    /**
     * @var ProgramCounter
     */
    protected $pc;
    /**
     * @var Memory
     */
    protected $memory;
    
    /**
     * @var Screen
     */
    protected $screen;
    
    /**
     *
     * @var Timer
     */
    protected $timer;
    
    /**
     * @var int Counts the number of ticks the virtual machine performs
     */
    protected $tick_counter = 0;
    
    protected static $opcodes = array(
        0x0 => 'i0___',
        0x1 => 'i1NNN',
        0x2 => 'i2NNN',
        0x3 => 'i3XNN',
        0x4 => 'i4XNN',
        0x5 => 'i5XY0',
        0x6 => 'i6XNN',
        0x7 => 'i7XNN',
        0x8 => 'i8XY_',
        0x9 => 'i9XY0',
        0xa => 'iANNN',
        0xb => 'iBNNN',
        0xc => 'iCXNN',
        0xd => 'iDXYN',
        0xe => 'iEX__',
        0xf => 'iFX__',
    );

    public function __construct()
    {
        $this->memory = new Memory;
        $this->registers      = new Registers;
        $this->pc     = new ProgramCounter;
        $this->screen = new Screen;
        $this->stack  = new Stack;
        $this->timer  = new Timer;

    }

    public function loadFile($filename)
    {
        $program = file_get_contents($filename);
        $this->memory->setMem(0x200, array_merge(unpack('C*', $program)));
    }

    public function loadArray($array)
    {
        $this->memory->setMem(0x200, $array);
    }

    public function getScreen() {
        return $this->screen;
    }
    public function step()
    {
        $this->timer->advance();
        $instruction = new Instruction($this->memory->getOpcode($this->pc));
        $method      = self::$opcodes[$instruction->getOpcode()];
        
        if (is_callable(array($this, $method))) {
            $this->$method($instruction);
        } else {
            $this->invalidOpcode($instruction);
        }
        
        $this->tick_counter++;
        
        //$this->breakpoint();
    }

    public function invalidOpcode($instruction) {
        throw new Exception("Opcode not supported. " . $instruction);
    }
    
    public function printBreakpoint() {
        echo "Opcode: 0x".dechex($this->memory->getOpcode($this->pc))."\n";
        echo "Ticks: ".$this->tick_counter."\n";
        echo "PC: 0x".dechex($this->pc->get())."\n";
        echo $this->registers->debug();
        echo "\n";
    }
    
    public function breakpoint() {
        return array(
            'opcode'    => $this->memory->getOpcode($this->pc),
            'ticks'     => $this->tick_counter,
            'pc'        => $this->pc->get(),
            'registers' => $this->registers->getAll()
        );
    }
    /**
     * 0___ Sub opcode
     */
    protected function i0___(Instruction $instruction)
    {
        switch ($instruction->getNNN()) {
            case 0x000:
                die("End of code");
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
     */
    protected function i1NNN(Instruction $instruction)
    {
        $this->pc->jumpTo($instruction->getNNN());
    }

    /**
     * 2NNN Calls subroutine at NNN.
     */
    protected function i2NNN(Instruction $instruction)
    {
        $this->pc->step();
        $this->stack->push($this->pc->get());
        $this->pc->jumpTo($instruction->getNNN());
    }

    /**
     * 3XNN	Skips the next instruction if VX equals NN
     */
    protected function i3XNN(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) == $instruction->getNN()) {
            $this->pc->skip();
        } else {
            $this->pc->step();
        }
    }
 
    /**
     * 4XNN	Skips the next instruction if VX doesn't equal NN.
     */
    protected function i4XNN(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) != $instruction->getNN()) {
            $this->pc->skip();
        } else {
            $this->pc->step();
        }
    }
    
    /**
     * 5XY0	Skips the next instruction if VX equals VY.
     */
    protected function i5XY0(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) == $this->registers->getV($instruction->getY())) {
            $this->pc->skip();
        } else {
            $this->pc->step();
        }
    }
    
    /**
     * 6XNN	Sets VX to NN.
     */
    protected function i6XNN(Instruction $instruction)
    {
        $this->registers->setV($instruction->getX(), $instruction->getNN());
        $this->pc->step();
    }

    /**
     * i7XNN Adds NN to VX.
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
     * i8XY_ Bit operations
     * 
     * 8XY0	Sets VX to the value of VY.
     * 8XY1	Sets VX to VX or VY.
     * 8XY2	Sets VX to VX and VY.
     * 8XY3	Sets VX to VX xor VY.
     * 8XY4	Adds VY to VX. VF is set to 1 when there's a carry, and to 0 when there isn't.
     * 8XY5	VY is subtracted from VX. VF is set to 0 when there's a borrow, and 1 when there isn't.
     * 8XY6	Shifts VX right by one. VF is set to the value of the least significant bit of VX before the shift.[2]
     * 8XY7	Sets VX to VY minus VX. VF is set to 0 when there's a borrow, and 1 when there isn't.
     * 8XYE	Shifts VX left by one. VF is set to the value of the most significant bit of VX before the shift.[2]
     * 
     * @param Instruction $instruction
     */
    protected function i8XY_(Instruction $instruction)
    {
        switch($instruction->getN()) {
            case 0x0:
                $this->registers->setV($instruction->getX(),$this->registers->getV($instruction->getY()));
                break;
            case 0x1:
                $value = $this->registers->getV($instruction->getX()) OR $this->registers->getV($instruction->getY());
                $this->registers->setV($instruction->getX(),$value);
                break;
            case 0x2:
                $value = $this->registers->getV($instruction->getX()) AND $this->registers->getV($instruction->getY());
                $this->registers->setV($instruction->getX(),$value);
                break;
            case 0x3:
                $value = $this->registers->getV($instruction->getX()) XOR $this->registers->getV($instruction->getY());
                $this->registers->setV($instruction->getX(),$value);
                break;
            case 0x4:
                $value = $this->registers->getV($instruction->getX()) + $this->registers->getV($instruction->getY());
                $this->registers->setFlag($value > 255);
                $value &= 0xFF;
                $this->registers->setV($instruction->getX(),$value);
                break;
            case 0x5:
                $this->registers->setFlag($this->registers->getV($instruction->getX()) >= $this->registers->getV($instruction->getY()));
                $value = $this->registers->getV($instruction->getX()) - $this->registers->getV($instruction->getY());
                $value = ($value < 0) ? 255 - abs($value) : $value;
                $value &= 0xFF;
                $this->registers->setV($instruction->getX(),$value);
                break;
            case 0x6:
                $value = $this->registers->getV($instruction->getY());
                $flag = (bool)($value & 0x01);
                $value >>= 1;
                $this->registers->setV($instruction->getX(),$value);
                $this->registers->setFlag($flag);
                break;
            case 0x7:
                $this->registers->setFlag($this->registers->getV($instruction->getY()) >= $this->registers->getV($instruction->getX()));
                $value = $this->registers->getV($instruction->getY()) - $this->registers->getV($instruction->getX());
                $value = ($value < 0) ? 255 - abs($value) : $value;
                $value &= 0xFF;
                $this->registers->setV($instruction->getX(),$value);
                break;
            case 0xE:
                $value = $this->registers->getV($instruction->getY());
                $flag = (bool)($value & 0x80);
                $value <<= 1;
                $this->registers->setV($instruction->getX(),$value);
                $this->registers->setFlag($flag);
                break;
            default:
                $this->InvalidOpcode($instruction);
        }
        //
        $this->pc->step();
    }
    /**
     * i9XY0 Skips the next instruction if VX doesn't equal VY.
     * @param Instruction $instruction
     */
    protected function i9XY0(Instruction $instruction)
    {
        if ($this->registers->getV($instruction->getX()) != $this->registers->getV($instruction->getY())) {
            $this->pc->skip();
        } else {
            $this->pc->step();
        }
    }
    /**
     * iANNN Sets I to the address NNN.
     * @param Instruction $instruction
     */
    protected function iANNN(Instruction $instruction)
    {
        $this->registers->setI($instruction->getNNN());
        $this->pc->step();
    }

    /**
     * iBNNN Jumps to the address NNN plus V0.
     * @param Instruction $instruction
     */
    protected function iBNNN(Instruction $instruction)
    {
        $this->pc->jumpTo($this->registers->getI() + $instruction->getNNN());
    }
    
    /**
     * iCXNN Sets VX to a random number, masked by NN.
     * @param Instruction $instruction
     */
    protected function iCXNN(Instruction $instruction)
    {
        $this->registers->setV($instruction->getX(), rand(0,255) & ($instruction->getNN()));
        $this->pc->step();
    }

    /**
     * iDXYN Sprites stored in memory at location in index register (I), maximum 8bits wide. Wraps around the screen. If when drawn, clears a pixel, register VF is set to 1 otherwise it is zero. All drawing is XOR drawing (i.e. it toggles the screen pixels)
     * @param Instruction $instruction
     */
    protected function iDXYN(Instruction $instruction)
    {
        $d_x   = $this->registers->getV($instruction->getX());
        $d_y   = $this->registers->getV($instruction->getY());
        $d_len = $instruction->getN();
        $pixel_flag = false;
        for ($j = 0; $j < $d_len; $j++) {
            for ($i = 0; $i < 8; $i++) {
                $byte = $this->memory->getByte($this->registers->getI() + $j);
               if ($byte & (1 << (7 - $i))) {
                    $pixel = $this->screen->getPixel($d_x + $i, $d_y + $j);
                    if ($pixel) {
                        $pixel_flag = true;
                    }
                    $this->screen->setPixel($d_x + $i, $d_y + $j, !$pixel);
                }
            }
        }
        $this->registers->setFlag($pixel_flag);

        $this->pc->step();
    }
    
    /**
     * iEX__ Key handling
     * 
     * EX9E Skips the next instruction if the key stored in VX is pressed.
     * EXA1 Skips the next instruction if the key stored in VX isn't pressed.
     * 
     * @param Instruction $instruction
     */
    protected function iEX__(Instruction $instruction)
    {
        switch($instruction->getNN()) {
            case 0x9E:
                break;
            case 0xA1:
                break;
            default:
                $this->InvalidOpcode($instruction);
        }
        //
        $this->pc->step();
    }
    
    /**
     * iFX__ Subopcode handling
     * 
     * FX07 Sets VX to the value of the delay timer.
     * FX0A A key press is awaited, and then stored in VX.
     * FX15	Sets the delay timer to VX.
     * FX18	Sets the sound timer to VX.
     * FX1E	Adds VX to I.[3]
     * FX29	Sets I to the location of the sprite for the character in VX. Characters 0-F (in hexadecimal) are represented by a 4x5 font.
     * FX33	Stores the Binary-coded decimal representation of VX, with the most significant of three digits at the address in I, the middle digit at I plus 1, and the least significant digit at I plus 2. (In other words, take the decimal representation of VX, place the hundreds digit in memory at location in I, the tens digit at location I+1, and the ones digit at location I+2.)
     * FX55	Stores V0 to VX in memory starting at address I.[4]
     * FX65	Fills V0 to VX with values from memory starting at address I.[4]
     * 
     * @param Instruction $instruction
     */
    protected function iFX__(Instruction $instruction)
    {
        switch($instruction->getNN()) {

            case 0x07:
                $this->registers->setV($instruction->getX(),$this->timer->getDelay());
                break;
                break;
            case 0x0A:
                $this->registers->setV($instruction->getX(),5);
                break;
            case 0x15:
                $this->timer->setDelay($this->registers->getV($instruction->getX()));
                break;
            case 0x18:
                $this->timer->setSound($this->registers->getV($instruction->getX()));
                break;
            case 0x1E:
                $value = $this->registers->getI() + $this->registers->getV($instruction->getX());
                $value &= 0xFFF;
                $this->registers->setI($value);
                break;
            case 0x29:
                $value = $this->registers->getV($instruction->getX()) * 5;
                $this->registers->setI($value & 0x1FF);
                break;
            case 0x55:
                for($i=0;$i<=$instruction->getX();$i++) {
                    $this->memory->setMem($this->registers->getI(), array($this->registers->getV($i)));
                    $this->registers->setI($this->registers->getI()+1);
                }
                
                break;
            case 0x65:
                for($i=0;$i<=$instruction->getX();$i++) {
                    $this->registers->setV($i,$this->memory->getByte($this->registers->getI()));
                    $this->registers->setI($this->registers->getI()+1);
                }
                break;
            default:
                $this->InvalidOpcode($instruction);
        }
        //
        $this->pc->step();
    }
    
    
}
