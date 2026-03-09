<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Post;
use App\Models\category;
use App\Models\Campaigns;
use App\Models\BlogCategory;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use App\Models\CampaignStore;
use App\Repositories\Store\Storeaccess;
use App\Http\Controllers\SlidesController;
use App\Repositories\Product\Productaccess;
use App\Http\Controllers\ElasticSearchController;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use DateTime;
class HomeController extends Controller
{

    private $slides_obj;
    private $productObj;
    private $_storeobj;
    private $elasticSearchObj;
    private $mailgun;

    public function __construct()
    {
        $this->productObj = new Productaccess();
        $this->_storeobj = new Storeaccess();
        $this->slides_obj = new SlidesController();
        $this->elasticSearchObj = new ElasticSearchController();
        $this->mailgun = new MailgunController();
    }
    
    public function newIndex()
    {
        $products = topSellingProducts();

        $slides_main = $this->slides_obj->getAllSlidesByCategory('home');
        $slide_small = $this->slides_obj->getAllSlidesByCategory('home-small');
        $topSelling = $this->productObj->getTopSellingProducts();
        $featuredProducts = $this->productObj->getFeaturedProducts();
        $topBrands = $this->_storeobj->getBrandsOfWeek();
        $flashsalesProduct = $this->productObj->getHomePageFlashProducts();
        $featureFlashsale = $this->productObj->getHomePageFlashProducts($status = 1);
        // $flashSaleAboutToBegain = $this->productObj->getFlashSaleProductsToBegain();
        $featureProducts = $this->productObj->getFeatureProducts();
        $query = DB::table('herd_users')->select('user_herd_id')->groupBy('user_herd_id')->get();
        $totalSupports = count($query);
        $liveCampaigns = DB::table('store_campaigns')->where('status', 'support')->count();
        $totalStores = DB::table('stores')->count();
        $totalGivenBack = DB::table('store_campaigns')->sum('toal_gave_back');

        // if ($this->isMobileDevice()) {
        //     return view('home/mobile/home', ['products' => $products, 'topSelling' => $topSelling, 'featuredProducts' => $featuredProducts, 'topBrands' => $topBrands, 'flashsalesProduct' => $flashsalesProduct, 'slide_small' => $slide_small, 'slides_main' => $slides_main, 'featureFlashsale' => $featureFlashsale, 'totalSupports' => $totalSupports, 'liveCampaigns' => $liveCampaigns, 'totalStores' => $totalStores, 'totalGivenBack' => $totalGivenBack, 'flashSaleAboutToBegain' => $flashSaleAboutToBegain, 'featureProducts' => $featureProducts]);
        // } else {
            return view('home/index', ['products' => $products, 'topSelling' => $topSelling, 'featuredProducts' => $featuredProducts, 'topBrands' => $topBrands,
             'flashsalesProduct' => $flashsalesProduct, 'slide_small' => $slide_small, 'slides_main' => $slides_main, 'featureFlashsale' => $featureFlashsale, 
             'totalSupports' => $totalSupports, 'liveCampaigns' => $liveCampaigns, 'totalStores' => $totalStores, 'totalGivenBack' => $totalGivenBack,
            //   'flashSaleAboutToBegain' => $flashSaleAboutToBegain, 
              'featureProducts' => $featureProducts]);

        // }
    }
    public function index()
    {   
        
        $productsYouMayLikeDB = [];
        $productsYouMayLikeElastic = [];
        if(!empty($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] == 1 && !empty($_COOKIE['tags'])){
            $tags = json_decode($_COOKIE['tags'], true); // Specify the second argument as true to decode as an array

            if (is_array($tags)) {
                // Use array_filter to remove empty elements
                $filteredTags = array_filter($tags, function($tag) {
                    return !empty($tag);
                });
                if (!empty($filteredTags)) {
                    // If there are non-empty elements, implode them
                    $all_filters['keyword'] = implode(" ", $filteredTags);
                } else {
                    // Handle the case where all elements are empty
                    $all_filters['keyword'] = "No valid tags found";
                }
            } else {
                // Handle the case where the JSON couldn't be decoded as an array
                $all_filters['keyword'] = "Invalid JSON format";
            }
        //    $tags = json_decode($_COOKIE['tags']);
        //    $all_filters['keyword'] = implode(" ",$tags);
     
           $productsYouMayLikeElastic = $this->elasticSearchObj->searchDocument($all_filters,$slug='');
        }
        $productsYouMayLikeDB = $this->productObj->getRecommendedProducts(18);
       
        foreach ($productsYouMayLikeDB as &$productYouMayLikeDB) {
            if (isset($productYouMayLikeDB['categories']) && count($productYouMayLikeDB['categories']) > 0) {
                $categories = $productYouMayLikeDB['categories'];
                $categorySlugs = [];
        
                foreach ($categories as $category) {
                    if ($category->parentCategoryID == 0) {
                        // Parent category
                        $categorySlugs[] = $category->category_slug;
        
                        // Find and add child categories for this parent
                        foreach ($categories as $childCategory) {
                            if ($childCategory->parentCategoryID == $category->id) {
                                $categorySlugs[] = $childCategory->category_slug;
                            }
                        }
                    }
                }
        
                // Replace the existing categories array with the new category array
                $productYouMayLikeDB['categories'] = $categorySlugs;
            }
        }
        // dd($productYouMayLikeDB);
        $mergedProducts = collect();

        // Process Elasticsearch products and apply checks for each field
        if (!empty($productsYouMayLikeElastic['records'])) {
            foreach ($productsYouMayLikeElastic['records'] as $record) {
                if (isset($record['_source'])) {
                    $source = $record['_source'];
                    $mergedProducts->push([
                        'product_id' => $source['product_id'] ?? null,
                        'user_id' => $source['user_id'] ?? null,
                        'store_id' => $source['store_id'] ?? null,
                        'title' => $source['title'] ?? 'Unknown Product',
                        'slug' => $source['slug'] ?? null,
                        'short_description' => $source['short_description'] ?? '',
                        'description' => $source['description'] ?? '',
                        'price' => $source['price'] ?? 0,
                        'quantity' => $source['quantity'] ?? 0,
                        'current_stock' => $source['current_stock'] ?? 0,
                        'sku' => $source['sku'] ?? null,
                        'weight' => $source['weight'] ?? 0,
                        'status' => $source['status'] ?? 'Inactive',
                        'feature_image' => $source['feature_image'] ?? null,
                        'gallery' => $source['gallery'] ?? '',
                        'productSize_length' => $source['productSize_length'] ?? null,
                        'productSize_width' => $source['productSize_width'] ?? null,
                        'productSize_height' => $source['productSize_height'] ?? null,
                        'maxquantitylimit' => $source['maxquantitylimit'] ?? null,
                        'product_type' => $source['product_type'] ?? 'simple',
                        'feature_price' => $source['feature_price'] ?? null,
                        'is_deleted' => $source['is_deleted'] ?? null,
                        'is_feature' => $source['is_feature'] ?? "0",
                        'feature_startdate' => $source['feature_startdate'] ?? null,
                        'feature_enddate' => $source['feature_enddate'] ?? null,
                        'product_status' => $source['product_status'] ?? null,
                        'sales' => $source['sales'] ?? 0,
                        'product_currency' => $source['product_currency'] ?? null,
                        'shipping_option_id' => $source['shipping_option_id'] ?? null,
                        'on_campaign' => $source['on_campaign'] ?? 0,
                        'user_revenue' => $source['user_revenue'] ?? 0,
                        'cashback_pool' => $source['cashback_pool'] ?? 0,
                        'supersupporter_pool' => $source['supersupporter_pool'] ?? 0,
                        'flashsale_status' => $source['flashsale_status'] ?? 'all',
                        'video_url' => $source['video_url'] ?? '',
                        'listing_type' => $source['listing_type'] ?? null,
                        'top_selling' => $source['top_selling'] ?? 0,
                        'home_flash_sale' => $source['home_flash_sale'] ?? 0,
                        'on_sale' => $source['on_sale'] ?? 0,
                        'sale_price' => $source['sale_price'] ?? null,
                        'discount_percentage' => $source['discount_percentage'] ?? 0,
                        'discount_id' => $source['discount_id'] ?? null,
                        'created_at' => $source['created_at'] ?? now(),
                        'updated_at' => $source['updated_at'] ?? now(),
                        'feature_status' => $source['feature_status'] ?? null,
                        'weight_type' => $source['weight_type'] ?? 'pound',
                        'dimention_type' => $source['dimention_type'] ?? 'inch',
                        'item_condition' => $source['item_condition'] ?? 'new',
                        'tags' => $source['tags'] ?? '',
                        'categories' => $source['categories'] ?? [],
                        'pin' => $source['pin'] ?? [],
                        'supported' => $source['supported'] ?? []
                    ]);
                }
            }
        }
        
        // Add database products if necessary to reach a maximum of 18 products
        if ($mergedProducts->count() < 18) {
            $dbProducts = collect($productsYouMayLikeDB)->take(18 - $mergedProducts->count());
            $mergedProducts = $mergedProducts->merge($dbProducts);
        }
        
        // Limit the collection to 18 products and return
        $mergedProducts = $mergedProducts->take(18);
        
        // dd($mergedProducts);
        $allCategories = category::where('parentCategoryID', 0)->where('category_slug', '!=', 'uncategorized')->get()->toArray();
        $products = topSellingProducts();
        $slides_main = $this->slides_obj->getAllSlidesByCategory('home');
        $slide_small = $this->slides_obj->getAllSlidesByCategory('home-small');
        $topSelling = $this->productObj->getTopSellingProducts();
        $featuredProducts = $this->productObj->getFeaturedProducts();
        $topBrands = $this->_storeobj->getBrandsOfWeek();
        $brands = $this->_storeobj->getBrands('created_at','asc',5,false);
        $flashsalesProduct = $this->productObj->getHomePageFlashProducts();
        $featureFlashsale = $this->productObj->getHomePageFlashProducts($status = 1);
        // $flashSaleAboutToBegain = $this->productObj->getFlashSaleProductsToBegain();
        $featureProducts = $this->productObj->getFeatureProducts();
        $simpleProducts = $this->productObj->getProducts('created_at','asc',12,true);
        $recommendedProducts = $this->productObj->getRecommendedProducts(18);
        $trendingProducts = $this->productObj->getTrendingProducts('desc',30, true);
        $bestSellers =  $this->productObj->getBestSellerProducts(12,true);
        $exploreMore =  $this->productObj->getExploreMoreProducts(12);
        $query = DB::table('herd_users')->select('user_herd_id')->groupBy('user_herd_id')->get();
        $totalSupports = count($query);
        // $liveCampaigns = DB::table('store_campaigns')->where('status', 'support')->count();
        $totalStores = DB::table('stores')->count();
        // $totalGivenBack = DB::table('store_campaigns')->sum('toal_gave_back');
        // echo 'Data: <pre>' .print_r($allCategories,true). '</pre>'; die();

            return view('home.new-index', ['simpleProducts'=>$simpleProducts,'recommendedProducts'=>$recommendedProducts,'bestSellers'=>$bestSellers,'productsYouMayLikeDB'=>$mergedProducts,
            'brands'=>$brands,'products' => $products, 'topSelling' => $topSelling, 'featuredProducts' => $featuredProducts,
             'topBrands' => $topBrands, 'flashsalesProduct' => $flashsalesProduct, 'slide_small' => $slide_small, 'slides_main' => $slides_main, 'featureFlashsale' => $featureFlashsale,
              'totalSupports' => $totalSupports, 'totalStores' => $totalStores, 'trendingProducts'=> $trendingProducts,
            //    'flashSaleAboutToBegain' => $flashSaleAboutToBegain,
                'featureProducts' => $featureProducts,'exploreMore' => $exploreMore, 'categories' => $allCategories]);

        
    }
    public function v2Index()
    {   
        
        $productsYouMayLikeDB = [];
        $productsYouMayLikeElastic = [];
        if(!empty($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] == 1 && !empty($_COOKIE['tags'])){
            $tags = json_decode($_COOKIE['tags'], true); // Specify the second argument as true to decode as an array

            if (is_array($tags)) {
                // Use array_filter to remove empty elements
                $filteredTags = array_filter($tags, function($tag) {
                    return !empty($tag);
                });
                if (!empty($filteredTags)) {
                    // If there are non-empty elements, implode them
                    $all_filters['keyword'] = implode(" ", $filteredTags);
                } else {
                    // Handle the case where all elements are empty
                    $all_filters['keyword'] = "No valid tags found";
                }
            } else {
                // Handle the case where the JSON couldn't be decoded as an array
                $all_filters['keyword'] = "Invalid JSON format";
            }
        //    $tags = json_decode($_COOKIE['tags']);
        //    $all_filters['keyword'] = implode(" ",$tags);
     
           $productsYouMayLikeElastic = $this->elasticSearchObj->searchDocument($all_filters,$slug='');
    

        }else{

            $productsYouMayLikeDB =  $this->productObj->getRecommendedProducts(12);
        }

        $allCategories = category::where('parentCategoryID', 0)->where('category_slug', '!=', 'uncategorized')->get()->toArray();
        $products = topSellingProducts();
        $slides_main = $this->slides_obj->getAllSlidesByCategory('home');
        $slide_small = $this->slides_obj->getAllSlidesByCategory('home-small');
        $topSelling = $this->productObj->getTopSellingProducts();
        $featuredProducts = $this->productObj->getFeaturedProducts();
        $topBrands = $this->_storeobj->getBrandsOfWeek();
        $brands = $this->_storeobj->getBrands('created_at','asc',5,false);
        $flashsalesProduct = $this->productObj->getHomePageFlashProducts();
        $featureFlashsale = $this->productObj->getHomePageFlashProducts($status = 1);
        // $flashSaleAboutToBegain = $this->productObj->getFlashSaleProductsToBegain();
        $featureProducts = $this->productObj->getFeatureProducts();
        $simpleProducts = $this->productObj->getProducts('created_at','asc',12,true);
        $recommendedProducts = $this->productObj->getRecommendedProducts(12);
        $trendingProducts = $this->productObj->getTrendingProducts('desc',30, true);
        $bestSellers =  $this->productObj->getBestSellerProducts(12,true);
        $exploreMore =  $this->productObj->getExploreMoreProducts(12);
        $query = DB::table('herd_users')->select('user_herd_id')->groupBy('user_herd_id')->get();
        $totalSupports = count($query);
        // $liveCampaigns = DB::table('store_campaigns')->where('status', 'support')->count();
        $totalStores = DB::table('stores')->count();
        // $totalGivenBack = DB::table('store_campaigns')->sum('toal_gave_back');
        // echo 'Data: <pre>' .print_r($allCategories,true). '</pre>'; die();

            return view('home.new-index', ['simpleProducts'=>$simpleProducts,'recommendedProducts'=>$recommendedProducts,'bestSellers'=>$bestSellers,'productsYouMayLikeDB'=>$productsYouMayLikeDB,
            'productsYouMayLikeElastic'=>$productsYouMayLikeElastic,'brands'=>$brands,'products' => $products, 'topSelling' => $topSelling, 'featuredProducts' => $featuredProducts,
             'topBrands' => $topBrands, 'flashsalesProduct' => $flashsalesProduct, 'slide_small' => $slide_small, 'slides_main' => $slides_main, 'featureFlashsale' => $featureFlashsale,
              'totalSupports' => $totalSupports, 'totalStores' => $totalStores, 'trendingProducts'=> $trendingProducts,
            //    'flashSaleAboutToBegain' => $flashSaleAboutToBegain,
                'featureProducts' => $featureProducts,'exploreMore' => $exploreMore, 'categories' => $allCategories]);

        
    }
    public function zanimals()
    {

        // if ($this->isMobileDevice()) {
        //     return view('zanimals.mobile.zanimals');
        // } else {
            return view('zanimals.zanimals');

        // }

    }
    public function showFeatureProducts(Request $request) {
        $categories = !empty($request->input('category')) ?  $request->input('category') :'';
        $featureProducts = $this->productObj->getFeatureProducts('true',$categories);
        if($request->ajax()) {
            return view('home/pageinate-featureproduct', compact('featureProducts'));
        } else {
            return view('home/featureproduct', compact('featureProducts'));
        }
        

    }

