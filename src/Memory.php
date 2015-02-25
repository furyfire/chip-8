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
     * @var array Memory
     */
    protected $memory = array();

    /**
     * @var array The default font sprites
     * @see http://devernay.free.fr/hacks/chip8/C8TECH10.HTM#font Source
     */
    protected static $sprites = array(
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
        $this->renderSprites();
    }

    /**
     * Writes the default font to memory
     */
    private function renderSprites()
    {
        $this->setMem(0x000, self::$sprites);

    }

    /**
     * Sets a specific area of memory
     *
     * @param int $offset Starting address
     * @param mixed $buffer A single byte or an array of bytes
     */
    public function setMem($offset, $buffer)
    {
        if (is_array($buffer)) {
            for ($i = 0; $i < count($buffer); $i++) {
                $this->memory[$offset + $i] = $buffer[$i];
            }
            return;
        }

        $this->memory[$offset] = $buffer;
    }

    /**
     * Reads one or serveal bytes from memory
     *
     * @param type $offset Starting address
     * @param type $length Number of bytes to read
     * @return array
     *
     * @todo Check ranges on input
     */

    public function getMemory($offset, $length)
    {
        return array_slice($this->memory, $offset, $length);
    }

    /**
     * Reads a single byte from memory
     *
     * @param type $address
     * @return int
     *
     * @todo Preform range check
     */
    public function getByte($address)
    {
        if (is_int($address) && $address >= 0 && $address <= 0xFFF) {
            return isset($this->memory[$address]) ? $this->memory[$address] : 0x00;
        }
        throw new \Exception("Out of range: 0x".dechex($address)."\n");
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
