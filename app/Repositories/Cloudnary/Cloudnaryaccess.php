<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Creditaccess
 *
 * @author Asif
 */

namespace App\Repositories\Cloudnary;

use App\BaseAccess;

class Cloudnaryaccess extends BaseAccess {

    public function __construct() {
        \Cloudinary::config(array(
            "cloud_name" => env('Cloudnary_Name'),
            "api_key" => env('Cloudnary_Api_key'),
            "api_secret" => env('Cloudnary_Secret')
        ));
    }

    public function uploadImage($images, $options) {
        $result = array();
        $arr = array();
        foreach ($images as $img) {
            //print_r($img->getRealPath());
            $res = \Cloudinary\Uploader::upload($img->getRealPath(), $options);
            $result['image_name'] = $res['public_id'];
            $result['format'] = $res['format'];
           // $result['url'] = $res['secure_url'];
            $result['url'] = $res['secure_url'];
            $arr[] = $result;
           
        }
         
        return $arr;
    }

    public function deleteImages($imageId) {
        $res = \Cloudinary\Uploader::destroy($imageId);
        return $res;
    }

}
