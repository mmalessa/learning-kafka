<?php

namespace App\Command;

use App\Message\DoSomething;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:messenger-dispatch',
    description: 'Dispatch message'
)]
class MessengerDispatchCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Not ready yet...");
        $id = rand(1, 100000);
        $timestamp = date('Y-m-d H:i:s');
        $textLength = rand(3, 20);
        $text = $this->getRandomWord($textLength);
        $this->bus->dispatch(new DoSomething($id, $text, $timestamp));

        return Command::SUCCESS;
    }

    private function getRandomWord($len = 10): string {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
}
