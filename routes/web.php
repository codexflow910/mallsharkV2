<?php

use App\Http\Controllers\UspsT;

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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\PoolTController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SlidesController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\MailgunController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ZendeskController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EasypostController;
use App\Http\Controllers\FeaturedController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BenchMarkController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\FavouritesController;
use App\Http\Controllers\ImageSizesController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use Packages\Admin\Http\Controllers\V2\AdminController  as PackagesAdminController;
use App\Http\Controllers\WoocommerceController;
use App\Http\Controllers\ElasticSearchController;
use App\Http\Controllers\PayPalPaymentController;
use App\Http\Controllers\FavouritesStoreController;
use App\Http\Controllers\NFTs\NftVoucherController;
use App\Http\Controllers\Support\SupportController;
use App\Http\Controllers\SuperadminReviewController;
use App\Http\Controllers\Admin\ZendeskController as AdminZendeskController;
use Packages\Distributors\Http\Controllers\V2\DistributorController as PackagesDistributorControllerV2;
use Packages\Order\Http\Controllers\V2\OrderController as OrderControllerV2;

Route::post('/check-apple-pay', [PaymentController::class, 'handleWebhook']);

Route::get('payment', function () {
    return view('payment');
})->name('payment');


Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
Route::get('/', [HomeController::class, 'index', 'as' => 'home']);
Route::get('/v2', [HomeController::class, 'v2Index', 'as' => 'home']);
Route::get('/new', [HomeController::class, 'newIndex', 'as' => 'newIndex']);
Route::get('/shipping', [EasypostController::class, 'home', 'as' => 'home']);
Route::get('/getRate', [EasypostController::class, 'getRatestemprary', 'as' => 'getRatestemprary']);
Route::post('/shipping_post', [EasypostController::class, 'getRates', 'as' => 'getRates']);

Route::get('/zanimals', [HomeController::class, 'zanimals', 'as' => 'zanimals']);

Route::get('/admin/analytics2', [AnalyticsController::class, 'getAnalyticsData']);

Route::post('/getcampres', [CampaignController::class, 'getRecentCampaignsResults']);
Route::post('/register-ajax', [UserController::class, 'register_ajax']);
// get related products ajax
Route::post('/getrelated', [ProductController::class, 'getCategoryProducts']);
Route::post('/getactive', [ProductController::class, 'getCategoryActiveProducts']);
Route::post('/getchlidcats', [SearchController::class, 'getChildCategories']);
//user routes start
Route::get('/brands', [StoreController::class, 'showStores']);
Route::get('/signup', [UserController::class, 'register']);
Route::get('/username-convert-slug', [UserController::class, 'userNameConvertSlug']);
Route::get('/updateindex', [ElasticSearchController::class, 'update_index']);
Route::get('/deleteuser/{id}', [UserController::class, 'deleteUserAndCleanup']);
Route::post('/signup', [UserController::class, 'register']);
Route::post('/resend-email', [UserController::class, 'ResendEmail']);
Route::get('/login', [UserController::class, 'login'])->name('login');
Route::get('/mailCheck', [MailgunController::class, 'sendMailgunEmail']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);
Route::get('/email-exist', [UserController::class, 'validateEmail']);
// home pape subscribe users
Route::post('/email-validate', [UserController::class, 'validateEmailSubscription']);
Route::get('/ubsubscribe/{id}', [UserController::class, 'UnsubscribeEmail']);
Route::post('/ubsubscribe/user', [UserController::class, 'UnsubscribeEmailUser']);
// Get Subscribed Users and import in BenchMark Email 
Route::get('/subscribed-user', [BenchMarkController::class, 'getEmailSubscribedUser']);
Route::get('/create-content-list/{name}', [BenchMarkController::class, 'createContentListBenchMark']);
Route::get('/getcontent-list-all', [BenchMarkController::class, 'getContentListAll']);
Route::get('/get-filtercontent-list', [BenchMarkController::class, 'getContentList']);
Route::get('/send-email-test/{email}', [BenchMarkController::class, 'testEmailSend']);
Route::get('/send-email/', [BenchMarkController::class, 'sendEmailToUsers']);
Route::get('/getemaildetail/{id}', [BenchMarkController::class, 'getEmailDetailById']);
Route::get('/delete-content-list/{id}', [BenchMarkController::class, 'deteleBenchMarkContentList']);
Route::get('/emailschedule/', [BenchMarkController::class, 'emailSchedule']);


Route::get('/forget-password', [UserController::class, 'forgetPassword']);
Route::post('/forget-password', [UserController::class, 'forgetPassword']);
Route::get('/change-password/{key}', [UserController::class, 'changePassword']);
Route::post('/change-password', [UserController::class, 'doChangepassword']);
Route::post('/login-ajax', [UserController::class, 'login_ajax']);
Route::get('/validate-email/{key}', [UserController::class, 'validateUser']);

Route::get('/esearch-delete-document', [ElasticSearchController::class, 'deleteIndex']);
Route::get('/esearch-create-index', [ElasticSearchController::class, 'createIndex']); //------------------------------create--------
Route::get('/esearch-map', [ElasticSearchController::class, 'esMApping']);

Route::get('/esearch-get', [ElasticSearchController::class, 'getDocument']);
Route::get('/esearch-search', [ElasticSearchController::class, 'searchDocument']);
Route::get('/esearch-update', [ElasticSearchController::class, 'updateDocument']);
Route::get('/esearch-delete', [ElasticSearchController::class, 'deleteDocument']);
//delete index
Route::get('/esearch-updatemapping', [ElasticSearchController::class, 'updateMapping']);
Route::post('/esearch-keyword', [ElasticSearchController::class, 'keywordsearch'])->name('esearch.keyword'); //---------------------keywordsearch-------------------------
Route::post('/esearch-keyword-store', [ElasticSearchController::class, 'keywordsearchStore'])->name('esearch-keyword-store');
// Elastic Search users
Route::get('/esearch-delete-document-user', [ElasticSearchController::class, 'deleteIndexuser']);
Route::get('/esearch-create-index-user', [ElasticSearchController::class, 'userCreateIndex']);
Route::get('/esearch-map-user', [ElasticSearchController::class, 'userMapping']);
Route::post('/esearch-keyword-user', [ElasticSearchController::class, 'keywordsearchuser']);

