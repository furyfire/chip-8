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

    public function getV($index)
    {
        if (is_int($index) and $index >= 0 and $index <= 15) {
            return $this->V[$index];
        }
        throw new Exception("Invalid register");
    }

    public function setV($index, $value)
    {
        if (is_int($index) and $index >= 0 and $index <= 15 and $value >= 0 and $index <= 255) {
            $this->V[$index] = $value;

            return;
        }
        throw new Exception("Invalid register");
    }

    public function setI($value)
    {
        if (is_int($value) and $value >= 0 and $value <= 0x1000) {
            $this->I = $value;

            return;
        }
        throw new Exception("I registery value invalid");
    }

    public function getI()
    {
        return $this->I;
    }

    public function flag($condition)
    {
        if ($condition) {
            $this->setFlag();
            return;
        }
        $this->clrFlag();
    }

    public function setFlag()
    {
        $this->V[15] = 1;
    }
    
    public function clrFlag()
    {
        $this->V[15] = 0;
    }

    public function debug()
    {
        $string = "I: 0x".dechex($this->I)."\n";
        foreach ($this->V as $key => $value) {
            $string .= "V$key: 0x".dechex($value)."\n";
        }

        return $string;
    }

    public function getAll()
    {
        return array(
            'I' => $this->I,
            'V' => $this->V,
        );
    }
}
