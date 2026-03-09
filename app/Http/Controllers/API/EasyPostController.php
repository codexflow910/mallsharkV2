<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UspsT;
use App\Models\Product;
use Exception;


class EasyPostController extends Controller
{

    private $_request;

    public function __construct(Request $request)
    {
        $this->_request = $request;
    }

    public function getPackageType($length, $width, $height, $weight, $quantity)
    {
        // Set weight limits for each package type (in pounds)
        $letterLimit = 3.5;
        $flatLimit = 13;
        $parcelLimit = 1120;
        $flatRateEnvelopeLimit = 1120;
        $flatRateLegalEnvelopeLimit = 1120;
        $flatRatePaddedEnvelopeLimit = 1120;
        $flatRateGiftCardEnvelopeLimit = 1120;
        $flatRateWindowEnvelopeLimit = 1120;
        $flatRateCardboardEnvelopeLimit = 1120;
        $smallFlatRateEnvelopeLimit = 1120;
        $smallFlatRateBoxLimit = 1120;
        $mediumFlatRateBoxLimit = 1120;
        $largeFlatRateBoxLimit = 1120;
        $softPackLimit = 320;

        // Set dimension limits for each package type (in inches)
        $letterDimensionLimit = ['length' => 11.5, 'width' => 6.125, 'height' => 0.25];
        $flatDimensionLimit = ['length' => 15, 'width' => 12, 'height' => 0.75];
        $parcelDimensionLimit = ['length' => 22, 'width' => 18, 'height' => 15];
        $flatRateEnvelopeDimensionLimit = ['length' => 12.5, 'width' => 9.5, 'height' => 0.5];
        $flatRateLegalEnvelopeDimensionLimit = ['length' => 15, 'width' => 9.5, 'height' => 0.5];
        $flatRatePaddedEnvelopeDimensionLimit = ['length' => 12.5, 'width' => 9.5, 'height' => 1.75];
        $flatRateGiftCardEnvelopeDimensionLimit = ['length' => 10, 'width' => 7, 'height' => 0.25];
        $flatRateWindowEnvelopeDimensionLimit = ['length' => 10, 'width' => 5, 'height' => 0.5];
        $flatRateCardboardEnvelopeDimensionLimit = ['length' => 12.5, 'width' => 9.5, 'height' => 3];
        $smallFlatRateEnvelopeDimensionLimit = ['length' => 10, 'width' => 6, 'height' => 0.25];
        $smallFlatRateBoxDimensionLimit = ['length' => 8.625, 'width' => 5.375, 'height' => 1.625];
        $mediumFlatRateBoxDimensionLimit = ['length' => 11.25, 'width' => 8.75, 'height' => 6];
        $largeFlatRateBoxDimensionLimit = ['length' => 12.25, 'width' => 12.25, 'height' => 6];
        $softPackDimensionLimit = ['length' => 18, 'width' => 14, 'height' => 8];

        // Calculate the total weight of the package
        $totalWidth = $width * $quantity;
        $totallength = $length * $quantity;
        $totalheight = $height * $quantity;
        $totalweight = $weight * $quantity;

        $girth = 2 * ($totallength + $totalWidth) + $totalheight;
        // echo '$totalWeight: <pre>' .print_r($totalWidth,true). '</pre>'; 

        // Check if package fits in Letter
        if ($totalweight <= $letterLimit && $totallength <= $letterDimensionLimit['length'] && $totalWidth <= $letterDimensionLimit['width'] && $totalheight <= $letterDimensionLimit['height']) {
            return 'Letter';
        }

        // Check if package fits in Flat
        if ($totalweight <= $flatLimit && $totallength <= $flatDimensionLimit['length'] && $totalWidth <= $flatDimensionLimit['width'] && $totalheight <= $flatDimensionLimit['height']) {
            return 'Flat';
        }

        // // Check if package fits in Parcel
        // if ($totalweight <= $parcelLimit && $totallength <= $parcelDimensionLimit['length'] && $totalWidth <= $parcelDimensionLimit['width'] && $totalheight <= $parcelDimensionLimit['height']) {
        //     return 'Parcel';
        // }
        // Check if package fits in Parcel
        if ($totalweight <= $parcelLimit && $girth <= '108') {
            return 'Parcel';
        }


        // Check if package fits in Flat Rate Envelope
        if ($totalweight <= $flatRateEnvelopeLimit && $totallength <= $flatRateEnvelopeDimensionLimit['length'] && $totalWidth <= $flatRateEnvelopeDimensionLimit['width'] && $totalheight <= $flatRateEnvelopeDimensionLimit['height']) {
            return 'FlatRateEnvelope';
        }

        // Check if package fits in Flat Rate Legal Envelope
        if ($totalweight <= $flatRateLegalEnvelopeLimit && $totallength <= $flatRateLegalEnvelopeDimensionLimit['length'] && $totalWidth <= $flatRateLegalEnvelopeDimensionLimit['width'] && $totalheight <= $flatRateLegalEnvelopeDimensionLimit['height']) {
            return 'FlatRateLegalEnvelope';
        }

        // Check if package fits in Flat Rate Padded Envelope
        if ($totalweight <= $flatRatePaddedEnvelopeLimit && $totallength <= $flatRatePaddedEnvelopeDimensionLimit['length'] && $totalWidth <= $flatRatePaddedEnvelopeDimensionLimit['width'] && $totalheight <= $flatRatePaddedEnvelopeDimensionLimit['height']) {
            return 'FlatRatePaddedEnvelope';
        }

        // Check if package fits in Flat Rate GiftCard Envelope
        if ($totalweight <= $flatRateGiftCardEnvelopeLimit && $totallength <= $flatRateGiftCardEnvelopeDimensionLimit['length'] && $totalWidth <= $flatRateGiftCardEnvelopeDimensionLimit['width'] && $totalheight <= $flatRateGiftCardEnvelopeDimensionLimit['height']) {
            return 'FlatRateGiftCardEnvelope';
        }

        // Check if package fits in Flat Rate Window Envelope
        if ($totalweight <= $flatRateWindowEnvelopeLimit && $totallength <= $flatRateWindowEnvelopeDimensionLimit['length'] && $totalWidth <= $flatRateWindowEnvelopeDimensionLimit['width'] && $totalheight <= $flatRateWindowEnvelopeDimensionLimit['height']) {
            return 'FlatRateWindowEnvelope';
        }

        // Check if package fits in Flat Rate Cardboard Envelope
        if ($totalweight <= $flatRateCardboardEnvelopeLimit && $totallength <= $flatRateCardboardEnvelopeDimensionLimit['length'] && $totalWidth <= $flatRateCardboardEnvelopeDimensionLimit['width'] && $totalheight <= $flatRateCardboardEnvelopeDimensionLimit['height']) {
            return 'FlatRateCardboardEnvelope';
        }

        // Check if package fits in Small Flat Rate Envelope
        if ($totalweight <= $smallFlatRateEnvelopeLimit && $totallength <= $smallFlatRateEnvelopeDimensionLimit['length'] && $totalWidth <= $smallFlatRateEnvelopeDimensionLimit['width'] && $totalheight <= $smallFlatRateEnvelopeDimensionLimit['height']) {
            return 'SmallFlatRateEnvelope';
        }

        // Check if package fits in Small Flat Rate Box Limit
        if ($totalweight <= $smallFlatRateBoxLimit && $totallength <= $smallFlatRateBoxDimensionLimit['length'] && $totalWidth <= $smallFlatRateBoxDimensionLimit['width'] && $totalheight <= $smallFlatRateBoxDimensionLimit['height']) {
            return 'SmallFlatRateBoxLimit';
        }

        // Check if package fits in Medium Flat Rate Box Limit
        if ($totalweight <= $mediumFlatRateBoxLimit && $totallength <= $mediumFlatRateBoxDimensionLimit['length'] && $totalWidth <= $mediumFlatRateBoxDimensionLimit['width'] && $totalheight <= $mediumFlatRateBoxDimensionLimit['height']) {
            return 'SmallFlatRateBoxLimit';
        }

        // Check if package fits in Large Flat Rate Box Limit
        if ($totalweight <= $largeFlatRateBoxLimit && $totallength <= $largeFlatRateBoxDimensionLimit['length'] && $totalWidth <= $largeFlatRateBoxDimensionLimit['width'] && $totalheight <= $largeFlatRateBoxDimensionLimit['height']) {
            return 'LargeFlatRateBoxLimit';
        }

        // Check if package fits in Soft Pack Limit
        if ($totalweight <= $softPackLimit && $totallength <= $softPackDimensionLimit['length'] && $totalWidth <= $softPackDimensionLimit['width'] && $totalheight <= $softPackDimensionLimit['height']) {
            return 'SoftPackLimit';
        }

        // If package doesn't fit in any package type, return null
        return '';
    }

