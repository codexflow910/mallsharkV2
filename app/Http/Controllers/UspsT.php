<?php

namespace App\Http\Controllers;

use App\Johnpaulmedina\Usps\RatePackage;
use App\Johnpaulmedina\Usps\Rate;
use App\Johnpaulmedina\Usps\InternationalLabel;
// use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;
use App\Johnpaulmedina\Usps\USPSBasee;
//use Request;

class UspsT extends Controller {

    public function __construct() {

        //$this->_homeobj = new Homeaccess();
    }

    /*
     * function to calculate rate for domestic rate
     */

    static public function getDpmesticRates($origen_zip = '91601', $dest_zip = '90001', $weightinpounds = '15.5', $shipService = "") {
        
         $service_type = $shipService;

        // if ($shipService == "usps-priority-exp") {
        //     $service_type = RatePackage::SERVICE_EXPRESS;
        // }
        // if ($shipService == "usps-first-class" && $weightinpounds <= 0.8) {
        //     $service_type = RatePackage::SERVICE_FIRST_CLASS;
        // }
        // dd($weightinpounds);
        if($shipService == 'First'){
            $service_type  = RatePackage::SERVICE_FIRST_CLASS;
            // dd($service_type);
        }else if($shipService == "Priority"){
            $service_type  = RatePackage::SERVICE_PRIORITY;
            
        }else if($shipService == 'Express'){
            $service_type  = RatePackage::SERVICE_EXPRESS;

        }
        $weightinpounds = $weightinpounds / 16;
        $rate = new Rate('370MINDB0146');
        // dd($weightinpounds);

        if ($weightinpounds > 70) {
            $service_type = RatePackage::MAIL_TYPE_POSTCARD;
        }
        $package = new RatePackage;

        $package->setService($service_type);
        if ($shipService == "First" && $weightinpounds <= 0.8) {
        // dd($weightinpounds);

            $package->setFirstClassMailType(RatePackage::MAIL_TYPE_PARCEL);
        }
        // dd($dest_zip);
        $package->setZipOrigination($origen_zip);
        // dd($package);
        $package->setZipDestination($dest_zip);
        // dd($package);
        // dd($weightinpounds);
        $package->setPounds($weightinpounds);
        $package->setOunces(0);
        $package->setContainer('');
        $package->setSize(RatePackage::SIZE_REGULAR);
        $package->setField('Machinable', true);

        // add the package to the rate stack
        $rate->addPackage($package);
            // dd($package);
        // Perform the request and print out the result
        $rate->getRate();
        $rate_info = $rate->getArrayResponse();
        // Was the call successful
        if ($rate->isSuccess()) {
            $response = array('rate' => $rate_info['RateV4Response']['Package']['Postage']['Rate']);
            return json_encode($response);
            // return $rate_info['RateV4Response']['Package']['Postage']['Rate'];

        } else {
            $response = array('error' => $rate->getErrorMessage());
            return json_encode($response);
        //    echo $errors = 'Error: ' .$rate->getErrorMessage();

        }
    }

