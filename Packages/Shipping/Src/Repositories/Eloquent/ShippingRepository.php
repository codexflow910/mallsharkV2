<?php

namespace Packages\Shipping\Repositories\Eloquent;

use Exception;
use Packages\Store\Models\Store;
use Illuminate\Support\Facades\DB;
use Packages\Shipping\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Packages\Product\Models\Product;
use Packages\Order\Models\StoreOrder;
use Illuminate\Support\Facades\Session;
use Packages\Core\Contracts\CoreHelpers;
use Packages\Shipping\Models\FreeShipping;
use Packages\Shipping\Models\FixedShipping;
use Packages\Shipping\Models\ShippingOption;
use Illuminate\Support\ItemNotFoundException;
use Packages\Shipping\Models\ShippingCarrier;
use Packages\Shipping\Models\ShippingProfile;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Shipping\Contracts\BaseApiResponse;
use Packages\Shipping\Contracts\ShippingHelpers;
use Packages\Shipping\Models\CarrierBasedShipping;
use Packages\Shipping\Models\StoreShippingCarrier;
use Packages\Shipping\Contracts\ShippingRepositoryInterface;

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

    public function storeCarrier($data)
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
                    'config' => $data['config'],
                    'enabled' => $data['enabled'] ?? true,
                ]
            );

            return $carrier;
        } catch (Exception $e) {
            throw new Exception('Error assigning carrier to store: ' . $e->getMessage());
        }
    }

    public function getCarrier(int $storeId, ?int $carrierId = null)
    {
        try {

            $carrier = StoreShippingCarrier::where('store_id', $storeId)
                ->when($carrierId, fn($query) => $query->where('shipping_carrier_id', $carrierId))
                ->get();

            if ($carrier->isEmpty()) {
                throw new ItemNotFoundException('No Carrier Found');
            }

            return $carrier;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error fetching carrier: ' . $e->getMessage());
        }
    }
}
