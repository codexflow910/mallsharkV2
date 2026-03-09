<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Location;

use App\BaseAccess;
use App\Models\CountriesList;
use App\Models\StatesList;
use App\Models\CitiesList;
use DB;
class LocationAccess extends BaseAccess {

    private $_countries;
    private $_state;
    private $_cities;

    public function __construct() {
        $this->_countries = new CountriesList();
        $this->_state = new StatesList();
        $this->_cities = new CitiesList();
    }

    public function getAllCountries() {
        return $this->_countries->select('country_name', 'country_code')->get()->toArray();
    }

    public function getAllCountryById($id) {
        return $this->_countries->select('country_name')->where('country_id', '=', $id)->first()->toArray();
    }

    public function getStateByCountryCode($countryCode) {
        $states = $this->_state->select('state_id','code','state_name')->where('country_id', '=', $countryCode)->get()->toArray();
        return $states;
    }
   
    public function getStateByCountryCode2($countryCode) {
        $states = DB::table('states_list')->select('state_code','state_name')->where('country_code', '=', $countryCode)->get()->toArray();
      
        return $states;
    }

    public function getLocationAuto($data) {
        $arr = array();
        $countries = $this->_countries->select('country_name')->limit(10)->where('country_name', 'like', $data['term'] . '%')->get()->toArray();
        $arr = array_merge($arr, $countries);

        if (count($arr) < 10) {
            $states = $this->_state->select('state_name')->limit(10)->where('state_name', 'like', $data['term'] . '%')->get()->toArray();

            $arr = array_merge($arr, $states);
        }
        if (count($arr) < 10) {
            $cities = CitiesList::select('city_name')->limit(10)->where('city_name', 'like', $data['term'] . '%')->get()->toArray();
            $arr = array_merge($arr, $cities);
        }
        return $arr;
    }

    public function getCountryByCountryCode($countryCode) {
       
        return $this->_countries->select('country_name', 'country_id')->where('country_id', '=', $countryCode)->get()->toArray();
    }

    public function getStateByStateCode($stateCode, $countryCode) {
       
       $states = $this->_state->select('state_id', 'state_name')->where('country_id', '=', $countryCode)->where('state_id', '=', $stateCode)->get()->toArray();
      
        return $states;
    }
    public function getStateByStateCode2($stateCode,$countryCode) {
       
       return $states = DB::table('states_list')->select('state_name')->where('state_code', '=', $stateCode)->where('country_code', '=', $countryCode)->get()->toArray();
    
    }
    public function getCitiesByStateCode($data) {
        $cities = CitiesList::select('city_name')->where('country_code', '=', $data['user_country'])->where('state_code', '=', $data['user_state'])->where('city_name', 'like', $data['term'] . '%')->get()->toArray();
        return $cities;
    }
    public function getCitiesByStateCode2($country_code,$state_code) {
        $cities = CitiesList::select('city_name')->where('country_code', '=', $country_code)->where('state_code', '=', $state_code)->get()->toArray();
        return $cities;
    }

}
