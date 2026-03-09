<?php

namespace Packages\Home\Services;

use Exception;
use Illuminate\Http\Request;
use Packages\Home\Contracts\HomeHelpers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Home\Repositories\Eloquent\HomeRepository;

use function PHPUnit\Framework\isEmpty;

class HomeService
{
    use HomeHelpers;
    protected $homeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        $this->homeRepository = $homeRepository;
    }
    
    public function getHomeBanners($bannerCategory) {

		$banners = $this->homeRepository->getHomeBanners($bannerCategory);
		$bannerArray = [];
		if (count($banners) > 0) {
			$i = 1;
			foreach ($banners as $main_slide) {
				$banner = []; // Initialize an array for each banner

				$path = $this->getAttachmentUrlById($main_slide->attachment_id, 'media');
				$webpUrl = webPImages($path,'medium');
				// dd($webpUrl);
				if ($webpUrl) {
					$banner['banner_image_path'] = $webpUrl;
				}else{
					$banner['banner_image_path'] = $this->getAttachmentUrlById($main_slide->attachment_id, 'media');
				}
				$banner['image_link'] = !empty($main_slide->link) ? $main_slide->link : 'javascript:void(0)';
				$banner['bg_color'] = !empty($main_slide->bg_color) ? $main_slide->bg_color : 'javascript:void(0)';

				$bannerArray[] = $banner;

				$i++;
			}
		}
		return $bannerArray;
	}

	public function getHomeCategories()
	{
		try {
			$categories = $this->homeRepository->getHomeCategories();
			if($categories->isEmpty()){
				throw new ItemNotFoundException('No categories found');
			}

			$categoryArray = [];

			foreach ($categories as $category) {
				$categoryData = $category->only(['id', 'categoryTitle', 'category_slug', 'image_id']);
				$categoryData['category_image'] = $this->getAttachmentUrlById((int)$category->image_id, 'small');

				// Limit the number of products to 10 after retrieval
				$parentProductArray = [];
				foreach ($category->products->take(10) as $product) {
					$productData = $product->only(['product_id', 'title', 'slug', 'price', 'on_sale', 'sale_price', 'discount_percentage', 'shipping_option_id']);
					$productData['parent_product_image'] = $this->getAttachmentUrlById((int)$product->feature_image, 'medium');
					$parentProductArray[] = $productData;
				}

				// Process child categories
				$childCategoryArray = [];
				foreach ($category->childCategories as $childCategory) {
					$childCategoryData = $childCategory->only(['id', 'categoryTitle', 'category_slug', 'image_id']);
					$childCategoryData['child_category_image'] = $this->getAttachmentUrlById((int)$childCategory->image_id, 'small');

					// Limit the number of products to 10 for each child category
					$childProductArray = [];
					foreach ($childCategory->products->take(10) as $childProduct) {
						$childProductData = $childProduct->only(['product_id', 'title', 'slug', 'price', 'on_sale', 'sale_price', 'discount_percentage', 'shipping_option_id']);
						$childProductData['child_product_image'] = $this->getAttachmentUrlById((int)$childProduct->feature_image, 'small');
						$childProductArray[] = $childProductData;
					}
					$childCategoryData['products'] = $childProductArray;

					$childCategoryArray[] = $childCategoryData;
				}

				$categoryData['products'] = $parentProductArray;
				$categoryData['child_categories'] = $childCategoryArray;

				$categoryArray[] = $categoryData;
			}

			return $categoryArray;

		} catch (Exception $e) {
			throw $e;
		}
	}

	public function getSideBarCategories()
	{
		try {		
			$allHeadings = $this->homeRepository->getCategoryHeadings();
			$categoryArray = [];
			$counterId = 1;
		
			foreach ($allHeadings as $row) {
				$parentCategories = $this->homeRepository->getParentCategoriesByHeading($row->category_heading);
		
				$categories = $parentCategories->map(function ($parent) use ($counterId) {
					return [
						'counterId' => $counterId,
						'id' => $parent->id,
						'title' => $parent->categoryTitle,
						'slug' => $parent->category_slug,
						'heading' => $parent->category_heading,
						'parentid' => $parent->parentCategoryID,
						'parent_slug' => $parent->parentCategory?->category_slug ?? null, // Use Eloquent relation
					];
				});
		
				$categoryArray[] = [
					'headings' => [
						'counterId' => $counterId,
						'heading' => $row->category_heading,
					],
					'categories' => $categories,
				];
		
				$counterId++;
			}

			if (empty($categoryArray)) {
				throw new ItemNotFoundException('No categories found');
			}
		
			return $categoryArray;

		} catch (Exception $e) {
			throw $e;
		}
	}

	public function getCategories()
    {
        $allCategories = $this->homeRepository->getParentCategories();
		
        $categoryArray = [];

        foreach ($allCategories as $category) {
            $childCategories = $this->homeRepository->getChildCategories($category->id);
            $childs = [];

            foreach ($childCategories as $child) {
                $childs[] = [
                    'id'       => $child->id,
                    'title'    => $child->categoryTitle,
                    'slug'     => $child->category_slug,
                    'parentid' => $category->id
                ];
            }

            $categoryArray[] = [
                'parent_category' => [
                    'id'    => $category->id,
                    'title' => $category->categoryTitle,
                    'slug'  => $category->category_slug
                ],
                'childs' => $childs,
            ];
        }

        return $categoryArray;
    }
	public function categoryHeadingSeo($slug){
		$category = $this->getCategoryBySlug($slug);
		$data = array();
		if($category){
			$staticTitle = 'Shop '.$category->categoryTitle.' Online |  Best '.$category->categoryTitle.' Available - Mallshark';
			$staticDescription = 'Explore top-quality '.$category->categoryTitle.' at Mallshark. With '.$category->categoryTitle.' different options, find the perfect '.$category->categoryTitle.' to suit your needs. Enjoy shopping with secure payments.';
			$metaTitle = (isset($category->meta_title) && !is_null($category->meta_title)) ? $category->meta_title : $staticTitle;
			$metaDescription = (isset($category->meta_description) && !is_null($category->meta_description)) ? $category->meta_description : $staticDescription;
			$keyword = $category->categoryTitle;
			$data = [
				'keyword' => $keyword,
				'title' => $metaTitle,
				'description' => $metaDescription
			];
		}
		return $data;
	}
}