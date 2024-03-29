<?php

namespace App\Message;

class DoSomething
{
    public function __construct(
        public int $id,
        public string $someText,
        public string $someTimestamp,
    ) {
    }
}
