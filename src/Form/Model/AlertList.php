<?php

namespace App\Form\Model;

use App\Entity\Alert as AlertEntity;
use App\Form\AlertListForm;

final class AlertList
{
    /** @var array<int, Alert> */
    private array $items = [];

    /** @param AlertEntity[] $alerts */
    public static function createFromEntities(array $alerts): self
    {
        $self = new self();

        $count = 0;
        foreach ($alerts as $alert) {
            $item = new Alert();
            $item->active = $alert->isActive();
            $item->name = $alert->getName();

            if ($count >= AlertListForm::MAX_ITEMS) {
                continue;
            }

            $self->items[$count] = $item;

            $count++;
        }

        return $self;
    }

    public function __construct()
    {
    }

    public function __get(string $field): string|bool|null
    {
        [$property, $index] = explode('_', $field);

        return $this->items[$index]?->{$property} ?? null;
    }

    public function __set(string $field, mixed $value): void
    {
        [$property, $index] = explode('_', $field);

        if (!isset($this->items[$index])) {
            $this->items[$index] = new Alert();
        }

        $this->items[$index]->{$property} = $value;
    }

    public function getItem(int $index): Alert
    {
        return $this->items[$index];
    }
}