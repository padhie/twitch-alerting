<?php

declare(strict_types=1);

namespace App\Form\Model;

final class Alert
{
    public bool $active = false;
    public ?string $name = null;
    public ?string $sound = null;
}