// Metamask payment order create
Route::post('/meta-mask', [StripeController::class, 'metamaskCreatOrder']);
Route::post('/meta-mask-store', [ProductController::class, 'metamaskFeatureProduct']);
// Elastic Search users ends
Route::get('/stripe-transfer', [StripeController::class, 'transfer']);
// Search results
Route::get('/product/{slug}', [ProductController::class, 'listingDetail'])->name('pro.detail');
Route::get('/search', [SearchController::class, 'getSearchResults'])->name('home.search'); //--------------------------------------------------------Q-----------------

// Easypost Routes
Route::get('/easy', [EasypostController::class, 'index']);

Route::get('/brand/{storeslug}/', [AdminController::class, 'storeHome', 'as' => 'storehome'])->name('storeHome');
Route::get('/store-marketing/', [StoreController::class, 'storeMArketing']);
Route::post('/stripe-response', [StripeController::class, 'stripeResponse']);
Route::get('/send', [StripeController::class, 'disperse'])->name('disperse');
Route::post('/apply-coupon', [DiscountController::class, 'applyCoupon']);

Route::post('/get-states', [ShippingController::class, 'get_states_by_country_code']);
Route::get('/addshiping', [ShippingController::class, 'addStoreShippingMethod']);
Route::get('/about-us', [ContentController::class, 'aboutUs']);
Route::get('/change-log', [ContentController::class, 'changeLog']);
Route::get('/features', [ContentController::class, 'features']);
Route::get('/content-page/{pagename}', [ContentController::class, 'contentPage']);
Route::get('/contact-us', [ContentController::class, 'contactUs']);
Route::post('/contact-us', [ContentController::class, 'contactStore'])->name('contact_us');
Route::get('/request-account-deletion', [ContentController::class, 'requestDeletion']);
Route::get('/generalfaq', [ContentController::class, 'contentFaq']);
Route::get('/sellerfaq', [ContentController::class, 'sellerFaq']);
Route::get('/buyerfaq', [ContentController::class, 'buyerFaq']);
Route::get('/privacy-policy', [ContentController::class, 'PrivacyPolicy']);
Route::get('/terms', [ContentController::class, 'termsAndConditions']);
Route::get('/seller-guide', [ContentController::class, 'sellerGuide']);
Route::get('order-pdf/{orderId}', [OrdersController::class, 'getOrderPdf'])->name('order-pdf');
Route::get('check-checkout-product', [CheckoutController::class, 'check_checkout_product']);
// usps
Route::get('/usps-domesticrates/', [UspsT::class, 'getDpmesticRates']);
Route::post('/get-states', [ShippingController::class, 'get_states_by_country_code']);

//product detail
Route::post('product/get-variations-info', [ProductController::class, 'getVariationsInfo']);

Route::post('/shipping/calculate', [ProductController::class, 'calculateShippingByprodcutId']);
//store

Route::get('/add-review', [ReviewsController::class, 'add_review']);
Route::post('/add-review', [ReviewsController::class, 'add_review']);
Route::delete('/delete-review/{id}', [ReviewsController::class, 'delete_review']);
Route::get('/reviews/{p_id}', [ReviewsController::class, 'get_reviews']);
Route::post('/admin-reviews/{productId}', [SuperadminReviewController::class, 'store']);
Route::put('/admin-reviews/{review}', [SuperadminReviewController::class, 'update']);
Route::delete('/admin-reviews/{review}', [SuperadminReviewController::class, 'destroy']);
Route::get('/getUserById/{user_id}', function ($user_id) {
    $user = DB::table('users')->where('user_id', '=', $user_id)->first();
    return response()->json(['name' => $user->name ?? 'Unknown']);
});
Route::get('/abc', [StoreController::class, 'abc']);
Route::get('/sales', [CampaignController::class, 'checkSales']);
Route::get('/sms', [UserController::class, 'sms']);
// check notification
Route::post('/user/check', [UserController::class, 'checkNewNotification']);
// notification read
Route::post('/user/check-notification', [UserController::class, 'userNotificationChangeStatus']);
Route::post('/user/read', [UserController::class, 'markRead']);
Route::post('/mark-as-reads', [UserController::class, 'markReadNotification']);
Route::post('/delete-notification', [UserController::class, 'deleteNotification']);

// Refund Order
Route::post('/refund_order', [RefundController::class, 'refund_order']);
Route::post('/cancel_order', [RefundController::class, 'cancel_order'])->name('cancel_order');
Route::post('/reject_order', [RefundController::class, 'rejectOrderStoreOwner'])->name('reject_order');
Route::post('/admin_complete_order', [RefundController::class, 'admin_complete_order']);
Route::post('return_refund/', [RefundController::class, 'return_refund'])->name('return_refund');
Route::post('return_refund_stripe/', [RefundController::class, 'return_refund_stripe'])->name('return_refund_stripe');
/*
 * Log in Access
 */
