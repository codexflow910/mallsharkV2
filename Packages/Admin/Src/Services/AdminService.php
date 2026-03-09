<?php

namespace Packages\Admin\Services;

use Exception;
use Illuminate\Support\ItemNotFoundException;
use Packages\Admin\Repositories\Eloquent\AdminRepository;

class AdminService
  
{
    
    protected $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }
    
    public function getStore()
    {
        $store = auth()->user()->store;
        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }
        return $store;
    }

    public function getCategoryTags($id) {
        return $this->adminRepository->getCategoryTags($id);
    }
    public function getorderstransferVendorPayment($store_id)
    {
        try {
            $amount = $this->adminRepository->getorderstransferVendorPayment($store_id);

            if ($amount) {
                return $amount;
            } else {

                return 0;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function gettransferVendorPayment($store_id) {
        try {
            $amount = $this->adminRepository->gettransferVendorPayment($store_id);
       
            if ($amount) {
                return $amount;
            } else {
              
                return 0;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function transferVendorPayment($store_id, $amount)
    {
        try{
                $message = $this->adminRepository->transferVendorPayment($store_id, $amount);
                if($message){
                    return $message;
                }
                else{
                    return false;
                }
        } catch (\Exception $e) {
                    throw $e;
                }
    }
    public function transferOrderVendorPayment($store_id, $amount, $orderIds)
    {
        try {
            $message = $this->adminRepository->transferOrderVendorPayment($store_id, $amount, $orderIds);
         
            if ($message) {
                return $message;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createTag(array $data) {
        
        $existingTag = $this->adminRepository->getTag($data['name'], $data['category_id']);

        if ($existingTag) {
            throw new Exception('Tag already exists');
        }

        return $this->adminRepository->createTag($data);
    }
   
    public function deleteTag($id) 
    {
        return $this->adminRepository->deleteTag($id);
    }
}