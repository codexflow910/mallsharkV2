<?php

namespace Packages\Shipping\Repositories\Eloquent\V2;

use Exception;
use Packages\Store\Models\V2\Store;
use Illuminate\Support\Facades\DB;
use Packages\Shipping\Models\V2\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Packages\Product\Models\V2\Product;
use Packages\Order\Models\V2\StoreOrder;
use Illuminate\Support\Facades\Session;
use Packages\Core\Contracts\V2\CoreHelpers;
use Packages\Shipping\Models\V2\FreeShipping;
use Packages\Shipping\Models\V2\FixedShipping;
use Packages\Shipping\Models\V2\ShippingOption;
use Illuminate\Support\ItemNotFoundException;
use Packages\Shipping\Models\V2\ShippingCarrier;
use Packages\Shipping\Models\V2\ShippingProfile;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Shipping\Contracts\V2\BaseApiResponse;
use Packages\Shipping\Contracts\V2\ShippingHelpers;
use Packages\Shipping\Models\V2\CarrierBasedShipping;
use Packages\Shipping\Models\V2\StoreShippingCarrier;
use Packages\Shipping\Contracts\V2\ShippingRepositoryInterface;

class ShippingRepository implements ShippingRepositoryInterface
{
    use BaseApiResponse;
    use ShippingHelpers;
    use CoreHelpers;

    protected $model;

    public function __construct(ShippingOption $model)
    {
        $this->model = $model;
    }

