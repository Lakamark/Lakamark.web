<?php

namespace App\Foundation\Queue;

use App\Foundation\Queue\Message\ServiceMethodMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class EnqueueMethod
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function enqueue(string $service, string $method, array $params = [], ?\DateTimeInterface $date = null): void
    {
        $stamps = [];
        // We should the service with a dealy
        if (null !== $date) {
            $delay = 1000 * ($date->getTimestamp() - time());
            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }
        $this->bus->dispatch(new ServiceMethodMessage($service, $method, $params), $stamps);
    }
}
