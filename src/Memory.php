<?php
namespace furyfire\chip8;

/**
 * Represents the internal memory of a chip-8 computer
 *
 * @see http://en.wikipedia.org/wiki/CHIP-8#Memory CHIP-8 Specifications on Wikipedia
 */
class Memory
{
    /**
     * @var \SplFixedArray Memory
     */
    protected $memory;

    /**
     * @var array The default font sprites
     * @see http://devernay.free.fr/hacks/chip8/C8TECH10.HTM#font Source
     */
    static $sprites = array(
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
        0xF0, 0x80, 0xF0, 0x80, 0x80, // F
    );

    /**
     * Initializes the memory
     */
    public function __construct()
    {
        $this->memory = new \SplFixedArray(10000);
        $this->renderSprites();
    }

    /**
     * Writes the default font to memory
     */
    private function renderSprites()
    {
        $this->setMemory(0x000, self::$sprites);

    }

    /**
     * Sets a specific area of memory
     *
     * @param int $start Starting address
     * @param mixed $buffer An array of bytes
     */
    public function setMemory($start, $buffer)
    {
        $i = 0;
        if (is_array($buffer)) {
            foreach ($buffer as $char) {
                $this->setByte($start + $i, $char);
                $i++;
            }
        }
    }

    /**
     * Reads one or serveal bytes from memory
     *
     * @param type $start Starting address
     * @param type $length Number of bytes to read
     * @throw \Exception Throws an exception if address is out of range
     * @return array
     *
     */

    public function getMemory($start, $length)
    {
        if (Helpers::validateAddress($start) && Helpers::validateAddress($start + $length)) {

            $mem = array();
            for ($i = 0; $i < $length; $i++) {
                $mem[] = $this->getByte($start + $i);
            }
            return $mem;
        }
        throw new \Exception("Out of range");
    }

    /**
     * Reads a single byte from memory
     *
     * @param int $address
     * @return int
     *
     */
    public function getByte($address)
    {
        if (Helpers::validateAddress($address)) {
            return isset($this->memory[$address]) ? $this->memory[$address] : 0x00;
        }
        throw new \InvalidArgumentException("Out of range: 0x".dechex($address)."\n");
    }

    /**
     * Sets a single byte in memory
     *
     * @param int $address Memory address
     * @param int $value An 8bit value
     * @return void
     */
    public function setByte($address, $value)
    {
        if (Helpers::validateAddress($address)) {
            if (Helpers::validateByte($value)) {
                $this->memory[$address] = $value;
                return;
            }
        }
        throw new \InvalidArgumentException("Out of range: 0x" . dechex($address) . "\n");
    }

    /**
     * Returns an opcode from memory supplying a program counter
     *
     * @param \furyfire\chip8\ProgramCounter $pc ProgramCounter object
     * @return
     */
    public function getInstruction(ProgramCounter $pc)
    {
        return new Instruction($this->getByte($pc->get()) << 8 | $this->getByte($pc->get() + 1));
    }
}
