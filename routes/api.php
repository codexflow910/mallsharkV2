<?php

use Illuminate\Http\Request;
use App\Http\Controllers\UspsT;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\BuyerController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\StoreController;
use Packages\Store\Http\Controllers\V2\StoreController as StoreControllerV2;
use App\Http\Controllers\API\OrdersController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\StripeController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\EasyPostController;
use App\Http\Controllers\API\ElasticSearchController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\ShopifyController;
use App\Http\Controllers\DiscountController;
use Google\Service\AndroidEnterprise\StoreCluster;
use App\Models\Product;
use App\Http\Controllers\ZendeskController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SlidesController;
use Google\Service\MyBusinessAccountManagement\Admin;
use Packages\Admin\Http\Controllers\V2\AdminController as AdminControllerV2;
/*
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/signup', [AuthController::class, 'register']);
Route::post('/signup', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'loginUser']);
Route::post('/login', [AuthController::class, 'loginUser']);
Route::post('/seller_login', [AuthController::class, 'loginSellerUser']);
Route::post('register-guest-user',[AuthController::class,'registerGuest']);
Route::get('/email-exist', [AuthController::class, 'validateEmail']);
Route::get('/forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
Route::get('/change-password', [AuthController::class, 'changePasswordKey']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::get('/deleteuser', [AuthController::class, 'deleteUserAndCleanup']);
Route::post('/deleteuser', [AuthController::class, 'deleteUserAndCleanup']);
Route::post('/register-ajax', [AuthController::class, 'register']);
Route::get('/register-ajax', [AuthController::class, 'register']);
Route::get('/validate-email', [AuthController::class, 'validateUser']);
Route::post('/request-account-deletion', [AuthController::class, 'requestDeletion']);
// Login with google & facebook
Route::post('/google-login', [AuthController::class, 'googleLogin']);
Route::post('/facebook-login', [AuthController::class, 'facebookLogin']);
// Search results
Route::get('/q',[SearchController::class, 'getSearchResults']);
Route::get('/store-home-filter',[SearchController::class, 'storeHome']);
Route::get('sorting-home',[HomeController::class,'sortingHomeProducts']);
Route::get('/banners', [HomeController::class, 'getHomeBanners']);
Route::get('/home', [HomeController::class, 'index']);
Route::get('/home/loadmore', [HomeController::class, 'loadMoreProducts']);
Route::get('/categories', [HomeController::class, 'getHomeCategories']);
Route::get('/product/{slug}', [ProductController::class, 'ProductDetail']);
Route::get('/brands', [StoreController::class, 'showStores']);
// Route::prefix(('/v1'))->group(function () {
Route::post('/cart', [CartController::class, 'index']);
Route::post('cart/add/', [CartController::class, 'addToCart']);
Route::post('check-quantity',[CartController::class,'checkQuantity']);
Route::post('cart/increase-quantity/', [CartController::class, 'cartProductIncreaseQuantity']);
Route::post('cart/decrease-quantity/', [CartController::class, 'cartProductDecreaseQuantity']);
Route::post('cart/delete', [CartController::class, 'delCartProducts']);
Route::post('cart/show_hide', [CartController::class, 'checkedToogleProductsToCart']);
Route::post('/checkout', [CheckoutController::class,'getCheckout']);
// });

Route::post('/shipping_post_api', [EasyPostController::class, 'getRates', 'as' => 'getRates']);
Route::post('/calculate_service_change',[EasypostController::class, 'createShipmentOnChange']);


Route::get('/getstates/{id}', [HomeController::class,'getStatsById']);
Route::get('/allstates', [HomeController::class,'getAllStats']);
Route::get('/getcountry/{id}', [HomeController::class,'getCountryById']);
Route::get('/getallcountries', [HomeController::class,'getAllCountries']);
Route::get('/states/{countryId}', [HomeController::class,'getStatesByCountryId']);
// Test route

Route::post('/testNotifBuyer', [StripeController::class, 'testNotifBuyer']);
Route::post('/testNotifSeller', [StripeController::class, 'testNotifSeller']);
    // Place Order
// webhhok stripe
Route::post('/paymentRecevied', [StripeController::class, 'paymentReceived']);

Route::post('/getOrderStatus', [StripeController::class, 'getOrderStatus']);
Route::post('/paymentintent', [StripeController::class, 'paymentIntent']);
Route::post('/confirm-payment-intent', [StripeController::class, 'confirmPaymentIntent']);
Route::post('/placeorder', [StripeController::class, 'placeOrder']);
    // product ask question 
Route::post('/ask-question', [ProductController::class,'askQuestion']);
Route::get('popular-tags',[SearchController::class,'getPopularTags']);
Route::post('/esearch-keyword', [ElasticSearchController::class, 'keywordsearch']);
Route::post('product/get-variations-info', [ProductController::class, 'getVariationsInfo']);
Route::post('/shipping_post', [EasyPostController::class, 'createShipment']);
Route::post('forgot-password-otp',[AuthController::class,'forgotPasswordVerify']);
Route::get('resend-forgot-password-otp',[AuthController::class,'resendForgotPasswordOtp']);
Route::get('reviews/sorting' ,[ProductController::class,'getSortedReviews']);
Route::post('email-verification',[AuthController::class,'emailVerification']);
Route::get('store-home',[StoreController::class,'storeHome']);
Route::get('store-home-filter/{id}',[StoreController::class,'storeHomeFilter']);
Route::get('order-confirmation', [CheckoutController::class, 'OrderConfirmation'])->name('checkout.success');
Route::middleware('auth:api')->group(function () {
    // User Routes
    Route::get('/search-tags',[SearchController::class, 'getSearchTags']);
    Route::get('/store-search-tag',[SearchController::class, 'getSearchResults']);
    // Route::post('/shipping_post', [EasyPostController::class, 'createShipment']);
    Route::get('/check-unfulfil-orders',[AuthController::class,'checkUnfulfilOrder']);
    Route::get('/logout', [AuthController::class, 'logout']);

    //Sales Reports
    Route::get('store-reports/', [ReportController::class, 'getReports']);
    Route::get('store-reports-by-category/', [ReportController::class, 'getReportsByCategory']);
    Route::get('get-store-categories/', [ReportController::class, 'getCategoryList']);
    Route::get('/sales-by-product', [ReportController::class, 'getSalesByProduct']);
    Route::get('/get-store-products', [ReportController::class, 'getProducts']);
    Route::get('/stock-reports', [ReportController::class, 'getStockReport']);
    Route::get('/loadmore-stock-reports', [ReportController::class, 'loadMoreStock']);

    //discounts
    // Route::get('/discounts', [DiscountController::class, 'index']);
    // Route::post('/discounts', [DiscountController::class, 'save'])->name('saveDiscountForm');
    // Route::put('/discounts/{id}', [DiscountController::class, 'update'])->name('discounts.update');
    // Route::get('/product-listing-discounts', [DiscountController::class, 'fetchProductsWithVariation']);
    // Route::get('/subCategory-listing-discounts', [DiscountController::class, 'getCategoryListForDiscounts']);
    // Route::get('/discount-details', [DiscountController::class, 'discountDetails'])->name('discount-details');
    // Route::post('/update-status/{id}', [DiscountController::class, 'updateStatus'])->name('discounts.updateStatus');
    Route::post('/confirm-update-status/{id}', [DiscountController::class, 'confirmUpdateStatus']);
    // Route::post('/discount/{id}', [DiscountController::class, 'archive'])->name('discounts.archive');
    // Route::post('/unarchive-discount/{id}', [DiscountController::class, 'unArchive'])->name('discounts.unarchive');
    // Route::get('/discounts-list', [DiscountController::class, 'discountList'])->name('discount.list');
    Route::get('/archive-discounts', [DiscountController::class, 'getArchiveDiscounts']);

    
    Route::post('/checknotification', [AuthController::class, 'userNotificationChangeStatus']);
    Route::post('/readnotification', [AuthController::class, 'markReadNotification']);
    Route::post('/delete-notification', [AuthController::class,'deleteNotification']);
    Route::get('all-notifications', [AuthController::class, 'userAllNotifications']);
    Route::get('/seller-all-notifications', [AuthController::class, 'SellerAllNotifications']);
    Route::get('/count-unread-notification', [AuthController::class, 'unreadCountNotification']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::get('/user-profile-id', [AuthController::class, 'userProfileById']);
    Route::post('/user-profile-id', [AuthController::class, 'userProfileById']);
    Route::get('/unit-user-profile', [AuthController::class, 'userProfileById']);
    Route::post('/unit-user-profile', [AuthController::class, 'userProfileById']);
    Route::get('get-user', [AuthController::class, 'userInfo']);
    
    Route::post('contact-us',[StoreController::class,'contactSupportApi']);
    Route::get('resend-otp-email',[AuthController::class,'resendOtp']);
    // Buyer Routes
    Route::get('/user-dashboard', [BuyerController::class, 'index']);
    Route::post('/update-user-profile', [BuyerController::class, 'updateUserProfile']);
    Route::post('/update-shipping-address', [BuyerController::class, 'updateShippingAddress']);
    Route::get('/user-info', [BuyerController::class, 'userInfo']);
    Route::post('/change-buyer-password', [BuyerController::class, 'buyerPasswordChange']);
    Route::get('/user-avatar', [BuyerController::class, 'uploadUserImg']);
    Route::post('/user-avatar', [BuyerController::class, 'uploadUserImg']);
    Route::get('/buyer/state', [BuyerController::class, 'getStatesofCountry']);
    Route::post('/buyer/state', [BuyerController::class, 'getStatesofCountry']);
    Route::get('/buyer-email-exist', [BuyerController::class, 'validateEmail']);
    Route::get('/reviews', [BuyerController::class, 'reviews']);
    Route::post('add-review',[BuyerController::class,'add_review']);
    Route::get('/kingdom', [BuyerController::class, 'userKingdom']);
    Route::get('/user/change-status', [BuyerController::class, 'changeAlertsStatus']);
    Route::post('/user/change-status', [BuyerController::class, 'changeAlertsStatus']);
    Route::get('/buyer/intrest', [BuyerController::class, 'updateUserIntrest']);
    Route::post('/buyer/intrest', [BuyerController::class, 'updateUserIntrest']);
    Route::get('/unit-user-info/', [BuyerController::class, 'userProfileById']);
    Route::post('/unit-user-info/', [BuyerController::class, 'userProfileById']);
    Route::get('/unit-buyer-orders', [BuyerController::class, 'userOrders']);
    Route::get('/buyer-orders', [BuyerController::class, 'userOrdersNew']);
    Route::post('cancel_buyer_order',[BuyerController::class,'cancel_order_api']);
    
   // Cart
   
    //Store Routes
    
    Route::post('/store/create', [StoreController::class, 'addStore']);
    Route::post('/store/address', [StoreController::class, 'addStoreAddress']);
    Route::get('/store/available', [StoreController::class, 'checkShopAvailablity']);
    Route::post('/store-logo', [StoreController::class, 'uploadBrandLogo']); // again
    Route::get('/chat', [StoreController::class, 'chat']);
    Route::post('seller-account-setting', [StoreController::class, 'updateAccountSetting']);
    Route::post('seller-account-notification',[StoreController::class,'seller_account_notification']);
    //  store status
    Route::post('/store-status/change', [StoreController::class, 'StoreStatusChange']);
    Route::post('store-about',[StoreController::class,'updateStoreAbout']);
    Route::get('store-about',[StoreController::class,'getStoreAbout']);
    //Bulk Product editing
    // Route::post('/bulk-update-products',[StoreController::class,'bulkUpdateProducts'])->name('bulk-update-products');

// middleware
// Route::group(['middleware' => ['StoreAccess']], function () {
    Route::get('/store-dashboard', [StoreController::class, 'index']);
    Route::get('/store/edit', [StoreController::class, 'editStore']);
    Route::post('/store/update', [StoreController::class, 'updateStore']);
    Route::post('/store/edit/address', [StoreController::class, 'updateEditStoreAddress']);
    //remaining
    // Route::get('brand-orders/', [OrdersController::class, 'get_brand_orders']);
    Route::post('brand-orders/', [OrdersController::class, 'updateOrderStatus']);
    Route::post('dispute_order/', [OrdersController::class, 'dispute_order']);
    Route::get('dispute_order_cron', [OrdersController::class, 'dispute_order_cron']);
    //store template managment
    Route::get('/store/templates', [StoreController::class, 'storeTemplates']);
    Route::post('/store/templates', [StoreController::class, 'storeTemplates']);
    
    Route::get('/stripe-refund', [StripeController::class, 'refundBack']);
    // Route::get('/stripe-transfer', [StripeController::class, 'transfer']);
    // Route::get('/stripe-create-product', [StripeController::class, 'createStripeProduct']);
    // Route::get('/stripe-create-plan', [StripeController::class, 'createStripePlan']);
    // Route::get('/stripe-create-customer-view', [StripeController::class, 'createCustomerView']);
    // Route::post('/stripe-create-customer', [StripeController::class, 'createCustomer']);
    // Route::get('/stripe-create-subscription', [StripeController::class, 'subscribeCustomerPlan']);
    // Route::post('/stripe-subscripe-plan', [StripeController::class, 'subscribePlanToCustomer']);
    // Route::get('/stripe-dashboard', [StripeController::class, 'stripeDashboard']);
    // Route::get('/purchase-credits/', [CreditController::class, 'purchaseCredits']);
    // Route::get('/purchase-store-user-credits/{id}', [CreditController::class, 'purchaseStoreUserCredits']);
    // Route::get('/product-credits/{id}', [CreditController::class, 'index']);
    // Route::get('/buy-credits/{id}', [CreditController::class, 'buyCredits']);
    Route::get('/store/reports', [StoreController::class, 'storeReports']);

    // product module route
    Route::get('/importshopify', [ShopifyController::class ,'shopify']);
    Route::get('/importallproducts', [ShopifyController::class ,'importallproducts']);
    Route::get('/shopify', [ShopifyController::class ,'index']);
    Route::post('/connectShopify', [ShopifyController::class ,'connectShopify']);
    Route::get('/disconnectShopify', [ShopifyController::class ,'disconnectShopify']);
    Route::post('/request-upload-csv', [ShopifyController::class ,'requestuploadcsv'])->name('/request-upload-csv');
    Route::post('/saveImportedProducts', [ShopifyController::class ,'saveImportedProducts']);
    Route::get('products', [ProductController::class, 'getPaginateProducts']);
    Route::get('products-no-paginate', [ProductController::class, 'getProducts']);
    Route::post('favourite-add', [ProductController::class, 'addtoFavourite']);
    Route::get('get-favourite-products',[ProductController::class,'getBuyerFavouriteProducts']);
    Route::post('delete-favourite',[ProductController::class,'deleteFavouriteProduct']);
    Route::post('/favouritestore/add', [StoreController::class, 'favouriteStore']);
    Route::get('/product-search', [ProductController::class, 'searchProductTitle']);
    Route::get('product-status-change',[ProductController::class, 'productStatusChange']);
    Route::get('product-delete',[ProductController::class, 'deleteProduct']);
    Route::get('/testShopify', [ShopifyController::class ,'testSingleProduct']);
    Route::get('/add-product', [ProductController::class ,'addProduct']);
    Route::get('/img', [ProductController::class ,'img']);
    Route::post('/add-product', [ProductController::class ,'addProduct']);
    Route::post('/upload-product-image', [ProductController::class ,'uploadProductImageRequest']);
    Route::post('/uploadImage', [ProductController::class ,'uploadProductCropImage']);
    Route::post('/childCategory', [ProductController::class ,'getChildCategory']);
    Route::post('/delete-product-image', [ProductController::class ,'deleteProductImage']);
    Route::get('/store-products', [StoreController::class, 'storeProducts']);
    Route::get('/fetchPackages', [StoreController::class, 'fetchPackages']);
    Route::post('/make-feature-product', [StoreController::class, 'makeFeatureProduct']);
    Route::get('get-categories',[ProductController::class,'mainCategory']);
    Route::get('get-shipping-option',[ProductController::class,'getShippingOptions']);
    Route::post('save-update-shipping-option',[ProductController::class,'updateShippingProfile']);
    Route::get('delete-shipping-option',[ProductController::class,'deleteShippingProfile']);
    Route::get('/seller-product/{slug}', [ProductController::class, 'SellerProductDetail']);
    // Route::post('/getrates',[EasypostController::class, 'getRateWhenProductAdd']);
    Route::get('love-products',[ProductController::class,'getLoveProducts']);
    

    Route::get('/store/oncampaign', [StoreController::class, 'getCampaignProdutsByStore']);
    Route::get('/store/next-campaign', [StoreController::class, 'getflashProduct']);
    Route::get('/change-status/{id}/{status}', [StoreController::class, 'ChangeStatus']);
    Route::post('/variation', [ProductController::class ,'addProductVariation']);
    Route::post('/variation/add', [ProductController::class ,'productVarient']);
    Route::get('/edit-product/{id}', [ProductController::class ,'editProduct']);
    Route::get('/editproductfaqs/{id}', [ProductController::class ,'editProductFaqs']);
    Route::post('/editproductAction', [ProductController::class ,'productUpdate']);
    Route::put('/updateproductfaq/{id}', [ProductController::class, 'updateProductFaqs']);
    Route::get('/editfaqs/{id}', [ProductController::class, 'vendoreditFaqs']);
    Route::post('/deleteproductfaq', [ProductController::class, 'deleteFaqs']);
    // Route::post('/uploadImage', [ProductController::class ,'uploadProductCropImage']);
    Route::get('/store/payments', [StoreController::class, 'payments']);
    Route::get('/store/paidby', [StoreController::class, 'getPaidAddress']);
    Route::get('/store/bankdetail', [StripeController::class, 'storebankdetail']);
    Route::post('/store/bankdetail', [StripeController::class, 'storebankdetail']);
    Route::post('/store/paidby', [StoreController::class, 'getPaidAddress']);
    Route::get('/store-products-delete/{id}', [StoreController::class, 'deleteProduct']);
    Route::post('/product-variation/show', [StoreController::class, 'getvariationsOfProduct']);
    Route::post('/check-service', [ProductController::class ,'checkService']);

    //store orders
    Route::get('brand-orders/', [OrdersController::class, 'getBrandOrdersPaginate']);
    Route::get('brand-orders-no-paginate/', [OrdersController::class, 'getBrandOrders']);
    Route::get('brand-order-details/{id}', [OrdersController::class, 'brandOrderdetails']);
    Route::get('store-orders',[OrdersController::class, 'getStoreOrders']);
    Route::get('search-orders',[OrdersController::class,'searchOrders']);
    Route::post('/v1/accept_refund_stripe',[OrdersController::class,'return_refund_stripe']);
    Route::post('/v1/reject_refund',[OrdersController::class,'rejectOrderStoreOwner']);
    Route::get('order-pdf/{orderId}', [OrdersController::class, 'getOrderPdf'])->name('order.pdf');

    //video upload
    Route::post('/videothumb', [App\Http\Controllers\ProductController::class ,'loadVideoThumb']);

    Route::post('/v1/transferAmount', [StripeController::class, 'activateStipe']);
    Route::get('/auth/callback', [StripeController::class, 'handleCallback']);
    Route::get('/v1/editstripeuser', [StripeController::class, 'editStripeAccount']);
    Route::get('/account', [StripeController::class, 'transferAmount']);
    Route::post('/v2/getstripeloginlink', [StripeController::class, 'createLoginLinkStripeAccount']);
    Route::post('/v2/onloadstripeconnect', [StripeController::class, 'onLoadConnectStripeAccount']);
    // icon select route
    Route::get('/select-store-icon', [StoreController::class, 'selectStoreIcon']);
    Route::post('/select-store-icon', [StoreController::class, 'selectStoreIcon']);

     Route::post('/icon/search', [StoreController::class, 'selectStoreIcon']);

    Route::post('/icon/search', [StoreController::class, 'SearchIcons']);
    Route::get('/icon/search-complete', [StoreController::class, 'SearchIconsWithAutoComplete']);
    // zend desk routes

    Route::get('/store/tickets', [ZendeskController::class, 'getStoreOwnerTickets']);
    Route::Post('/store/ticket/create', [ZendeskController::class, 'createTicket']);
    Route::get('/store/ticket-detail/{id}', [ZendeskController::class, 'getStoreTicketById']);
    Route::Post('/store/update-ticket/{id}', [ZendeskController::class, 'updateStoreTicket']);
    Route::get('/store/mark-ticket/{id}/{status}', [ZendeskController::class, 'updateTicketStatus']);
    // Route::get('brand-orders/', [OrdersController::class, 'get_store_order'])->name('brand-orders');

    //campaign routes
    // Route::get('/run-camapign', [CampaignController::class, 'runCampaign']);
    // Route::get('/campaign-cron', [CampaignController::class, 'campaign']);
    // Route::get('/flashsale-cron', [CampaignController::class, 'flashSaleEnd']);
    Route::get('/store/campaign', [StoreController::class, 'campaignCollect']);
    // Route::post('/camp/flashsale', [CampaignController::class, 'updateFlashProduct']);

    // shipping routes
    Route::get('/addshippingOption', [ShippingController::class ,'createShippingOption']);

    Route::post('/shippingOptions', [ProductController::class ,'shippingOptionsPopup']);
    Route::post('/saveshipping', [ProductController::class ,'saveShippingProfile']);
    Route::post('/updateshipping', [ProductController::class ,'updateShippingProfile']);
    //flash sale on
    // Route::post('/flashsale/on', [CampaignController::class, 'flasSaleOn']);
//   });
    Route::get('store-active', [StoreController::class, 'storeActivates']);
    //store routes
    Route::get('/unit-store-dashboard/{id}', [StoreController::class, 'index']);
    Route::get('/unit-store-products/{id}', [StoreController::class, 'storeProducts']);
    Route::get('/unit-store-campaign/{id}', [StoreController::class, 'campaignCollect']);
    Route::get('/unit-next-campaign/{id}', [StoreController::class, 'getflashProduct']);
    Route::get('/unit-oncampaign/{id}', [StoreController::class, 'getCampaignProdutsByStore']);
    Route::get('/unit-sale-reports/{id}', [StoreController::class, 'storeReports']);

    //start selling
    Route::get('/unit-start-selling', [StoreController::class, 'sellerLanding']);
    // Route::post('add-img',function(Request $request){
    //     dd($request->all());
    // });

    // Buyer Api's
    Route::get('/buyer-orders', [BuyerController::class, 'buyerOrders']);
   
    // Route::post('shippig_post_rate',[BuyerController::class,'getrates']);
    
    // Route::put('/slides/{slideId}/update-link', [SlidesController::class, 'updateSlideLink']);
    // Route::post('/slides/create-set', [SlidesController::class, 'createNewSet'])->name('createNewSet');
    // Route::post('/slides/update-set/{setId}', [SlidesController::class, 'updateSlideSet'])->name('updateSlideSet');
    // Route::delete('/slides/delete-set/{setId}', [SlidesController::class, 'deleteSlideSet'])->name('deleteSlideSet');
});
Route::get('push-notification' ,[AuthController::class, 'push_notification']);
Route::post('shippig_post_rate',[BuyerController::class,'getrates']);
Route::post('/getrates',[EasypostController::class, 'getRateWhenProductAddApi']);
Route::post('/getrate_usps_service',[UspsT::class, 'getShippingRateUspsProductCreateApi']);
Route::get('/youtube-videos', [AdminController::class, 'fetchYoutubeVideos']);
Route::post('/sync-youtube-videos', [AdminController::class, 'syncYoutubeVideos']);
Route::post('/product-videos-toggle', [AdminController::class, 'toggleProductVideo']);
Route::get('/product-videos/{productId}/assigned', [AdminController::class, 'getAssignedVideos']);
Route::get('/admin/orders/export',[AdminController::class, 'export'])->name('admin.orders.export');
Route::post('/products/{id}/mallshark-selected', [ProductController::class, 'toggleMallshark']);
Route::post('/stores/{id}/shopify-connected', [StoreController::class, 'toggleShopifyConnected']);
Route::post('/stores/{id}/enable/ai', [AdminControllerV2::class, 'toggleAiFeature']);
Route::post('/stores/reset-ai-tokens', [AdminControllerV2::class, 'resetAiToken']);
Route::get('/slides/sets', [SlidesController::class, 'getSlidesSets'])->name('getSlidesSets');
// Route::get('/slides/set/{set_id}', [SlidesController::class, 'getSlideSet'])->name('getSlideSet');
Route::get('/v2/slides/active-set', [SlidesController::class, 'getActiveSlideSet'])->name('getActiveSlideSet');
Route::get('/v3/slides/active-set', [SlidesController::class, 'getActiveSlideSet'])->name('getActiveSlideSet');
Route::post('/stores/{id}/wholesale-connected', [StoreControllerV2::class, 'toggleWholesale']);
Route::post('/stores/{id}/unique-product-selling', [StoreControllerV2::class, 'toggleUniqueProductSelling']);