    public function get404Page(){
        return view('error.404page');
    }
    public function get500Page(){
        return view('error.500page');
    }
    public function get400Page(){
        return view('error.400page');
    }
    public function get503Page(){
        return view('error.503page');
    }
    public function getcomingsoon(){
        return view('error.comingsoon');
    }
    public function mailSend(){
    try {

        $html=view('mail-bk.activateUser');
        $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
        $toEmail = "noman@mindblazetech.com";
        $toCompletename = "Bilal";
        $params = [ 'from'    => 'support@mallshark.net',
        'to'      => $toEmail,
        'subject' => $subject,
        'html'    => "$html",
        ]; 
        
        $results = $this->mailgun->SendMailgunMail($params);   
        // echo 'Data: <pre>' .print_r($results,true). '</pre>'; die();
    } catch (\Throwable $th) {
         dd($th);
    }
    }

    public function articleListingAjax(Request $request){
        $slug = $request->input('slug');

        $postCategories = BlogCategory::where('category_slug' , $slug);
    }

    public function articleListing(){

        $posts = Post::paginate(10);
        $postCategories = BlogCategory::get();
        // echo 'Data: <pre>' .print_r($posts,true). '</pre>'; die();

        $color_code = ['#64EEFF','#0510FA','#D4F4F7','#F6F6F6'];

        $products = [
            [   'color_code' => 'lightBlue',
                'heading' => ' 5 Advantages of using credit card machines',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
                'img' => 'https://www.polytec.com.au/img/products/960-960/mercurio-grey.jpg'
            ],

            [
                'color_code' => 'blue',
                'heading' => ' 6 Crusial things to check before you select a payment',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
            ],

            [
                'color_code' => 'cyan',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
                
            ],

            [
                'color_code' => 'grey',
                'heading' => ' Choosing to be a consultant and a business coach',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
                
            ],

            [
                'color_code' => 'lightGrey',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
                'img' => 'https://www.polytec.com.au/img/products/960-960/mercurio-grey.jpg'
            ],

            [   'color_code' => 'lightBlue',
                'heading' => ' 6 Crucial things to check before you select a payment',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.'
             ],

             [   'color_code' => 'lightBlue',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so ...'
             ],

             [
                'color_code' => 'blue',
                'heading' => ' 6 C0rusial things to check before you select a payment',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who .',
             ],

             [
                'color_code' => 'cyan',
                'heading' => '  Choosing to be a consultant and a business coach',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
                'img' => 'https://www.polytec.com.au/img/products/960-960/mercurio-grey.jpg'
             ],

             [
                'color_code' => 'lightGrey',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so beguiled.',
                'img' => 'https://www.polytec.com.au/img/products/960-960/mercurio-grey.jpg'
            ],

            [   'color_code' => 'lightBlue',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so ...'
            ],

            [   'color_code' => 'lightBlue',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so ...',
                'img' => 'https://www.polytec.com.au/img/products/960-960/mercurio-grey.jpg'
            ],

            [
                'color_code' => 'blue',
                'heading' => ' Become a seller',
                'paragraph' => 'Sometimes features require a short  descriptions, this can be detailed descriptions. on the other hand we denounce with right indigation start selling',
            ],

            [   'color_code' => 'cyan',
                'heading' => ' Three ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so ...',
                'img' => 'https://www.polytec.com.au/img/products/960-960/mercurio-grey.jpg'

            ],

            [   'color_code' => 'cyan',
                'heading' => ' T    hree ways to gain from the globalized Economy',
                'paragraph' => 'on the other hand we denounce with right indigation and dislike men who are so ...'
            ],
           
        ];

        return view('Article/article-listing', ['posts' => $posts, 'postCategories' => $postCategories, 'products' => $products]);
    }

