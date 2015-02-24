<?php
namespace furyfire\chip8;

class Timer
{
   protected $delay;
   protected $sound;
   
   protected $last_run;
   protected $leftovers = 0;
   
   public function __construct()
   {
       $this->delay = 0;
       $this->sound = 0;
       
       $this->last_run = microtime(true);
   }
   
   public function getDelay() {
       return $this->delay;
   }
   public function setDelay($time) {
       if(is_int($time) and $time >= 0 and $time <= 0x1FF) {
           $this->delay = $time;
           return;
       }
       throw new Exception("Can not set timer");
   }
   public function setSound($time) {
       if(is_int($time) and $time >= 0 and $time <= 0x1FF) {
           $this->sound = $time;
       }
   }
   
   public function getSound() {
       return $this->sound;
   }
   
   
   public function advance() {
       
       $delta = microtime(true) - $this->last_run + $this->leftovers;
       
       $ticks = $delta*60;
       $this->leftovers = $ticks - floor($ticks);
       
       if($this->delay) {
           $this->delay  -=  floor($ticks);
           if($this->delay < 0) {
               $this->delay = 0;
           }
       }
       if($this->sound) {
           $this->sound  -=  floor($ticks);
           if($this->sound < 0) {
               $this->sound = 0;
           }
       }
       //echo "delay: ".$delta."\n";
       $this->last_run = microtime(true);
   }
}
