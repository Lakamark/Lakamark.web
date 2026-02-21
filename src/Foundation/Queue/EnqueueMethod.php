<?php

namespace App\Foundation\Queue;

use App\Foundation\Queue\Message\ServiceMethodMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

readonly class EnqueueMethod
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * To create a que for send in message later.
     *
     * @throws ExceptionInterface
     */
    public function enqueue(
        string $serviceName,
        string $methodName,
        array $parameters = [],
        ?\DateTimeInterface $date = null,
    ): void {
        $stamps = [];
        if (null !== $date) {
            $delay = 1000 * ($date->getTimestamp() - time());

            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }

        $this->messageBus->dispatch(new ServiceMethodMessage($serviceName, $methodName, $parameters), $stamps);
    }
}