    public function getRates(Request $request)
    {
        try {

            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'shippingdata' => 'required|array',
                    'shippingdata.*.length' => 'required',
                    'shippingdata.*.product_id' => 'required',
                    'shippingdata.*.width' => 'required',
                    'shippingdata.*.height' => 'required',
                    'shippingdata.*.weight' => 'required',
                    'shippingdata.*.product_quantity' => 'required',
                    'shippingdata.*.store_shipping_id' => 'required',
                    'user_shipping_id' => 'nullable',
                    'user_name' => 'required_without:user_shipping_id',
                    'user_street' => 'required_without:user_shipping_id',
                    'user_city' => 'required_without:user_shipping_id',
                    'user_state' => 'required_without:user_shipping_id',
                    'user_zip' => 'required_without:user_shipping_id',
                ]
            );
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            // die;

            $shipdata = $request['shippingdata'];
            if (isset($request['user_shipping_id'])) {


                $user_info = DB::table('shipping_addresses')->where('sh_id', $request['user_shipping_id'])->select(
                    'shipping_addresses.full_name',
                    'shipping_addresses.street',
                    'shipping_addresses.post_code',
                    'shipping_addresses.flat_other',
                    'shipping_addresses.city',
                    'shipping_addresses.user_id',
                    'users.email',
                    'users.contact_no',
                    'countries.name as country_name',
                    'countries.code as country_code',
                    'states.state_name',
                    'states.code as state_code'
                )
                    ->join('users', 'users.user_id', '=', 'shipping_addresses.user_id')
                    ->join('countries', 'countries.id', '=', 'shipping_addresses.country')->join('states', 'states.state_id', '=', 'shipping_addresses.state')->first();

                // User Shipping Address
                $full_name = isset($user_info->full_name) ? $user_info->full_name : "";
                $street = isset($user_info->street) ? $user_info->street : "";
                $user_city = isset($user_info->city) ? $user_info->city : "";
                $post_code = isset($user_info->post_code) ? $user_info->post_code : "";
                $user_state_code = isset($user_info->state_code) ? $user_info->state_code : "";
            } else {
                $full_name = isset($request->user_name) ? $request->user_name : "";
                $street = isset($request->user_street) ? $request->user_street : "";
                $user_city = isset($request->user_city) ? $request->user_city : "";
                $post_code = isset($request->user_zip) ? $request->user_zip : "";
                $user_state_code = isset($request->user_state) ? $request->user_state : "";
            }

