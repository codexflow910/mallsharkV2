<?php

namespace Packages\Admin\Services\V4;

use Exception;
use Illuminate\Support\ItemNotFoundException;
use Packages\Admin\Repositories\Eloquent\V4\AdminRepository;

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

    public function createTag(array $data) {
        
        $existingTag = $this->adminRepository->getTag($data['name'], $data['category_id']);

        if ($existingTag) {
            throw new Exception('Tag already exists');
        }

        return $this->adminRepository->createTag($data);
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
    public function gettransferVendorPayment($store_id)
    {
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


    public function getDeductionByStoreId($storeId)
    {
        try {
        $deduction = $this->adminRepository->getByStoreId($storeId);
        if (!$deduction) {
            throw new ItemNotFoundException('Company profile has not been created');
        }

        return $deduction;
     } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createOrUpdate(array $data)
    {
        try{
        $existing = $this->adminRepository->getByStoreId($data['store_id']);

        if ($existing) {
            return $this->adminRepository->update($existing, $data);
        }

        return $this->adminRepository->create($data);
      }  catch (\Exception $e) {
            throw $e;
        }
    }
    public function transferVendorPayment($store_id, $amount)
    {
        try {
            $message = $this->adminRepository->transferVendorPayment($store_id, $amount);
            if ($message) {
                return $message;
            } else {
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


    public function deleteTag($id) 
    {
        return $this->adminRepository->deleteTag($id);
    }
}