    public function saveShippingProfile($data)
    {
        $userId = Auth::user()->user_id;
        if (empty($userId)) {
            throw new UnauthorizedException('No User Found');
        }
        $storeData = Store::where('user_id', $userId)->first();
        if (empty($storeData)) {
            throw new ItemNotFoundException('No Store Found');
        }
        $data['store_id'] = $storeData['store_id'];

        if (empty($data['profile_name'])) {
            throw new Exception('Profile name is missing');
        }
        $profile_name = $data['profile_name'];
        if (ShippingOption::where('profile_name', $profile_name)->where('user_id', $userId)->where('delete_status', 1)->exists()) {
            $message = "Profile is already exist with this name";
            return  ['status' => true, 'data' => $message];
        }
        if (!empty($profile_name)) {
            $data['profile_name'] = $profile_name;
            $shippingOptions = $this->createShippingProfile($data, $userId);
            return ['status' => true, 'data' => $shippingOptions];
        } else {
            throw new ItemNotFoundException('Incorrect');
        }
    }
    public function updateShippingProfile($data)
    {
        try {
            $userId = Auth::user()->user_id;
            if (empty($userId)) {
                throw new UnauthorizedException('No User Found');
            }
            $storeData = Store::where('user_id', $userId)->first();
            if (empty($storeData)) {
                throw new ItemNotFoundException('No Store Found');
            }
            $data['store_id'] = $storeData['store_id'];

            if (empty($data['profile_name'])) {
                throw new Exception('Profile name is missing');
            }
            $profile_name = $data['profile_name'];

            if (!empty($profile_name)) {
                $data['profile_name'] = $profile_name;
                $shippingOptions = $this->updateShippingProfileById($data, $userId);
                if (empty($shippingOptions)) {
                    throw new ItemNotFoundException('Shipping Profile Not Found');
                }
                return ['status' => true, 'data' => $shippingOptions];
            } else {
                throw new ItemNotFoundException('Incorrect');
            }
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getShippingOptions()
    {
        $user = auth()->user();
        if (empty($user)) {
            throw new UnauthorizedException('No User Found');
        }
        $shipping_options = $this->getShippingOptionByUserId($user->user_id);
        if (empty($shipping_options)) {
            throw new ItemNotFoundException('No Shipping Found');
        }
        return $shipping_options;
    }
    public function deleteShippingProfile($shippingId)
    {
        try {

            $userId = auth()->user()->user_id;

            // Find shipping profile, throw exception if not found
            $shippingProfile = $this->model
                ->where('id', $shippingId)
                ->where('user_id', $userId)
                ->first();

            if (!$shippingProfile) {
                throw new ItemNotFoundException('Shipping Profile not found');
            }

            DB::table('shipping_options')
                ->where('id', $shippingId)
                ->update(['delete_status' => '0']);

            // Find "Free Shipping" profile
            $freeShippingProfile = $this->model->where('profile_name', 'Free Shipping')->first();

            if ($freeShippingProfile) {
                Product::where('user_id', $userId)
                    ->where('shipping_option_id', $shippingId)
                    ->update(['shipping_option_id' => $freeShippingProfile->id]);
            }

            return true;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getCarriers()
    {
        try {
            $carriers = ShippingCarrier::all();
            return $carriers;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function storeNewCarrier($data)
    {
        try {
            $carrier = ShippingCarrier::create($data);
            return $carrier;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createProfile($data)
    {
        try {
            $profile = ShippingProfile::create($data);
            return $profile;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createZone($data)
    {
        try {
            $zone = Zone::create($data);
            return $zone;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createFixedShipping($data)
    {
        try {
            $fixedShipping = FixedShipping::create($data);
            return $fixedShipping;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createCarrierShipping($data)
    {
        try {
            $carrierShipping = CarrierBasedShipping::create($data);
            return $carrierShipping;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createFreeShipping($data)
    {
        try {
            return FreeShipping::create($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function findProfile($id)
    {
        return ShippingProfile::with('zones.states')->find($id);
    }

    public function deleteFixedShipping($profileType, $typeId)
    {
        try {
            FixedShipping::where('profile_type', $profileType)
                ->where('type_id', $typeId)
                ->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function deleteCarrierShipping($profileType, $typeId)
    {
        try {
            CarrierBasedShipping::where('profile_type', $profileType)
                ->where('type_id', $typeId)
                ->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function deleteGeneralShipping($profileId)
    {
        try {
            FixedShipping::where('profile_type', 'general')->where('type_id', $profileId)->delete();
            CarrierBasedShipping::where('profile_type', 'general')->where('type_id', $profileId)->delete();
            FreeShipping::where('profile_type', 'general')->where('type_id', $profileId)->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function deleteZonesWithShipping($profileId)
    {
        try {
            $zones = Zone::where('shipping_profile_id', $profileId)->get();

            if (!$zones) {
                throw new ItemNotFoundException('Zones not found');
            }

            foreach ($zones as $zone) {
                // Remove related shipping
                FixedShipping::where('profile_type', 'custom')->where('type_id', $zone->id)->delete();
                CarrierBasedShipping::where('profile_type', 'custom')->where('type_id', $zone->id)->delete();
                FreeShipping::where('profile_type', 'custom')->where('type_id', $zone->id)->delete();

                // Detach states
                $zone->states()->detach();

                // Delete zone
                $zone->delete();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function deleteFreeShipping($profileType, $typeId)
    {
        try {
            FreeShipping::where('profile_type', $profileType)
                ->where('type_id', $typeId)
                ->delete();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getProfiles($storeId, $profileId)
    {
        $query = ShippingProfile::with([
            'fixedShipping',
            'carrierShipping.carrier',
            'freeShipping',
            'zones.fixedShipping',
            'zones.carrierShipping.carrier',
            'zones.freeShipping',
            'zones.states',
        ])
            ->where('store_id', $storeId);

        if (!empty($profileId)) {
            $query->where('id', $profileId);
        }

        $result = $query->get();

        if ($result->isEmpty()) {
            throw new ItemNotFoundException('No shipping profiles found.');
        }

        return $result->map(function ($profile) {
            if ($profile->is_general) {
                foreach ($profile->zones as $zone) {
                    unset($zone->fixedShipping, $zone->carrierShipping);
                }
            } else {
                unset($profile->fixedShipping, $profile->carrierShipping);
            }

            return $profile;
        });
    }

    public function assignOrUpdateCarrierToStore(array $data)
    {
        try {
            $carrier = StoreShippingCarrier::updateOrCreate(
                [
                    'store_id' => $data['store_id'],
                    'shipping_carrier_id' => $data['shipping_carrier_id'],
                ],
                [
                    'enabled' => $data['enabled'] ?? false,
                ]
            );

            return $carrier;
        } catch (Exception $e) {
            throw new Exception('Error assigning carrier to store: ' . $e->getMessage());
        }
    }

    public function getCarrier($storeId, $carrierId = null)
    {
        try {
            $carriers = StoreShippingCarrier::with('carrier')
                ->where('store_id', $storeId)
                ->when($carrierId, fn($query) => $query->where('shipping_carrier_id', $carrierId))
                ->get();
            // Append config data for each carrier
            if(!$carriers){
                return [];
            }
            $carriers->transform(function ($item) {
                $carrierSlug = strtolower($item->carrier->slug ?? '');
                $config = null;

                switch ($carrierSlug) {
                    case 'usps':
                        $config = DB::table('usps_config')
                            ->where('store_id', $item->store_id)
                            ->first();
                        break;

                    case 'fedex':
                        $config = DB::table('fedex_config')
                            ->where('store_id', $item->store_id)
                            ->first();
                        break;

                    case 'shipstation':
                        $config = DB::table('shipstation_config')
                            ->where('store_id', $item->store_id)
                            ->first();
                        break;
                }

                // Attach config to the response object
                $item->config_data = $config;
                return $item;
            });

            return $carriers;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error fetching carrier: ' . $e->getMessage());
        }
    }

    public function storeUspsConfig(array $data)
    {
        try {
            return DB::table('usps_config')->updateOrInsert(
                ['store_id' => $data['store_id']],
                [
                    'consumer_key'    => $data['consumer_key'],
                    'consumer_secret' => $data['consumer_secret'],
                    'crid'            => $data['crid'],
                    'mailer_id'       => $data['mailer_id'],
                    'eps_number'      => $data['eps_number'],
                    'updated_at'      => now(),
                    'created_at'      => now(),
                ]
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function storeFedexConfig(array $data)
    {
        try {
            return DB::table('fedex_config')->updateOrInsert(
                ['store_id' => $data['store_id']],
                [
                    'consumer_key'    => $data['consumer_key'],
                    'consumer_secret' => $data['consumer_secret'],
                    'account_number'  => $data['account_number'],
                    'updated_at'      => now(),
                    'created_at'      => now(),
                ]
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function storeShipstationConfig(array $data)
    {
        try {
            return DB::table('shipstation_config')->updateOrInsert(
                ['store_id' => $data['store_id']],
                [
                    'consumer_key'    => $data['consumer_key'],
                    'consumer_secret' => $data['consumer_secret'],
                    'updated_at'      => now(),
                    'created_at'      => now(),
                ]
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function deleteStoreCarrier($storeId, $carrierId)
    {
        try {
            DB::beginTransaction();

            $result = StoreShippingCarrier::where('store_id', $storeId)
                ->where('shipping_carrier_id', $carrierId)
                ->delete();

            if (!$result) {
                throw new Exception("Carrier not found for this store");
            }
            
            $carrierSlug = ShippingCarrier::where('id', $carrierId)->value('slug');
            if (!$carrierSlug) {
                throw new Exception('Carrier not found.');
            }

            // Step 3: Dynamically determine config table
            $tableName = strtolower($carrierSlug) . '_config';

            // Step 4: Check if table exists before deleting
            if (!Schema::hasTable($tableName)) {
                throw new Exception("Configuration table '{$tableName}' does not exist.");
            }

            DB::table($tableName)
                ->where('store_id', $storeId)
                ->delete();

            DB::commit();

            return ucfirst($carrierSlug) . ' carrier deleted successfully.';

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
