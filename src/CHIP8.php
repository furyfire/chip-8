<?php
namespace furyfire\chip8;

class CHIP8 extends CHIP8Instructions
{
    const STATE_RESET   = 0;
    const STATE_RUNNING = 1;
    const STATE_AWAIT   = 2;
    const STATE_TERM    = 3;


    /**
     *
     * @var Psr\Log\LoggerInterface Logger
     */
    protected $logger;
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
    protected $state = self::STATE_RESET;
    /**
     * Instance a new CHIP-8 emulator
     */
    public function __construct( \Psr\Log\LoggerInterface $logger)
    {
        $this->logger       = $logger;

        $this->memory       = new Memory;
        $this->registers    = new Registers;
        $this->pc           = new ProgramCounter;
        $this->screen       = new Screen;
        $this->stack        = new Stack;
        $this->timer        = new Timer;
        $this->keyboard     = new Keyboard;

        $this->setState(self::STATE_RESET);
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
        $this->logger->notice("Loading program from file",array('filename'=>$filename));
        if(!file_exists($filename)) {
            $this->logger->critical("File not found",array('filename'=>$filename));
            throw new Exception("File not found");
        }
        $program = file_get_contents($filename);
        $this->memory->setMemory(0x200, array_merge(unpack('C*', $program)));
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
        $this->logger->notice("Loading program from array");
        $this->memory->setMemory(0x200, $array);
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
        if($this->getState() == self::STATE_RESET) {
            $this->setState(self::STATE_RUNNING);
        }

        $this->timer->advance();

        if ($this->getState() == self::STATE_RUNNING) {
            $instruction = $this->memory->getInstruction($this->pc);
            $method      = self::$opcodes[$instruction->getOpcode()];

            if (!is_callable(array($this, $method))) {

                $this->invalidOpcode($instruction);
            }
            $this->$method($instruction);
            $this->tickCounter++;
        }


        return $this->getState();
    }


    public function setState($state)
    {
        if($state != $this->state) {
            switch($state) {
                case self::STATE_RUNNING:
                    $this->logger->notice("Program started");
                    break;
                case self::STATE_TERM:
                    $this->logger->notice("Program ended");
                    break;
            }
            $this->logger->info("STATE: $this->state -> $state");
            $this->state = $state;
        }
    }

    public function getState()
    {
        return $this->state;
    }

    /**
     * Slight hacking to support the Await key instruction
     * @param type $key 1-16
     */
    public function pressWaitingKey($key)
    {
        $this->logger->info("KeyPressed",array($key));
        $instruction = $this->memory->getInstruction($this->pc);
        $this->registers->setV($instruction->getX(), $key);
        $this->setState(self::STATE_RUNNING);
    }

    /**
     * Unrecognized Opcode
     *
     * @param Instruction $instruction The current instruction
     * @throws \Exception
     */
    protected function invalidOpcode(Instruction $instruction)
    {
        $msg = "Opcode not supported";
        $this->logger->critical($msg, $this->breakpoint());
        throw new \Exception($msg." ".$instruction);
    }

    /**
     * Format a breakpoint for console output
     */
    public function logBreakpoint()
    {
        $this->logger->info("Breakpoint",$this->breakpoint());
    }

    /**
     * Provides an array of registry settings
     *
     * @return array Breakpoint data
     */
    public function breakpoint()
    {
        return array(
            'opcode'    => $this->memory->getInstruction($this->pc)->get(),
            'ticks'     => $this->tickCounter,
            'pc'        => $this->pc->get(),
            'registers' => $this->registers->getAll(),
        );
    }
}
