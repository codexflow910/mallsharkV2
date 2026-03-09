<?php

namespace Packages\SocialLogin\Repositories\Eloquent;

use Packages\SocialLogin\Contracts\SocialLoginRepositoryInterface;
use Packages\Auth\Models\User;
use Packages\Auth\Models\DeviceToken;
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
        return $this->model->firstOrCreate(
            ['email' => $userData['email']],
            ['name' => $userData['name'], 'register_source' => $userData['register_source']]
        );
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
