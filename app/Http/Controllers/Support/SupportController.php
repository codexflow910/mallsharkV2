<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Admin\Adminaccess;
use App\Repositories\Posts\Postaccess;
use App\Repositories\Buyer\Buyeraccess;
use App\Repositories\Location\LocationAccess;
use App\Http\Controllers\ElasticSearchController;
use App\Repositories\Filters\Filtersaccess;
use Redirect;
class SupportController extends Controller {

    private $_request;
    private $_adminobj;
    private $_buyerobj;
    private $_locationobj;
    private $_esObj;
    private $filter_obj;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->_adminobj = new Adminaccess();
       
        $this->_buyerobj = new Buyeraccess();
        $this->_locationobj = new LocationAccess();
        $this->_esObj = new ElasticSearchController();
        $this->filter_obj = new Filtersaccess();
    }

    public function index() {
        $unactivatedUser = $this->_adminobj->countActivateAndDeactiveUsers(0);
        $activatedUser = $this->_adminobj->countActivateAndDeactiveUsers(2);
        $unactiveListing = $this->_adminobj->getActiveListing(0);
        $activeListing = $this->_adminobj->getActiveListing(1);
        return View('support/supportdashboard', ['unactivatedUser' => $unactivatedUser, 'activatedUser' => $activatedUser, 'unactiveListing' => $unactiveListing, 'activeListing' => $activeListing]);
    }

    public function listManagement() {
        $data = $this->_request->all();
        $results = $this->_esObj->listSearch($data);
        return view('support/listmanagement', ['listingresults' => $results]);
    }

    public function ChangeStatus($productId, $status) {
        $result = $this->_adminobj->ChangeStatusByid($productId, $status);
        if ($result) {
            $this->_request->session()->flash('SuccessMessage', 'Add status changed successfully.');
        } else {
            $this->_request->session()->flash('ErrorMessage', 'Add Status is not changed please contact to technical dept.');
        }
        $redirect = url()->previous();
        return redirect($redirect);
    }

    public function allUsers() {
        $getData = $this->_request->all();
        $allUsers = $this->_esObj->userSearch($getData);
        return view('support/users', ['allUsers' => $allUsers]);
    }

    public function userInfo($userId) {
        if ($this->_request->isMethod('post')) {

//            $this->_request->validate([
//                'txtname' => 'required',
//                'email' => 'unique:users,email',
//                'password' => 'required|confirmed',
//            ]);
            $postedData = $this->_request->input();
            $result = $this->_adminobj->updateuserProfile($postedData, $userId);
            if ($result == true) {
                $this->_request->session()->flash('SuccessMessage', 'User information has been updated successfully');
                return redirect('/support/allusers');
            } else {

                $this->_request->session()->flash('ErrorMessage', 'Some thing went wrong');
                return redirect('support/allusers');
            }
        }
        $userData = $this->_adminobj->getuserProfile($userId);
        if (!empty($userData['user_country_id'])) {
            $countryCode = $userData['user_country_id'];
            $states = $this->_locationobj->getStateByCountryCode($countryCode);
        } else {
            $states = '';
        }
        return view('support/userdetail', ['userData' => $userData, 'states' => $states]);
    }

    public function postdetail() {
        $slug = collect(request()->segments())->last();
        $postData = $this->_adminobj->getPostDataBySlug($slug);
        $postCategories = $this->_Postaccess->getCateoriesOfProducts($postData['product_id']);
        return View('posts/post-detail', ['postData' => $postData, 'postCategories' => $postCategories]);
    }

    public function userChangeStatus($userId, $status) {
        $result = $this->_adminobj->udpdateStatus($userId, $status);
        if ($result) {
            $this->_request->session()->flash('SuccessMessage', 'User status changed successfully.');
        } else {
            $this->_request->session()->flash('ErrorMessage', 'Something went wrong.');
        }
        $redirect = url()->previous();
        return redirect($redirect);
    }

    public function editPost($id) {
        $ProductCondition = $this->filter_obj->getFiltervaluesBYId('1');
        $result = $this->_adminobj->getPostadmin($id);
        return View('support/editpost', ['prodcutDetails' => $result, 'ProductCondition' => $ProductCondition]);
    }

    public function updatePostbySupport() {
        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            $this->_Postaccess->updatePostById($postedData);
            $this->_request->session()->flash('SuccessMessage', 'Post has been updated Successfully');
            return Redirect::to('/support/listing');
        }
    }

}
