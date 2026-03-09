<?php

namespace App\Http\Controllers;

use Exception;
use Zendesk;
use App\Models\Faqs;
use App\Models\JoinIn;
use App\Models\Subscriber;
use App\Models\ContentPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\MailgunController;
use romanzipp\Turnstile\Rules\TurnstileCaptcha;

class ContentController extends Controller {

    private $contentModel;
    private $mailgun;
    private $_request;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->contentModel = new ContentPages();
        $this->mailgun = new MailgunController();
    }

    /*
     * create new page
     */

    public function createContentPage(Request $req) {
        $page_name = $req->input('page_name');
        $page_content = $req->input('page_content');
        $slug = str_slug($page_name, '-');
        for ($i = 1; $i < 21; $i++) {

            $results = $this->contentModel->where('page_slug', $slug)->get()->toArray();

            if (count($results) > 0) {
                $slug = str_slug($page_name, '-') . $i;
            } else {
                break;
            }
        }

        $this->contentModel->page_name = $page_name;
        $this->contentModel->status = 1;
        $this->contentModel->page_slug = $slug;
        $this->contentModel->page_content = $page_content;
        $this->contentModel->save();
        $message = '<div class="alert alert-success"> Page is added sucsessfully </div>';
        return Redirect::back()->with('message', $message);
    }

    /*
     * Update 
     */

    public function updateContentPage(Request $req) {
        $page_id = $req->input('page_id');


       $page_name = $req->input('page_name');

        $page_content = $req->input('page_content');


        if ($page_id != '' && $page_name != '' && $page_content != '') {
            
            $this->contentModel->where('id', $page_id)->update(array('page_name' => $page_name, 'page_content' => $page_content));
            
        }
      
        $message = '<div class="alert alert-success"> Page update sucsessfully </div>';
        return Redirect::back()->with('message', $message);
    }

    /*
     * get all pages
     */

    public function getAllPages() {


        return $results = $this->contentModel->get()->toArray();
    }

    public function AdminPage() {
        $pages = $this->getAllPages();
        $page_id = isset($_GET['page_id']) ? $_GET['page_id'] : '';
        $page_status = isset($_GET['status']) ? $_GET['status'] : '';
        if ($page_id != '' && $page_status == '') {
            $obj = $this->contentModel->find($page_id);
            $obj->delete();
            $message = '<div class="alert alert-success"> deleted sucsessfully </div>';
            return redirect('admin/content-pages')->with(array('pages' => $pages, 'message' => $message));
        }
        if ($page_id != '' && $page_status != '') {
            $this->contentModel->where('id', $page_id)->update(array('status' => $page_status));
            $message = '<div class="alert alert-success"> Status updated sucsessfully </div>';
            return redirect('admin/content-pages')->with(array('pages' => $pages, 'message' => $message));
        }
        return view('content-pages.admin')->with(array('pages' => $pages));
    }

    public function aboutUs() {
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.about');
        // }else{
            return view('content-pages.about');
        // }
    }
    public function contentPage($pageSlug) {
        $content='';
        if($pageSlug!=''){
            $content=$this->contentModel->where('page_slug', $pageSlug)->get()->toArray();
        }
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.content-page')->with('content',$content);
        // }else{
        return view('content-pages.content-page')->with('content',$content);

        // }
    }

    public function contactUs() {
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.contact-us');
        // } else{
            return view('content-pages.contact');
        // }
    }
    public function requestDeletion() {
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.contact-us');
        // } else{
            return view('content-pages.request-deletion');
        // }
    }

    public function sellerGuide() {
            return view('content-pages.seller_guide');
    }
    public function contactStore(Request $request){
        $payload = $request->validate([
            'cf-turnstile-response' => ['required', 'string', new TurnstileCaptcha()],
        ],
        [
            'cf-turnstile-response.required' => 'Human verification is required.',
            'cf-turnstile-response.string' => 'Invalid Turnstile response format.',
        ]);
        
        $request->validate([
            'first_name' => 'required',
            'email' => 'required',
            'message' => 'required'
            ]);

            $message = $request->message;
            $orderNumber = $request->phone;
            $toCompletename = $request->first_name;
            $lastname = $request->last_name;
            $toFullname = $request->first_name.' '.$request->last_name ;
            $subject = 'Customer Support Query';
            $toEmail = $request->email;
            $reason_text = ucfirst($request->reason);
            $support = ucfirst($request->support);

             $html=view('mail.contactUser',['message'=>$message, 'orderNumber' => $orderNumber , 'toCompletename' => $toCompletename, 'reason_text' =>$reason_text, 'support' =>$support , 'fullname' =>$toFullname]);
               $params = [ 
               'from'    => 'MallShark  '. $toEmail,
               'to'      => env('SUPPORT_EMAIL'),
               'subject' => $subject,
               'text'    => $toCompletename,
               'html'    => "$html",
            ]; 
               $results = $this->mailgun->SendMailgunMail($params); 
                // user reply
               $subject = 'Customer Support';
               $toEmail = $request->email;
   
                $html=view('mail.contact-us',['message'=>$message, 'phone' => $orderNumber , 'toCompletename' => $toCompletename,'reason_text' =>$reason_text, 'support' =>$support]);
                // $html=view('mail.contactUserReply',['message'=>$message, 'phone' => $phone , 'toCompletename' => $toCompletename,'reason_text' =>$reason_text, 'support' =>$support]);
                $params = [ 
                  'from'    => 'MallShark  '. env('SUPPORT_EMAIL'),
                  'to'      => $toEmail,
                  'subject' => $subject,
                  'text'    => $toCompletename,
                  'html'    => "$html",
               ]; 
                $customFieldOrderNumber = '30884140137613';
                $customFieldSupportType = '30884075534861';

                // Create Zendesk user if not exists
                try {
                    $zendeskUser = Zendesk::users()->createOrUpdate([
                        'name' => $toCompletename,
                        'email' => $toEmail,
                        'role' => 'end-user',
                    ]);

                    $zendeskId = $zendeskUser->user->id;

                    // Create the Zendesk ticket
                    $newTicket = Zendesk::tickets()->create([
                        'subject' => 'Support Request from ' . $toFullname,
                        'comment' => [
                            'body' => $message,
                        ],
                        'priority' => 'high', 
                        'requester_id' => $zendeskId,
                        'type' => 'problem',
                        'custom_fields' => [
                            ['id' => $customFieldOrderNumber, 'value' => $orderNumber ?? ''],
                            ['id' => $customFieldSupportType, 'value' => $support ?? ''],
                        ],
                    ]);
                } catch (\Exception $e) {
                    return redirect()->route('contact_us')->with('error', 'Failed to create Zendesk ticket. Please try again.');
                }

                  $results = $this->mailgun->SendMailgunMail($params); 
               return redirect()->route('contact_us')->with('success','Your Query submit has been updated successfully');
        }

    public function contentFaq() {
        $faqs = Faqs::get();
        // if ($this->isMobileDevice()) {
        //     return view('content-pages.mobile.general_faq', compact('faqs'));
        //     }else{
                return view('content-pages.general_faq', compact('faqs'));
            // }
        
    }

    public function sellerFaq() {
        $faqs = Faqs::get();
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.seller_faq', compact('faqs'));
        // }else{
            return view('content-pages.seller_faq', compact('faqs'));
        // }
    }

    public function buyerFaq() {
        $faqs = Faqs::get();
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.buyer_faq', compact('faqs'));
        // }else{
            return view('content-pages.buyer_faq', compact('faqs'));
        // }
    }

    public function PrivacyPolicy() {
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.privacy-policy');
        // }else{
        return view('content-pages.privacy-policy'); 
        // }
    }

    public function termsAndConditions() {
        // if ($this->isMobileDevice()) {
        // return view('content-pages.mobile.terms');
        // }else{
        return view('content-pages.terms');
        // }
    }
    // All Faqs
    public function getFaqs() {
        $faqs = Faqs::paginate(10);
        //dd($faqs);
        return view('content-pages.get_all_faqs', compact('faqs'));
    } 

    // Add Faqs
    public function addFaqs()
    {
        return view('content-pages.add_faqs');
    }

   // Store Faqs
    public function storefaq(Request $request){
        $faqs_obj = new Faqs();
        $faqs_obj->question = $request->question;
        $faqs_obj->answer = $request->answer;
        $faqs_obj->type = $request->type;
        $faqs_obj->save();
        return redirect()->route('adminFaqs')->with('success', 'Faqs Added Successfully');
    }

    // Edit Faqs
    public function editFaqs($id){
        $faqs = Faqs::findOrFail($id);
        return view('content-pages.edit_faqs', compact('faqs'));
    }

    // Update Faqs
    public function updateFaqs(Request $request, $id){
        $faqs = Faqs::find($id);
        $faqs->question = $request->input('question');
        $faqs->answer = $request->input('answer');
        $faqs->type = $request->input('type');
        $faqs->update();
        return redirect()->route('adminFaqs')->with('success', 'Faqs Updated Successfully');
    }

    // Delete Faqs
    public function deleteFaqs(Request $request)
    {
        DB::table('faqs')->where('faq_id',$request->id)->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Poof! Your faqs has been deleted!'
            ]
        );
    }

    public function changeLog() {
        return view('pages.changelog');
        
    }
    public function features() {
        return view('pages.features');
        
    }

    public function joinIn(Request $request){
        try{
            $request->validate([
                'email' => 'required',
            ]);
            $email = $request->input('email');
            $joinIn = JoinIn::where('email',$email)->first();
            if(empty($joinIn)){
                $joinIn = new JoinIn();
                $joinIn->email = $email;
                $joinIn->save();
            }
            return response()->json([
                'status' => true,
                'message' => 'Thanks for Subscribe Mallshark'
            ],200);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        
    }

    public function subscribe(Request $request){
        try{
            // Validate the email field and ensure it follows email format
            $request->validate([
                'email' => 'required|email',
            ]);
    
            // Get the email from the request
            $email = $request->input('email');
    
            // Check if the subscriber already exists
            $subscriber = Subscriber::where('email', $email)->first();
    
            if (empty($subscriber)) {
                // If not found, create a new subscriber
                $subscriber = new Subscriber();
                $subscriber->email = $email;
                $subscriber->subscribed = '1'; // Set the default value of 'subscribe' as 1
                $subscriber->save();
            }
    
            // Return a success response
            return response()->json([
                'status' => true,
                'message' => 'Thanks for subscribing to Mallshark'
            ], 200);
    
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error($e->getMessage());
    
            // Return a clean error response
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
    

}
