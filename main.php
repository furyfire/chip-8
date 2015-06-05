<?php
include "vendor/autoload.php";

use furyfire\chip8\CHIP8;

    $log   = new Psr\Log\NullLogger('');
    $chip8 = new CHIP8($log);
    $chip8->loadFile($argv[1]);
    while ($state = $chip8->step()) {
        switch ($state) {
            case CHIP8::STATE_TERM:
                echo "Program ended";
                die;
                break;
            case CHIP8::STATE_RUNNING:
                if ($chip8->getScreen()->updated()) {
                    $chip8->getScreen()->renderToAsciiArt();
                }
                break;
            case CHIP8::STATE_AWAIT:
                //$chip8->pressWaitingKey();
                break;
        }
    }