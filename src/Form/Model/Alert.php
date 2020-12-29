<?php

namespace App\Form\Model;

use App\Entity\Alert as AlertEntity;
use App\Form\AlertForm;

final class Alert
{
    public bool $active_0 = false;
    public ?string $name_0 = null;
    public ?string $sound_0 = null;

    public bool $active_1 = false;
    public ?string $name_1 = null;
    public ?string $sound_1 = null;

    public bool $active_2 = false;
    public ?string $name_2 = null;
    public ?string $sound_2 = null;

    public bool $active_3 = false;
    public ?string $name_3 = null;
    public ?string $sound_3 = null;

    public bool $active_4 = false;
    public ?string $name_4 = null;
    public ?string $sound_4 = null;

    public bool $active_5 = false;
    public ?string $name_5 = null;
    public ?string $sound_5 = null;

    public bool $active_6 = false;
    public ?string $name_6 = null;
    public ?string $sound_6 = null;

    public bool $active_7 = false;
    public ?string $name_7 = null;
    public ?string $sound_7 = null;

    public bool $active_8 = false;
    public ?string $name_8 = null;
    public ?string $sound_8 = null;

    /**
     * @param AlertEntity[] $alerts
     */
    public static function createFromEntities(array $alerts): self
    {
        $self = new self();

        $count = 0;
        foreach ($alerts as $alert) {
            if ($count >= AlertForm::MAX_ITEMS) {
                continue;
            }

            $nameField = 'name_' . $count;
            $activeField = 'active_' . $count;

            $self->{$nameField} = $alert->getName();
            $self->{$activeField} = $alert->isActive();

            $count++;
        }

        return $self;
    }

    public function __construct()
    {
    }
}