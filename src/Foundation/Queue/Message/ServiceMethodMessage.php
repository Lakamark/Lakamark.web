<?php

namespace App\Foundation\Queue\Message;

readonly class ServiceMethodMessage
{
    public function __construct(
        private string $serviceName,
        private string $method,
        private array $params = [],
    ) {
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
