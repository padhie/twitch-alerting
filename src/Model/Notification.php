<?php

namespace App\Model;

final class Notification
{
    private string $type;
    private string $message;
    private array $variables;

    public function __construct(string $type, string $message, array $variables = [])
    {
        $this->type = $type;
        $this->message = $message;
        $this->variables = $variables;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}