    public function individualArticle(){ 

        return view('Article/individual-article');
    }
    public function sellOnMallshark(){ 

        return view('Store/selling');
    }
    public function compainLanding(){ 
        $compaign = Campaigns::where('slug', 'picvendor')->first();
        return view('compain/compain-landing', ["compaign" => $compaign]);
    }
    public function campaings(){ 
        $userId = getCurrentUserId();
        $store = getStoreInfoByUserid($userId);
       
        $campaignStoreData = CampaignStore::getByStoreId($store->store_id);
        $totalActiveProducts = getTotalActiveProductsByStore($store->store_id);  
        return view('compain/campaigns', ["camaignStoreData" => $campaignStoreData, 'store' => $store, 'totalActiveProducts' => $totalActiveProducts]);
    }
    public function siteMap(){
        $sitemap = Sitemap::create()
        ->add(Url::create('/'))
        ->add(Url::create('/products'));
        
        //search listing page
        $sitemap->add(Url::create("/search")
        ->setPriority(1)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        //deals page
        $sitemap->add(Url::create("/welcome-deal")
        ->setPriority(0.9)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        //Best Sellers
        $sitemap->add(Url::create("/best-sellers")
        ->setPriority(0.9)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        //Become a seller
        $sitemap->add(Url::create("/sellonmallshark")
        ->setPriority(1)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
    // Dynamically adding URLs from the database
    $products = \App\Models\Product::where('status','1')->get();
    foreach ($products as $product) {
        $sitemap->add(Url::create("/product/{$product->slug}")
            ->setLastModificationDate($product->updated_at)
            ->setPriority(0.9)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
    }

    $categories = getAllCategories();
    foreach ($categories as $category) {
       
        if(!empty($category['cat_info'])){
            // if($category['cat_info']['title'] == 'uncategorized'){
            //     continue;
            // }
            $sitemap->add(Url::create("/{$category['cat_info']['slug']}")
            ->setLastModificationDate(new DateTime($category['cat_info']['updated_at']))
            ->setPriority(0.8)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        }
        if(!empty($category['childs'])){
            foreach($category['childs'] as $child){
                // if($child['title'] == 'uncategorized'){
                //     continue;
                // }
                $sitemap->add(Url::create("/{$category['cat_info']['slug']}/{$child['slug']}")
                ->setLastModificationDate(new DateTime($child['updated_at']))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
            }
            
        }
        

    }

    // Store the sitemap to the file
    $filePath = public_path('sitemap.xml');
    $sitemap->writeToFile($filePath);

    // Read the sitemap content
   

    // Prepare the XSL stylesheet line
    
    
    // Overwrite the file with the updated content
    // Ensure the XML declaration is first, followed by the XSL stylesheet
    
    
    // Write the corrected content back to the file
    
    // Return the sitemap response
    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
    }
}
