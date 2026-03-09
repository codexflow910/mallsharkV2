<?php

namespace Packages\SocialLogin\Repositories\Eloquent\V4;

use Packages\SocialLogin\Contracts\SocialLoginRepositoryInterface;
use Packages\Auth\Models\V4\User;
use Packages\Auth\Models\V4\DeviceToken;
use Packages\SocialLogin\Contracts\BaseApiResponse;
class SocialLoginRepository implements SocialLoginRepositoryInterface
{
    use BaseApiResponse;
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function findOrCreateUser(array $userData)
    {
        $user = $this->model->updateOrCreate(
            ['email' => $userData['email']], // condition
            [
                'name'            => $userData['name'],
                'register_source' => $userData['register_source'],
                'status'          => $userData['status'],
            ]
        );
        return $user->load('get_states');
    }

    public function findDeviceToken($userId, $fcmToken)
    {
        return DeviceToken::where('user_id', $userId)->where('fcm_token', $fcmToken)->first();
    }

    public function createDeviceToken(array $data)
    {
        return DeviceToken::create($data);
    }
}
