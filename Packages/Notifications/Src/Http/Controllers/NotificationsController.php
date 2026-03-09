<?php

namespace Packages\Notifications\Http\Controllers;


use Exception;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\ItemNotFoundException;
use Packages\Notifications\Contracts\BaseApiResponse;
use Packages\Notifications\Services\NotificationsService;
use Packages\Notifications\Http\Requests\GoogleLoginRequest;
use Packages\Notifications\Http\Requests\FacebookLoginRequest;

class NotificationsController extends Controller
{
    use BaseApiResponse;

    protected $notificationsService;

    public function __construct(NotificationsService $notificationsService)
    {
        $this->notificationsService = $notificationsService;

    }

    public function allNotifications()
    {
        try {
            $result = $this->notificationsService->fetchUserNotifications();

            return $this->successResponse($result, "Notifications retrieved successfully.");

        } catch (Exception $e) {
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function sellerAllNotifications()
    {
        try {
            $response = $this->notificationsService->getSellerNotifications();

            return $this->successResponse($response, "Notifications retrieved successfully.");

        } catch (Exception $e) {
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteNotification($notification_id)
    {
        try {
            $response = $this->notificationsService->deleteNotification($notification_id);

            return $this->successResponse($response, "Notification deleted successfully.");
        } catch (Exception $e) {
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function unreadCountNotification(Request $request)
    {
        try {
            $appFlag = $request->input('app_flag');

            if (!in_array($appFlag, ['seller', 'buyer'])) {
                throw new ValidationException('Invalid app_flag. Must be seller or buyer.');
            }

            $unreadCount = $this->notificationsService->getUnreadNotificationsCount($appFlag);

            return $this->successResponse($unreadCount, 'Unread notifications count fetched successfully.');

        } catch (Exception $e) {
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function markReadNotification(Request $request)
    {
        try {
            $notificationId = $request->query('notification_id'); // Now passed via query string

            if (!$notificationId) {
                throw new ValidationException('Notification ID is required.');
            }

            $updatedNotification = $this->notificationsService->markAsRead($notificationId);

            return $this->successResponse($updatedNotification, 'Notification marked as read successfully.');

        } catch (Exception $e) {
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function userNotificationChangeStatus(Request $request)
    {
        try {
            $appFlag = $request->input('app_flag');

            if (!$appFlag) {
                throw new ValidationException('App flag is required.');
            }

            $response = $this->notificationsService->userNotificationChangeStatus($appFlag);

            return $response['status']
                ? $this->successResponse(null, $response['message'])
                : $this->errorResponse($response['message']);
        } catch (\Exception $e) {

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

}
