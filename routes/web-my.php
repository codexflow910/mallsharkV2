<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ElasticSearchController;
use App\Http\Controllers\SearchController; 
use App\Http\Controllers\StoreController;  
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Support\ZendeskController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\OrdersController;  
use App\Http\Controllers\ReviewsController; 
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\TransportersController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ZendeskController;
use App\Http\Controllers\FavouritesController;   
use App\Http\Controllers\WalletController;





Route::get('/', [HomeController::class, 'index']);
//});
Route::get('/terms-conditions', function () {
    return View('content-pages/terms');
});
Route::get('/admin/analytics2', [AnalyticsController::class, 'getAnalyticsData']);

Route::post('/getcampres', [CampaignController::class, 'getRecentCampaignsResults']);
Route::post('/register-ajax', [CampaignController::class, 'getRecentCampaignsResults']);
// get related products ajax
Route::post('/getrelated', [ProductController::class, 'getCategoryProducts']);
Route::post('/getchlidcats', [SearchController::class, 'getChildCategories']);
//user routes start
Route::get('/stores', [StoreController::class, 'showStores']);
Route::get('/register', [UserController::class, 'register']);
Route::get('/updateindex', [ElasticSearchController::class, 'update_index']);
Route::get('/deleteuser/{id}', [UserController::class, 'deleteUserAndCleanup']);
Route::post('/register', [UserController::class, 'register']);
Route::get('/login', [UserController::class, 'login']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);
Route::get('/email-exist', [UserController::class, 'validateEmail']);
Route::get('/forget-password', [UserController::class, 'forgetPassword']);
Route::post('/forget-password', [UserController::class, 'forgetPassword']);
Route::get('/change-password/{key}', [UserController::class, 'changePassword']);
Route::post('/change-password', [UserController::class, 'doChangepassword']);
Route::post('/login-ajax', [UserController::class, 'login_ajax']);
Route::get('/validate-email/{key}', [UserController::class, 'validateUser']);
// Elastic Search Main
Route::get('/esearch', [ElasticSearchController::class, 'searchMain']);
Route::get('/esearch-get',[ElasticSearchController::class, 'searchGet']);
Route::get('/esearch-map', [ElasticSearchController::class, 'esMApping']);
Route::get('/esearch-get', [ElasticSearchController::class, 'getDocument']);
Route::get('/esearch-search', [ElasticSearchController::class, 'searchDocument']);
Route::get('/esearch-update', [ElasticSearchController::class, 'updateDocument']);
Route::get('/esearch-delete', [ElasticSearchController::class, 'deleteDocument']);
Route::get('/esearch-delete-document', [ElasticSearchController::class, 'deleteIndex']);
Route::get('/esearch-create-index', [ElasticSearchController::class, 'createIndex']);
Route::get('/esearch-updatemapping', [ElasticSearchController::class, 'updateMapping']);
Route::post('/esearch-keyword', [ElasticSearchController::class, 'keywordsearch']);
// Elastic Search users
Route::get('/esearch-map-user', [ElasticSearchController::class, 'userMapping']);
Route::get('/esearch-create-index-user', [ElasticSearchController::class, 'userCreateIndex']);
Route::post('/esearch-keyword-user', [ElasticSearchController::class, 'keywordsearchuser']);
Route::get('/esearch-delete-document-user', [ElasticSearchController::class, 'deleteIndexuser']);

// Elastic Search users ends
Route::get('/stripe-transfer', [StripeController::class, 'transfer']);
// Search results
Route::get('/search-result',[SearchController::class, 'getSearchResults']);
// store home
Route::get('/store-home/{storeslug}/',  [AdminController::class, 'storehome']);
Route::get('/store-marketing/', [StoreController::class, 'storeMArketing']);
Route::post('/stripe-response',[StripeController::class, 'stripeResponse']);
//shipping_address
Route::get('/shipping-address', [StripeController::class, 'index']);
Route::post('/shipping-address',[StripeController::class, 'index']);
Route::post('/get-states', [StripeController::class, 'get_states_by_country_code']);
Route::get('/addshiping', [ShippingController::class, 'addStoreShippingMethod']);
Route::get('/about-us', [ContentController::class, 'aboutUs']);
Route::get('/content-page/{pagename}', [ContentController::class, 'contentPage']);
Route::get('/contact-us', [ContentController::class, 'contactUs']);
Route::get('/faqs', [ContentController::class, 'contentFaq']);
Route::get('/privacy-policy', [ContentController::class, 'PrivacyPolicy']);
Route::get('/terms-and-conditions', [ContentController::class, 'termsAndConditions']);
Route::get('order-pdf/{orderId}/', [OrdersController::class, 'getOrderPdf']);

