<?php
namespace furyfire\chip8;

class CHIP8 extends CHIP8Instructions
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
     * @var Timer
     */
    protected $timer;

    /**
     * @var int Counts the number of ticks the virtual machine performs
     */
    protected $tickCounter = 0;

    /**
     * Create a new CHIP-8 emulator
     */
    public function __construct()
    {
        $this->memory       = new Memory;
        $this->registers    = new Registers;
        $this->pc           = new ProgramCounter;
        $this->screen       = new Screen;
        $this->stack        = new Stack;
        $this->timer        = new Timer;
    }

    /**
     * Loads a binary file
     *
     * Load a binary file into the emulator using a relative or absolute path.
     * The content of the file will be loading into memory starting at location 0x200 as according to spec
     *
     * @param type $filename The binary file to load
     */
    public function loadFile($filename)
    {
        $program = file_get_contents($filename);
        $this->memory->setMem(0x200, array_merge(unpack('C*', $program)));
    }

    /**
     * Loads an array
     *
     * Loads a file into the emulator using an array of hex values.
     * The content will be placed into memory starting at location 0x200 as according to spec
     *
     * @param array $array Program code
     */
    public function loadArray($array)
    {
        $this->memory->setMem(0x200, $array);
    }

    /**
     * Returns the screen object
     *
     * @return Screen The screen object
     */
    public function getScreen()
    {
        return $this->screen;
    }
    
    /**
     * Advance the emulator one step
     *
     * Calling this function will advance the emulator one step. Usercode must implement the main loop.
     */
    public function step()
    {
        $this->timer->advance();
        $instruction = new Instruction($this->memory->getOpcode($this->pc));
        $method      = self::$opcodes[$instruction->getOpcode()];

        if (!is_callable(array($this, $method))) {
            $this->invalidOpcode($instruction);
        }
        $this->$method($instruction);
        $this->tickCounter++;
    }

    /**
     * Unrecognized Opcode
     *
     * @param type $instruction The current instruction
     * @throws Exception
     */
    private function invalidOpcode($instruction)
    {
        throw new Exception("Opcode not supported. ".$instruction);
    }

    /**
     * Format a breakpoint
     */
    public function printBreakpoint()
    {
        echo "Opcode: 0x".dechex($this->memory->getOpcode($this->pc))."\n";
        echo "Ticks: ".$this->tickCounter."\n";
        echo "PC: 0x".dechex($this->pc->get())."\n";
        echo $this->registers->debug();
        echo "\n";
    }

    /**
     * Provides an array of registry settings
     *
     * @return array Breakpoint data
     */
    public function breakpoint()
    {
        return array(
            'opcode'    => $this->memory->getOpcode($this->pc),
            'ticks'     => $this->tickCounter,
            'pc'        => $this->pc->get(),
            'registers' => $this->registers->getAll(),
        );
    }
}