            $selectPackage_usps = $this->getPackageType($request->length, $request->width, $request->height, $request->weight, '1');
            $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';

            $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));

            $selectrate = [];
            foreach ($shipdata as $value) {

                $store_info = DB::table('shipping_options')->select(
                    'shipping_options.origin_zipcode as store_zip',
                    'shipping_options.flat_shipping_rate',
                    'shipping_options.shipping_city',
                    'shipping_options.shipping_address1',
                    'shipping_options.shipping_address2',
                    'stores.store_name',
                    'stores.contact_info',
                    'users.email as store_email',
                    'countries.name as country_name',
                    'countries.code as country_code',
                    'states.state_name',
                    'states.code as state_code'
                )
                    ->join('stores', 'stores.store_id', '=', 'shipping_options.store_id')
                    ->join('users', 'users.user_id', '=', 'stores.user_id')
                    ->join('countries', 'countries.id', '=', 'shipping_options.shipping_country_id')
                    ->join('states', 'states.state_id', '=', 'shipping_options.shipping_state_id')
                    ->where('shipping_options.store_id', $value['store_shipping_id'])->get();
                // Srore From Address
                // dd($store_info);
                $company =  isset($store_info[0]->store_name) ? $store_info[0]->store_name : "company";
                $shipping_address1 =  isset($store_info[0]->shipping_address1) ? $store_info[0]->shipping_address1 : "";
                $shipping_address2 =  isset($store_info[0]->shipping_address2) ? $store_info[0]->shipping_address2 : "";
                $zip =  isset($store_info[0]->store_zip) ? $store_info[0]->store_zip : "";
                $city =  isset($store_info[0]->shipping_city) ? $store_info[0]->shipping_city : "";
                $state_code =  isset($store_info[0]->state_code) ? $store_info[0]->state_code : "";




                $shipment = $client->shipment->create([
                    "from_address" => [
                        "company" => $company,
                        "street1" => $shipping_address1,
                        "street2" => $shipping_address2,
                        "city"    => $city,
                        "state"   => $state_code,
                        "zip"     => $zip,
                        "phone"   => "415-456-7890",
                    ],
                    "to_address" => [
                        "name"    => $full_name,
                        "street1" => $street,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "310-808-5243",
                    ],
                    "parcel" => [
                        "length" => $value['length'],
                        "width"  => $value['width'],
                        "height" => $value['height'],
                        "weight" => $value['weight'],
                        //    "predefined_package" =>  $selectPackage,
                    ],

                ]);
                if (!empty($shipment['messages'])) {
                    if ($shipment['messages'][0]['type'] == 'rate_error') {
                        $error = $shipment['messages'][0]['message'];
                        return response()->json(['error' => $error], 401);
                    }
                } else {


                    $ratesArray = [];
                    foreach ($shipment->rates as $rate) {
                        if ($rate->service != 'GroundAdvantage') {
                            $ratesArray[] = [
                                'rate_id' => $rate->id,
                                'ship_id' => $shipment->id,
                                'service' => $rate->service,
                                'carrier' => $rate->carrier,
                                'rate' => $rate->rate * $value['product_quantity'],
                                'currency' => $rate->currency,
                            ];
                        }
                    }
                    $selectrate["product_id-" . $value['product_id']] = $ratesArray;
                }
            }
            return response()->json(['status' => true, 'message' => 'Rates retrieved successfully', 'rates' => $selectrate], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // OnChange Request user change service
    public function getRateWhenProductAdd(Request $request)
    {
        // return $request;
        $array = array();
        for ($i = 10; $i >= 1; $i--) {
            $selectPackage_usps = $this->getPackageType($request->productSize_length, $request->productSize_width, $request->productSize_height, $request->productWeight, $i);
            $array[$i] = $selectPackage_usps;
        }


        // return $array;
        $arryValue = array_filter($array);
        // return $arryValue;
        if (!empty($arryValue)) {
            $highestIndex = max(array_keys($arryValue));
            // return $highestIndex;
            $highestIndxeData = $arryValue[$highestIndex];
            // dd($highestIndxeData);
            $selectPackage =  isset($highestIndxeData) ?  $highestIndxeData : '';
        } else {
            $highestIndex = 0;
            $selectPackage = "";
        }

        // return $selectPackage;
        $insertedData = array();
        // EasypostController::SHIPPING_API;
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        try {
            $shipment = $client->shipment->create([
                "from_address" => [
                    // "id" => "adr_4d55c33ca13a11eda5cdac1f6bc7bdc6"
                    "company" => "EasyPost",
                    "street1" => "118 2nd Street",
                    // "street2" => "4th Floor",
                    "city"    => "San Francisco",
                    "state"   => "CA",
                    "zip"     => "90278",
                    "phone"   => "415-456-7890",
                ],
                "to_address" => [
                    // "id" => "adr_4d535cc2a13a11eda041ac1f6bc72124"
                    "name"    => "Dr. Steve Brule",
                    "street1" => "179 N Harbor Dr",
                    "city"    => "Redondo Beach",
                    "state"   => "CA",
                    "zip"     => "90277",
                    "phone"   => "310-808-5243",
                ],
                "parcel" => [
                    "length" => $request->productSize_length,
                    "width"  => $request->productSize_width,
                    "height" => $request->productSize_height,
                    "weight" => $request->productWeight,
                    "predefined_package" =>  $selectPackage,
                ],

            ]);
        } catch (Exception $e) {

            $response = "An error was returned from our shipment generation service" . htmlspecialchars($e->getMessage());

            return $error = array('error' => $response);
        }
        // return $shipment;
        if (!empty($shipment['messages'])) {
            if ($shipment['messages'][0]['type'] == 'rate_error') {
                // $error = $shipment['messages'][0]['message'];
                // return $error = array('error' => $error);

                //returning  max limit 10 temporarily

                $success = "success";
                return $success = array('success' => $success, 'maxLimitQuantity' => '10');
            }
        } else {
            $success = "success";
            return $success = array('success' => $success, 'maxLimitQuantity' => $highestIndex);
        }
    }
    public function getRateWhenProductAddApi(Request $request)
    {
        // return $request;
        $array = array();
        for ($i = 10; $i >= 1; $i--) {
            $selectPackage_usps = $this->getPackageType($request->productSize_length, $request->productSize_width, $request->productSize_height, $request->productWeight, $i);
            $array[$i] = $selectPackage_usps;
        }


        // return $array;
        $arryValue = array_filter($array);
        // return $arryValue;
        if (!empty($arryValue)) {
            $highestIndex = max(array_keys($arryValue));
            // return $highestIndex;
            $highestIndxeData = $arryValue[$highestIndex];
            // dd($highestIndxeData);
            $selectPackage =  isset($highestIndxeData) ?  $highestIndxeData : '';
        } else {
            $highestIndex = 0;
            $selectPackage = "";
        }

        // return $selectPackage;
        $insertedData = array();
        // EasypostController::SHIPPING_API;
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        try {
            $shipment = $client->shipment->create([
                "from_address" => [
                    // "id" => "adr_4d55c33ca13a11eda5cdac1f6bc7bdc6"
                    "company" => "EasyPost",
                    "street1" => "118 2nd Street",
                    // "street2" => "4th Floor",
                    "city"    => "San Francisco",
                    "state"   => "CA",
                    "zip"     => "90278",
                    "phone"   => "415-456-7890",
                ],
                "to_address" => [
                    // "id" => "adr_4d535cc2a13a11eda041ac1f6bc72124"
                    "name"    => "Dr. Steve Brule",
                    "street1" => "179 N Harbor Dr",
                    "city"    => "Redondo Beach",
                    "state"   => "CA",
                    "zip"     => "90277",
                    "phone"   => "310-808-5243",
                ],
                "parcel" => [
                    "length" => $request->productSize_length,
                    "width"  => $request->productSize_width,
                    "height" => $request->productSize_height,
                    "weight" => $request->productWeight,
                    "predefined_package" =>  $selectPackage,
                ],

            ]);
        } catch (Exception $e) {

            $response = "An error was returned from our shipment generation service" . htmlspecialchars($e->getMessage());

            return $error = array('error' => $response);
        }
        // return $shipment;
        if (!empty($shipment['messages'])) {
            if ($shipment['messages'][0]['type'] == 'rate_error') {
                // $error = $shipment['messages'][0]['message'];
                // return $error = array('error' => $error);

                //returning  max limit 10 temporarily

                // $success = "success";
                return $success = array('status' => false, 'message' => 'No matching rates were found');
            }
        } else {
            $success = "success";
            return $success = array('status' => $success, 'maxLimitQuantity' => $highestIndex);
        }
    }
    public function createShipment(Request $request)
    {

        // // sleep(10); 
        // $noDimensions = true;
        // // $noDimensions = false;
        // if ($noDimensions) {
        //     // dump('called');
        //     $request['product_length'] = null;
        //     $request['product_width'] = null;
        //     $request['product_height'] = null;
        // }
        // dd($request->all());
        $product_slug = $request->input('product_slug');
        $params = array();
        parse_str($request->shipment_data, $params);

        $shipping_service = isset($request->value) ? $request->value : "ParcelSelect";
        $product_title = isset($request->product_title) ? $request->product_title : "";
        $product_weight = isset($request->weight) && $request->weight != 0 ? $request->weight : "1";
        $product_length = isset($request->product_length) ? $request->product_length : null;
        $product_width  = isset($request->product_width) ? $request->product_width : null;
        $product_height = isset($request->product_height) ? $request->product_height : null;
        $product_quantity = isset($request->product_quantity) ? $request->product_quantity : "1";
        $product_price = isset($request->product_price) ? $request->product_price : "1";
        $selectPackage_usps = $this->getPackageType($product_length, $product_width, $product_height, $product_weight, $product_quantity);
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';
        // dd($product_weight,$product_length,$product_width,$product_height);
        if ($product_height != null && $product_weight != null && $product_width != null && $product_length != null) {

            $totalproduct_weight = $product_weight * $product_quantity;
            $totalproduct_length = $product_length * $product_quantity;
            $totalproduct_width  = $product_width * $product_quantity;
            $totalproduct_height = $product_height * $product_quantity;
        }
        
// dd($totalproduct_weight);
        // echo '$product_length: <pre>' .print_r($product_length,true). '</pre>'; 
        // echo '$product_width: <pre>' .print_r($product_width,true). '</pre>'; 
        // echo '$product_height: <pre>' .print_r($product_height,true). '</pre>'; 
        // echo '$product_quantity: <pre>' .print_r($product_quantity,true). '</pre>'; 
        // echo '$product_weight: <pre>' .print_r($product_weight,true). '</pre>'; die;
        // echo '$selectPackage: <pre>' .print_r($selectPackage,true). '</pre>'; die();
        // echo 'Data: <pre>' .print_r($params,true). '</pre>'; die();
        if (sizeof($params) == 1) {
            // dd(12);
            $setArrayKey = array_keys($params);
            $orignal_value_shiping_id = $setArrayKey[0];
            $user_info = DB::table('shipping_addresses')->where('sh_id', $orignal_value_shiping_id)->select(
                'shipping_addresses.full_name',
                'shipping_addresses.street',
                'shipping_addresses.post_code',
                'shipping_addresses.flat_other',
                'shipping_addresses.city',
                'shipping_addresses.user_id',
                'users.email',
                'users.contact_no',
                'countries.name as country_name',
                'countries.code as country_code',
                'states.state_name',
                'states.code as state_code'
            )
                ->join('users', 'users.user_id', '=', 'shipping_addresses.user_id')
                ->join('countries', 'countries.id', '=', 'shipping_addresses.country')->join('states', 'states.state_id', '=', 'shipping_addresses.state')->first();

            $store_info = DB::table('shipping_options')->select(
                'shipping_options.origin_zipcode as store_zip',
                'shipping_options.flat_shipping_rate',
                'shipping_options.shipping_city',
                'shipping_options.shipping_address1',
                'shipping_options.shipping_address2',
                'stores.store_name',
                'stores.contact_info',
                'users.email as store_email',
                'countries.name as country_name',
                'countries.code as country_code',
                'states.state_name',
                'states.code as state_code'
            )
                ->join('stores', 'stores.store_id', '=', 'shipping_options.store_id')
                ->join('users', 'users.user_id', '=', 'stores.user_id')
                ->join('countries', 'countries.id', '=', 'shipping_options.shipping_country_id')
                ->join('states', 'states.state_id', '=', 'shipping_options.shipping_state_id')
                ->where('shipping_options.id', $request->shipping_option_id)->get();

            //  dd($store_info);
            // User Shipping Address
            $full_name = isset($user_info->full_name) ? $user_info->full_name : "";
            $street = isset($user_info->street) ? $user_info->street : "";
            $user_city = isset($user_info->city) ? $user_info->city : "";
            $post_code = isset($user_info->post_code) ? $user_info->post_code : "";
            $user_country_code = isset($user_info->country_code) ? $user_info->country_code : "";
            if ($user_country_code == '1') {
                $country = 'US';
            } else {
                $country = 'CA';
            }
            $user_state_code = isset($user_info->state_code) ? $user_info->state_code : "";
            $user_phone = isset($user_info->contact_no) ? $user_info->contact_no : "";
            $user_email = isset($user_info->email) ? $user_info->email : "";
            $flat_other = isset($user_info->flat_other) ? $user_info->flat_other : "";

            // Srore From Address
            $company =  isset($store_info[0]->store_name) ? $store_info[0]->store_name : "company";
            $shipping_address1 =  isset($store_info[0]->shipping_address1) ? $store_info[0]->shipping_address1 : "";
            $shipping_address2 =  isset($store_info[0]->shipping_address2) ? $store_info[0]->shipping_address2 : "";
            $phone =  isset($store_info[0]->contact_info) ? $store_info[0]->contact_info : "";
            $zip =  isset($store_info[0]->store_zip) ? $store_info[0]->store_zip : "";
            $city =  isset($store_info[0]->shipping_city) ? $store_info[0]->shipping_city : "";
            $country_code =  isset($store_info[0]->country_code) ? $store_info[0]->country_code : "";
            $state_code =  isset($store_info[0]->state_code) ? $store_info[0]->state_code : "";
            $store_email =  isset($store_info[0]->store_email) ? $store_info[0]->store_email : "";
            $flat_shipping_rate =  isset($store_info[0]->flat_shipping_rate) ? $store_info[0]->flat_shipping_rate : "";


            try {
                $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
                $shipment = $client->shipment->create([
                    "from_address" => [
                        "company" => $company,
                        "street1" => $shipping_address1,
                        // "street2" => $shipping_address2,
                        "city"    => $city,
                        "state"   => $state_code,
                        "zip"     => $zip,
                        "phone"   => "415-456-7890",
                        'country' => $country_code,
                        // 'email'   => $store_email
                    ],
                    "to_address" => [
                        "name"    => $full_name,
                        "street1" => $street,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "415-456-7891",
                        'country' => $country,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $product_length,
                        "width"  => $product_width,
                        "height" => $product_height,
                        "weight" => $product_weight,
                        // "predefined_package" =>  $selectPackage,
                    ],
                    'customs_info' => [
                        'eel_pfc' => 'NOEEI 30.37(a)',
                        'customs_certify' => true,
                        'customs_signer' => 'MallShark',
                        'contents_type' => 'merchandise',
                        'contents_explanation' => 'Customs contents explanation',
                        'restriction_type' => 'none',
                        'restriction_comments' => '',
                        'non_delivery_option' => 'return',
                        'customs_items' => [
                            [
                                'description' => $product_title,
                                'quantity' => $product_quantity,
                                'weight' => $product_weight,
                                'value' => $product_price,
                                'hs_tariff_number' => '123456',
                                'origin_country' => 'US'
                            ]
                        ]
                    ]
                ]);

                //  dd($shipment);
            } catch (Exception $e) {

                // $response = array(
                //     'code' => 'error',
                //     'msg' => "An error was returned from our shipment generation service, please show this to the administrator:<br/><br/>" . preg_replace("/\r\n|\r|\n/", '<br/>', htmlspecialchars($e->getMessage())),
                // );
                // echo json_encode($response);
                // die;
            }
            $rates = $shipment->rates;
            $shipData = [];
            foreach ($rates as $rate) {

                array_push($shipData, json_decode($rate, true));
            };
            return json_encode($shipData);
            // dd($rateArray);
            return json_encode($shipment->rates);
            $flagSelected = false;
            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return $error = array('error' => $error);
                }
            } else {


                
            }
            //echo '$shipment if condtion: <pre>' .print_r($shipment,true). '</pre>'; die();
        } else {
           

            // Store From Address
            $store_info = DB::table('shipping_options')->select(
                'shipping_options.origin_zipcode as store_zip',
                'shipping_options.flat_shipping_rate',
                'shipping_options.shipping_city',
                'shipping_options.shipping_address1',
                'shipping_options.shipping_address2',
                'stores.store_name',
                'stores.contact_info',
                'users.email as store_email',
                'countries.name',
                'countries.code as country_code',
                'states.state_name',
                'states.code as state_code'
            )
                ->where('shipping_options.id', $request->shipping_option_id)
                ->join('users', 'users.user_id', '=', 'shipping_options.user_id')
                ->join('stores', 'stores.user_id', 'users.user_id')
                ->join('countries', 'countries.id', '=', 'shipping_options.shipping_country_id')
                ->join('states', 'states.state_id', '=', 'shipping_options.shipping_state_id')->get();
            // echo 'store: <pre>' .print_r($store_info,true). '</pre>'; 
            // echo 'user: <pre>' .print_r($params,true). '</pre>'; 

            // echo 'Data: <pre>' .print_r($params,true). '</pre>'; die();
            // $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
            $userId = '';
            $country_id = isset($params['country']) ? $params['country'] : "";
            $state_id = isset($params['states_countries']) ? $params['states_countries'] : "";
            $userInfo = getUserById($userId);
            $stateInfo = getStatebyId($state_id);
            // echo '$stateInfo: <pre>' .print_r($stateInfo,true). '</pre>'; die();
            $countryInfo = getCountryById($country_id);

            $user_data = array();

            $user_data['full_name'] = isset($params['full_name']) ? $params['full_name'] : "";
            $user_data['user_country_code'] = isset($countryInfo['code']) ? $countryInfo['code'] : "";
            $user_data['user_state_code'] = isset($stateInfo['code']) ? $stateInfo['code'] : "";
            $user_data['street'] = isset($params['street_address']) ? $params['street_address'] : "";
            $user_data['zipcode'] = isset($params['zipcode']) ? $params['zipcode'] : "";
            $user_data['user_city'] = isset($params['city_name']) ? $params['city_name'] : "";
            $user_data['user_phone'] = isset($userInfo->contact_no) ? $userInfo->contact_no : "";
            $user_data['user_email'] = isset($userInfo->email) ? $userInfo->email : "";
            //  echo 'Data: <pre>' .print_r($user_data,true). '</pre>'; 
            // echo '$user_data: <pre>' .print_r($user_data,true). '</pre>'; die();
            // User Shipping Address
            $full_name = isset($params['full_name']) ? $params['full_name'] : "";
            $user_country_code = isset($params['country']) ? $params['country'] : "";
            if ($user_country_code == '1') {
                $country = 'US';
            } else {
                $country = 'CA';
            }
            $user_state_code = isset($user_data['user_state_code']) ? $user_data['user_state_code'] : "";
            $street = isset($params['street_address']) ? $params['street_address'] : "";
            $post_code = isset($params['zipcode']) ? $params['zipcode'] : "";
            $user_city = isset($params['city_name']) ? $params['city_name'] : "";
            $user_phone = isset($userInfo->contact_no) ? $userInfo->contact_no : "";
            $user_email = isset($userInfo->email) ? $userInfo->email : "";
            $flat_other = isset($params['flat_other']) ? $params['flat_other'] : "";
            //dd($post_code);

            $street_address = str_replace('+', ' ', $street);

            // Store From Address
            $company =  isset($store_info[0]->store_name) ? $store_info[0]->store_name : "company";
            $shipping_address1 =  isset($store_info[0]->shipping_address1) ? $store_info[0]->shipping_address1 : "";
            $shipping_address2 =  isset($store_info[0]->shipping_address2) ? $store_info[0]->shipping_address2 : "";
            $phone =  isset($store_info[0]->contact_info) ? $store_info[0]->contact_info : "";
            $zip =  isset($store_info[0]->store_zip) ? $store_info[0]->store_zip : "";
            $city =  isset($store_info[0]->shipping_city) ? $store_info[0]->shipping_city : "";
            $country_code =  isset($store_info[0]->country_code) ? $store_info[0]->country_code : "";
            $state_code =  isset($store_info[0]->state_code) ? $store_info[0]->state_code : "";
            $store_email =  isset($store_info[0]->store_email) ? $store_info[0]->store_email : "";
            $flat_shipping_rate =  isset($store_info[0]->flat_shipping_rate) ? $store_info[0]->flat_shipping_rate : "";
            // dimension validation
            // dd('12',$product_height);
            if (($product_height == null || $product_height == 0) && ($product_length == null || $product_length == 0) &&  ($product_width == null || $product_width == 0)) {
                try {
                    // dd($product_weight);
                    $rateFirst =  UspsT::getDpmesticRates($zip, $post_code, $product_weight, 'First');
                    $ratePriority =  UspsT::getDpmesticRates($zip, $post_code, $product_weight, 'Priority');
                    $rateExpress =  UspsT::getDpmesticRates($zip, $post_code, $product_weight, 'Express');
                    $rateFirst = json_decode($rateFirst);
                    $ratePriority = json_decode($ratePriority);
                    $rateExpress = json_decode($rateExpress);
                    $rateFirst->service = 'First';
                    $ratePriority->service = 'Priority';
                    $rateExpress->service = 'Express';
                    // dd($rateFirst);
                    $response =  [$rateFirst, $ratePriority, $rateExpress];
                    //    if any rate has error pop that off from response array
                    foreach ($response as $key => $rate) {
                        if (isset($rate->error)) {

                            unset($response[$key]);
                        }
                    }
                    // dd($response);
                    // return $response;
                    $product = Product::with('shippingOption')->where('slug', '=', $product_slug)->where('status', '1')->first();
                    $packageNames = array();
                    // dd($product);
                    if (isset($product)) {
                        $product_id = $product->product_id;
                        $packageNames = explode(',', $product->shippingOption->shipping_service);
                    }

                    //   dd($response);
                    $filteredRates = [];
                    foreach ($response as $rate) {
                        if (in_array($rate->service, $packageNames)) {
                            $filteredRates[] = $rate;
                        }
                    }
                    // dd($filteredRates);
                    $rates['product_id'] = $product_id;
                    $rates['rate'] = $filteredRates;
                    // dd($rates);
                    return $rates;
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }


            try {
                // dd(12);
                $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
                $shipment = $client->shipment->create([
                    "from_address" => [
                        "company" => $company,
                        "street1" => $shipping_address1,
                        "street2" => $shipping_address2,
                        "city"    => $city,
                        "state"   => $state_code,
                        "zip"     => $zip,
                        "phone"   => "415-456-7891",
                        // 'country' => $country_code,
                        // 'email'   => $store_email
                    ],
                    "to_address" => [
                        "name"    => $full_name,
                        "street1" => $street_address,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "415-456-7891",
                        // 'country' => $country,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $totalproduct_length,
                        "width"  => $totalproduct_width,
                        "height" => $totalproduct_height,
                        "weight" => $totalproduct_weight,
                        "predefined_package" =>  $selectPackage,
                    ],
                    'customs_info' => [
                        'eel_pfc' => 'NOEEI 30.37(a)',
                        'customs_certify' => true,
                        'customs_signer' => 'MallShark',
                        'contents_type' => 'merchandise',
                        'contents_explanation' => 'Customs contents explanation',
                        'restriction_type' => 'none',
                        'restriction_comments' => '',
                        'non_delivery_option' => 'return',
                        'customs_items' => [
                            [
                                'description' => $product_title,
                                'quantity' => $product_quantity,
                                'weight' => $product_weight,
                                'value' => $product_price,
                                'hs_tariff_number' => '123456',
                                'origin_country' => 'US'
                            ]
                        ]
                    ]
                ]);
            } catch (Exception $e) {

                dd($e->getMessage());
            }
            if (empty($shipment)) {
                $response = "An error was returned from our shipment generation service not created shipment, Please update your shipping profile:";
                return $error = array('error' => $response);
            }

            // dd($shipment);

            $rates = $shipment->rates;
            if ($rates) {


                $shipData = [];
                foreach ($rates as $rate) {
                    if ($rate->service != 'GroundAdvantage') {
                        array_push($shipData, json_decode($rate, true));
                    }
                };
                // dd($shipData);
                $product = Product::with('shippingOption')->where('slug', '=', $product_slug)->where('status', '1')->first();
                $packageNames = array();
                // dd($product);
                if (isset($product)) {
                    $product_id = $product->product_id;
                    $packageNames = explode(',', $product->shippingOption->shipping_service);
                }
                //$packageNames = explode(',', $product->shippingOption->shipping_service);
                // dd($shipData);
                $filteredRates = [];
                foreach ($shipData as $rate) {
                    if (in_array($rate['service'], $packageNames)) {
                        $filteredRates[] = $rate;
                    }
                }
                // dd($filteredRates);
                // $shippingRate = [];
                $rates['product_id'] = $product_id;
                $rates['rate'] = $filteredRates;
                return json_encode($rates);

                return json_encode($shipment->rates);
            }

            $flagSelected = false;
            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return $error = array('error' => $error);
                }
            } else {
                // if (@count($shipment->rates) > 0) {
                //     for ($i = 0; $i < count($shipment->rates); $i++) {
                //         //match our rate to the service
                //         // if ($shipment->rates[$i]->service == $shipping_service) {
                //             $flagSelected = true;
                //             $selectedRate = $shipment->rates[$i];
                //             $retuen_response = array(
                //                 'rate' => $selectedRate->rate,
                //                 'ship_id' => $shipment->id,
                //                 'rate_id' => $selectedRate->id,
                //                 'store_data' =>$store_info[0],
                //                 'user_info' =>$user_data,
                //             );
                //             return $retuen_response;
                //             break;
                //         // }
                //     }

                //     if ($flagSelected === false) {
                //         $totalWeightVerify = $product_weight;
                //         if ($totalWeightVerify > 256) {
                //             for ($i = 0; $i < count($shipment->rates); $i++) {
                //                 //match our rate to the service
                //                 if ($shipment->rates[$i]->service == 'Priority') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_data,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }
                //             }
                //         }
                //     }
                // }
            }
        }
    }
    public function createShipmentOnChange(Request $request)
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'shipping_service' => 'required',
                'product_id' => 'required',
                'store_shipping_id' => 'required',
                'length' => 'required',
                'width' => 'required',
                'height' => 'required',
                'weight' => 'required',
                'product_quantity' => 'required',
                'user_shipping_id' => 'nullable',
                'user_street' => 'required_without:user_shipping_id', // Require 'street' when 'user_shipping_id' is not defined
                'user_state_code' => 'required_without:user_shipping_id', // Require 'street' when 'user_shipping_id' is not defined
                'user_post_code' => 'required_without:user_shipping_id', // Require 'street' when 'user_shipping_id' is not defined
                'user_city' => 'required_without:user_shipping_id', // Require 'city' when 'user_shipping_id' is not defined
                'user_name' => 'required_without:user_shipping_id', // Require 'name' when 'user_shipping_id' is not defined
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        };
        try {
            $shipping_service = isset($request->shipping_service) ? $request->shipping_service : "";
            $product_title = isset($request->product_title) ? $request->product_title : "";
            $product_weight = isset($request->weight) ? $request->weight : "1";
            $product_length = isset($request->product_length) ? $request->product_length : "1";
            $product_width  = isset($request->product_width) ? $request->product_width : "1";
            $product_height = isset($request->product_height) ? $request->product_height : "1";
            $product_quantity = isset($request->product_quantity) ? $request->product_quantity : "1";
            $product_price = isset($request->product_price) ? $request->product_price : "1";
            $selectPackage_usps = $this->getPackageType($product_length, $product_width, $product_height, $product_weight, $product_quantity);
            $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';

            $user_shipping_id = $request->user_shipping_id;
            if (isset($user_shipping_id)) {

                $user_info = DB::table('shipping_addresses')->where('sh_id', $user_shipping_id)->select(
                    'shipping_addresses.full_name',
                    'shipping_addresses.street',
                    'shipping_addresses.post_code',
                    'shipping_addresses.flat_other',
                    'shipping_addresses.city',
                    'shipping_addresses.user_id',
                    'users.email',
                    'users.contact_no',
                    'countries.name as country_name',
                    'countries.code as country_code',
                    'states.state_name',
                    'states.code as state_code'
                )
                    ->join('users', 'users.user_id', '=', 'shipping_addresses.user_id')
                    ->join('countries', 'countries.id', '=', 'shipping_addresses.country')->join('states', 'states.state_id', '=', 'shipping_addresses.state')->first();

                // User Shipping Address
                $full_name = isset($user_info->full_name) ? $user_info->full_name : "";
                $street = isset($user_info->street) ? $user_info->street : "";
                $user_city = isset($user_info->city) ? $user_info->city : "";
                $post_code = isset($user_info->post_code) ? $user_info->post_code : "";
                $user_state_code = isset($user_info->state_code) ? $user_info->state_code : "";
            } else {
                $full_name = isset($request->user_name) ? $request->user_name : "";
                $street = isset($request->user_street) ? $request->user_street : "";
                $user_city = isset($request->user_city) ? $request->user_city : "";
                $post_code = isset($request->user_post_code) ? $request->user_post_code : "";
                $user_state_code = isset($request->user_state_code) ? $request->user_state_code : "";
            }
            $store_info = DB::table('shipping_options')->select(
                'shipping_options.origin_zipcode as store_zip',
                'shipping_options.flat_shipping_rate',
                'shipping_options.shipping_city',
                'shipping_options.shipping_address1',
                'shipping_options.shipping_address2',
                'stores.store_name',
                'stores.contact_info',
                'users.email as store_email',
                'countries.name as country_name',
                'countries.code as country_code',
                'states.state_name',
                'states.code as state_code'
            )
                ->join('stores', 'stores.store_id', '=', 'shipping_options.store_id')
                ->join('users', 'users.user_id', '=', 'stores.user_id')
                ->join('countries', 'countries.id', '=', 'shipping_options.shipping_country_id')
                ->join('states', 'states.state_id', '=', 'shipping_options.shipping_state_id')
                ->where('shipping_options.id', $request->store_shipping_id)->get();

            // Srore From Address
            $company =  isset($store_info[0]->store_name) ? $store_info[0]->store_name : "company";
            $shipping_address1 =  isset($store_info[0]->shipping_address1) ? $store_info[0]->shipping_address1 : "";
            $shipping_address2 =  isset($store_info[0]->shipping_address2) ? $store_info[0]->shipping_address2 : "";
            $zip =  isset($store_info[0]->store_zip) ? $store_info[0]->store_zip : "";
            $city =  isset($store_info[0]->shipping_city) ? $store_info[0]->shipping_city : "";
            $state_code =  isset($store_info[0]->state_code) ? $store_info[0]->state_code : "";

            $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
            $shipment = $client->shipment->create([
                "from_address" => [
                    "company" => $company,
                    "street1" => $shipping_address1,
                    "street2" => $shipping_address2,
                    "city"    => $city,
                    "state"   => $state_code,
                    "zip"     => $zip,
                    "phone"   => "415-456-7890",
                ],
                "to_address" => [
                    "name"    => $full_name,
                    "street1" => $street,
                    "city"    => $user_city,
                    "state"   => $user_state_code,
                    "zip"     => $post_code,
                    "phone"   => "415-456-7891",
                ],
                "parcel" => [
                    "length" => $product_length,
                    "width"  => $product_width,
                    "height" => $product_height,
                    "weight" => $product_weight,
                    // "predefined_package" =>  $selectPackage,
                ],

            ]);

            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return response()->json([
                        'status' => false,
                        'message' => $error,
                    ]);
                }
            } else {

                if (@count($shipment->rates) > 0) {
                    for ($i = 0; $i < count($shipment->rates); $i++) {
                        //match our rate to the service
                        foreach ($shipment->rates as $i => $rate) {
                            if ($shipment->rates[$i]->service == $shipping_service) {

                                $selectedRate = $shipment->rates[$i];
                                $retuen_response = array(
                                    'service' => $selectedRate->service,
                                    'rate' => $selectedRate->rate * $product_quantity,
                                    'ship_id' => $shipment->id,
                                    'rate_id' => $selectedRate->id,
                                );
                                return response()->json([
                                    'status' => true,
                                    'message' => " You have been successfully selected for the rate.",
                                    'data' => $retuen_response
                                ]);
                                break;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
