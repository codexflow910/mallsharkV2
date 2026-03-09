<?php

namespace Packages\Notifications\Services\V4;

use Exception;
use Google_Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ItemNotFoundException;
use Packages\Notifications\Contracts\V4\NotificationsHelpers;
use Packages\Notifications\Repositories\Eloquent\V4\NotificationsRepository;

class NotificationsService

{
    use NotificationsHelpers;
    protected $notificationsRepository;

    public function __construct(NotificationsRepository $notificationsRepository)
    {
        $this->notificationsRepository = $notificationsRepository;
    }

    public function fetchUserNotifications()
    {
        return $this->notificationsRepository->getUserNotifications();
    }

    public function getSellerNotifications()
    {
        return $this->notificationsRepository->getSellerNotifications();
    }

    public function deleteNotification($notificationId)
    {
        return $this->notificationsRepository->deleteNotification($notificationId);
    }

    public function getUnreadNotificationsCount($appFlag)
    {
        return $this->notificationsRepository->countUnreadNotifications($appFlag);
    }
    
    public function markAsRead($notificationId)
    {
        return $this->notificationsRepository->updateNotificationStatus($notificationId);
    }

    public function userNotificationChangeStatus($appFlag)
    {
        return $this->notificationsRepository->userNotificationChangeStatus($appFlag);
    }

}
