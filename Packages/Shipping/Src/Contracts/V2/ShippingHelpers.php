<?php

namespace Packages\Shipping\Contracts\V2;

use Exception;
use Packages\Shipping\Models\V2\ShippingOption;
use Packages\Shipping\Models\V2\ShippingProfile;
use Packages\Store\Models\V2\{Store};
use Illuminate\Support\Facades\DB;
use Packages\Shipping\Models\V2\ShippingCarrier;

trait ShippingHelpers
{
	public function createShippingProfile($data, $userId)
	{
		$is_domesticfree = 0;
		$shippingOptions = new ShippingOption();
		$shippingOptions->user_id = !empty($userId) ? $userId : '';
		$shippingOptions->profile_name = isset($data['profile_name']) ? $data['profile_name'] : '';
		$shippingOptions->shipping_origin = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
		$shippingOptions->origin_zipcode = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
		$shippingOptions->is_domesticfree = $is_domesticfree;
		$shippingOptions->domestic_handling_fee = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : 0;
		$shippingOptions->international_handling_fee = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : 0;
		$shippingOptions->shipping_service = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';
		$shippingOptions->flat_shipping_rate = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';
		$shippingOptions->shipping_country_id = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
		$shippingOptions->shipping_state_id = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
		$shippingOptions->shipping_city = isset($data['shipping_city']) ? $data['shipping_city'] : '';
		$shippingOptions->shipping_address1 = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
		$shippingOptions->shipping_address2 = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
		$shippingOptions->store_id = isset($data['store_id']) ? $data['store_id'] : '';
		$shippingOptions->save();
		return $shippingOptions;
	}

	public function updateShippingProfileById($data, $userId)
	{
		$is_domesticfree = 0;
		$shippingOptions = new ShippingOption();
		$updateParms['shipping_service'] = isset($data['edit_ship_service']) ? $data['edit_ship_service'] : '';
		$updateParms['shipping_service'] = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';
		$updateParms['user_id'] = $userId;
		$updateParms['processing_time_id'] = isset($data['process_time']) ? $data['process_time'] : '';
		$updateParms['profile_name'] = isset($data['profile_name']) ? $data['profile_name'] : '';
		$updateParms['shipping_origin'] = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
		$updateParms['origin_zipcode'] = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
		$updateParms['is_internationalfree'] = isset($data['international_free_shipping']) ? 1 : 0;
		$updateParms['is_domesticfree'] = $is_domesticfree;
		$updateParms['domestic_handling_fee'] = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : '';
		$updateParms['international_handling_fee'] = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : '';
		$updateParms['shipping_country_id'] = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
		$updateParms['shipping_state_id'] = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
		$updateParms['shipping_city'] = isset($data['shipping_city']) ? $data['shipping_city'] : '';
		$updateParms['shipping_address1'] = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
		$updateParms['shipping_address2'] = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
		$updateParms['flat_shipping_rate'] = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';
		try {
			$result = ShippingOption::where('id', $data['shipping_option_id'])->first();
			if (empty($result)) {

				return false;
			}
			$shippingOptions->where('id', $data['shipping_option_id'])->update($updateParms);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		return $shippingOptions;
	}

	public static function getProductsIdsForShippingId($profileId)
	{
		$shippingProfile = ShippingProfile::find($profileId);

		if (!$shippingProfile) {
			return [];
		}

		return $shippingProfile->products()->pluck('products.product_id')->toArray();
	}
	public static function getAccessibleStore($userId)
    {
        $store = Store::where('user_id', $userId)->first();

        if (!$store) {
            $pivot = DB::table('store_user')->where('user_id', $userId)->first();

            if ($pivot) {
                $store = Store::find($pivot->store_id);
            }
        }

        return $store;
    }

	public static function getShippingCarrierBySlug($slug)
	{
		return ShippingCarrier::where('slug', $slug)->first();
	}
}
