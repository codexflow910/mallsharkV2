<?php

namespace Packages\Notifications\Repositories\Eloquent\V2;

use Illuminate\Support\Facades\DB;
use Packages\Notifications\Models\V2\User;
use Illuminate\Support\ItemNotFoundException;
use Packages\Notifications\Models\V2\Notification;
use Packages\Notifications\Contracts\V2\BaseApiResponse;
use Packages\Notifications\Contracts\V2\NotificationsRepositoryInterface;

class NotificationsRepository implements NotificationsRepositoryInterface
{
    use BaseApiResponse;

    public function __construct()
    {
    }

    public function getUserNotifications()
    {
        try {
            $userId = $this->currentUserId();

            $notifications = Notification::where('recipient_id', $userId)
                ->where('app_flag', 'buyer')
                ->orderBy('notification_id', 'DESC')
                ->get();

            $unreadCount = Notification::where('recipient_id', $userId)
                ->where('app_flag', 'buyer')
                ->where('seen', 0)
                ->count();

            $notifications->each(function ($notification) {
                $notification->is_read = $notification->status == 1;
            });

            if(empty($notifications)){
                throw new ItemNotFoundException('No notifications found');
            }

            return [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ];
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function getSellerNotifications()
    {
        try {
            $userId = $this->currentUserId();

            $notifications = Notification::where('recipient_id', $userId)
                ->where('app_flag', 'seller')
                ->orderBy('notification_id', 'DESC')
                ->get();

            $unreadCount = Notification::where('recipient_id', $userId)
                ->where('app_flag', 'seller')
                ->where('seen', false)
                ->count();

            // Map notifications to add the `is_read` attribute
            $notifications->each(function ($notification) {
                $notification->is_read = $notification->status == 1;
            });

            if(empty($notifications)){
                throw new ItemNotFoundException('No notifications found');
            }

            return [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ];
        }  catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteNotification($notificationId)
    {
        try {
            $userId = $this->currentUserId();

            $deleted = Notification::where('notification_id', $notificationId)
                ->where('recipient_id', $userId)
                ->delete();
                
            if(!$deleted){
                throw new ItemNotFoundException('Notification not found or already deleted');
            }

            $notifications = Notification::where('recipient_id', $userId)
                ->orderBy('notification_id', 'DESC')
                ->get();

            foreach ($notifications as $s_notification) {
                if ($s_notification->status == 0) {
                    $s_notification->is_read = false;
                } else {
                    $s_notification->is_read = true;
                }
            }

            return $notifications;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function countUnreadNotifications($appFlag)
    {
        try {
            $userId = $this->currentUserId();

            return Notification::where('recipient_id', $userId)
                ->where('app_flag', $appFlag)
                ->where('seen', 0)
                ->count();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateNotificationStatus($notificationId)
    {
        try {
            $userId = $this->currentUserId();

            DB::table('notifications')
                ->where('notification_id', $notificationId)
                ->where('recipient_id', $userId)
                ->update(['status' => 1,'seen' => 1]);

            $notification = Notification::where('notification_id', $notificationId)
                ->where('recipient_id', $userId)
                ->first();

            if ($notification) {
                $notification->is_read = $notification->seen == 1;
                return $notification;
            }

            throw new ItemNotFoundException('Notification not found');

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function userNotificationChangeStatus($appFlag)
    {
        try {
            DB::beginTransaction();
                $userId = $this->currentUserId();

                User::where('user_id', $userId)
                    ->update(['has_notification' => 0]);

                DB::table('notifications')
                    ->where('recipient_id', $userId)
                    ->where('app_flag', $appFlag)
                    ->update(['seen' => 1]);

                DB::commit();

                return ['status' => true, 'message' => 'All notifications marked as seen'];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function currentUserId()
    {
        $userId = auth()->user()->user_id;

        if (empty($userId)) {
            throw new ItemNotFoundException('User not found');
        }
        return $userId;
    }
    
}
