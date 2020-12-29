<?php

namespace App\Model;

use App\Entity\Alert as AlertEntity;

final class Alert
{
    private ?string $name = null;
    private ?string $file = null;
    private bool $active = false;

    public static function createFromEntity(AlertEntity $alert): self
    {
        $self = new self();
        $self->name = $alert->getName();
        $self->active = $alert->isActive();

        return $self;
    }

    public static function createEmpty(): self
    {
        $self = new self();
        $self->name = '';

        return $self;
    }

    private function __construct()
    {
    }
}