<?php
include "vendor/autoload.php";

use furyfire\chip8\CHIP8;

include "programs.php";


$chip8 = new CHIP8;
$chip8->loadArray(${$argv[1]});
$t=0;
while(1) {
    $chip8->step();
    if($chip8->getScreen()->updated()) {
        echo "==============\n";
        $chip8->getScreen()->renderToAsciiArt();
        $t++;
        //$chip8->getScreen()->renderToImage('screen/'.$t .".png");
        //usleep(500000);
       }
}