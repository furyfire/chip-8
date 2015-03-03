<?php
namespace furyfire\chip8;

class Registers
{
    protected $V;
    protected $I = 0;

    public function __construct()
    {
        //Intialize all registeries values as 0
        $this->V = array_fill(0, 16, 0x00);
    }

    /**
     * Returns a requested register
     *
     * @param int $index Register index
     * @return int Value of register
     * @throws \InvalidArgumentException
     */
    public function getV($index)
    {
        if (is_int($index) and $index >= 0 and $index <= 15) {
            return $this->V[$index];
        }
        throw new \InvalidArgumentException("Invalid register");
    }

    /**
     * Sets a register value
     *
     * @param type $index Register index
     * @param type $value An 8bit value
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setV($index, $value)
    {
        if (is_int($index) and $index >= 0 and $index <= 15 and $value >= 0 and $value <= 255) {
            $this->V[$index] = $value;

            return;
        }
        throw new \InvalidArgumentException("Invalid register and/or value");
    }

    /**
     * Sets the I register value
     *
     * @param type $value The new 12bit value
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setI($value)
    {
        if (is_int($value) and $value >= 0 and $value <= 0x1000) {
            $this->I = $value;

            return;
        }
        throw new \InvalidArgumentException("I registery value invalid");
    }

    /**
     * Returns the I register
     *
     * @return int 12 bit value
     */
    public function getI()
    {
        return $this->I;
    }

    /**
     * Sets the Flag according to a boolean condition
     *
     * @param type $condition Boolean condition
     * @return void
     */
    public function flag($condition)
    {
        if ($condition) {
            $this->setFlag();
            return;
        }
        $this->clrFlag();
    }

    /**
     * Sets the Flag
     */
    public function setFlag()
    {
        $this->V[15] = 1;
    }

    /**
     * Clears the Flag
     */
    public function clrFlag()
    {
        $this->V[15] = 0;
    }

    /**
     * Returns a debug string of the registers
     *
     * @return string
     */
    public function debug()
    {
        $string = "I: 0x".dechex($this->I)."\n";
        foreach ($this->V as $key => $value) {
            $string .= "V$key: 0x".dechex($value)."\n";
        }

        return $string;
    }

    /**
     * Returns an associated array of the registers
     *
     * @return array
     */
    public function getAll()
    {
        return array(
            'I' => $this->I,
            'V' => $this->V,
        );
    }
}