// usps
Route::get('/usps-domesticrates/', 'UspsT@getDpmesticRates');
Route::post('/get-states', [OrdersController::class, 'get_states_by_country_code']);

//product detail
Route::post('product/get-variations-info',[ProductController::class, 'getVariationsInfo']);
Route::get('/product-detail/{slug}', [ProductController::class, 'listingDetail']);
Route::post('/shipping/calculate', [ProductController::class, 'calculateShippingByprodcutId']);
//store
Route::get('/store/create', [StoreController::class, 'addStore']);
Route::post('/store/create', [StoreController::class, 'addStore']);
Route::get('/add-review', [ReviewsController::class, 'add_review']);
Route::post('/add-review', [ReviewsController::class, 'add_review']);
Route::get('/abc', [StoreController::class, 'abc']);
Route::get('/sales', [CampaignController::class, 'checkSales']);
Route::get('/sms', [UserController::class, 'sms']);
// check notification
Route::post('/user/check', [UserController::class, 'checkNotification']);
/*
 * Log in Access
 */

Route::group(['middleware' => ['loggedin']], function () {
    /// buyer
    Route::get('/user-dashboard', [BuyerController::class, 'index']);
    Route::get('/user-account', [BuyerController::class, 'userAccount']);
    Route::post('/user-account', [BuyerController::class, 'userAccount']);
    Route::get('/user-info', [BuyerController::class, 'userInfo']);
    Route::post('/user-avatar', [BuyerController::class, 'uploadUserImg']);
    Route::post('/user-avatar-temp', [BuyerController::class, 'uploadTempImg']);
    Route::post('/buyer/state', [BuyerController::class, 'getStatesofCountry']);
    Route::get('/buyer-email-exist', [BuyerController::class, 'validateEmail']);
    Route::get('/edit-shipping-address', [BuyerController::class, 'editShippingAddress']);
    Route::post('/edit-shipping-address', [BuyerController::class, 'editShippingAddress']);
    Route::get('/user-profile', [UserController::class, 'userProfile']);
    Route::get('/user-profile/{id}', [UserController::class, 'userProfile']);
    //store
    Route::get('/store/available', [StoreController::class, 'checkShopAvailablity']);
    Route::post('/store-logo', [StoreController::class, 'uploadBrandLogo']);
    //cart 
    Route::get('cart/', [CartController::class, 'index']);
    Route::post('cart/add/', [CartController::class, 'addToCart']);
    Route::post('cart/counter/', [CartController::class, 'cartCounter']);
    Route::post('cart/delete', [CartController::class, 'delCartProducts']);
    Route::post('cart/calculate', [CartController::class, 'calculateCart']);
    Route::post('cart/updatebasket', [CartController::class, 'getCartdetail']);
    Route::post('cart/update', [CartController::class, 'updateCart']);
    Route::post('cart/addnote', [CartController::class, 'addNote']);
    Route::post('cart/deletebasket', [CartController::class, 'delCartBasket']);
    //orders
    Route::get('create-order/',[OrdersController::class, 'create_order'] );
    Route::post('update-tracking/', [CartController::class, 'updateTrackingId']);

    Route::get('orders-detail/', [OrdersController ::class, 'ordersDtail']);
    Route::post('save-order', [OrdersController ::class, 'SaveOrder']);
    Route::get('/buyer-orders', [BuyerController::class, 'userOrders']);
    //transportes
    Route::post('/add-transporter-cities',[TransportersController::class, 'addTransporterCities']);
    Route::post('/get-transporter-cities',[TransportersController::class, 'getStatecCitieslocations']);
    Route::post('/edit-customer',[TransportersController::class, 'editCustomer']);
    Route::post('/check-products',[TransportersController::class, 'categoryProducts']);
    Route::post('/getstatecities',[TransportersController::class, 'getStatecCities']);
    //check out
    Route::get('/checkout', [CheckoutController::class, 'getCheckout']);
    Route::post('/checkout',[CheckoutController::class, 'saveShippingAddress']);

    // zend desk routes
    Route::get('/user/tickets', [ZendeskController::class, 'getUserTickets']);
    Route::Post('/user/ticket/create', [ZendeskController::class, 'createUserTicket']);
    Route::get('/user/ticket-detail/{id}', [ZendeskController::class, 'getUserTicketById']);
    Route::Post('/user/update-ticket/{id}', [ZendeskController::class, 'updateUserTicket']);
    Route::get('/user/mark-ticket/{id}/{status}', [ZendeskController::class, 'updateTicketStatus']);

    // favorites routes
    Route::get('/buyer-favorites',[FavouritesController::class, 'index']);
    Route::post('/favourite/add', [FavouritesController::class, 'addtoFavourite']);

    /*
     * shipping_address
     */
    Route::get('/shipping-address',[FavouritesController::class, 'index']);
    Route::post('/shipping-address', [FavouritesController::class, 'index']);
    Route::get('/addshiping',[FavouritesController::class, 'addStoreShippingMethod']);

    //cart module route
    Route::get('cart/', [CartController::class, 'index']);
    Route::post('cart/add/',[CartController::class, 'addToCart']);
    Route::post('cart/counter/', [CartController::class, 'cartCounter']);
    //Route::get('cart/delete', 'CartController@delCartProducts');
    Route::post('cart/delete', [CartController::class, 'delCartProducts']);
    Route::post('cart/calculate', [CartController::class, 'calculateCart']);
    Route::post('cart/updatebasket', [CartController::class, 'getCartdetail']);
    Route::post('cart/update', [CartController::class, 'updateCart']);
    Route::post('cart/addnote', [CartController::class, 'addNote']);
    Route::post('cart/deletebasket', [CartController::class, 'delCartBasket']);

    //chat routes
    Route::get('/chat', [StoreController::class, 'chat']);
    Route::post('/messages/seen', [MessageController::class, 'makeSeen']);
    Route::get('chat/{id}', [MessageController::class, 'chatHistory'])
  //  Route::get('site-support/{id}/{user}', [MessageController::class, 'chatHistory']);
    //Route::post('ajax/message/send', [MessageController::class, 'ajaxSendMessage']);
    //Route::get('/admin/deactivate', [AdminController::class, 'deactivate']);
    // sellere landing
    Route::get('/sellonmallshark', [StoreController::class, 'sellerLanding']);
    // ask question
    Route::post('/ask-question', [ProductController::class, 'askQuestion']);
    // pledge support
    Route::post('product/pledge-support',[ProductController::class, 'pledgeSupport']);
    // add zit
    Route::post('/add-zit', [WalletController::class, 'creditWalletWithPurchaseZit']);
    // icon purchase/display
    Route::post('/icon-purchase', [UserController::class, 'iconPurchase']);
    Route::post('/icon-details', [UserController::class, 'iconDetails']);
    Route::get('/icon-details', [UserController::class, 'iconDetailsAjax']);

    //reviews
    Route::get('/reviews', [BuyerController::class, 'reviews']);
    //about
    Route::get('/user-about', [BuyerController::class, 'userAbout']);
    //kingdom
    Route::get('/kingdom', [BuyerController::class, 'userKingdom']);
    // user wallet
    Route::get('/wallet', [BuyerController::class, 'userWallet']);
    // notification read
    Route::post('/user/read', [UserController::class, 'markRead']);

    //view all notifications
    Route::get('/all-notifications', [UserController::class, 'userAllNotifications']);
    //sms and email alerts
    Route::post('/user/change-status', [BuyerController::class, 'changeAlertsStatus']);
    Route::post('/buyer/intrest', [BuyerController::class, 'updateUserIntrest']);
});
/*
 * Store Access
 */
