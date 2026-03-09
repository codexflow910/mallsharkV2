<?php

namespace Packages\Auth\Repositories\Eloquent\V2;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Packages\Auth\Models\V2\DeviceToken;
use Packages\Auth\Models\V2\{User,CustomerWallet};
use Packages\Auth\Contracts\V2\AuthHelpers;
use App\Http\Controllers\MailgunController;
use Packages\Auth\Contracts\V2\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Auth\Contracts\V2\AuthRepositoryInterface;
use Packages\Core\Models\V2\MallsharkPurchasedPackage;

class AuthRepository implements AuthRepositoryInterface
{
    use BaseApiResponse;
    use AuthHelpers;
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

        $user['user_otp'] = $otp;
        $user['forget_key_date'] = $date;
        $user->save();

        return $user;
    }

     public function createNewPassword($data)
    {
        try {
            $password = md5($data['password']);
            if(isset($data['token']) && !empty($data['token'])) {
                $user = $this->model->where('login_key', '=', $data['token'])->first();
                $updateData = array(
                    'password' => $password,
                    'status' => '1',
                    'guest_user' => 0,
                );
                if (!$user) {
                    throw new ItemNotFoundException('Invalid token provided.');
                }
            } else if(isset($data['email']) && !empty($data['email'])) {
                $user = $this->model->where('email', '=', $data['email'])->first();
                $updateData = array(
                    'password' => $password,
                    'forget_key' => ''
                );
                if (!$user) {
                    throw new ItemNotFoundException('User not found.');
                }
            } else {
                throw new UnauthorizedException('Token is required to reset password.');
            }
            $response = $user->update($updateData);

            if ($response == true) {
                return $user;
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
    public function subscriptionPackage($storeId)
    {
        if (!$storeId) {
            throw new \InvalidArgumentException("store_id is required");
        }
        $storePackage = MallsharkPurchasedPackage::where('store_id', $storeId)
            ->where('expired_at', '>', now())
            ->where('status','active')
            ->latest('created_at')            
            ->first();

        return $storePackage;
    }

    public function assignUserToStore($storeId, $userId)
	{
		return DB::table('store_user')->insert([
			'store_id' => $storeId,
			'user_id'  => $userId,
		]);
	}

    public function assignPermissionsToUser($userId, $permissions)
    {
        try {
            DB::beginTransaction();

            DB::table('model_has_permissions')
                ->where('model_id', $userId)
                ->delete();

            // Insert new permissions
            $insertData = collect($permissions)->map(function ($permId) use ($userId) {
                return [
                    'permission_id' => $permId,
                    'model_id'      => $userId,
                    'model_type'    => User::class,
                ];
            })->toArray();
            $response = DB::table('model_has_permissions')->insert($insertData);
            
            DB::commit();
            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStoreAccessors($storeId)
    {
        $accessors = DB::table('store_user')
            ->join('users', 'store_user.user_id', '=', 'users.user_id')
            ->where('store_user.store_id', $storeId)
            ->select('users.user_id', 'users.name', 'users.email', 'store_user.store_id')
            ->get();

        $result = $accessors->map(function ($user) {
            return [
                'user_id'          => $user->user_id,
                'name'             => $user->name,
                'email'            => $user->email,
                'store_id'         => $user->store_id,
                'permissions'      => AuthHelpers::getModelPermissions($user->user_id)
            ];
        });

        return $result;
    }

   public function removeStoreAccessor(int $storeId, int $userId)
    {
        try {
            $deleted = DB::table('store_user')
                ->where('user_id', $userId)
                ->where('store_id', $storeId)
                ->delete();
                
            if ($deleted > 0) {
                DB::table('model_has_permissions')
                    ->where('model_id', $userId)
                    ->delete();
            }

            return $deleted > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function storeWalletAddress($userId, $walletAddress,$currency)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new ItemNotFoundException('User not found.');
            }
            $wallet = CustomerWallet::updateOrCreate(
                ['user_id' => $userId],
                [
                    'currency' => $currency,
                    'wallet_address' => $walletAddress,
                ]
            );
            
            return $wallet;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getCustomerWalletAddress($userId)
    {
        try {
            $wallet = CustomerWallet::where('user_id', $userId)->first();
            return $wallet;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
