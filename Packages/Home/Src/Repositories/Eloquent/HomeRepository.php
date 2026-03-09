<?php

namespace Packages\Home\Repositories\Eloquent;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Packages\Core\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Packages\Core\Contracts\CoreHelpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Home\Contracts\HomeRepositoryInterface ;



class HomeRepository implements HomeRepositoryInterface
{
      
    public function getHomeBanners($category) {
         $slides = DB::table('slides')
                ->where('category', $category)
                ->select('slides.*')
                ->get();
        return $slides;
    }

    public function getHomeCategories()
    {
        try {
            return $Categories = Category::with([
                'products' => function ($query) {
                    $query->where('status', '1');
                },
                'childCategories' => function ($query) {
                    $query->whereHas('products', function ($query) {
                        $query->where('status', '1');
                    });
                },
                'childCategories.products' => function ($query) {
                    $query->where('status', '1');
                }
            ])
            ->where('parentCategoryID', 0)
            ->where('categoryTitle', '!=', 'Uncategorized')
            ->whereHas('products', function ($query) {
                $query->where('status', '1');
            })
            ->limit(16)
            ->get();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function getSideBarCategories() {

        $all_headings = DB::table('categories')
        ->join('product_category', 'product_category.category_id', 'categories.id')
        ->join('products', 'products.product_id', 'product_category.product_id')
        ->select('categories.*') // Select all columns from the categories table
        ->where('categories.parentCategoryID', '=', 0)
        ->where('products.status', '=', '1')
        ->where(function($query) {
            $query->where('categories.category_heading', '!=', '')
                  ->where('categories.category_heading', '!=', '0');
        })
        
        
        ->where('categories.status', '1')
        ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude 'Uncategorized'
        ->groupBy('categories.category_heading') // Group by category_heading for unique records
        ->get();
        $category_Array = array();
        $counterId = 1;
        
        foreach ($all_headings as $row) {
           
           
            $parentArray = array();
            $categories = array();
            $cat_info = ['counterId' => $counterId,'heading' => $row->category_heading];
            $parent_categories = DB::table('categories')
            ->join('product_category', 'product_category.category_id', 'categories.id')
            ->join('products', 'products.product_id', 'product_category.product_id')
            ->where('categories.status', '1')
            ->where('category_heading', '=', $row->category_heading)
            ->groupby('categories.id')
            ->get();
            // echo 'Data: <pre>' .print_r($parent_categories,true). '</pre>';
            $childs = array();
            foreach($parent_categories as $parent){
                if ($parent->parentCategoryID != 0) {
                    // Get parent category slug
                    $parent_category_info = DB::table('categories')
                        ->where('id', '=', $parent->parentCategoryID)
                        ->first(); // Get the parent category
        
                    // Add parent category slug to the array if it exists
                    $parentCategorySlug = $parent_category_info ? $parent_category_info->category_slug : null;
                } else {
                    $parentCategorySlug = null; // No parent
                }
                $categories[] = ['counterId' => $counterId , 'id' => $parent->id, 'title' => $parent->categoryTitle, 'slug' => $parent->category_slug, 'heading' => $parent->category_heading, 'parentid' => $parent->parentCategoryID,'parent_slug' => $parentCategorySlug ];
                
            }
            
            $category_Array[] = ['headings' => $cat_info, 'categories' => $categories];
            $counterId ++ ;
            
        }
        // echo 'Data: <pre>' .print_r($category_Array,true). '</pre>';
        return $category_Array;
    }

    public function getCategoryHeadings()
    {
        return Category::where('parentCategoryID', 0)
            ->where('categoryTitle', '!=', 'Uncategorized')
            ->where('status', '1')
            ->whereHas('products', function ($query) {
                $query->where('status', '1');
            })
            ->whereNotNull('category_heading')
            ->where('category_heading', '!=', '')
            ->where('category_heading', '!=', '0')
            ->groupBy('category_heading')
            ->get();
    }

    public function getParentCategoriesByHeading($heading)
    {
        return Category::where('status', '1')
            ->where('category_heading', '=', $heading)
            ->whereHas('products', function ($query) {
                $query->where('status', '1');
            }) // First, ensure categories have active products
            ->with('parentCategory:id,category_slug') // Then, eager load parentCategory
            ->groupBy('id')
            ->get();
    }
   
    public function getParentCategories()
    {
        return DB::table('categories')
            ->select('categories.*')
            ->where('categories.parentCategoryID', '=', 0)
            ->where('categories.categoryTitle', '!=', 'Uncategorized')
            ->get();
    }

    public function getChildCategories($parentId)
    {
        return DB::table('categories')
            ->where('parentCategoryID', '=', $parentId)
            ->get();
    }
}