Route::get('/stripe/mobile-success', [StripeController::class, 'handleMobileSuccess']);
Route::get('/stripe/mobile-cancel', [StripeController::class, 'handleMobileCancel']);
Route::post('join-in', [ContentController::class, 'joinIn'])->name('join.in');
Route::post('subscriber', [ContentController::class, 'subscribe'])->name('subscriber');
Route::group(['middleware' => ['checkRole']], function () {
    Route::group(['middleware' => ['permission:view-users']], function () {
        Route::get('/admin/allusers', [AdminController::class, 'allUsers']);
        Route::post('/admin/create-user', [AdminController::class, 'createUser']);
    });
    Route::get('/all-notifications', [UserController::class, 'userAllNotifications']);

    Route::get('paypal/payment', [PayPalPaymentController::class, 'createPayment'])->name('paypal.payment');
    Route::get('paypal/success', [PayPalPaymentController::class, 'success'])->name('paypal.success');
    Route::get('paypal/cancel', [PayPalPaymentController::class, 'cancel'])->name('paypal.cancel');
    /// buyer
    Route::get('/user-dashboard', [BuyerController::class, 'index']);
    Route::get('/user-account', [BuyerController::class, 'userAccount']);
    Route::post('/user-account', [BuyerController::class, 'userAccount']);
    Route::get('/user-info', [BuyerController::class, 'userInfo']);
    Route::post('/user-info', [BuyerController::class, 'userInfo']);
    Route::post('/user-avatar', [BuyerController::class, 'uploadUserImg']);
    Route::post('/user-avatar-temp', [BuyerController::class, 'uploadTempImg']);
    Route::post('/buyer/state', [BuyerController::class, 'getStatesofCountry']);
    Route::get('/buyer-email-exist', [BuyerController::class, 'validateEmail']);
    Route::get('/edit-shipping-address', [BuyerController::class, 'editShippingAddress']);
    Route::post('/edit-shipping-address', [BuyerController::class, 'editShippingAddress']);
    Route::get('/user-profile', [UserController::class, 'userProfile']);
    Route::get('/user-profile/{id}', [UserController::class, 'userProfile']);
    Route::get('/buyer-orders', [BuyerController::class, 'userOrdersNew'])->name('buyer-orders');
    Route::get('/new-buyer-orders', [BuyerController::class, 'userOrdersNew']);
    Route::get('/new-buyer-orders-detail/{id}', [BuyerController::class, 'userOrderDetail']);
    Route::post('/new-buyer-orders-modal', [BuyerController::class, 'BuyerOrderdetailsModal']);
    Route::post('/new-buyer-post-modal', [BuyerController::class, 'buyerOrderPostModal']);

    // favourite store
    Route::post('/favouritestore/add', [FavouritesStoreController::class, 'store']);
    Route::post('/favouritestore/remove', [FavouritesStoreController::class, 'destroy']);
    // favorite store routes
    Route::get('/buyer-favorite-store', [FavouritesStoreController::class, 'index']);
    // favorites routes
    Route::get('/buyer-favorites', [FavouritesController::class, 'index']);
    Route::post('/favourite/add', [FavouritesController::class, 'addtoFavourite']);
    Route::post('/favourite/remove', [FavouritesController::class, 'removeFavourite']);
    Route::post('/user/change-status', [BuyerController::class, 'changeAlertsStatus']);
    Route::post('/buyer/intrest', [BuyerController::class, 'updateUserIntrest']);
    // featured Products routes
    Route::get('/store-featureproduct', [FeaturedController::class, 'index'])->middleware('permission:view-feature-products');
    Route::post('/featureproduct/remove', [FeaturedController::class, 'removeFeatured']);
    Route::get('/remove', [FeaturedController::class, 'remove']);
});
//store
Route::get('/store/available', [StoreController::class, 'checkShopAvailablity']);
Route::post('/store-logo', [StoreController::class, 'uploadBrandLogo']);
//cart
Route::get('cart/', [CartController::class, 'index'])->name('cart');
Route::post('cart/counter/', [CartController::class, 'cartCounter']);
Route::post('cart/delete', [CartController::class, 'delCartProducts']);
Route::post('cart/deleteCheckout', [CartController::class, 'delCheckoutProducts']);
Route::post('cart/deletecrypto', [CartController::class, 'delCartProductsCrypto']);
Route::post('cart/calculate', [CartController::class, 'calculateCart']);
Route::post('cart/updatebasket', [CartController::class, 'getCartdetail']);
Route::post('cart/update', [CartController::class, 'updateCart']);
Route::post('cart/addnote', [CartController::class, 'addNote']);
Route::post('cart/deletebasket', [CartController::class, 'delCartBasket']);
Route::post('cart/deletebasketcrypto', [CartController::class, 'delCartBasketCrypto']);
Route::post('cart/deletebasketajax', [CartController::class, 'delCartBasketAjax']);
//orders
Route::get('create-order/', [OrdersController::class, 'create_order']);
Route::post('update-tracking/', [OrdersController::class, 'updateTrackingId'])->name('update-tracking');

Route::get('orders-detail/', [OrdersController::class, 'ordersDtail']);
Route::post('save-order', [CheckoutController::class, 'SaveOrder']);
Route::post('/set-checkout-session', [CheckoutController::class, 'setCheckoutSession']);
Route::get('order-confirmation', [CheckoutController::class, 'OrderConfirmation']);

Route::post('/check-products', [ProductController::class, 'categoryProducts']);
Route::post('/detecategory', [ProductController::class, 'deleteCategory']);
//check out
Route::get('alert-cart', [CheckoutController::class, 'alert_checkout']);
Route::get('/checkout', [CheckoutController::class, 'getCheckout']);
Route::get('/checkout-new', [CheckoutController::class, 'checkout']);
Route::post('/checkout-save-new', [CheckoutController::class, 'checkout_save']);
Route::post('/checkout', [CheckoutController::class, 'saveShippingAddress']);

// zend desk routes
Route::get('/user/tickets', [ZendeskController::class, 'getUserTickets']);
Route::Post('/user/ticket/create', [ZendeskController::class, 'createUserTicket']);
Route::get('/user/ticket-detail/{id}', [ZendeskController::class, 'getUserTicketById']);
Route::Post('/user/update-ticket/{id}', [ZendeskController::class, 'updateUserTicket']);
Route::get('/user/mark-ticket/{id}/{status}', [ZendeskController::class, 'updateTicketStatus']);

Route::get('/addshiping', [ShippingController::class, 'addStoreShippingMethod']);
Route::post('cart/add/', [CartController::class, 'addToCart']);
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
Route::get('chat/{id}', [MessageController::class, 'chatHistory'])->name('message.read');
Route::get('site-support/{id}/{user}', [MessageController::class, 'chatHistory']);
Route::post('ajax/message/send', [MessageController::class, 'ajaxSendMessage']);
Route::get('/admin/deactivate', [AdminController::class, 'deactivate']);
// sellere landing
Route::get('/sellonmallshark', [StoreController::class, 'sellerLanding']);
Route::get('/store-name', [StoreController::class, 'createSellerName']);
Route::post('/store-name', [StoreController::class, 'createSellerName']);
Route::post('edit/store-name', [StoreController::class, 'editSellerName']);

// ask question
Route::post('/ask-question', [ProductController::class, 'askQuestion'])->name('ask_question');
// pledge support
Route::post('product/pledge-support', [ProductController::class, 'pledgeSupport']);
// add zit
Route::post('/add-zit', [WalletController::class, 'creditWalletWithPurchaseZit']);
// icon purchase/display
Route::post('/icon-purchase', [UserController::class, 'iconPurchase']);
Route::post('/icon-details', [UserController::class, 'iconDetails']);
Route::get('/icon-details', [UserController::class, 'iconDetailsAjax']);
Route::get('/signup-confirmation', [UserController::class, 'SignupConfirmation']);

//reviews
Route::get('/reviews', [BuyerController::class, 'reviews']);
//about
Route::get('/user-about', [BuyerController::class, 'userAbout']);

//kingdom
Route::get('/kingdom', [BuyerController::class, 'userKingdom']);
// user wallet
Route::get('/wallet', [BuyerController::class, 'userWallet']);

//sms and email alerts

// Import Products
Route::get('/importproducts', [ProductController::class, 'importShopifyProducts']);

/*
 * Store Access
 */
