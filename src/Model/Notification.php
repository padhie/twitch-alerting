<?php

namespace App\Model;

final class Notification
{
    /**
     * @param array<int|string, mixed> $variables
     */
    public function __construct(
        public readonly string $type,
        public readonly string $message,
        public readonly array $variables = []
    ) {
    }
}