<?php

namespace Packages\Product\Models\V3;

use App\Models\Attachments;
use App\Models\category;
use App\Models\DiscountApplicable;
use App\Models\Favourites;
use App\Models\OrderItems;
use App\Models\ProductCart;
use App\Models\Store;
use App\Models\User;
use App\Models\Review;
use Packages\Review\Models\V3\SuperadminReview;
use App\Models\ShippingOption;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Packages\Search\Contracts\SearchHelpers;
use Stripe\OrderItem;
use Illuminate\Support\Str;
use Packages\Shipping\Models\V3\ShippingProfile;
use Packages\Product\Models\V3\ProductCustomization;
use Packages\Product\Models\V3\CustomImageSetting;
use Packages\Product\Models\V3\CustomTextStyle;
use Packages\Product\Config\V3\ProductConstants;
use Packages\Store\Config\V3\StoreConstants;

use Algolia\AlgoliaSearch\SearchClient;
use Packages\Store\Models\V3\Countries;
class Product extends Model
{
    use Searchable;
    use SearchHelpers;
    use SoftDeletes;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $guarded = [];
    protected static function booted()
    {
        static::created(function ($product) {
            $product->searchable();
        });

        static::updated(function ($product) {
            // Only index if product is active and store is approved
            if ($product->status == ProductConstants::STATUS_ACTIVE && $product->store && $product->store->store_status == StoreConstants::STATUS_APPROVED) {
                $product->searchable();
            } else {
                // Remove from search index if product is not active or store not approved
                $product->unsearchable();
            }
        });

        static::deleted(function ($product) {
            $product->unsearchable();
        });
        static::saved(function ($product) {
            $product->syncToSuggestionIndex();
        });

        static::deleted(function ($product) {
            $product->removeFromSuggestionIndex();
        });
    }
    public function searchableAs()
    {
        return env('PRODUCT_INDEX');
    }

