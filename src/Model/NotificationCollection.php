<?php

namespace App\Model;

final class NotificationCollection
{
    /** @var Notification[] */
    private array $notifications = [];

    public function add(Notification $notification): void
    {
        foreach ($this->notifications as $innerNotification) {
            if (
                $innerNotification->type === $notification->type
                && $innerNotification->message === $notification->message
            ) {
                return;
            }
        }

        $this->notifications[] = $notification;
    }

    public function addWithDuplicate(Notification $notification): void
    {
        $this->notifications[] = $notification;
    }

    /**
     * @return Notification[]
     */
    public function getAllNotifications(): array
    {
        return $this->notifications;
    }
}