    public function getShippingRateUspsProductCreate(Request $request) {
        $weight = $request->input('productWeight');
        try {
            if (!empty($weight) && $weight > 0) {
                $response = $this->getDpmesticRates('91601', '91730', $weight, 'Priority');
                // dd($response);
                $responseData = json_decode($response, true); // Decode the JSON response
                // return $responseData;
                if (isset($responseData['error'])) { // Check if 'error' key exists in the decoded response
                    return response()->json([
                        'status' => false,
                        'message' => $responseData['error']
                    ]);
                } else {
                    return response()->json([
                        'status' => true,
                        'message' => 'Rate Matched'
                    ]);
                }
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
    
    public function getShippingRateUspsProductCreateApi(Request $request) {
        $weight = $request->input('productWeight');
        try {
            if (!empty($weight) && $weight > 0) {
                $response = $this->getDpmesticRates('91601', '91730', $weight, 'Priority');
                $responseData = json_decode($response, true); // Decode the JSON response
                if (isset($responseData['error'])) { // Check if 'error' key exists in the decoded response
                    return response()->json([
                        'status' => false,
                        'message' => $responseData['error']
                    ]);
                } else {
                    return response()->json([
                        'status' => true,
                        'message' => 'Rate Matched'
                    ]);
                }
            }
            // return 12;
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function shippingRates(Request $req){
        $params = array();
        parse_str($req->shipment_data, $params);
        $array_data = explode("&", $req->shipment_data);
        $shipment_data = [];
        foreach ($array_data as $item) {
            // Split each item by "=" symbol
            $key_value = explode("=", $item);
            
            // Add the key-value pair to the result array
            $shipment_data[$key_value[0]] = $key_value[1];
        }
        // dd($shipment_data);
        $service = isset($req->service) ? $req->service : null;
        $origin_zip = isset($shipment_data['zipcode']) ? $shipment_data['zipcode']  : null;
        $dest_zip = isset($req->origin_zip) ? $req->origin_zip : null;
        $weight = isset($req->weight) ? $req->weight : null;
        if(isset($dest_zip) && isset($origin_zip) && isset($weight) ){
            
            $rate = $this->getDpmesticRates($origin_zip,$dest_zip,$weight,$service);
            return $rate;
        }

    }

    public function getinternationalRates($origen_zip = '91601', $dest_zip = '91730', $weightinpounds = '10') {

        $rate = new Rate('370MINDB0146');
        $rate->setInternationalCall(true);
        $rate->addExtraOption('Revision', 2);
        $package = new RatePackage;
        $package->setPounds(30.12345678);
        $package->setOunces(0);
        $package->setField('Machinable', 'True');
        $package->setField('MailType', 'Package');
        $package->setField('GXG', array(
            'POBoxFlag' => 'Y',
            'GiftFlag' => 'Y'
        ));
        $package->setField('ValueOfContents', 200);
        $package->setField('Country', 'Canada');
        $package->setField('Container', 'RECTANGULAR');
        $package->setField('Size', 'LARGE');
        $package->setField('Width', 10);
        $package->setField('Length', 15);
        $package->setField('Height', 10);
        $package->setField('Girth', 0);
        $package->setField('OriginZip', 18701);
        $package->setField('CommercialFlag', 'N');
        $package->setField('AcceptanceDateTime', '2022-05-05T13:15:00-06:00');
        $package->setField('DestinationPostalCode', 'M4C 1G6');
// add the package to the rate stack
        $rate->addPackage($package);
// Perform the request and print out the result
        $rate->getRate();
        $rate_info = $rate->getArrayResponse();

        //die();
// Was the call successful
        if ($rate->isSuccess()) {

            $rate = $rate_info['IntlRateV2Response']['Package']['Service'][0]['Postage'];
            $days = $rate_info['IntlRateV2Response']['Package']['Service'][0]['SvcCommitments'];
            $arr = array();
            $arr['rate'] = $rate;
            $arr['days'] = $days;
            echo 'Data: <pre>' .print_r($arr,true). '</pre>';
        } else {
            echo 'Error: ' . $rate->getErrorMessage();
        }
    }

    public function getDpmesticRatesRecalculate(Request $request) {
       // dd($request);
        $shipService    = $request->value;
        $weightinpounds =  $request->weight;
        $origen_zip     =  $request->origin_zip;
        $dest_zip =  $request->destination_zip;

        $service_type = RatePackage::SERVICE_PRIORITY;
        if ($shipService == "usps-priority") {
            $service_type = RatePackage::SERVICE_PRIORITY;

        }
        if ($shipService == "usps-priority-exp") {
            $service_type = RatePackage::SERVICE_EXPRESS;
        }
        if ($shipService == "usps-first-class" && $weightinpounds <= 0.8) {
            $service_type = RatePackage::SERVICE_FIRST_CLASS;
        }

        $rate = new Rate('370MINDB0146');

        if ($weightinpounds > 70) {
            $service_type = RatePackage::MAIL_TYPE_POSTCARD;
        }
        $package = new RatePackage;

        $package->setService($service_type);
        if ($shipService == "usps-first-class" && $weightinpounds <= 0.8) {
            $package->setFirstClassMailType(RatePackage::MAIL_TYPE_PARCEL);
        }

        $package->setZipOrigination($origen_zip);
        $package->setZipDestination($dest_zip);
        $package->setPounds($weightinpounds);
        $package->setOunces(0);
        $package->setContainer('');
        $package->setSize(RatePackage::SIZE_REGULAR);
        $package->setField('Machinable', true);

// add the package to the rate stack
        $rate->addPackage($package);

// Perform the request and print out the result
        $rate->getRate();
        $rate_info = $rate->getArrayResponse();

// Was the call successful
        if ($rate->isSuccess()) {

            return $rate_info['RateV4Response']['Package']['Postage']['Rate'];
            // in zit
            // return $rate_info['RateV4Response']['Package']['Postage']['Rate'] * 10;
        } else {
           echo $errors = 'Error: ' .$rate->getErrorMessage();
            //echo 'Error: ' . $rate->getErrorMessage();
            //return view('error.usps-error', ['errors' => $errors]);
        }
   }


}
