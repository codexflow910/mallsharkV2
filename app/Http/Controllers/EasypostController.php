<?php

namespace App\Http\Controllers;

use Session;

use Exception;
use EasyPost\EasyPost;
use EasyPost\EasyPostClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;


class EasypostController extends Controller
{
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
        if ($totalweight <= $parcelLimit && $girth<='108' ){
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    //  const SHIPPING_API = 'EZTKfb0b81e348784de5a32694c0baa74935QQgBOKukjjJ7ADWdBKTm3w';


    public function home()
    {
        return view('/easypost/index');
    }

    public function getRatestemprary()
    {

        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));

        $parcel = $client->parcel->create([
            'length' => 30.2,
            'width' => 30.9,
            'height' => 5,
            'weight' => 100
        ]);
        echo 'Data: <pre>' .print_r($parcel,true). '</pre>'; die();
        // $shipment = $client->shipment->create([
        //     'to_address' => [
        //         'name' => 'Dr. Steve Brule',
        //         'street1' => '179 N Harbor Dr',
        //         'city' => 'Alaska',
        //         'state' => 'California',
        //         'zip' => '90277',
        //         'country' => 'US',
        //         'phone' => '3331114444',
        //         'email' => 'dr_steve_brule@gmail.com'
        //     ],
        //     'from_address' => [
        //         'name' => 'EasyPost',
        //         'street1' => '417 Montgomery Street',
        //         'street2' => '5th Floor',
        //         'city' => 'Alaska',
        //         'state' => 'California	',
        //         'zip' => '90278',
        //         'country' => 'US',
        //         'phone' => '3331114444',
        //         'email' => 'support@easypost.com'
        //     ],
        //     'parcel' => [
        //         'length' => 30,
        //         'width' => 30,
        //         'height' => 30,
        //         'weight' => 10
        //     ]
        // ]);
        // echo 'Data: <pre>' . print_r($shipment, true) . '</pre>';
        die();
    }
    public function getRates(Request $request)
    {
        // dd($request);
        $selectPackage_usps = $this->getPackageType($request->length, $request->width, $request->height, $request->weight, '1');
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';
        // dd($selectPackage);
        echo 'Data: <pre>' . print_r($selectPackage, true) . '</pre>';
        $insertedData = array();

        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        $shipment = $client->shipment->create([
            "from_address" => [
                // "id" => "adr_4d55c33ca13a11eda5cdac1f6bc7bdc6"
                "company" => "EasyPost",
                "street1" => $request->origin_street,
                // "street2" => "4th Floor",
                "city"    => $request->origin_city,
                "state"   => $request->origin_state,
                "zip"     => $request->origin,
                "phone"   => "415-456-7890",
            ],
            "to_address" => [
                // "id" => "adr_4d535cc2a13a11eda041ac1f6bc72124"
                "name"    => "Dr. Steve Brule",
                "street1" => $request->destination_street,
                "city"    => $request->destination_city,
                "state"   => $request->destination_state,
                "zip"     => $request->destination,
                "phone"   => "310-808-5243",
            ],
            "parcel" => [
                "length" => $request->length,
                "width"  => $request->width,
                "height" => $request->height,
                "weight" => $request->weight,
                //    "predefined_package" =>  $selectPackage,
            ],

        ]);
        if (!empty($shipment['messages'])) {
            if ($shipment['messages'][0]['type'] == 'rate_error') {
                $error = $shipment['messages'][0]['message'];
                return $error = array('error' => $error);
            }
        } else {



            //  echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
            $rates = $shipment->rates;
            echo 'Data: <pre>' .print_r($rates,true). '</pre>'; die();

                ?>

            <table class="table" style="border: 1px solid;">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Rates</th>
                        <th>Carrier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rates as $rate) { ?>
                        <tr>
                            <td><?php echo $rate->service; ?></td>
                            <td><?php echo $rate->rate; ?></td>
                            <td><?php echo $rate->carrier; ?></td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        <?php
        }
        die();

        //  $shipmented = $client->shipment->retrieve('shp_f1544cbb491a462fa2f3a84486ed38ee');
        //  dd($shipmented);
        for ($i = 0; $i < count($shipment->rates); $i++) {
            //match our rate to the service
            if ($shipment->rates[$i]->service == 'Priority') {
                $flagSelected = true;
                $selectedRate = $shipment->rates[$i];
                // return $selectedRate->rate;
                // break;
                $boughtShipment = $client->shipment->buy($shipment->id, $selectedRate);
                echo '$boughtShipment: <pre>' . print_r($boughtShipment, true) . '</pre>';
                die();
                $shipmentWithLabel =   $client->shipment->label($shipment->id, ["file_format" => "pdf"]);
                $insertedData['easyPostLabelPNG'] = $shipmentWithLabel->postage_label->label_url;
                $insertedData['easyPostLabelPDF'] = $shipmentWithLabel->postage_label->label_pdf_url;
                $insertedData['easyPostLabelRateId'] = $shipmentWithLabel->selected_rate->id;

                $insertedData['easyPostLabelCarrier'] = $shipmentWithLabel->selected_rate->carrier;
                $insertedData['easyPostLabelService'] = $shipmentWithLabel->selected_rate->service;
                $insertedData['shipmentStatus'] = $shipmentWithLabel->tracker->status;
                $insertedData['estDeliveryDate'] = $shipmentWithLabel->tracker->est_delivery_date;
                $insertedData['trackingCode'] = $shipmentWithLabel->tracking_code;

                $insertedData['shipmentCost'] = number_format($selectedRate->rate, 2, '.', '');
                $insertedData['easyPostShipmentId'] = $shipment->id;
                $insertedData['easyPostShipmentTrackingUrl'] = $shipmentWithLabel->tracker->public_url;
                $insertedData['easyPostLabelSize'] = $shipmentWithLabel->postage_label->label_size;
                $tracking_number = $insertedData['trackingCode'];
                $randomFileName = $tracking_number . ".png";
                $randomFileNamePDF = $tracking_number . ".pdf";
                $downloadResult = file_get_contents($shipmentWithLabel->postage_label->label_url);
                $downloadResultPDF = file_get_contents($shipmentWithLabel->postage_label->label_pdf_url);
                // $logs_dir = get_home_path() . 'downloads/labels/';
                //    $filedata = Storage::disk('local')->get($downloadResult);

                $logs_dir = 'downloads/labels/';
                if (!is_dir($logs_dir)) {
                    mkdir($logs_dir, 0777, true);
                }

                //  Storage::disk('local')->put('file.txt', 'noman');
                // $filedata = Storage::disk('local')->put($logs_dir.'example.png', $downloadResult);
                $filePutResult = File::put($logs_dir . $randomFileName, $downloadResult);
                $filePutResultPDF = File::put($logs_dir . $randomFileNamePDF, $downloadResultPDF);
                // $filePutResult = file_put_contents($logs_dir .'example.png' , $downloadResult);
                // echo '$filePutResult: <pre>' .print_r($filePutResult,true). '</pre>'; die();
            }
        }
    }
    public function getParcel(){
        $array = array();
        for ($i = 10; $i >= 1; $i--) {
        
       
    //    echo $i;
       $parcelpackage =  $this->getPackageType('20','20','20', '20', $i);
       $array[$i] = $parcelpackage;
      
    //    echo 'Data: <pre>' .print_r($parcelpackage,true). '</pre>'; 
       echo "( package".$i." " .$parcelpackage." )" ;
    }
    $array = array_filter($array);
    $highestIndex = max(array_keys($array));
    $highestIndxeData = $array[$highestIndex];
   
    // echo 'Data: <pre>' .print_r($highestIndex,true). '</pre>';
    }
    public function getRateWhenProductAdd(Request $request)
    {
        // dd($request);
        $array = array();
        for ($i = 10; $i >= 1; $i--) { 
        $selectPackage_usps = $this->getPackageType($request->productSize_length,$request->productSize_width,$request->productSize_height, $request->productWeight, $i);
        $array[$i] = $selectPackage_usps;
        }

    // dd($array);

    $arryValue = array_filter($array);
    if (!empty($arryValue)) {
        $highestIndex = max(array_keys($arryValue));
        $highestIndxeData = $arryValue[$highestIndex];
         $selectPackage =  isset($highestIndxeData) ?  $highestIndxeData : '';
    }else{
        $highestIndex = 0;
        $selectPackage = "";
    }
    
        // dd($selectPackage);
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

    public function index()
    {
        $insertedData = array();
        EasypostController::SHIPPING_API;


        //      $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));

        //      $parcel = $client->parcel->create([
        //          'length' => 20.2,
        //          'width' => 10.9,
        //          'height' => 5,
        //          'weight' => 65.9,
        //          'predefined_package' =>'PMODSack'
        //      ]);


        // echo '$parcel: <pre>' .print_r($parcel,true). '</pre>'; die();

        //EasyPost::setApiKey($_ENV['EASYPOST_API_KEY']);
        $client = new \EasyPost\EasyPostClient(env('EASYPOST_API_KEY'));
        // // dd($client);
        $selectPackage_usps = $this->getPackageType(8.36, 58.18, 0.79, 1078.7, '1');
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : '';
        //  dd($selectPackage);
        $shipment = $client->shipment->create([
            "from_address" => [
                // "id" => "adr_4d55c33ca13a11eda5cdac1f6bc7bdc6"
                "company" => "EasyPost",
                "street1" => "118 2nd Street",
                // "street2" => "4th Floor",
                "city"    => "San Francisco",
                "state"   => "CA",
                "zip"     => "90028",
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
                "length" => 15.36,
                "width"  => 15.18,
                "height" => 60.79,
                "weight" => 1100.7,
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
                        'description' => 'Customs item description',
                        'quantity' => 5,
                        'weight' => 16.2,
                        'value' => 100.00,
                        'hs_tariff_number' => '123456',
                        'origin_country' => 'US'
                    ]
                ]
            ]
        ]);
        if (!empty($shipment['messages'])) {
            if ($shipment['messages'][0]['type'] == 'rate_error') {
                $error = $shipment['messages'][0]['message'];
                return $error = array('error' => $error);
            }
        } else {
            echo 'Data: <pre>' . print_r($shipment, true) . '</pre>';
            die();
        }
        //  $shipmented = $client->shipment->retrieve('shp_f1544cbb491a462fa2f3a84486ed38ee');
        //  dd($shipmented);
        if (!empty($shipment['messages'])) {
            if ($shipment['messages'][0]['type'] == 'rate_error') {
                $error = $shipment['messages'][0]['message'];
                return $error = array('error' => $error);
            }
        } else {
            for ($i = 0; $i < count($shipment->rates); $i++) {
                //match our rate to the service
                if ($shipment->rates[$i]->service == 'Priority') {
                    $flagSelected = true;
                    $selectedRate = $shipment->rates[$i];
                    // return $selectedRate->rate;
                    // break;
                    $boughtShipment = $client->shipment->buy($shipment->id, $selectedRate);
                    echo '$boughtShipment: <pre>' . print_r($boughtShipment, true) . '</pre>';
                    die();
                    $shipmentWithLabel =   $client->shipment->label($shipment->id, ["file_format" => "pdf"]);
                    $insertedData['easyPostLabelPNG'] = $shipmentWithLabel->postage_label->label_url;
                    $insertedData['easyPostLabelPDF'] = $shipmentWithLabel->postage_label->label_pdf_url;
                    $insertedData['easyPostLabelRateId'] = $shipmentWithLabel->selected_rate->id;

                    $insertedData['easyPostLabelCarrier'] = $shipmentWithLabel->selected_rate->carrier;
                    $insertedData['easyPostLabelService'] = $shipmentWithLabel->selected_rate->service;
                    $insertedData['shipmentStatus'] = $shipmentWithLabel->tracker->status;
                    $insertedData['estDeliveryDate'] = $shipmentWithLabel->tracker->est_delivery_date;
                    $insertedData['trackingCode'] = $shipmentWithLabel->tracking_code;

                    $insertedData['shipmentCost'] = number_format($selectedRate->rate, 2, '.', '');
                    $insertedData['easyPostShipmentId'] = $shipment->id;
                    $insertedData['easyPostShipmentTrackingUrl'] = $shipmentWithLabel->tracker->public_url;
                    $insertedData['easyPostLabelSize'] = $shipmentWithLabel->postage_label->label_size;
                    $tracking_number = $insertedData['trackingCode'];
                    $randomFileName = $tracking_number . ".png";
                    $randomFileNamePDF = $tracking_number . ".pdf";
                    $downloadResult = file_get_contents($shipmentWithLabel->postage_label->label_url);
                    $downloadResultPDF = file_get_contents($shipmentWithLabel->postage_label->label_pdf_url);
                    // $logs_dir = get_home_path() . 'downloads/labels/';
                    //    $filedata = Storage::disk('local')->get($downloadResult);

                    $logs_dir = 'downloads/labels/';
                    if (!is_dir($logs_dir)) {
                        mkdir($logs_dir, 0777, true);
                    }

                    //  Storage::disk('local')->put('file.txt', 'noman');
                    // $filedata = Storage::disk('local')->put($logs_dir.'example.png', $downloadResult);
                    $filePutResult = File::put($logs_dir . $randomFileName, $downloadResult);
                    $filePutResultPDF = File::put($logs_dir . $randomFileNamePDF, $downloadResultPDF);
                    // $filePutResult = file_put_contents($logs_dir .'example.png' , $downloadResult);
                    // echo '$filePutResult: <pre>' .print_r($filePutResult,true). '</pre>'; die();
                }
            }
        }
        $data = json_encode($insertedData);
        // echo '$data: <pre>' .print_r($data,true). '</pre>'; die();
        // $boughtShipment = $client->shipment->buy(array(
        //     'rate' => $selectedRate
        //     //'insurance'	=>  0.00
        // ));
        // $boughtShipment = $client->shipment->buy($shipment->id, $shipment->lowestRate());

        //echo $boughtShipment;
        //    echo '$downloadResult: <pre>' .print_r($downloadResult,true). '</pre>'; die();

        // $shipment = $client->shipment->retrieve("shp_2869a10b04ab4428837aca88d98c9bfd");

        // $shipmentWithRegeneratedrates = $client->$shipment->regenerateRates('shp_2869a10b04ab4428837aca88d98c9bfd');

        // echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
        //echo $shipment;
        // $pickup = $client->pickup->retrieve();
        // $pickup =  $client->pickup->create([
        //     'address' => ['id' => 'adr_da2857d09b1511edb1dfac1f6bc7b362'],
        //     'shipment' => ['id' => 'shp_e85da1de13124789a3335eb3935f0f48'],
        //     'reference' => 'my-first-pickup',
        //     'min_datetime' => date('2022-10-01 10:30:00'),
        //     'max_datetime' => date('2022-10-02 10:30:00'),
        //     'is_account_address' => true,
        //     // 'instructions' => 'Special pickup instructions'
        // ]);
        //    echo '$shipment: <pre>' .print_r($shipment,true). '</pre>'; die();
        // $boughtShipment = $client->shipment->buy($shipment->id, $shipment->lowestRate());

        // echo $boughtShipment;
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
        $params = array();
        parse_str($request->shipment_data, $params);

        $shipping_service = isset($request->value) ? $request->value : "ParcelSelect";
        $product_title = isset($request->product_title) ? $request->product_title : "";
        $product_weight = isset($request->weight) ? $request->weight : "1";
        $product_length = isset($request->product_length) ? $request->product_length : null;
        $product_width  = isset($request->product_width) ? $request->product_width : null;
        $product_height = isset($request->product_height) ? $request->product_height : null;
        $product_quantity = isset($request->product_quantity) ? $request->product_quantity : "1";
        $product_price = isset($request->product_price) ? $request->product_price : "1";
        $selectPackage_usps = $this->getPackageType($product_length, $product_width, $product_height, $product_weight, $product_quantity);
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';

        if($product_height != null && $product_weight != null && $product_width != null && $product_height != null  ){

            $totalproduct_weight = $product_weight * $product_quantity;
            $totalproduct_length = $product_length * $product_quantity;
            $totalproduct_width  = $product_width * $product_quantity;
            $totalproduct_height = $product_height * $product_quantity;
        }    

        // echo '$product_length: <pre>' .print_r($product_length,true). '</pre>'; 
        // echo '$product_width: <pre>' .print_r($product_width,true). '</pre>'; 
        // echo '$product_height: <pre>' .print_r($product_height,true). '</pre>'; 
        // echo '$product_quantity: <pre>' .print_r($product_quantity,true). '</pre>'; 
        // echo '$product_weight: <pre>' .print_r($product_weight,true). '</pre>'; die;
        // echo '$selectPackage: <pre>' .print_r($selectPackage,true). '</pre>'; die();
        // echo 'Data: <pre>' .print_r(sizeof($params),true). '</pre>'; die();
        if (sizeof($params) == 1) {

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


                // dd($shipment->rates);
                // if (@count($shipment->rates) > 0) {
                // for ($i = 0; $i < count($shipment->rates); $i++) {
                //     //match our rate to the service
                //     // if ($shipment->rates[$i]->service == $shipping_service) {
                //         $flagSelected = true;
                //         $selectedRate = $shipment->rates[$i];
                //         $retuen_response = array(
                //             'rate' => $selectedRate->rate,
                //             'ship_id' => $shipment->id,
                //             'rate_id' => $selectedRate->id,
                //             'store_data' =>$store_info[0],
                //             'user_info' =>$user_info,
                //             'service' => $selectedRate->service,
                //         );
                //         return $retuen_response;
                //         // break;
                //      }
                //         // elseif ($shipment->rates[$i]->service == 'Express') {
                //         //     $flagSelected = true;
                //         //     $selectedRate = $shipment->rates[$i];
                //         //     $retuen_response = array(
                //         //         'rate' => $selectedRate->rate,
                //         //         'ship_id' => $shipment->id,
                //         //         'rate_id' => $selectedRate->id,
                //         //         'store_data' =>$store_info[0],
                //         //         'user_info' =>$user_info,
                //         //     );
                //         //     return $retuen_response;
                //         //     break;
                //         // }
                //         // elseif ($shipment->rates[$i]->service == 'First') {
                //         //     $flagSelected = true;
                //         //     $selectedRate = $shipment->rates[$i];
                //         //     $retuen_response = array(
                //         //         'rate' => $selectedRate->rate,
                //         //         'ship_id' => $shipment->id,
                //         //         'rate_id' => $selectedRate->id,
                //         //         'store_data' =>$store_info[0],
                //         //         'user_info' =>$user_info,
                //         //     );
                //         //     return $retuen_response;
                //         //     break;
                //         // }
                //         // elseif ($shipment->rates[$i]->service == 'ParcelSelect') {
                //         //     $flagSelected = true;
                //         //     $selectedRate = $shipment->rates[$i];
                //         //     $retuen_response = array(
                //         //         'rate' => $selectedRate->rate,
                //         //         'ship_id' => $shipment->id,
                //         //         'rate_id' => $selectedRate->id,
                //         //         'store_data' =>$store_info[0],
                //         //         'user_info' =>$user_info,
                //         //     );
                //         //     return $retuen_response;calculate_service_change_crypto
                //         //     break;
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
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }elseif ($shipment->rates[$i]->service == 'Express') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }elseif ($shipment->rates[$i]->service == 'First') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }elseif ($shipment->rates[$i]->service == 'ParcelSelect') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;calculate_service_change_crypto
                //                 }
                //             }
                //         }
                //     }

                //     //  echo '$selectedRate: <pre>' .print_r($selectedRate->rate,true). '</pre>'; die();
                // }
            }
            //echo '$shipment if condtion: <pre>' .print_r($shipment,true). '</pre>'; die();
        } else {

            // dd($request->all());
            // dump($product_length);
            // dump($product_width);
            // dump($product_weight);
            // dd($product_height);

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
            $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
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
            // dd($product_weight);
            if(($product_height == null || $product_height == 0) && ($product_length == null || $product_length == 0) &&  ($product_width == null || $product_length == 0)) {
                try{
                    $rateFirst =  UspsT::getDpmesticRates($zip,$post_code,$product_weight,'First');
                    $ratePriority =  UspsT::getDpmesticRates($zip,$post_code,$product_weight,'Priority');
                    $rateExpress =  UspsT::getDpmesticRates($zip,$post_code,$product_weight,'Express');
                    $rateFirst = json_decode($rateFirst);
                    $ratePriority = json_decode($ratePriority);
                    $rateExpress = json_decode($rateExpress);
                    $rateFirst->service = 'First';
                    $ratePriority->service = 'Priority';
                    $rateExpress->service = 'Express';
                
                    $response =  [$rateFirst,$ratePriority,$rateExpress];
                //    if any rate has error pop that off from response array
                    foreach($response as $key=>$rate){
                        if(isset($rate->error)){

                           unset($response[$key]);
                        }
                    }
                    return $response;
                }catch(Exception $e){
                    dd($e->getMessage());
                }
                
            }
            
            
            try {
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
                            ],
                            'carrier_accounts' => [env('EasypostUSPSAccount')] // Specify both carrier accounts
                ]);
            } catch (Exception $e) {

                dd($e->getMessage());
              
            }
            if (empty($shipment)) {
                $response = "An error was returned from our shipment generation service not created shipment, Please update your shipping profile:";
                return $error = array('error' => $response);
            }

         
            $rates = $shipment->rates;
            if ($rates) {


                $shipData = [];
                foreach ($rates as $rate) {

                    array_push($shipData, json_decode($rate, true));
                };
                return json_encode($shipData);
               
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
    public function createShipmentCrypto(Request $request)
    {
        sleep(10);
        $params = array();
        parse_str($request->shipment_data, $params);

        $shipping_service = isset($request->value) ? $request->value : "ParcelSelect";
        $product_title = isset($request->product_title) ? $request->product_title : "";
        $product_weight = isset($request->weight) ? $request->weight : "1";
        $product_length = isset($request->product_length) ? $request->product_length : "1";
        $product_width  = isset($request->product_width) ? $request->product_width : "1";
        $product_height = isset($request->product_height) ? $request->product_height : "1";
        $product_quantity = isset($request->product_quantity) ? $request->product_quantity : "1";
        $product_price = isset($request->product_price) ? $request->product_price : "1";
        $selectPackage_usps = $this->getPackageType($product_length, $product_width, $product_height, $product_weight, $product_quantity);
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';
        // echo '$product_length: <pre>' .print_r($product_length,true). '</pre>'; 
        // echo '$product_width: <pre>' .print_r($product_width,true). '</pre>'; 
        // echo '$product_height: <pre>' .print_r($product_height,true). '</pre>'; 
        // echo '$product_quantity: <pre>' .print_r($product_quantity,true). '</pre>'; 
        // echo '$product_weight: <pre>' .print_r($product_weight,true). '</pre>'; die;
        // echo '$selectPackage: <pre>' .print_r($selectPackage,true). '</pre>'; die();
        // echo 'Data: <pre>' .print_r(sizeof($params),true). '</pre>'; die();
        if (sizeof($params) == 1) {

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


                // dd($shipment->rates);
                // if (@count($shipment->rates) > 0) {
                // for ($i = 0; $i < count($shipment->rates); $i++) {
                //     //match our rate to the service
                //     // if ($shipment->rates[$i]->service == $shipping_service) {
                //         $flagSelected = true;
                //         $selectedRate = $shipment->rates[$i];
                //         $retuen_response = array(
                //             'rate' => $selectedRate->rate,
                //             'ship_id' => $shipment->id,
                //             'rate_id' => $selectedRate->id,
                //             'store_data' =>$store_info[0],
                //             'user_info' =>$user_info,
                //             'service' => $selectedRate->service,
                //         );
                //         return $retuen_response;
                //         // break;
                //      }
                //         // elseif ($shipment->rates[$i]->service == 'Express') {
                //         //     $flagSelected = true;
                //         //     $selectedRate = $shipment->rates[$i];
                //         //     $retuen_response = array(
                //         //         'rate' => $selectedRate->rate,
                //         //         'ship_id' => $shipment->id,
                //         //         'rate_id' => $selectedRate->id,
                //         //         'store_data' =>$store_info[0],
                //         //         'user_info' =>$user_info,
                //         //     );
                //         //     return $retuen_response;
                //         //     break;
                //         // }
                //         // elseif ($shipment->rates[$i]->service == 'First') {
                //         //     $flagSelected = true;
                //         //     $selectedRate = $shipment->rates[$i];
                //         //     $retuen_response = array(
                //         //         'rate' => $selectedRate->rate,
                //         //         'ship_id' => $shipment->id,
                //         //         'rate_id' => $selectedRate->id,
                //         //         'store_data' =>$store_info[0],
                //         //         'user_info' =>$user_info,
                //         //     );
                //         //     return $retuen_response;
                //         //     break;
                //         // }
                //         // elseif ($shipment->rates[$i]->service == 'ParcelSelect') {
                //         //     $flagSelected = true;
                //         //     $selectedRate = $shipment->rates[$i];
                //         //     $retuen_response = array(
                //         //         'rate' => $selectedRate->rate,
                //         //         'ship_id' => $shipment->id,
                //         //         'rate_id' => $selectedRate->id,
                //         //         'store_data' =>$store_info[0],
                //         //         'user_info' =>$user_info,
                //         //     );
                //         //     return $retuen_response;calculate_service_change_crypto
                //         //     break;
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
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }elseif ($shipment->rates[$i]->service == 'Express') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }elseif ($shipment->rates[$i]->service == 'First') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;
                //                 }elseif ($shipment->rates[$i]->service == 'ParcelSelect') {
                //                     $selectedRate = $shipment->rates[$i];
                //                     $retuen_response = array(
                //                         'rate' => $selectedRate->rate,
                //                         'ship_id' => $shipment->id,
                //                         'rate_id' => $selectedRate->id,
                //                         'store_data' =>$store_info[0],
                //                         'user_info' =>$user_info,
                //                     );
                //                     return $retuen_response;
                //                     break;calculate_service_change_crypto
                //                 }
                //             }
                //         }
                //     }

                //     //  echo '$selectedRate: <pre>' .print_r($selectedRate->rate,true). '</pre>'; die();
                // }
            }
            //echo '$shipment if condtion: <pre>' .print_r($shipment,true). '</pre>'; die();
        } else {

            //    dd($request);

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
            $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
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

            try {
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
                        "street1" => $street,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "415-456-7891",
                        // 'country' => $country,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $product_length,
                        "width"  => $product_width,
                        "height" => $product_height,
                        "weight" => $product_weight,
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

                // $response = array(
                //     'code' => 'error',
                //     'msg' => "An error was returned from our shipment generation service, please show this to the administrator:<br/><br/>" . preg_replace("/\r\n|\r|\n/", '<br/>', htmlspecialchars($e->getMessage())),
                // );
                // echo json_encode($response);
                // die;
            }
            if (empty($shipment)) {
                $response = "An error was returned from our shipment generation service not created shipment, Please update your shipping profile:";
                return $error = array('error' => $response);
            }

            // echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
            $rates = $shipment->rates;
            if ($rates) {


                $shipData = [];
                foreach ($rates as $rate) {

                    array_push($shipData, json_decode($rate, true));
                };
                return json_encode($shipData);
                // dd($rateArray);
                return json_encode($shipment->rates);
            }
            // die('called');
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

    public function createShipmentOnChnage(Request $request)
    {
        // sleep(10); 
        //  dd($request->shipment_data);
        $params = array();
        parse_str($request->shipment_data, $params);
        $array_data = explode("&", $request->shipment_data);
        $shipment_data = [];
        foreach ($array_data as $item) {
            // Split each item by "=" symbol
            $key_value = explode("=", $item);

            // Add the key-value pair to the result array
            $shipment_data[$key_value[0]] = $key_value[1];
        }
        // dd($shipment_data);

        $shipping_service = isset($request->value) ? $request->value : "";
        $product_title = isset($request->product_title) ? $request->product_title : "";
        $product_weight = isset($request->weight) ? $request->weight : "1";
        $product_length = isset($request->product_length) ? $request->product_length : "1";
        $product_width  = isset($request->product_width) ? $request->product_width : "1";
        $product_height = isset($request->product_height) ? $request->product_height : "1";
        $product_quantity = isset($request->product_quantity) ? $request->product_quantity : "1";
        $product_price = isset($request->product_price) ? $request->product_price : "1";
        $selectPackage_usps = $this->getPackageType($product_length, $product_width, $product_height, $product_weight, $product_quantity);
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';

        $total_product_weight = $product_weight * $product_quantity;
        $total_product_length = $product_length * $product_quantity;
        $total_product_width  = $product_width * $product_quantity;
        $total_product_height = $product_height * $product_quantity;

        if (sizeof($params) == 1) {

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
                        "street2" => $shipping_address2,
                        "city"    => $city,
                        "state"   => $state_code,
                        "zip"     => $zip,
                        "phone"   => "415-456-7890",
                        // 'country' => $country_code,
                        // 'email'   => $store_email
                    ],
                    "to_address" => [
                        "name"    => $full_name,
                        "street1" => $street,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "415-456-7891",
                        // 'country' => $user_country_code,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $product_length,
                        "width"  => $product_width,
                        "height" => $product_height,
                        "weight" => $product_weight,
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
                //  dd($shipment);
            } catch (Exception $e) {

                $response = array(
                    'code' => 'error',
                    'msg' => "An error was returned from our shipment generation service, please show this to the administrator:<br/><br/>" . preg_replace("/\r\n|\r|\n/", '<br/>', htmlspecialchars($e->getMessage())),
                );
                echo json_encode($response);
                die;
            }
            //  dd($shipment);
            $flagSelected = false;
            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return $error = array('error' => $error);
                }
            } else {



                //dd($shipment);
                if (@count($shipment->rates) > 0) {
                    for ($i = 0; $i < count($shipment->rates); $i++) {
                        //match our rate to the service
                        foreach ($shipment->rates as $i => $rate) {
                            if ($shipment->rates[$i]->service == $shipping_service) {
                                //echo 'first';
                                $flagSelected = true;
                                $selectedRate = $shipment->rates[$i];
                                $retuen_response = array(
                                    'rate' => $selectedRate->rate,
                                    'ship_id' => $shipment->id,
                                    'rate_id' => $selectedRate->id,
                                    'store_data' => $store_info[0],
                                    'user_info' => $user_info,
                                    'service' => $selectedRate->service,
                                );
                                return $retuen_response;
                                break;
                            }
                        }
                    }

                    if ($flagSelected === false) {
                        $totalWeightVerify = $product_weight;
                        if ($totalWeightVerify > 256) {
                            for ($i = 0; $i < count($shipment->rates); $i++) {
                                //match our rate to the service
                                if ($shipment->rates[$i]->service == $shipping_service) {
                                    $selectedRate = $shipment->rates[$i];
                                    $retuen_response = array(
                                        'rate' => $selectedRate->rate,
                                        'ship_id' => $shipment->id,
                                        'rate_id' => $selectedRate->id,
                                        'store_data' => $store_info[0],
                                        'user_info' => $user_info,
                                    );
                                    return $retuen_response;
                                    break;
                                }
                            }
                        }
                    }

                    //  echo '$selectedRate: <pre>' .print_r($selectedRate->rate,true). '</pre>'; die();
                }
            }
            //echo '$shipment if condtion: <pre>' .print_r($shipment,true). '</pre>'; die();
        } else {
            //   dd($request->shipping_option_id);
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


            $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
            $country_id = isset($params['country']) ? $params['country'] : "";
            $state_id = isset($params['state']) ? $params['state'] : "";
            $userInfo = getUserById($userId);
            $stateInfo = getStatebyId($state_id);
            $countryInfo = getCountryById($country_id);

            $user_data = array();

            // echo '$params: <pre>' .print_r($shipment_data,true). '</pre>'; 
            // echo '$store_info: <pre>' .print_r($store_info,true). '</pre>'; die();

            $user_data['full_name'] = isset($params['full_name']) ? $params['full_name'] : "";
            $user_data['user_country_code'] = isset($countryInfo['code']) ? $countryInfo['code'] : "";
            $user_data['user_state_code'] = isset($stateInfo['code']) ? $stateInfo['code'] : "";
            $user_data['street'] = isset($params['street_address']) ? $params['street_address'] : "";
            $user_data['zipcode'] = isset($params['zipcode']) ? $params['zipcode'] : "";
            $user_data['user_city'] = isset($params['city_name']) ? $params['city_name'] : "";
            $user_data['user_phone'] = isset($userInfo->contact_no) ? $userInfo->contact_no : "";
            $user_data['user_email'] = isset($userInfo->email) ? $userInfo->email : "";
            $user_data['flat_other'] = isset($params['flat_other']) ? $params['flat_other'] : "";

            // User Shipping Address
            // $full_name = isset($params['full_name']) ? $params['full_name'] : "";
            $full_name = isset($shipment_data['first_name']) ? $shipment_data['first_name'] . ' ' . $shipment_data['last_name']  : "";
            $user_country_code = isset($countryInfo['code']) ? $countryInfo['code'] : "";
            $user_state_code = isset($shipment_data['states_countries']) ? $shipment_data['states_countries'] : "";
            $street = isset($shipment_data['street_address']) ? $shipment_data['street_address'] : "";
            $post_code = isset($shipment_data['zipcode']) ? $shipment_data['zipcode'] : "";
            $user_city = isset($shipment_data['city_name']) ? $shipment_data['city_name'] : "";
            $user_phone = isset($userInfo->contact_no) ? $userInfo->contact_no : "";
            $user_email = isset($userInfo->email) ? $userInfo->email : "";

            $street_address = str_replace('+', ' ', $street);

            //dd($post_code);

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

            try {
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
                        // 'country' => $user_country_code,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $total_product_length,
                        "width"  => $total_product_width,
                        "height" => $total_product_height,
                        "weight" => $total_product_weight,
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
                            ],
                            'carrier_accounts' => [env('EasypostUSPSAccount')] // Specify both carrier accounts
                ]);
            } catch (Exception $e) {

                $response = array(
                    'code' => 'error',
                    'msg' => "An error was returned from our shipment generation service, please show this to the administrator:<br/><br/>" . preg_replace("/\r\n|\r|\n/", '<br/>', htmlspecialchars($e->getMessage())),
                );
                echo json_encode($response);
                die;
            }

            if (empty($shipment)) {
                $response = "An error was returned from our shipment generation service not created shipment, Please update your shipping profile:";
                return $error = array('error' => $response);
            }
            //  echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
            // echo '$shipment: <pre>' .print_r($shipment,true). '</pre>'; die();
            $flagSelected = false;

            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return $error = array('error' => $error);
                }
            }
            $rates = $shipment->rates;
            // if($rates){


            //     $shipData = [];
            //     foreach($rates as $rate ){

            //         array_push($shipData,json_decode($rate,true));
            //     };
            //     return json_encode($shipData);
            //         // dd($rateArray);
            //         return json_encode($shipment->rates);
            //     }
            //  else {
            if (@count($shipment->rates) > 0) {
                for ($i = 0; $i < count($shipment->rates); $i++) {
                    //match our rate to the service
                    if ($shipment->rates[$i]->service == $shipping_service) {
                        $flagSelected = true;
                        $selectedRate = $shipment->rates[$i];
                        $retuen_response = array(
                            'rate' => $selectedRate->rate,
                            'ship_id' => $shipment->id,
                            'rate_id' => $selectedRate->id,
                            'store_data' => $store_info[0],
                            'user_info' => $user_data,
                        );
                        return $retuen_response;
                        break;
                    }
                }

                if ($flagSelected === false) {
                    $totalWeightVerify = $product_weight;
                    if ($totalWeightVerify > 16) {
                        for ($i = 0; $i < count($shipment->rates); $i++) {
                            //match our rate to the service
                            if ($shipment->rates[$i]->service == 'Priority') {
                                $selectedRate = $shipment->rates[$i];
                                $retuen_response = array(
                                    'rate' => $selectedRate->rate,
                                    'ship_id' => $shipment->id,
                                    'rate_id' => $selectedRate->id,
                                    'store_data' => $store_info[0],
                                    'user_info' => $user_data,
                                );
                                return $retuen_response;
                                break;
                            }
                        }
                    }
                }
            }
            // }
        }
    }

    public function createShipmentOnChnageCrypto(Request $request)
    {
        sleep(10);
        //  dd($request->shipment_data);
        $params = array();
        parse_str($request->shipment_data, $params);
        $array_data = explode("&", $request->shipment_data);
        $shipment_data = [];
        foreach ($array_data as $item) {
            // Split each item by "=" symbol
            $key_value = explode("=", $item);

            // Add the key-value pair to the result array
            $shipment_data[$key_value[0]] = $key_value[1];
        }
        // dd($shipment_data);

        $shipping_service = isset($request->value) ? $request->value : "";
        $product_title = isset($request->product_title) ? $request->product_title : "";
        $product_weight = isset($request->weight) ? $request->weight : "1";
        $product_length = isset($request->product_length) ? $request->product_length : "1";
        $product_width  = isset($request->product_width) ? $request->product_width : "1";
        $product_height = isset($request->product_height) ? $request->product_height : "1";
        $product_quantity = isset($request->product_quantity) ? $request->product_quantity : "1";
        $product_price = isset($request->product_price) ? $request->product_price : "1";
        $selectPackage_usps = $this->getPackageType($product_length, $product_width, $product_height, $product_weight, $product_quantity);
        $selectPackage =  isset($selectPackage_usps) ?  $selectPackage_usps : 'null';

        if (sizeof($params) == 1) {

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
                        "street2" => $shipping_address2,
                        "city"    => $city,
                        "state"   => $state_code,
                        "zip"     => $zip,
                        "phone"   => "415-456-7890",
                        // 'country' => $country_code,
                        // 'email'   => $store_email
                    ],
                    "to_address" => [
                        "name"    => $full_name,
                        "street1" => $street,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "415-456-7891",
                        // 'country' => $user_country_code,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $product_length,
                        "width"  => $product_width,
                        "height" => $product_height,
                        "weight" => $product_weight,
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
                //  dd($shipment);
            } catch (Exception $e) {

                $response = array(
                    'code' => 'error',
                    'msg' => "An error was returned from our shipment generation service, please show this to the administrator:<br/><br/>" . preg_replace("/\r\n|\r|\n/", '<br/>', htmlspecialchars($e->getMessage())),
                );
                echo json_encode($response);
                die;
            }
            //  dd($shipment);
            $flagSelected = false;
            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return $error = array('error' => $error);
                }
            } else {



                //dd($shipment);
                if (@count($shipment->rates) > 0) {
                    for ($i = 0; $i < count($shipment->rates); $i++) {
                        //match our rate to the service
                        foreach ($shipment->rates as $i => $rate) {
                            if ($shipment->rates[$i]->service == $shipping_service) {
                                //echo 'first';
                                $flagSelected = true;
                                $selectedRate = $shipment->rates[$i];
                                $retuen_response = array(
                                    'rate' => $selectedRate->rate,
                                    'ship_id' => $shipment->id,
                                    'rate_id' => $selectedRate->id,
                                    'store_data' => $store_info[0],
                                    'user_info' => $user_info,
                                    'service' => $selectedRate->service,
                                );
                                return $retuen_response;
                                break;
                            }
                        }
                    }

                    if ($flagSelected === false) {
                        $totalWeightVerify = $product_weight;
                        if ($totalWeightVerify > 256) {
                            for ($i = 0; $i < count($shipment->rates); $i++) {
                                //match our rate to the service
                                if ($shipment->rates[$i]->service == $shipping_service) {
                                    $selectedRate = $shipment->rates[$i];
                                    $retuen_response = array(
                                        'rate' => $selectedRate->rate,
                                        'ship_id' => $shipment->id,
                                        'rate_id' => $selectedRate->id,
                                        'store_data' => $store_info[0],
                                        'user_info' => $user_info,
                                    );
                                    return $retuen_response;
                                    break;
                                }
                            }
                        }
                    }

                    //  echo '$selectedRate: <pre>' .print_r($selectedRate->rate,true). '</pre>'; die();
                }
            }
            //echo '$shipment if condtion: <pre>' .print_r($shipment,true). '</pre>'; die();
        } else {
            //   dd($request->shipping_option_id);
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


            $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
            $country_id = isset($params['country']) ? $params['country'] : "";
            $state_id = isset($params['state']) ? $params['state'] : "";
            $userInfo = getUserById($userId);
            $stateInfo = getStatebyId($state_id);
            $countryInfo = getCountryById($country_id);

            $user_data = array();

            // echo '$params: <pre>' .print_r($shipment_data,true). '</pre>'; 
            // echo '$store_info: <pre>' .print_r($store_info,true). '</pre>'; die();

            $user_data['full_name'] = isset($params['full_name']) ? $params['full_name'] : "";
            $user_data['user_country_code'] = isset($countryInfo['code']) ? $countryInfo['code'] : "";
            $user_data['user_state_code'] = isset($stateInfo['code']) ? $stateInfo['code'] : "";
            $user_data['street'] = isset($params['street_address']) ? $params['street_address'] : "";
            $user_data['zipcode'] = isset($params['zipcode']) ? $params['zipcode'] : "";
            $user_data['user_city'] = isset($params['city_name']) ? $params['city_name'] : "";
            $user_data['user_phone'] = isset($userInfo->contact_no) ? $userInfo->contact_no : "";
            $user_data['user_email'] = isset($userInfo->email) ? $userInfo->email : "";
            $user_data['flat_other'] = isset($params['flat_other']) ? $params['flat_other'] : "";

            // User Shipping Address
            // $full_name = isset($params['full_name']) ? $params['full_name'] : "";
            $full_name = isset($shipment_data['first_name']) ? $shipment_data['first_name'] . ' ' . $shipment_data['last_name']  : "";
            $user_country_code = isset($countryInfo['code']) ? $countryInfo['code'] : "";
            $user_state_code = isset($shipment_data['states_countries']) ? $shipment_data['states_countries'] : "";
            $street = isset($shipment_data['street_address']) ? $shipment_data['street_address'] : "";
            $post_code = isset($shipment_data['zipcode']) ? $shipment_data['zipcode'] : "";
            $user_city = isset($shipment_data['city_name']) ? $shipment_data['city_name'] : "";
            $user_phone = isset($userInfo->contact_no) ? $userInfo->contact_no : "";
            $user_email = isset($userInfo->email) ? $userInfo->email : "";

            //dd($post_code);

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

            try {
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
                        "street1" => $street,
                        "city"    => $user_city,
                        "state"   => $user_state_code,
                        "zip"     => $post_code,
                        "phone"   => "415-456-7891",
                        // 'country' => $user_country_code,
                        // 'email'   => $user_email
                    ],
                    "parcel" => [
                        "length" => $product_length,
                        "width"  => $product_width,
                        "height" => $product_height,
                        "weight" => $product_weight,
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

                // $response = array(
                //     'code' => 'error',
                //     'msg' => "An noman error was returned from our shipment generation service, please show this to the administrator:<br/><br/>" . preg_replace("/\r\n|\r|\n/", '<br/>', htmlspecialchars($e->getMessage())),
                // );
                // echo json_encode($response);
                // die;
            }

            if (empty($shipment)) {
                $response = "An error was returned from our shipment generation service not created shipment, Please update your shipping profile:";
                return $error = array('error' => $response);
            }
            //  echo 'Data: <pre>' .print_r($shipment,true). '</pre>'; die();
            // echo '$shipment: <pre>' .print_r($shipment,true). '</pre>'; die();
            $flagSelected = false;

            if (!empty($shipment['messages'])) {
                if ($shipment['messages'][0]['type'] == 'rate_error') {
                    $error = $shipment['messages'][0]['message'];
                    return $error = array('error' => $error);
                }
            }
            $rates = $shipment->rates;
            // if($rates){


            //     $shipData = [];
            //     foreach($rates as $rate ){

            //         array_push($shipData,json_decode($rate,true));
            //     };
            //     return json_encode($shipData);
            //         // dd($rateArray);
            //         return json_encode($shipment->rates);
            //     }
            //  else {
            if (@count($shipment->rates) > 0) {
                for ($i = 0; $i < count($shipment->rates); $i++) {
                    //match our rate to the service
                    if ($shipment->rates[$i]->service == $shipping_service) {
                        $flagSelected = true;
                        $selectedRate = $shipment->rates[$i];
                        $retuen_response = array(
                            'rate' => $selectedRate->rate,
                            'ship_id' => $shipment->id,
                            'rate_id' => $selectedRate->id,
                            'store_data' => $store_info[0],
                            'user_info' => $user_data,
                        );
                        return $retuen_response;
                        break;
                    }
                }

                if ($flagSelected === false) {
                    $totalWeightVerify = $product_weight;
                    if ($totalWeightVerify > 16) {
                        for ($i = 0; $i < count($shipment->rates); $i++) {
                            //match our rate to the service
                            if ($shipment->rates[$i]->service == 'Priority') {
                                $selectedRate = $shipment->rates[$i];
                                $retuen_response = array(
                                    'rate' => $selectedRate->rate,
                                    'ship_id' => $shipment->id,
                                    'rate_id' => $selectedRate->id,
                                    'store_data' => $store_info[0],
                                    'user_info' => $user_data,
                                );
                                return $retuen_response;
                                break;
                            }
                        }
                    }
                }
            }
            // }
        }
    }
}
