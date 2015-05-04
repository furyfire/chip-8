<?php

namespace furyfire\chip8;

class Keyboard
{

    protected $keys = array();

    public function __construct()
    {
        $this->keys = array_fill(0, 16, 0);
    }

    /**
     * Press a key
     *
     * @param int $key Numeric key value (1-16)
     */
    public function pressKey($key)
    {
        if (Helpers::validateKey($key)) {
            $this->keys[$key] = true;
            return;
        }
        throw new \InvalidArgumentException("Invalid commit");
    }

    /**
     * Depress key
     *
     * @param int $key Numeric key value
     */
    public function depressKey($key)
    {
        if (Helpers::validateKey($key)) {
            $this->keys[$key] = false;
            return;
        }
        throw new \InvalidArgumentException("Invalid commit");
    }

    /**
     * Get the current status of a key (True for pressed, otherwise false)
     *
     * @param type $key Numeric key value
     * @return boolean
     */
    public function getKey($key)
    {
        if (Helpers::validateKey($key)) {
            return $this->keys[$key];
        }
        throw new \InvalidArgumentException("Invalid commit");
    }
}