Route::group(['middleware' => ['StoreAccess']], function () {
    Route::get('/store-dashboard', [StoreController::class, 'index']);
    Route::get('/store/edit', [StoreController::class, 'editStore']);
    Route::post('/store/edit', [StoreController::class, 'editStore']);
    Route::get('brand-orders/',[OrdersController::class, 'get_store_order']);
    //store template managment
    Route::get('/store/templates', [StoreController::class, 'storeTemplates']);
    Route::post('/store/templates', [StoreController::class, 'storeTemplates']);
    Route::get('/stripe-refund', [StripeController::class, 'refundBack']);
    Route::get('/stripe-transfer', [StripeController::class, 'transfer']);
    Route::get('/stripe-create-product',[StripeController::class, 'createStripeProduct']);
    Route::get('/stripe-create-plan', [StripeController::class, 'createStripePlan']);
    Route::get('/stripe-create-customer-view', [StripeController::class, 'createCustomerView']);
    Route::post('/stripe-create-customer',[StripeController::class, 'refundBack'] array('as' => 's-create-customer', 'uses' => 'StripeController@createCustomer'));
    Route::get('/stripe-create-subscription', [StripeController::class, 'subscribeCustomerPlan']);
    Route::post('/stripe-subscripe-plan', [StripeController::class, 'subscribePlanToCustomer']);
    Route::get('/stripe-dashboard',[StripeController::class, 'stripeDashboard']);
    Route::get('/purchase-credits/', [CreditController::class, 'stripeDashboard']);
    Route::get('/purchase-store-user-credits/{id}', [CreditController::class, 'purchaseStoreUserCredits']);
    Route::get('/product-credits/{id}', [CreditController::class, 'index']'CreditController@index');
    Route::get('/buy-credits/{id}', [CreditController::class, 'buyCredits']);
    Route::get('/store/reports', [StoreController::class, 'storeReports']);
    // product module route


    Route::get('/add-product',[ProductController::class, 'addProduct']);
    Route::get('/img', [ProductController::class, 'img']'ProductController@img');
    Route::post('/add-product', [ProductController::class, 'addProduct']);
    Route::post('/ProductImageRequest', [ProductController::class, 'uploadProductImageRequest']);
    Route::post('/uploadImage', [ProductController::class, 'uploadProductCropImage']);
    Route::post('/childCategory',[ProductController::class, 'getChildCategory']);
    Route::post('/deleteProductImage',[ProductController::class, 'deleteProductImage']);
    Route::get('/store-products', [StoreController::class, 'storeProducts']);
    Route::get('/product-search', [StoreController::class, 'getproductsWithAutoComplete']);
    Route::get('/store/oncampaign', [StoreController::class, 'getCampaignProdutsByStore']);
    Route::get('/store/next-campaign', [StoreController::class, 'getflashProduct']);
    Route::get('/change-status/{id}/{status}', [StoreController::class, 'ChangeStatus']);
    Route::post('/variation', [ProductController::class, 'addProductVariation']);
    Route::post('/variation/add', [ProductController::class, 'productVarient']);
    Route::get('/edit-product/{id}', [ProductController::class, 'editProduct']);
    Route::post('/editproductAction', [ProductController::class, 'editproductAction']);
    Route::post('/uploadImage', [ProductController::class, 'uploadProductCropImage']);
    Route::get('/store/payments', [StoreController::class, 'payments']);
    Route::get('/store-products-delete/{id}', [StoreController::class, 'deleteProduct']);
    Route::post('/product-variation/show', [StoreController::class, 'getvariationsOfProduct']);
    Route::post('/check-service', [ProductController::class, 'checkService']);
    //video upload
    Route::post('/videothumb', [ProductController::class, 'loadVideoThumb']);
    //  store status
    Route::post('/store-status/change', [StoreController::class, 'activateStore']);
    // icon select route
    Route::get('/select-store-icon', [StoreController::class, 'selectStoreIcon']);
    Route::post('/select-store-icon', [StoreController::class, 'selectStoreIcon']);

    // Route::post('/icon/search', 'StoreController@selectStoreIcon');

    Route::post('/icon/search', [StoreController::class, 'SearchIcons']);
    Route::get('/icon/search-complete', [StoreController::class, 'SearchIconsWithAutoComplete']);
    // zend desk routes

    Route::get('/store/tickets', [ZendeskController::class, 'getStoreOwnerTickets']);
    Route::Post('/store/ticket/create', [ZendeskController::class, 'createTicket']);
    Route::get('/store/ticket-detail/{id}', [ZendeskController::class, 'getStoreTicketById']);
    Route::Post('/store/update-ticket/{id}',[ZendeskController::class, 'updateStoreTicket'] );
    Route::get('/store/mark-ticket/{id}/{status}', [ZendeskController::class, 'updateTicketStatus']);
    Route::get('brand-orders/', [ProductController::class, 'get_store_order']);

    //campaign routes
    Route::get('/run-camapign', [CampaignController::class, 'runCampaign']);
    Route::get('/campaign-cron', [CampaignController::class, 'campaign']);
    Route::get('/flashsale-cron', [CampaignController::class, 'flashSaleEnd']);
    Route::get('/store/campaign', [StoreController::class, 'campaignCollect']);
    Route::post('/camp/flashsale', [CampaignController::class, 'updateFlashProduct']);
    // shipping routes
    Route::post('/shippingOptions', [ProductController::class, 'shippingOptionsPopup']);
    Route::post('/saveshipping', [ProductController::class, 'updateShippingProfile']);
    //flash sale on
    Route::post('/flashsale/on', [CampaignController::class, 'checkSales']);
});


