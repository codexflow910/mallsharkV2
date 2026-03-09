<?php

namespace Packages\Dashboard\Services;

use Exception;
use Google_Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ItemNotFoundException;
use Packages\Dashboard\Contracts\DashboardHelpers;
use Packages\Dashboard\Config\DashboardConstants;
use Packages\Dashboard\Repositories\Eloquent\DashboardRepository;

class DashboardService

{
    use DashboardHelpers;
    protected $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function storeInfo()
    {
        try {
            $userData = (auth()->user())->toArray();
            $storeId = $this->getStore()->store_id;
            $storeData = $this->dashboardRepository->getStoreDataByStoreId($storeId);

            $totalProducts = $this->getStoreProductsCount($storeData->store_id, "decode");

            $prodesTemp = html_entity_decode(strip_tags($storeData->about_store));
			$storeAbout = str_replace(array("\r", "\n"), '', $prodesTemp);
			$storeData->about_store = $storeAbout;
        

            $storeData->store_url = DashboardConstants::STORE_URL . $storeData->store_slug;

            $store_logo  = $storeData->store_logo;
         
            $store_banner_img = $storeData->banner_image_id;
            if ($store_logo && !empty($store_logo)) {

                $store_logo = $this->getAttachmentUrlById((int) $store_logo, 'media');
            }
            $storeGallery = [];
            // if ($store_banner_img && !empty($store_banner_img)) {
            //     $unserializedLogo = unserialize($store_banner_img);
            //     if (isset($unserializedLogo) && !empty($unserializedLogo)) {
            //         foreach ($unserializedLogo as $banner_img_id) {
            //             $storeGallery[] = $this->getAttachmentUrlById((int) $banner_img_id, 'media');
            //         }
            //     }
            // }
          
            if (!empty($storeData->banner_image_id)) {
                $unserializedBanners = @unserialize($storeData->banner_image_id);

                if ($unserializedBanners && is_array($unserializedBanners)) {
                    foreach ($unserializedBanners as $banner_img_id) {
                        $url = getAttachmentUrlById((int) $banner_img_id, 'media');
                        $storeGallery[] = [
                            'banner_img_id' => (int) $banner_img_id,
                            'url' => $url,
                            'filename' => basename($url),
                        ];
                    }
                }
            }
            $storeData->store_logo = $store_logo;
            $storeData->banner_image_id = $storeGallery;

            $totalProductsActivecheck = false;
            $stripeChek = false;
            $profileCheck = false;
            if ($totalProducts >= 1) {
                $totalProductsActivecheck = true;
            }
            if (isset($storeData) && $storeData->stripe == '1') {
                $stripeChek = true;
            }
            if (isset($storeData->is_completed) && ($storeData->is_completed != 0)) {
                $profileCheck = true;
            }
            $storeData->totalProductsActivecheck = $totalProductsActivecheck;
            $storeData->stripeCheck = $stripeChek;
            $storeData->profileCheck = $profileCheck;
            $storeData->products_count = $totalProducts;
            $storeData->store_status = $this->checkStoreStatus($storeData->store_status);
            return ['storeData' => $storeData, 'userData' => $userData];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function ordersStats($timeFilter = 'all_time')
    {
        try {
            $storeId = $this->getStore()->store_id;
            return $this->dashboardRepository->getOrderCounts($storeId, $timeFilter);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function productStats()
    {
        try{
            $storeId = $this->getStore()->store_id;
            return $this->dashboardRepository->getProductCounts($storeId);
        } catch (\Exception $e) {
            throw $e;        
        }
    }

    public function getOrderPercentages($filter = 'all_time')
    {
        try {
            $storeId = $this->getStore()->store_id;
            return $this->dashboardRepository->getOrderPercentages($storeId, $filter);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function discountLive()
    {
        try {
            $storeId = $this->getStore()->store_id;
            return $this->dashboardRepository->discountLive($storeId);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getStore()
    {
        $store = auth()->user()->store;
        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }
        return $store;
    }
    

}