    /**
     * Get the value used to index the model.
     * This ensures consistent objectID format in Algolia (simple product_id).
     */
    public function getScoutKey()
    {
        return $this->product_id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName()
    {
        return 'product_id';
    }
    public function toSearchableArray()
    {   
        try {
            if ($this->store && $this->store->store_status == StoreConstants::STATUS_APPROVED) {
                if ($this->status == ProductConstants::STATUS_ACTIVE) {
                    $searchAbleArray = parent::toArray();
                    $searchAbleArray = [
                        'id' => $this->product_id,
                        'slug' => $this->slug,
                        'user_id' => $this->user_id,
                        'current_stock' => $this->current_stock,
                        'name' => $this->title,
                        'price' => $this->price,
                        'on_sale' => $this->on_sale,
                        'sale_price' => $this->sale_price,
                        'description' => Str::limit($this->description, 500),
                        'effective_price' => $this->on_sale ? $this->sale_price : $this->price,
                        'product_type' => $this->product_type,
                        'discount_percentage' => $this->discount_percentage,
                        'created_at' => $this->created_at,
                        'count_sales' => $this->sales > 0 ? $this->sales : 0,
                        'status' => $this->status,
                        'shipping_option_id' => $this->shipping_option_id ? $this->shipping_option_id : 221,
                        'mallshark_selected' => $this->mallshark_selected ? $this->mallshark_selected : 0,
                        'tags' => $this->tags ? $this->tags->pluck('tag')->toArray() : null,
                        'aitags' => $this->aitags ? $this->aitags->pluck('aitags')->toArray() : null,
                        'brand_distributor_id' => $this->brand_distributor_id ? $this->brand_distributor_id : null,
                        'brand_id' => $this->brand_id ? $this->brand_id : null,
                        'is_custom' => $this->is_custom ? $this->is_custom : 0,
                        'random_ranking' => rand(0, 999999),
                        'availability' => $this->availability ? $this->availability : null,
                        'rating' => $this->getAverageRating(),
                        'in_stock' => $this->current_stock ?? 0,
                    ];
                    $feature_image_id = $this->feature_image;
                    if ($feature_image_id) {
                        try {
                            $path = getAttachmentUrlByIdApi($feature_image_id, 'medium');
                            // dd($path);
                            if ($path) {
                                $webpUrl = webPImages($path, 'medium');
                                if ($webpUrl) {
                                    $searchAbleArray['feature_image'] = $webpUrl;
                                    $searchAbleArray['feature_image_alt'] = getImageAltById($feature_image_id);
                                } else {
                                    // If webP conversion fails, use the original path
                                    $searchAbleArray['feature_image'] = $path;
                                    $searchAbleArray['feature_image_alt'] = getImageAltById($feature_image_id);
                                }
                            } else {
                                \Log::channel('algolia')->warning('Failed to get attachment URL', [
                                    'product_id' => $this->product_id,
                                    'feature_image_id' => $feature_image_id
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::channel('algolia')->error('Error processing feature image', [
                                'product_id' => $this->product_id,
                                'feature_image_id' => $feature_image_id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }             
                    // Handle categories with hierarchy
                    $searchAbleArray['categories'] = $this->categories->map(function ($category) {
                        return $this->getCategoryHierarchy($category);
                    })->flatten()->unique()->toArray() ?? [];
                    $tags = $this->tags ? $this->tags->pluck('tag_title')->toArray() : [];
                    $aitags = $this->aiTags ? $this->aiTags->pluck('tag_title')->toArray() : [];
                    $labels = $this->labels ? $this->labels->pluck('slug')->toArray() : [];
                    $store = $this->store ? $this->store->store_name : '';
                    $storeId = $this->store ? $this->store->store_id : '';
                    // Check if tags exist; if not, assign a default value or empty array
                
                    $searchAbleArray['tags'] = !empty($tags) ? $tags : [];
                    $searchAbleArray['aitags'] = !empty($aitags) ? $aitags : [];
                    $searchAbleArray['store'] = !empty($store) ? $store : '';
                    $searchAbleArray['storeId'] = !empty($storeId) ? $storeId : '';
                    $searchAbleArray['labels'] = !empty($labels) ? $labels : [];
                    $searchAbleArray['store_name'] = !empty($store) ? $store : '';
                    $searchAbleArray['shipping_countries'] = $this->getShippingCountries();

                    // Index product properties for category-based filtering
                    $props = $this->properties;
                    if ($props && $props->count() > 0) {
                        $specsArray = [];
                        $colorValues = [];
                        foreach ($props as $prop) {
                            $key = strtolower(str_replace(' ', '_', trim($prop->filter_key)));
                            if ($key === 'color') {
                                $colorValues[] = $prop->value;
                            } else {
                                $specsArray[$key][] = $prop->value;
                            }
                        }
                        if (count($colorValues) === 1) {
                            $searchAbleArray['color'] = $colorValues[0];
                        } elseif (count($colorValues) > 1) {
                            $searchAbleArray['color'] = $colorValues;
                        }
                        foreach ($specsArray as $k => $v) {
                            $specsArray[$k] = count($v) === 1 ? $v[0] : $v;
                        }
                        if (!empty($specsArray)) {
                            $searchAbleArray['properties'] = $specsArray;
                        }
                    }
                    $searchAbleArray['model_class'] = BaseProduct::class;
                    return $searchAbleArray;
                }else{
                    return [];
                }
            }else{
                return [];
            }
        } catch (\Throwable $e) {
            // Optionally log the exception: \Log::error($e);
            return [];
        }
    }
    
    /**
     * Calculate the average rating from reviews.
     */
    private function getAverageRating()
    {
        try {
            $ratings = $this->reviews ? $this->reviews->pluck('rating')->toArray() : [];
            $totalCount = count($ratings);
            if ($totalCount > 0) {
                return round(array_sum($ratings) / $totalCount, 2);
            }
            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // Helper function for category hierarchy
    private function getCategoryHierarchy($category)
    {
        $categories = [$category->category_slug];
    
        // Traverse up to find all parents
        while ($category->parent) {
            $category = $category->parent; // Uses the 'parent' relationship
            $categories[] = $category->category_slug;
        }
    
        return array_reverse($categories); // Return hierarchy from parent to child
    }

    /**
     * Get unique country names from store's shipping origins.
     */
    private function getShippingCountries()
    {
        try {
            $storeId = $this->store_id;
            if (!$storeId) return [];

            $countryIds = \DB::table('store_shipping_origins')
                ->where('store_id', $storeId)
                ->pluck('store_country')
                ->unique()
                ->toArray();

            if (empty($countryIds)) {
                return [];
            }

            return Countries::whereIn('id', $countryIds)
                ->pluck('name')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    // suggestions indexing
    public function toSuggestionArray()
    {
        return [
            'objectID' => $this->product_id,
            'query' => $this->title,
            'store_id' => $this->store_id,
        ];
    }
    public function syncToSuggestionIndex()
    {
        $client = SearchClient::create(
            config('scout.algolia.id'),
            config('scout.algolia.secret')
        );

        $index = $client->initIndex('query_suggestions');

        $index->saveObject($this->toSuggestionArray());
    }
    // delete from suggestion index
    public function removeFromSuggestionIndex()
    {
        $client = SearchClient::create(
            config('scout.algolia.id'),
            config('scout.algolia.secret')
        );

        $index = $client->initIndex('query_suggestions');

        $index->deleteObject($this->product_id);
    }
    public function store()
    {
        return $this->hasOne(Store::class, 'store_id', 'store_id');
    }
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'product_id');
    }
    public function parentCategories()
    {
        return $this->belongsToMany(category::class, 'product_category', 'product_id', 'category_id')->where('parentCategoryID',0);
    }
    public function childCategories()
    {
        return $this->belongsToMany(category::class, 'product_category', 'product_id', 'category_id')->where('parentCategoryID','!=',0);
    }
    public function categories()
    {
        return $this->belongsToMany(category::class, 'product_category', 'product_id', 'category_id');
    }
    
    // Get Multiple FAQS
    public function faqs()
    {
        return $this->hasMany(ProductFaq::class, 'product_id', 'product_id');
    }

    // Get for product cart items 
    public function cartItems()
    {
        return $this->hasMany(ProductCart::class, 'product_id');
    }


    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id')->orderBy('order','asc');
    }

   
    public function shippingOption()
    {
        return $this->belongsTo(ShippingOption::class, 'shipping_option_id', 'id');
    }

    public function productImages(){
        return $this->hasOne(Attachments::class, 'attachment_id', 'feature_image');
    }
    
    public function orderItems(){
        return $this->hasMany(OrderItems::class, 'item_id', 'product_id');
    }
    public function favouriteProducts(){
    
        return $this->hasOne(Favourites::class,'product_id','product_id');
    }

    public function applicables()
    {
        return $this->morphMany(DiscountApplicable::class, 'applicable');
    }

    public function discountApplicables()
    {
        return $this->morphMany(DiscountApplicable::class, 'applicable');
    }

    public function tags()
    {
        return $this->hasMany(ProductTags::class, 'product_id', 'product_id');
    }
    
    public function specifications()
    {
        return $this->hasMany(ProductSpecifications::class, 'product_id', 'product_id');
    }

    public function properties()
    {
        return $this->hasMany(ProductProperty::class, 'product_id', 'product_id');
    }
    public function aiTags()
    {
        return $this->hasMany(ProductAiTags::class, 'product_id', 'product_id');
    }
 
    public function shippingProfiles()
    {
        return $this->belongsToMany(ShippingProfile::class, 'product_shipping', 'product_id', 'shipping_id')->withTimestamps();
    }
    public function videos()
    {
        return $this->belongsToMany(Video::class, 'product_video', 'product_id', 'video_id');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'label_products', 'product_id', 'label_id');
    } 
    public function superadminReview()
    {
        return $this->hasMany(SuperadminReview::class, 'product_id', 'product_id');
    }
    public function customization()
    {
        return $this->hasOne(ProductCustomization::class, 'product_id', 'product_id');
    }

    public function textStyles()
    {
        return $this->hasMany(CustomTextStyle::class, 'product_id', 'product_id');
    }

    public function imageSettings()
    {
        return $this->hasOne(CustomImageSetting::class, 'product_id', 'product_id');
    }

    public function characterLimit()
    {
        return $this->hasOne(CustomTextCharacter::class, 'product_id', 'product_id');
    }
    public function tiers()
    {
        return $this->hasMany(ProductTier::class, 'product_id');
    }
}
