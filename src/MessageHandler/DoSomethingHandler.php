<?php

namespace App\MessageHandler;

use App\Message\DoSomething;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DoSomethingHandler
{
    public function __invoke(DoSomething $message)
    {
        print_r($message);
    }
}
