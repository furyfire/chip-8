<?php
namespace furyfire\chip8;

class CHIP8 extends CHIP8Instructions
{
    const STATE_ENDED   = 0;
    const STATE_RUNNING = 1;
    const STATE_AWAIT   = 2;
    /**
     * @var Registers The V and the I registers for the current emulator
     */
    protected $registers;
    /**
     * @var ProgramCounter The programcounter object
     */
    protected $pc;
    /**
     * @var Memory Representation of the CHIP-8 Memory
     */
    protected $memory;

    /**
     * @var Screen The screen object
     */
    protected $screen;

    /**
     * @var Timer Contains the Delay and the Sound timer
     */
    protected $timer;

    /**
     * @var Keyboard Contains the keyboard implementation
     */
    protected $keyboard;

    /**
     * @var int Counts the number of ticks the virtual machine performs
     */
    protected $tickCounter = 0;

    /**
     *
     * @var int State of the machine (See STATE_ constants)
     */
    protected $state = true;
    /**
     * Instance a new CHIP-8 emulator
     */
    public function __construct()
    {
        $this->memory       = new Memory;
        $this->registers    = new Registers;
        $this->pc           = new ProgramCounter;
        $this->screen       = new Screen;
        $this->stack        = new Stack;
        $this->timer        = new Timer;
        $this->keyboard     = new Keyboard;

        $this->state        = self::STATE_RUNNING;
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
     * Returns the keyboard object
     *
     * @return Keyboard The keyboard object
     */
    public function getKeyboard()
    {
        return $this->keyboard;
    }

    /**
     * Advance the emulator one step
     *
     * Calling this function will advance the emulator one step. Usercode must implement the main loop.
     */
    public function step()
    {
        $this->timer->advance();

        $instruction = $this->memory->getInstruction($this->pc);
        $method      = self::$opcodes[$instruction->getOpcode()];

        if (!is_callable(array($this, $method))) {
            $this->invalidOpcode($instruction);
        }
        $this->$method($instruction);
        $this->tickCounter++;

        return $this->state;
    }


    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    /**
     * Slight hacking to support the Await key instruction
     * @param type $key 1-16
     */
    public function pressWaitingKey($key) {
        $instruction = $this->memory->getInstruction($this->pc);
        $this->registers->setV($instruction->getX(), $key);
        $this->setState(self::STATE_RUNNING);
    }

    /**
     * Unrecognized Opcode
     *
     * @param Instruction $instruction The current instruction
     * @throws Exception
     */
    private function invalidOpcode(Instruction $instruction)
    {
        throw new Exception("Opcode not supported. ".$instruction);
    }

    /**
     * Format a breakpoint for console output
     */
    public function printBreakpoint()
    {
        echo "Opcode: ".$this->memory->getInstruction($this->pc->get())."\n";
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
            'opcode'    => $this->memory->getInstruction($this->pc->get())->get(),
            'ticks'     => $this->tickCounter,
            'pc'        => $this->pc->get(),
            'registers' => $this->registers->getAll(),
        );
    }
}
