<?php

namespace Packages\Shipping\Services\V4;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;
use Packages\Shipping\Contracts\V4\ShippingHelpers;
use Packages\Shipping\Contracts\V4\BaseApiResponse;
use Packages\Shipping\Repositories\Eloquent\V4\ShippingRepository;


class ShippingService

{
    use BaseApiResponse;
    use ShippingHelpers;
    protected $shippingRepository;

    public function __construct(ShippingRepository $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }
    public function updateShippingProfile($data)
    {
        try {
            if (isset($data['shipping_option_id']) && $data['shipping_option_id'] != '') {
                //    Update Shipping Profile
                $updateProfile = $this->shippingRepository->updateShippingProfile($data);

                if ($updateProfile['status'] == true) {
                    return $updateProfile['data'];
                } else {
                    throw new ItemNotFoundException('No Store Found');
                }
            } else {
                // Save Shipping profile
                $saveProfile = $this->shippingRepository->saveShippingProfile($data);

                if ($saveProfile['status'] == true) {
                    return $saveProfile['data'];
                } else {
                    throw new ItemNotFoundException('No Store Found');
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getShippingOptions()
    {
        try {
            $shipping_options =  $this->shippingRepository->getShippingOptions();
            $shippingOption = [];
            foreach ($shipping_options as $shipping_option) {
                if ($shipping_option->delete_status == 1) {
                    array_push($shippingOption, $shipping_option);
                }
            }
            if (isset($shippingOption) && !empty($shippingOption)) {
                return $shippingOption;
            } else {
                throw new ItemNotFoundException('No Shipping Profile Found');
            }
        } catch (\Exception $e) {

            throw $e;
        }
    }
    public function deleteShippingProfile($shipping_id)
    {
        try {
            return $this->shippingRepository->deleteShippingProfile($shipping_id);
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getCarriers()
    {
        try {
            $carriers = $this->shippingRepository->getCarriers();
            $userId = auth()->id();
            $store = ShippingHelpers::getAccessibleStore($userId);

            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            $storeId = $store->store_id;
            $storeConnectedServices = $this->shippingRepository->getCarrier($storeId);

            // Map connected carriers by carrier_id for quick lookup
            if(!$storeConnectedServices){
                $storeConnectedServices = collect([]);
                $connectedMap = collect([]);
            }else{
                $connectedMap = $storeConnectedServices->keyBy('shipping_carrier_id');
            }
            

            // Attach connection info to each carrier
            $carriers->transform(function ($carrier) use ($connectedMap) {
                if (isset($connectedMap[$carrier->id])) {
                    $storeCarrier = $connectedMap[$carrier->id];
                    $carrier->connected = true;
                    $carrier->config_data = $storeCarrier->config_data ?? null;
                } else {
                    $carrier->connected = false;
                    $carrier->config_data = null;
                }
                return $carrier;
            });

            return $carriers;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function storeNewCarrier($data)
    {
        try {
            $data['slug'] = str_slug($data['name']);
            return $this->shippingRepository->storeNewCarrier($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function storeProfile($data)
    {
        DB::beginTransaction();

        try {
            $store = ShippingHelpers::getAccessibleStore(auth()->user()->user_id);
            $profileData = [
                'store_id' => $store->store_id,
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state_id' => $data['state_id'],
                'zip_code' => $data['zip_code'],
                'is_general' => $data['is_general'],
            ];

            $profileData = $this->shippingRepository->createProfile($profileData);

            if (!$data['is_general'] && !empty($data['zones'])) {
                foreach ($data['zones'] as $zone) {

                    $zoneData = [
                        'shipping_profile_id' => $profileData->id,
                        'name' => $zone['name'],
                    ];
                    $createZone = $this->shippingRepository->createZone($zoneData);
                    // Attach states to zone
                    $zoneStates = $createZone->states()->sync($zone['state_ids']);
                    $details = $zone['shipping_details'] ?? [];

                    if (!empty($details['fixed_shipping'])) {
                        $this->shippingRepository->createFixedShipping([
                            'profile_type' => 'custom',
                            'type_id' => $createZone->id,
                            ...$details['fixed_shipping']
                        ]);
                    }

                    if (!empty($details['carrier_shipping'])) {
                        $this->shippingRepository->createCarrierShipping([
                            'profile_type' => 'custom',
                            'type_id' => $createZone->id,
                            ...$details['carrier_shipping']
                        ]);
                    }

                    if (!empty($details['free_shipping'])) {
                        $this->shippingRepository->createFreeShipping([
                            'profile_type' => 'custom',
                            'type_id' => $createZone->id,
                            ...$details['free_shipping']
                        ]);
                    }
                }
            } elseif ($data['is_general']) {

                $details = $data['shipping_details'] ?? [];

                if (!empty($details['fixed_shipping'])) {
                    $this->shippingRepository->createFixedShipping([
                        'profile_type' => 'general',
                        'type_id' => $profileData->id,
                        ...$details['fixed_shipping']
                    ]);
                }

                if (!empty($details['carrier_shipping'])) {
                    $this->shippingRepository->createCarrierShipping([
                        'profile_type' => 'general',
                        'type_id' => $profileData->id,
                        ...$details['carrier_shipping']
                    ]);
                }

                if (!empty($details['free_shipping'])) {
                    $this->shippingRepository->createFreeShipping([
                        'profile_type' => 'general',
                        'type_id' => $profileData->id,
                        ...$details['free_shipping']
                    ]);
                }
            }
            DB::commit();

            return $profileData;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProfile($data, $id)
    {
        DB::beginTransaction();

        try {
            $profile = $this->shippingRepository->findProfile($id);

            if (!$profile) {
                throw new itemNotFoundException('Shipping profile not found.');
            }

            $profileData = [
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state_id' => $data['state_id'],
                'zip_code' => $data['zip_code'],
                'is_general' => $data['is_general'],
            ];
            $updatedProfile = $profile->update($profileData);

            if ($data['is_general']) {
                // Delete any existing shipping details first
                $this->shippingRepository->deleteGeneralShipping($profile->id);

                if (isset($data['shipping_details'])) {
                    $shippingDetails = $data['shipping_details'];

                    if (!empty($shippingDetails['fixed_shipping'])) {
                        $this->shippingRepository->createFixedShipping([
                            'profile_type' => 'general',
                            'type_id' => $profile->id,
                            ...$shippingDetails['fixed_shipping']
                        ]);
                    }

                    if (!empty($shippingDetails['carrier_shipping'])) {
                        $this->shippingRepository->createCarrierShipping([
                            'profile_type' => 'general',
                            'type_id' => $profile->id,
                            ...$shippingDetails['carrier_shipping']
                        ]);
                    }

                    if (!empty($shippingDetails['free_shipping'])) {
                        $this->shippingRepository->createFreeShipping([
                            'profile_type' => 'general',
                            'type_id' => $profile->id,
                            'free_shipping_time' => $shippingDetails['free_shipping']['free_shipping_time']
                        ]);
                    }
                }
            } else {
                // Delete existing zones and related shipping
                $this->shippingRepository->deleteZonesWithShipping($profile->id);
                foreach ($data['zones'] as $zone) {
                    $zoneData = [
                        'shipping_profile_id' => $profile->id,
                        'name' => $zone['name'],
                    ];

                    $createdZone = $this->shippingRepository->createZone($zoneData);
                    $createdZone->states()->sync($zone['state_ids']);

                    if (!empty($zone['shipping_details']['fixed_shipping'])) {
                        $this->shippingRepository->createFixedShipping([
                            'profile_type' => 'custom',
                            'type_id' => $createdZone->id,
                            ...$zone['shipping_details']['fixed_shipping']
                        ]);
                    }

                    if (!empty($zone['shipping_details']['carrier_shipping'])) {
                        $this->shippingRepository->createCarrierShipping([
                            'profile_type' => 'custom',
                            'type_id' => $createdZone->id,
                            ...$zone['shipping_details']['carrier_shipping']
                        ]);
                    }

                    if (!empty($zone['shipping_details']['free_shipping'])) {
                        $this->shippingRepository->createFreeShipping([
                            'profile_type' => 'custom',
                            'type_id' => $createdZone->id,
                            'free_shipping_time' => $zone['shipping_details']['free_shipping']['free_shipping_time']
                        ]);
                    }
                }
            }
            DB::commit();

            return $updatedProfile;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteProfile($id)
    {
        DB::beginTransaction();

        try {
            $profile = $this->shippingRepository->findProfile($id);

            if (!$profile) {
                throw new itemNotFoundException('Shipping profile not found.');
            }

            // Delete zones and related data if custom
            if (!$profile->is_general) {
                foreach ($profile->zones as $zone) {
                    // Delete carrier-based shipping if exists
                    $this->shippingRepository->deleteFreeShipping('custom', $zone->id);
                    $this->shippingRepository->deleteCarrierShipping('custom', $zone->id);
                    $this->shippingRepository->deleteFixedShipping('custom', $zone->id);

                    // Detach zone states and delete zone
                    $zone->states()->detach();
                    $zone->delete();
                }
            } else {
                // Delete general profile shipping data
                $this->shippingRepository->deleteFreeShipping('general', $profile->id);
                $this->shippingRepository->deleteCarrierShipping('general', $profile->id);
                $this->shippingRepository->deleteFixedShipping('general', $profile->id);
            }

            $profile->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getprofiles($storeId, $profileId)
    {
        return $this->shippingRepository->getProfiles($storeId, $profileId);
    }

    public function assignOrUpdateCarrierToStore($data)
    {
        return $this->shippingRepository->assignOrUpdateCarrierToStore($data);
    }

    public function getCarrier($storeId, $carrierId = null)
    {
        return $this->shippingRepository->getCarrier($storeId, $carrierId);
    }

    public function storeCarrierConfig(array $data)
    {
        $carrier = strtolower($data['carrier']);

        switch ($carrier) {
            case 'usps':
                return $this->shippingRepository->storeUspsConfig($data);

            case 'fedex':
                return $this->shippingRepository->storeFedexConfig($data);

            case 'shipstation':
                return $this->shippingRepository->storeShipstationConfig($data);

            default:
                throw new Exception("Unsupported carrier: {$carrier}");
        }
    }

   public function deleteStoreCarrier($storeId, $carrierId)
    {
        try {

            return $this->shippingRepository->deleteStoreCarrier($storeId, $carrierId);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