Route::post('/storebanner', [StoreController::class, 'storeBannerImage']);
Route::post('/updatebanner', [StoreController::class, 'updateBannerImage']);
Route::post('/updatebannercreate', [StoreController::class, 'updateBannerImageCreate']);
Route::group(['middleware' => ['checkRole']], function () {
    Route::get('/product-listing-discounts', [DiscountController::class, 'fetchProductsWithVariation']);
    Route::get('/subCategory-listing-discounts', [DiscountController::class, 'getCategoryListForDiscounts']);
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discount.index');
    Route::get('/discounts-list', [DiscountController::class, 'discountList'])->name('discount.list');
    Route::post('/discounts', [DiscountController::class, 'save'])->name('saveDiscountForm');
    Route::post('/discount-active', [DiscountController::class, 'saveAndActive'])->name('activeDiscountForm');
    Route::post('/discount-active-confirm', [DiscountController::class, 'saveAndActiveConfirm'])->name('confirmDiscountForm');

    Route::get('/discounts/{id}/edit', [DiscountController::class, 'edit'])->name('discounts.edit');
    Route::put('/discounts/{id}', [DiscountController::class, 'update'])->name('discounts.update');
    Route::put('/update-discount-active/{id}', [DiscountController::class, 'updateAndActive'])->name('activeDiscountUpdate');
    Route::put('/update-discount-active-confirm/{id}', [DiscountController::class, 'updateAndActiveConfirm'])->name('activeDiscountUpdateConfirm');
    Route::post('/update-status/{id}', [DiscountController::class, 'updateStatus'])->name('discounts.updateStatus');
    Route::post('/confirm-update-status/{id}', [DiscountController::class, 'confirmUpdateStatus']);
    Route::get('/discount-details', [DiscountController::class, 'discountDetails'])->name('discount-details');
    Route::post('/discount/{id}', [DiscountController::class, 'archive'])->name('discounts.archive');
    Route::post('/unarchive-discount/{id}', [DiscountController::class, 'unArchive'])->name('discounts.unarchive');
    Route::get('/archive-disounts', [DiscountController::class, 'getArchiveDiscounts'])->name('archive.discounts');

    Route::get('/store/create', [StoreController::class, 'addStore']);
    Route::post('/store/create', [StoreController::class, 'addStore']);
    Route::get('/store-dashboard', [StoreController::class, 'index']);
    Route::get('/store/edit', [StoreController::class, 'editStore']);
    Route::post('/store/edit', [StoreController::class, 'updateEditStore']);
    Route::get('/stock-your-store', [StoreController::class, 'stockYourStore']);
    Route::get('brand-orders/', [OrdersController::class, 'getBrandOrders'])->name('brand-orders');
    Route::get('brand-order-details/{id}', [OrdersController::class, 'brandOrderdetails'])->name('brand-order-details');
    Route::post('brand-order-details-status', [OrdersController::class, 'brandOrderdetailsStatus'])->name('brand-order-details-status');

    //reports
    Route::get('/reports', [ReportController::class, 'reportView']);
    Route::get('store-reports/', [ReportController::class, 'getReports']);
    Route::get('store-reports-by-category/', [ReportController::class, 'getReportsByCategory']);
    Route::get('get-store-categories/', [ReportController::class, 'getCategoryList']);
    Route::get('/sales-by-product', [ReportController::class, 'getSalesByProduct']);
    Route::get('/get-store-products', [ReportController::class, 'getProducts']);
    Route::get('/stock-reports', [ReportController::class, 'getStockReport']);

    Route::post('dispute_order/', [OrdersController::class, 'dispute_order'])->name('dispute_order');
    Route::get('dispute_order_cron', [OrdersController::class, 'dispute_order_cron'])->name('dispute_order_cron');
    //store template managment
    Route::get('/store/templates', [StoreController::class, 'storeTemplates']);
    Route::post('/store/templates', [StoreController::class, 'storeTemplates']);

    Route::post('/paymentintent', [StripeController::class, 'paymentIntentGooglePay']);
    Route::get('/stripe-refund', [StripeController::class, 'refundBack']);
    Route::get('/stripe-transfer', [StripeController::class, 'transfer']);
    Route::get('/stripe-create-product', [StripeController::class, 'createStripeProduct']);
    Route::get('/stripe-create-plan', [StripeController::class, 'createStripePlan']);
    Route::get('/stripe-create-customer-view', [StripeController::class, 'createCustomerView']);
    Route::post('/stripe-create-customer', [StripeController::class, 'createCustomer']);
    Route::get('/stripe-create-subscription', [StripeController::class, 'subscribeCustomerPlan']);
    Route::post('/stripe-subscripe-plan', [StripeController::class, 'subscribePlanToCustomer']);
    Route::get('/stripe-dashboard', [StripeController::class, 'stripeDashboard']);
    Route::get('/purchase-credits/', [CreditController::class, 'purchaseCredits']);
    Route::get('/purchase-store-user-credits/{id}', [CreditController::class, 'purchaseStoreUserCredits']);
    Route::get('/product-credits/{id}', [CreditController::class, 'index']);
    Route::get('/buy-credits/{id}', [CreditController::class, 'buyCredits']);
    Route::get('/store/reports', [StoreController::class, 'storeReports']);

    // WooCommerce Routes
    Route::get('/woocommerce', [WoocommerceController::class, 'index'])->name('woocommerce');
    Route::post('/woocommerce/connect', [WoocommerceController::class, 'connect'])->name('connectWoocommerce');
    Route::get('/woocommerce/disconnect-woo', [WoocommerceController::class, 'disconnectWoo'])->name('disconnectWoocommerce');
    Route::get('/woocommerce/import-products', [WoocommerceController::class, 'importWoocommerce'])->name('importWoocommerce');
    Route::post('/woocommerce/saveImportedProducts', [WoocommerceController::class, 'saveWooProducts'])->name('saveWooProducts');

    // product module route
    Route::get('/importshopify', [ShopifyController::class, 'shopify'])->name('shopify');
    Route::get('/importallproducts', [ShopifyController::class, 'importallproducts'])->name('importallproducts');
    Route::get('/shopify', [ShopifyController::class, 'index']);
    Route::post('/connectShopify', [ShopifyController::class, 'connectShopify'])->name('connectShopify');
    Route::get('/disconnectShopify', [ShopifyController::class, 'disconnectShopify'])->name('disconnectShopify');
    Route::post('/saveImportedProducts', [ShopifyController::class, 'saveImportedProducts'])->name('saveImportedProducts');
    Route::get('/shopify-csv', [ShopifyController::class, 'shopifyCsv'])->name('shopify.csv');
    Route::post('/import-shopify-csv', [ShopifyController::class, 'importShopifyCsv'])->name('import-shopify-csv');
    Route::post('save-csv-products', [ShopifyController::class, 'CsvProducts'])->name('save.csv.products');
    Route::get('csv-export', [ProductController::class, 'csvExport'])->name('csv.export');
    Route::get('/testShopify', [ShopifyController::class, 'testSingleProduct'])->name('testShopify');

    Route::get('/read-images', [ImageSizesController::class, 'index']);
    Route::get('/store-read-images', [ImageSizesController::class, 'StoreOptimizeImages']);
    Route::get('/read-images-single/{file}', [ImageSizesController::class, 'getAttachmentIdSingleImage']);

    Route::get('/add-product', [ProductController::class, 'addProduct']);
    Route::get('/img', [ProductController::class, 'img']);
    Route::post('/ProductImageRequest', [ProductController::class, 'uploadProductImageRequest']);
    Route::post('/storebannersImageRequest', [StoreController::class, 'uploadStoreImageRequest']);
    Route::post('/updateStorebannersImageRequest', [StoreController::class, 'updateEditStoreBannerImage']);
    Route::post('/bannerCropeduploadImage', [StoreController::class, 'uploadBannerCropImage']);
    Route::post('/editProductImageUpload', [ProductController::class, 'uploadEditProductImage']);
    Route::get('/rendorImagePath/{id}', [ProductController::class, 'getImagePathbyId']);
    Route::get('/rendorImagePathBanner/{id}', [ProductController::class, 'getImagePathbyIdBanner']);
    Route::post('/add-product', [ProductController::class, 'addProduct'])->name('add-product');
    Route::post('/uploadImage', [ProductController::class, 'uploadProductCropImage']);
    Route::post('/childCategory', [ProductController::class, 'getChildCategory']);
    Route::post('/deleteProductImage', [ProductController::class, 'deleteProductImage']);
    Route::post('/deleteEditableProductImage', [ProductController::class, 'deleteEditProductImage']);
    Route::post('/deleteEditProducttempImage', [ProductController::class, 'deleteEditProductFeatureImage']);
    Route::post('/deleteEditProductThumbnailImage', [ProductController::class, 'deleteEditProductFeatureImage']);
    Route::post('/deleteProducttempImage', [ProductController::class, 'deleteProductTempImage']);
    Route::post('/deleteBanner', [ProductController::class, 'deleteBannerImage']);
    Route::post('/deleteBannerCreate', [ProductController::class, 'deleteBannerImageCreate']);
    Route::post('/updateBanner', [ProductController::class, 'updateBannerImage']);
    Route::get('/store-products', [StoreController::class, 'storeProducts'])->name('store-products');
    Route::get('/fetchPackages', [StoreController::class, 'fetchPackages'])->name('fetchPackages');
    Route::post('/make-feature-product', [StoreController::class, 'makeFeatureProduct']);
    Route::get('/product-search', [StoreController::class, 'getproductsWithAutoComplete']);
    Route::get('/store/oncampaign', [StoreController::class, 'getCampaignProdutsByStore']);
    Route::get('/store/next-campaign', [StoreController::class, 'getflashProduct']);
    Route::get('/change-status/{id}/{status}', [StoreController::class, 'ChangeStatus']);
    Route::post('/variation', [ProductController::class, 'addProductVariation']);
    Route::post('/variation/add', [ProductController::class, 'productVarient']);
    Route::get('/edit-product/{id}', [ProductController::class, 'editProduct']);
    Route::post('/get-variation-details', [ProductController::class, 'getProductIdByVariations']);
    Route::post('/edit-variation-details', [ProductController::class, 'editProductIdByVariations']);
    Route::post('/edit-select-feature-image', [ProductController::class, 'editProductIdByFeatureImage']);
    Route::post('/edit-set-selected-feature-image', [ProductController::class, 'editProductIdBySetSelectedFeatureImage']);
    Route::post('/create-variation-details', [ProductController::class, 'CreateProductIdByVariations']);
    Route::get('/editproductfaqs/{id}', [ProductController::class, 'editProductFaqs']);
    Route::post('/editproductAction', [ProductController::class, 'editproductAction'])->name('editproductAction');
    //Bulk Product editing
    Route::get('/bulk-update',[StoreController::class,'bulkUpdate'])->name('bulk-update');
    Route::post('/bulk-update-products',[StoreController::class,'bulkUpdateProducts'])->name('bulk-update-products');

    Route::put('/updateproductfaq/{id}', [ProductController::class, 'updateProductFaqs']);
    Route::get('/editfaqs/{id}', [ProductController::class, 'vendoreditFaqs'])->name('vendor.editFaqs');
    Route::post('/deleteproductfaq', [ProductController::class, 'deleteFaqs']);
    Route::get('/store/payments', [StoreController::class, 'payments'])->name('addStripe');
    Route::get('/store/paidby', [StoreController::class, 'getPaidAddress']);
    Route::get('/store/bankdetail', [StripeController::class, 'storebankdetail']);
    Route::post('/store/bankdetail', [StripeController::class, 'storebankdetail']);
    Route::post('/store/paidby', [StoreController::class, 'getPaidAddress']);
    Route::get('/store-products-delete/{id}', [StoreController::class, 'deleteProduct']);
    Route::post('/product-variation/show', [StoreController::class, 'getvariationsOfProduct']);
    Route::post('/check-service', [ProductController::class, 'checkService']);
    //video upload
    Route::post('/videothumb', [ProductController::class, 'loadVideoThumb']);
    //  store status
    Route::post('/store-status/change', [StoreController::class, 'activateStore']);
    Route::post('/transferAmount', [StripeController::class, 'activateStipe']);
    Route::post('/editstripeuser', [StripeController::class, 'editStripeAccount']);
    Route::get('/account', [StripeController::class, 'transferAmount']);
    Route::post('/getstripeloginlink', [StripeController::class, 'createLoginLinkStripeAccount']);
    Route::post('/onloadstripeconnect', [StripeController::class, 'onLoadConnectStripeAccount']);
    Route::post('/updateStripeIdOnBoard', [StripeController::class, 'updateStripeIddB']);
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

    //campaign routes
    Route::get('/run-camapign', [CampaignController::class, 'runCampaign']);
    Route::get('/campaign-cron', [CampaignController::class, 'campaign']);
    Route::get('/flashsale-cron', [CampaignController::class, 'flashSaleEnd']);
    Route::get('/store/campaign', [StoreController::class, 'campaignCollect']);
    Route::post('/camp/flashsale', [CampaignController::class, 'updateFlashProduct']);

    // shipping routes
    Route::get('/addshippingOption', [ShippingController::class, 'createShippingOption']);

    Route::post('/shippingOptions', [ProductController::class, 'shippingOptionsPopup']);
    Route::post('/saveshipping', [ProductController::class, 'updateShippingProfile']);
    Route::post('/savemanagedshipping', [ProductController::class, 'updateManageShippingProfile']);
    Route::post('/deleteshipping', [ProductController::class, 'deleteShippingProfile']);
    Route::get('/managedshipping', [ProductController::class, 'mangeShippingProfile']);
    //flash sale on
    Route::post('/flashsale/on', [CampaignController::class, 'flasSaleOn']);
    Route::get("campaigns", [HomeController::class, "campaings"]);

    Route::get('/admin/distributor/brands', [PackagesDistributorControllerV2::class, 'getBrands']);
    Route::get('/admin/distributors/list', [PackagesDistributorControllerV2::class, 'getAllDistributors']);
    Route::post('/admin/distributor/register-brand', [PackagesDistributorControllerV2::class, 'registerBrand']);
    Route::get('/admin/distributor/detail/{id}', [PackagesDistributorControllerV2::class, 'getDistributorDetail']);
    Route::get('/admin/distributor/brands-list', [PackagesDistributorControllerV2::class, 'getDistributorBrandsList']);
    Route::post('/admin/distributor/change-status', [PackagesDistributorControllerV2::class, 'changeDistributorStatus']);
    Route::post('/admin/distributor/assign-brand', [PackagesDistributorControllerV2::class, 'assignBrandToDistributor']);
    Route::post('/admin/distributor/document/change-status', [PackagesDistributorControllerV2::class, 'changeDocumentStatus']);
});
Route::get('store-active', [StoreController::class, 'storeActivates']);
Route::group(['middleware' => ['checkRole']], function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/stripeaccounts', [AdminController::class, 'getAllStripeVendorAccounts']);
    Route::get('/admin/importCSV', [AdminController::class, 'shopifyCsv']);
    Route::post('/admin/save-csv', [AdminController::class, 'importCsv'])->name('admin.save.csv');

    Route::get('/admin/youtube-videos', [AdminController::class, 'getYoutubeVideos'])->name('admin.youtube.videos')->middleware('permission:manage-youtube-videos');
    Route::get('/admin/product-videos/{product_id}', [AdminController::class, 'getProductVideos'])->name('admin.product.videos');
    Route::get('admin/mallshark-purchased-packages', [AdminController::class, 'mallsharkPurchasedPackages'])->name('admin.purchased.packages')->middleware('permission:manage-seller-packages');

    //Vendor Edit Payment Routes
    Route::get('/store-deduction/{storeId}', [PackagesAdminController::class, 'getByStoreId']);
    Route::post('/store-deduction', [PackagesAdminController::class, 'createOrUpdate']);
    // vendor payment
    Route::get('/get-transfer-Amount', [PackagesAdminController::class, 'gettransferVendorPayment']);
    Route::get('/get-orders-transfer-Amount', [PackagesAdminController::class, 'getorderstransferVendorPayment']);
    //transfer amount to vendor
    Route::post('/transfer-Amount', [PackagesAdminController::class, 'transferVendorPayment']);
    Route::post('/order-transfer-Amount', [PackagesAdminController::class, 'transferOrderVendorPayment']);
    //user routes
    Route::get('/admin/orders', [AdminController::class, 'allOrders'])->middleware('permission:view-orders');
    Route::get('/admin/crypto-refunds', [AdminController::class, 'getCryptoRefunds'])->middleware('permission:view-orders');
    Route::get('admin/order-details/{id}', [AdminController::class, 'OrderDetailsAdmin'])->name('orderDetailsAdmin')->middleware('permission:view-orders');
    Route::post('/send-wallet-address-email', [OrderControllerV2::class, 'sendWalletAddressEmail'])->middleware('permission:view-orders');
    Route::post('/items-mark-refunded', [OrderControllerV2::class, 'markItemsAsRefunded'])->middleware('permission:view-orders');
    Route::post('/admin/assignrole', [AdminController::class, 'updateUserRole']);
    Route::get('/admin/users-update/{id}/{status}', [AdminController::class, 'userChangeStatus'])->middleware('permission:edit-user');
    Route::get('/admin/userdetail/{id}', [AdminController::class, 'userInfo'])->middleware('permission:edit-user');
    Route::post('/admin/userdetail/{id}', [AdminController::class, 'userInfo'])->middleware('permission:edit-user');
    Route::delete('/admin/delete-user-account/{id}', [AdminController::class, 'deleteUserAccount'])->middleware('permission:edit-user');
    Route::delete('/admin/delete-store/{id}', [AdminController::class, 'deleteStore'])->middleware('permission:edit-user');
    // categories routes
    Route::post('/check-slug-unique', [AdminController::class, 'checkSlugUnique'])->name('checkSlugUnique');
    Route::get('/admin/categories', [AdminController::class, 'manageCategories'])->middleware('permission:manage-categories');
    Route::post('/admin/addcategories', [AdminController::class, 'addCategories']);
    Route::post('/change-category-status', [AdminController::class, 'changeCategoryStatus'])->name('changeCategoryStatus');
    //    content pages
    Route::get('/admin/content-pages', [ContentController::class, 'AdminPage'])->middleware('permission:manage-cms-pages');
    Route::post('/admin/edit-page', [ContentController::class, 'updateContentPage']);
    Route::post('/admin/add-page', [ContentController::class, 'createContentPage']);

    //    Add Faqs pages  getFaqs
    Route::get('/admin/faqs', [ContentController::class, 'getFaqs'])->name('adminFaqs')->middleware('permission:manage-faqs');
    Route::get('/admin/addfaqs', [ContentController::class, 'addFaqs']);
    Route::post('/admin/store-faqs', [ContentController::class, 'storefaq']);
    Route::get('/admin/editfaqs/{id}', [ContentController::class, 'editFaqs'])->name('admin.editFaqs');
    Route::put('/admin/update-faqs/{id}', [ContentController::class, 'updateFaqs']);
    Route::post('/admin/deletefaq', [ContentController::class, 'deleteFaqs']);

    // Article Module (Post and post category)
    Route::get('/admin/articles', [PostController::class, 'index'])->middleware('permission:manage-cms-pages');
    Route::get('/admin/create/post', [PostController::class, 'createPost']);
    Route::post('/admin/create/post', [PostController::class, 'storePost']);
    Route::get('/admin/editpost/{id}', [PostController::class, 'editPost']);
    Route::post('/admin/updatepost/', [PostController::class, 'updatePost']);
    Route::post('/deletepost', [PostController::class, 'deleteBlogPost']);
    Route::post('/post-image', [PostController::class, 'uploadPostAvatar']);
    //  Post category
    Route::get('/admin/postcategories', [PostController::class, 'createPostCategories'])->middleware('permission:manage-categories');
    Route::post('/admin/addpostcategories', [PostController::class, 'storePostCategories']);
    Route::get('/admin/editcategory/{id}', [PostController::class, 'editPostCategory']);
    Route::post('/admin/updatecategory/', [PostController::class, 'updateCategory']);
    Route::get('/category/available', [PostController::class, 'checkPostCategoryAvailablity']);
    Route::post('/deletecategory', [PostController::class, 'deleteCategory']);

    //    zend desk routes
    Route::post('/admin/addticket', [AdminZendeskController::class, 'createTicket']);
    Route::get('/admin/alltickets', [AdminZendeskController::class, 'getAlltickets']);
    Route::get('/admin/agents', [AdminZendeskController::class, 'getAllUsers']);
    Route::get('/admin/ticket/create', [AdminZendeskController::class, 'createTicket']);
    Route::get('/admin/ticket-detail/{id}', [AdminZendeskController::class, 'getTicketById']);
    Route::get('/admin/mark-ticket/{id}/{status}', [AdminZendeskController::class, 'updateTicketStatus']);
    Route::Post('/admin/update-ticket/{id}', [AdminZendeskController::class, 'updateTicket']);
    Route::get('/admin/delete-ticket/{id}', [AdminZendeskController::class, 'deleteTicket']);
    Route::get('/admin/email-template/', [AdminController::class, 'emailTemplate']);
    Route::get('/admin/pools-stats', [PoolTController::class, 'getPoolStats']);
    //all store products
    Route::get('/admin/store-products', [AdminController::class, 'storeProducts'])->middleware('permission:view-products');
    Route::get('/admin-edit-product/{id}', [ProductController::class, 'adminEditProduct'])->middleware('permission:edit-product');
    Route::get('/admin/stores', [AdminController::class, 'getStoresAdmin'])->middleware('permission:view-vendors');
    Route::get('/admin/tiktok-videos', [AdminController::class, 'tiktokVideos']);
    Route::post('/tiktok-videos/save', [AdminController::class, 'saveVideos'])->name('admin.tiktok.videos.save');
    Route::post('/admin/tiktok-videos/store', [AdminController::class, 'store'])->name('admin.tiktok.videos.store');
    Route::post('/admin/product-metadata', [AdminController::class, 'getProductMetadata'])->name('admin.product.metadata');
    Route::get('/admin/mission-complete-sellers', [AdminController::class, 'missionCompleteSellers'])->name('admin.missionCompleteSellers')->middleware('permission:view-vendors');
    Route::post('/admin/enableStore', [AdminController::class, 'adminEnableStore']);
    Route::post('/admin/activateStore', [AdminController::class, 'adminActivateStore']);
    Route::post('/admin/disableStore', [AdminController::class, 'adminDisableStore']);
    Route::post('/admin/deactivateStore', [AdminController::class, 'adminDeactivateStore']);
    Route::post('/admin/rejectStore', [AdminController::class, 'adminRejectStore']);
    Route::post('/admin/approvedStore', [AdminController::class, 'adminApprovedStore']);
    Route::post('/admin/product-bulk-update',  [AdminController::class, 'productBulkUpdate'])->name("bulk.action"); //admin bulk approved/disapproved 
    Route::post('/admin/feature-status-update', [AdminController::class, 'updateFeatureStatus']);
    Route::post('/admin/facebook-feed-multi', [AdminController::class, 'facebookFeedMulti']);

    Route::get('admin-change-status/{id}/{status}/{storeid}', [AdminController::class, 'changeProductStatus']);
    //false sale for home page
    Route::get('/admin/flash-product/{id}', [AdminController::class, 'setFeatureProduct']);
    Route::post('admin/add-slide', [SlidesController::class, 'addSlide']);

    Route::get('admin-change-status/{id}/{status}', [AdminController::class, 'changeProductStatusWise'])->middleware('permission:restore-product|reject-product|approve-product|delete-product');
    Route::get('/admin/product/{slug}', [AdminController::class, 'productDetail']);
    //product delete or reject
    Route::post('admin/product-status', [AdminController::class, 'ProductStatusUpdate']);
    Route::get('admin/slides', [SlidesController::class, 'getDashboard'])->middleware('permission:manage-banners');
    Route::post('/admin/add-slide', [SlidesController::class, 'addSlide'])->name('addslide');
    Route::post('/admin/edit-slide', [SlidesController::class, 'updateSlide'])->name('editslide');
    Route::get('/admin/delete-slide/{slide_id}', [SlidesController::class, 'deleteSlide']);
    Route::get('/admin/fsale-products', [AdminController::class, 'getFlashSaleProducts']);
    // admin sale report
    Route::get('admin/sale-report', [AdminController::class, 'saleReport']);
    Route::get('admin/product-report', [AdminController::class, 'productReport']);
    Route::post('/admin/search-stores', [AdminController::class, 'getStoresName']);
    Route::post('/admin/search-product', [AdminController::class, 'getProductName']);
    //search admin store
    Route::get('/admin/sale-report-auto', [AdminController::class, 'getStoresAuto']);
    Route::post('/admin/stores', [AdminController::class, 'getStoresName']);

    // Laravel Coupon Code 
    Route::get('/admin/coupons', [CouponController::class, 'index'])->name('coupon.index');
    Route::get('/admin/coupon-create', [CouponController::class, 'create']);
    Route::post('/admin/coupon-create', [CouponController::class, 'store']);
    Route::get('/admin/editcoupon/{id}', [CouponController::class, 'edit'])->name('coupon.editCoupon');
    Route::post('/admin/coupondelete', [CouponController::class, 'destroy'])->name('coupon.delete');

    Route::resource('admin/roles', RoleController::class)->middleware('permission:manage-roles');
    Route::resource('admin/permissions', PermissionController::class);
    Route::group(['prefix' => 'slides'], function () {
        Route::get('/banner-management', [SlidesController::class, 'bannerManagement'])->name('banners');
        Route::get('/sets', [SlidesController::class, 'bannerListing'])->name('bannersListing');
        Route::post('/create-set', [SlidesController::class, 'createNewSet'])->name('createNewSet');
        Route::post('/update-set/{setId}', [SlidesController::class, 'updateSlideSet'])->name('updateSlideSet');
        Route::post('/add', [SlidesController::class, 'addSlideToSet'])->name('addSlideToSet');
        Route::post('/edit', [SlidesController::class, 'editSlideToSet'])->name('editSlideToSet');
        Route::get('/delete/{slide_id}', [SlidesController::class, 'deleteSlideFromSet']);
        Route::get('/set/{set_id}', [SlidesController::class, 'getSlideSet'])->name('getSlideSet');
        Route::delete('/delete-set/{setId}', [SlidesController::class, 'deleteSlideSet'])->name('deleteSlideSet');
        Route::post('/activate-set/{setId}', [SlidesController::class, 'ActivateSlideSet'])->name('activateSlideSet');
        Route::put('/{slideId}/update-link', [SlidesController::class, 'updateSlideLink']);
    });

    // });
});
Route::post('admin/banner-upload', [AdminController::class, 'addbanner']);
Route::group(['middleware' => ['checkRole']], function () {
    //support routes
    Route::get('/support/dashboard', [SupportController::class, 'index']);
    Route::get('/support/allusers', [SupportController::class, 'allUsers']);
    Route::post('/support/assignrole', [SupportController::class, 'updateUserRole']);
    Route::get('/support/users-update/{id}/{status}', [SupportController::class, 'userChangeStatus']);
    Route::get('/support/userdetail/{id}', [SupportController::class, 'userInfo']);
    Route::post('/support/userdetail/{id}', [SupportController::class, 'userInfo']);
    Route::get('/support/tickets-list/', [AdminZendeskController::class, 'getAlltickets']);
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
Route::get('unit-brand-orders/{id}', [OrdersController::class, 'get_store_order']);
//start selling
Route::get('/unit-start-selling', [StoreController::class, 'sellerLanding']);
//cart
Route::get('unit-cart/{id}', [CartController::class, 'index']);
Route::get('/unit-checkout/{id}', [CheckoutController::class, 'getCheckout']);
Route::get('/unit-categories', [AdminController::class, 'manageCategories']);
Route::get('/unit-slides', [SlidesController::class, 'getDashboard']);
Route::get('/unit-fsale-products', [AdminController::class, 'getFlashSaleProducts']);
Route::get('/unit-alltickets', [AdminZendeskController::class, 'getAlltickets']);
Route::get('/unit-content-pages', [ContentController::class, 'AdminPage']);
Route::get('/unit-pools-stats', [PoolTController::class, 'getPoolStats']);
Route::get('/unit-allusers', [AdminController::class, 'allUsers']);
Route::get('unit-sale-report', [AdminController::class, 'saleReport']);

// Not Found Custom page 
Route::fallback(function () {
    return response()->view('error.no-found-page', [], 404);
});

// Staking routes
Route::get('/staking', function () {
    return view('content-pages.staking_page');
});

// ICO routes
Route::get('/ico', function () {
    return view('content-pages.ico_page');
});
Route::get('/cw', function () {
    return view('content-pages.connectWallet');
});
Route::get('/mint', function () {
    return view('content-pages.mint_nft');
});
Route::get('/test', function () {
    return view('content-pages.testPage');
});
Route::get('/vouchers-to-mint', [NftVoucherController::class, 'voucherToMint']);
Route::resource('nftvouchers', NftVoucherController::class);
// User Social Lite 
Route::get('/google-login', [UserController::class, 'googleRedirect']);
Route::get('/google-callback', [UserController::class, 'googleCallback']);
Route::get('/facebook-login', [UserController::class, 'facebookRedirect']);
Route::get('/facebook-callback', [UserController::class, 'facebookCallback']);
Route::get('/download-files/{id}', [OrdersController::class, 'downLoadFiles']);
Route::get('/feature-products', [HomeController::class, 'showFeatureProducts']);
Route::get('/phpinfo', function () {
    return phpinfo();
});
Route::post('/calculate_service', [EasypostController::class, 'createShipment']);
Route::post('/calculate_service_crypto', [EasypostController::class, 'createShipmentCrypto']);
Route::post('/calculate_service_change', [EasypostController::class, 'createShipmentOnChnage']);
Route::post('/calculate_usps_service_change', [UspsT::class, 'shippingRates']);
Route::post('/calculate_service_change_crypto', [EasypostController::class, 'createShipmentOnChnageCrypto']);
Route::post('/getrates', [EasypostController::class, 'getRateWhenProductAdd']);
Route::post('/getrate_usps_service', [UspsT::class, 'getShippingRateUspsProductCreate']);

Route::get('/test-abc', function () {
    return view('abc');
});
Route::get('/usps-rates', [CheckoutController::class, 'getUspsrate']);
Route::get('/easypostParcel', [EasypostController::class, 'getParcel']);

Route::get('/404', [HomeController::class, 'get404Page']);
Route::get('/500', [HomeController::class, 'get500Page']);
Route::get('/400', [HomeController::class, 'get400Page']);
Route::get('/503', [HomeController::class, 'get503Page']);
Route::get('/comingsoon', [HomeController::class, 'getcomingsoon']);

Route::get("new-order-page", [OrdersController::class, "getBrandOrders"]);
Route::post("updateOrderStatus", [OrdersController::class, "updateOrderStatus"]);

Route::post('order-detail-items', [OrdersController::class, 'brandOrderdetailsModal']);
Route::get("mailsend", [HomeController::class, "mailSend"]);
Route::post('/getratesTest', [EasypostController::class, 'getRateWhenProductAdd']);
Route::get('site-map',[HomeController::class, 'siteMap']);

Route::get("article-listing", [HomeController::class, "articleListing"]);
Route::post("articles-ajax", [HomeController::class, "articleListingAjax"]);
Route::get("individual-article", [HomeController::class, "individualArticle"]);
Route::get("selling", [HomeController::class, "sellOnMallshark"]);
Route::get('/how-to-connect-woocommerce', [WoocommerceController::class, 'wooTerms'])->name('wooterms');
Route::get('/{slug}/{subSlug?}', [SearchController::class, 'getSearchResults'])
    ->name('category.landing');
// Manullly charge request to stripe
Route::get('/charge', [StripeController::class, 'chargeManully']);
Route::get('/stripe-connected', function () {
    return view('Store/stripe-connected');
});
Route::post('/product/almostSoldout', [ProductController::class, 'almostSoldOut'])->name('pro.detail.almostSoldOut');
