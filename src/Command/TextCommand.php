<?php

namespace furyfire\chip8\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
//use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use furyfire\chip8\CHIP8;

class TextCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('textual')
            ->setDescription('Run a Chip-8 program in the console using the CLI window as display')
            ->addArgument(
                'file', InputArgument::REQUIRED, 'File to load'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log   = new Logger('');
        $log->pushHandler(new ConsoleHandler($output));
        $chip8 = new CHIP8($log);
        $chip8->loadFile($input->getArgument('file'));
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
    }

}