//    Admin routes
Route::group(['middleware' => ['AdminAccess']], function () {
    Route::get('/admin/dashboard', 'Admin\AdminController@index');
    //user routes
    Route::get('/admin/allusers', [CampaignController::class, 'checkSales']);
    Route::post('/admin/assignrole', [CampaignController::class, 'checkSales']);
    Route::get('/admin/users-update/{id}/{status}',[CampaignController::class, 'checkSales']);
    Route::get('/admin/userdetail/{id}', [AdminController::class, 'checkSales']);
    Route::post('/admin/userdetail/{id}', [AdminController::class, 'checkSales']);
    // categories routes
    Route::get('/admin/categories', [AdminController::class, 'checkSales']);
    Route::post('/admin/addcategories', [AdminController::class, 'checkSales']);
    //    content pages
    Route::get('/admin/content-pages', [ContentController::class, 'checkSales']);
    Route::post('/admin/edit-page', [ContentController::class, 'checkSales']);
    Route::post('/admin/add-page', [ContentController::class, 'checkSales']);
    //    zend desk routes
    Route::post('/admin/addticket', [ZendeskController::class, 'checkSales');
    Route::get('/admin/alltickets', [ZendeskController::class, 'checkSales']);
    Route::get('/admin/agents', [ZendeskController::class, 'checkSales']);
    Route::get('/admin/ticket/create', [ZendeskController::class, 'checkSales']);
    Route::get('/admin/ticket-detail/{id}', [ZendeskController::class, 'checkSales']);
    Route::get('/admin/mark-ticket/{id}/{status}', [ZendeskController::class, 'checkSales']);
    Route::Post('/admin/update-ticket/{id}', [ZendeskController::class, 'checkSales']);
    Route::get('/admin/delete-ticket/{id}', [ZendeskController::class, 'checkSales']);
    Route::get('/admin/email-template/',[AdminController::class, 'emailTemplate'] );
    //filter routes
    Route::get('/filters', 'FiltersController@getfilters');
    Route::post('filters/update', 'FiltersController@updateFilters');
    Route::get('filters/{id}/{status}', 'FiltersController@statusFilters');
    Route::get('del-filters/{id}', 'FiltersController@deleteFilters');
    // pool stats
    Route::get('/admin/pools-stats', [PoolTController::class, 'getPoolStats']);
    //all store products
    Route::get('/admin/store-products',[AdminController::class, 'storeProducts']);

    Route::get('admin-change-status/{id}/{status}',[AdminController::class, 'changeProductStatus']);
    //false sale for home page 
    Route::get('/admin/flash-product/{id}',[AdminController::class, 'setFeatureProduct']);
    //product delete or reject
    Route::post('admin/product-status', [AdminController::class, 'ProductStatusUpdate']);
    Route::get('admin/slides',[AdminController::class, 'getDashboard']);
    Route::post('admin/add-slide',[AdminController::class, 'addslide']);

    Route::get('admin-change-status/{id}/{status}',[AdminController::class, 'changeProductStatus']);
    Route::get('/admin/product-detail/{slug}',[AdminController::class, 'productDetail']);
    //product delete or reject
    Route::post('admin/product-status',[AdminController::class, 'emailTemplate']);
    Route::get('admin/slides', [SlidesController::class, 'getDashboard']);
    Route::post('/admin/add-slide',[SlidesController::class, 'addSlide']);
    Route::post('/admin/edit-slide',[SlidesController::class, 'updateSlide']);
    Route::get('/admin/delete-slide/{slide_id}', [AdminController::class, 'deleteSlide']);
    Route::get('/admin/fsale-products', [AdminController::class, 'getFlashSaleProducts']);
    // admin sale report
    Route::get('admin/sale-report', [AdminController::class, 'saleReport']);
    //search admin store
    Route::get('/admin/sale-report-auto', [AdminController::class, 'getStoresAuto']);
    Route::post('/admin/stores', [AdminController::class, 'getStoresName']);
});
Route::post('admin/banner-upload', 'Admin\AdminController@addbanner');
Route::group(['middleware' => ['SupportAccess']], function () {
    //support routes
    Route::get('/support/dashboard',[SupportController::class, 'index']);
    Route::get('/support/allusers', [SupportController::class, 'allUsers']);
    Route::post('/support/assignrole', [SupportController::class, 'updateUserRole']);
    Route::get('/support/users-update/{id}/{status}', [SupportController::class, 'userChangeStatus']);
    Route::get('/support/userdetail/{id}', [SupportController::class, 'userInfo']);
    Route::post('/support/userdetail/{id}', [SupportController::class, 'userInfo']);
    Route::get('/support/tickets-list/', [ZendeskController::class, 'getStoresName']);
    //    zend desk routes
    Route::post('/support/addticket',[ZendeskController::class, 'createTicket']);
    Route::get('/support/alltickets', [ZendeskController::class, 'getAlltickets']);
    Route::get('/support/agents', [ZendeskController::class, 'getAllUsers']);
    Route::get('/support/ticket/create', [ZendeskController::class, 'createTicket']);
    Route::get('/support/ticket-detail/{id}', [ZendeskController::class, 'getTicketById']);
    Route::get('/support/mark-ticket/{id}/{status}',[ZendeskController::class, 'updateTicketStatus']);
    Route::Post('/support/update-ticket/{id}', [ZendeskController::class, 'updateTicket']);
    Route::get('/support/delete-ticket/{id}', [ZendeskController::class, 'deleteTicket']);
    //filters route
    Route::get('support/filters', [FiltersController::class, 'getfilters']);
    Route::post('support/filters/update', [FiltersController::class, 'updateFilters']);
    Route::get('support/filters/{id}/{status}', [FiltersController::class, 'statusFilters']);
    Route::get('support/del-filters/{id}', [FiltersController::class, 'deleteFilters']);
    Route::get('support-filters', [FiltersController::class, 'getfilters']);
});

// unit testing routes
Route::get('/unit-user-info/{id}', [BuyerController::class, 'userInfo']);
Route::get('/unit-user-account/{id}', [BuyerController::class, 'userAccount']);
Route::get('/unit-buyer-orders/{id}', [BuyerController::class, 'userOrders']);
Route::get('/unit-tickets', [ZendeskController::class, 'getUserTickets']);
Route::get('/unit-buyer-favorites/{id}', [FavouritesController::class, 'index']);
Route::get('/unit-wallet/{id}', [BuyerController::class, 'userWallet']);
Route::get('/unit-user-profile/{id}', [UserController::class, 'userProfile']);
Route::get('/unit-user-dashboard', [BuyerController::class, 'userWallet']);
//store routes
Route::get('/unit-store-dashboard/{id}', [StoreController::class, 'index']);
Route::get('/unit-store-products/{id}', [StoreController::class, 'storeProducts']);
Route::get('/unit-store-campaign/{id}', [StoreController::class, 'campaignCollect']);
Route::get('/unit-next-campaign/{id}', [StoreController::class, 'getflashProduct']);
Route::get('/unit-oncampaign/{id}', [StoreController::class, 'getCampaignProdutsByStore']);
Route::get('/unit-sale-reports/{id}', [StoreController::class, 'storeReports']);
Route::get('unit-brand-orders/{id}', 'OrdersController@get_store_order');
//start selling
Route::get('/unit-start-selling', [StoreController::class, 'sellerLanding']);
//cart
Route::get('unit-cart/{id}', [CartController::class, 'sellerLanding']);
Route::get('/unit-checkout/{id}', [CartController::class, 'sellerLanding']);
Route::get('/unit-filters', [CartController::class, 'sellerLanding']);
Route::get('/unit-categories', [AdminController::class, 'manageCategories']);
Route::get('/unit-slides', [SlidesController::class, 'getDashboard']);
Route::get('/unit-fsale-products', [AdminController::class, 'getFlashSaleProducts']);
Route::get('/unit-alltickets',[ZendeskController::class, 'getAlltickets'] );
 Route::get('/unit-content-pages', [ContentController::class, 'AdminPage']);
 Route::get('/unit-pools-stats', [PoolTController::class, 'getPoolStats']);
 Route::get('/unit-allusers', [AdminController::class, 'allUsers']);
  Route::get('unit-sale-report', [AdminController::class, 'saleReport']);