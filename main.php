<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

include "vendor/autoload.php";
use furyfire\chip8\CHIP8;

include "programs/programs.php";



// create a log channel
$log = new Logger('');
$log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));

$chip8 = new CHIP8($log);
//$chip8->loadArray(${$argv[1]});
$chip8->loadFile($argv[1]);
while ($state = $chip8->step()) {
    //$chip8->logBreakpoint();
    switch ($state) {
        case CHIP8::STATE_TERM:
            echo "Program ended";
            die;
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
