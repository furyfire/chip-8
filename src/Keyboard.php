<?php

namespace furyfire\chip8;

class Keyboard
{

    protected $keys = array();

    public function __construct()
    {
        $this->keys = array_fill(0, 16, 0);
    }

    public function pressKey($key)
    {
        $this->keys[$key] = true;
    }

    public function depressKey($key)
    {
        $this->keys[$key] = true;
    }


    public function getKey($key)
    {
        return $this->keys[$key];
    }
}
