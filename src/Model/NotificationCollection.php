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
                $innerNotification->getType() === $notification->getType()
                && $innerNotification->getMessage() === $notification->getMessage()
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

    public function getAllNotifications(): array
    {
        return $this->notifications;
    }

    public function getNotificationsByType(string $type): array
    {
        return array_filter(
            $this->notifications,
            static function (Notification $notification) use ($type): bool {
                return $notification->getType() === $type;
            }
        );
    }
}