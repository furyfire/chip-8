<?php
include "vendor/autoload.php";

use furyfire\chip8\CHIP8;

include "programs/programs.php";

$chip8 = new CHIP8();
$chip8->loadArray(${$argv[1]});

while ($state = $chip8->step()) {
    switch ($state) {
        case CHIP8::STATE_ENDED:
            echo "Program ended";
            break;
        case CHIP8::STATE_RUNNING:
            if ($chip8->getScreen()->updated()) {
                echo "==============\n";
                $chip8->getScreen()->renderToAsciiArt();
                //$chip8->getScreen()->renderToImage('screen/'.$t .".png");
            }
            break;
        case CHIP8::STATE_AWAIT:
            $chip8->pressWaitingKey(1);
            break;
    }
}
