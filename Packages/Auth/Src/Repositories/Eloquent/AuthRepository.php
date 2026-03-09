<?php

namespace Packages\Auth\Repositories\Eloquent;

use Exception;
use Carbon\Carbon;
use Packages\Auth\Models\DeviceToken;
use Packages\Auth\Models\User;
use App\Http\Controllers\MailgunController;
use Packages\Auth\Contracts\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Auth\Contracts\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    use BaseApiResponse;
    protected $model;
    private $mailgun;
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->mailgun = new MailgunController();
    }

    public function getUserByEmail($email)
    {
        return $this->model::with('get_states')->where('email', $email);  
    }

    public function findDeviceToken($userId, $fcmToken)
    {
        return DeviceToken::where('user_id', $userId)->where('fcm_token', $fcmToken)->first();
    }
    public function updateDeviceToken($userId,$fcmToken,$appFlag)
    {
        return DeviceToken::where('user_id', $userId)->where('app_flag',$appFlag)->update(['fcm_token'=>$fcmToken]);
    }
    public function createDeviceToken(array $data)
    {
        return DeviceToken::create($data);
    }

    public function updatePassword($newPassword)
    {
        $user = auth()->user();
        if (!$user) {
            throw new ItemNotFoundException('User not found');
        }
    
        $user->password = md5($newPassword);
        $user->save();
    
        return $user;
    }

    public function updateForgotKey($email, $otp)
    {
        $date = Carbon::now();
        $user = $this->getUserByEmail($email)->first();

        if(empty($user)) {
            throw new ItemNotFoundException('User not found');
        }

        $user['forget_key'] = $otp;
        $user['forget_key_date'] = $date;
        $user->save();

        return $user;
    }

    public function createNewPassword($data)
    {
        try {
            $password = md5($data['password']);
            $email = $data['email'];

            $updateData = array(
                'password' => $password,
                'forget_key' => ''
            );

            $user = $this->model->where('email', '=', $email)->first();

            if (!$user) {
                throw new ItemNotFoundException('Given Email does not exist.');
            }

            $response = $user->update($updateData);

            if ($response == true) {
                return $this->getUserByEmail($email)->first();
            }

            return false;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
     public function requestDeletion($data)
    {
        try {
           
           $user = User::with('store')->where('email',$data['email'])->first();
           if (!$user) {
			throw new UnauthorizedException('No User found');
		    }
        
            if($user){
                if($user->store()->exists()){
                    $data['store_name'] = $user->store->store_name;
                }
                $user->is_mark_deleted = '1';
                $user->save();
            }
                $subject = 'Request for delete user account';
                $html = view('mail.delete-account', ['data' => $data]);
                $params = [
                    'from'    => 'MallShark  ' . $data['email'],
                    'to'      => env('NO_REPLY_EMAIL'),
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                return $user;

        } catch (Exception $e) {
             throw $e;
        }
    }

    public function logoutUser($user, $appFlag, $fcmToken)
    {
        try {
            if (isset($appFlag) && $appFlag == 'seller') {

                $tokenExists = DeviceToken::where('user_id', $user->user_id)
                ->where('fcm_token', $fcmToken)
                ->where('app_flag', 'seller')
                ->exists();

                if (!$tokenExists) {
                    throw new Exception('Device token not found');
                }
                DeviceToken::where('user_id', $user)->where('fcm_token', $fcmToken)->where('app_flag', 'seller')->delete();
            }
            if (isset($appFlag) && $appFlag == 'buyer') {

                $tokenExists = DeviceToken::where('user_id', $user->user_id)
                ->where('fcm_token', $fcmToken)
                ->where('app_flag', 'buyer')
                ->exists();

                if (!$tokenExists) {
                    throw new Exception('Device token not found');
                }
                DeviceToken::where('user_id', $user)->where('fcm_token', $fcmToken)->where('app_flag', 'buyer')->delete();
            }
            $user->token()->revoke();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function updateUserProfile($user, $updateData)
    {
        try {
            $res = User::where('user_id', '=', $user)->update($updateData);
            return $res;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function updateShippingAddress($data, $userId)
    {
        try {
            $user = User::where('user_id', '=', $userId)->first(); 
            
            if ($user) {
                return $user->update($data); 
            } else {
                throw new ItemNotFoundException('User not found');
            }
        } catch (\Exception $e) {
            throw $e; 
        }
    }


    public function analytics($user)
    {
        try {

            $analytics = [
                'pendingOrders' => $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'pending')->count(),
                'shippedOrders' => $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'shipped')->count(),
                'deliveredOrders' => $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'completed')->count(),
            ];
            return $analytics;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }
    
}
