<?php
namespace furyfire\chip8;

class Timer
{
    protected $delay;
    protected $sound;

    protected $lastrun;
    protected $leftovers = 0;

    public function __construct()
    {
        $this->delay = 0;
        $this->sound = 0;

        $this->lastrun = microtime(true);
    }

    /**
     * Returns the current value of the delay timer
     *
     * @return int Delay timer value
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Sets the delay timer
     *
     * @param type $time 8bit timer value
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setDelay($time)
    {
        if (Helpers::validateByte($time)) {
            $this->delay = $time;
            return;
        }
        throw new \InvalidArgumentException("Not a valid timer value");
    }

    /**
     * Sets the sound timer
     *
     * @param type $time 8bit timer value
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setSound($time)
    {
        if (Helpers::validateByte($time)) {
            $this->sound = $time;
            return;
        }
        throw new \InvalidArgumentException("Not a valid sound value");
    }

    /**
     * Advance the buildtin timers according to passed time.
     */
    public function advance()
    {
        $delta = microtime(true) - $this->lastrun + $this->leftovers;

        $ticks = $delta*60;
        $this->leftovers = $ticks - floor($ticks);

        if ($this->delay) {
            $this->delay  -=  floor($ticks);
            if ($this->delay < 0) {
                $this->delay = 0;
            }
        }
        if ($this->sound) {
            $this->sound  -=  floor($ticks);
            if ($this->sound < 0) {
                $this->sound = 0;
            }
        }
        $this->lastrun = microtime(true);
    }
}
