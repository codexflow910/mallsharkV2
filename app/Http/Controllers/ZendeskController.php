<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use Zendesk;
use Redirect;
use Session;

//use Redirect;
class ZendeskController extends Controller {

    private $request;
    private $_request;

    public function __construct(Request $request) {
        $this->_request = $request;
    }
 
    public function getStoreOwnerTickets() {
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $zendeskId = $userData->zendesk_user_id;
            if(!empty($zendeskId)){
                $allTickets = Zendesk::users($zendeskId)->requests()->findAll();
            }else{
                $allTickets = '';
            }
            // if ($this->isMobileDevice()) {
            //     return view('Store/mobile/tickets', ['allTickets' => $allTickets]);
            // } else {
                return view('Store/zendesk/tickets', ['allTickets' => $allTickets]);
            // }
        }
    }

    public function getUserTickets() {
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $zendeskId = $userData->zendesk_user_id;
            $allTickets = Zendesk::users($zendeskId)->requests()->findAll();
            if ($this->isMobileDevice()) {
                return view('buyer/mobile/tickets', ['allTickets' => $allTickets]);
            } else {
                return view('buyer/zendesk/tickets', ['allTickets' => $allTickets]);
            }
        }
    }

    public function createTicket() {

        //365108742692
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $zendeskId = $userData->zendesk_user_id;
            if (empty($zendeskId)) {
//                 $zendeskResult = Zendesk::users()->create([
//                     'name' => $userData->name,
//                     'email' => $userData->email,
// //                  'phone' => '+1-954-704-6031',
//                     'role' => 'end-user',
//                         //'details' => 'This user has been created with the API.'
//                 ]);
                try {
                    $zendeskResult = Zendesk::users()->create(
                        [
                        'name' => $userData->name,
                        'email' => $userData->email,
                        'phone' => $userData->contact_no,
                        'role'  => 'end-user',
                        'details' => 'This user has been created with the API.'
                        ]
                    );

                } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
                    echo 'Please check your credentials. Make sure to change the $subdomain, $username, and $token variables in this file.';
                }
                if (isset($zendeskResult)) {
                    $zendeskResult = $zendeskResult->user;
                    $zendId = $zendeskResult->id;
                    $zendIdd = strval($zendId);
                    $zendeskId = $zendIdd;
                    $q = DB::table('users')->where('user_id', '=', $userid)->update(['zendesk_user_id' => $zendIdd]);
                }
            }

            $postedData = $this->_request->input();
            $newTicket = Zendesk::tickets()->create([
                'subject' => $postedData['subject'],
                'comment' => [
                    'body' => $postedData['message']
                ],
                'priority' => 'normal',
                'requester_id' => $zendeskId,
                'type' => $postedData['type']
            ]);
            $this->_request->session()->flash('SuccessMessage', 'Ticket  has been created successfully.');
            return redirect('store/tickets');
        }
    }

    public function createUserTicket() {
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $zendeskId = $userData->zendesk_user_id;
          //  echo 'Data: <pre>' .print_r($zendeskId,true). '</pre>';
            // if (empty($zendeskId)) {
            //     $zendeskResult = Zendesk::users()->create([
            //         'name' => $userData->name,
            //         'email' => $userData->email,
            //         'role' => 'end-user',
            //     ]);
            //     if (isset($zendeskResult)) {
            //         $zendeskResult = $zendeskResult->user;
            //         $zendId = $zendeskResult->id;
            //         $zendIdd = strval($zendId);
            //         $zendeskId = $zendIdd;
            //         $q = DB::table('users')->where('user_id', '=', $userid)->update(['zendesk_user_id' => $zendIdd]);
            //     }
            // }
            $postedData = $this->_request->input();
            $newTicket = Zendesk::tickets()->create([
                'subject' => $postedData['subject'],
                'comment' => [
                    'body' => $postedData['message']
                ],
                'priority' => 'normal',
                'requester_id' => $zendeskId,
                'type' => $postedData['type']
            ]);
            $this->_request->session()->flash('SuccessMessage', 'Ticket  has been created successfully.');
            return redirect('user/tickets');
        }
    }

    public function getStoreTicketById($id) {
        $ticketDetail = Zendesk::tickets()->find($id);
        $ticketDetail = $ticketDetail->ticket;
        $submitterId = $ticketDetail->requester_id;
        $ticketcomments = Zendesk::tickets($id)->comments()->findAll();
        $userDetail = Zendesk::users()->find($submitterId);
        // if ($this->isMobileDevice()) {
        //     return view('Store/mobile/ticket-detail', ['ticketDetail' => $ticketDetail, 'ticketcomments' => $ticketcomments->comments, 'userDetail' => $userDetail->user]);
        // } else {
            return view('Store/zendesk/ticket-detail', ['ticketDetail' => $ticketDetail, 'ticketcomments' => $ticketcomments->comments, 'userDetail' => $userDetail->user]);
        // }
    }

    public function getUserTicketById($id) {
        //  $a=getAendeskUserInfo(369388460371);
        $ticketDetail = Zendesk::tickets()->find($id);

        $ticketDetail = $ticketDetail->ticket;
        $submitterId = $ticketDetail->requester_id;
        $ticketcomments = Zendesk::tickets($id)->comments()->findAll();

        $userDetail = Zendesk::users()->find($submitterId);
        // if ($this->isMobileDevice()) {
        //     return view('buyer/mobile/ticket-detail', ['ticketDetail' => $ticketDetail, 'ticketcomments' => $ticketcomments->comments, 'userDetail' => $userDetail->user]);
        // } else {
            return view('buyer/zendesk/ticket-detail', ['ticketDetail' => $ticketDetail, 'ticketcomments' => $ticketcomments->comments, 'userDetail' => $userDetail->user]);
        // }
    }

    public function updateStoreTicket($id) {
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
        }
        $data = array();
        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            if (isset($postedData['message'])) {
                $data['comment']['body'] = $postedData['message'];
                $data['comment']['author_id'] = $userData->zendesk_user_id;
            }
            //else if (isset($postedData['status'])) {
            $data['status'] = "open";
            //}
            Zendesk::tickets()->update($id, $data);
            $this->_request->session()->flash('SuccessMessage', 'Ticket  has been updated successfully.');
            return redirect()->back();
        }
    }

    public function updateUserTicket($id) {
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
        }
        $data = array();
        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            if (isset($postedData['message'])) {
                $data['comment']['body'] = $postedData['message'];
                $data['comment']['author_id'] = $userData->zendesk_user_id;
            }
            //else if (isset($postedData['status'])) {
            $data['status'] = "open";
            //}
            Zendesk::tickets()->update($id, $data);
            $this->_request->session()->flash('SuccessMessage', 'Ticket  has been updated successfully.');
            return redirect()->back();
        }
    }

    public function updateTicketStatus($id, $status) {
        $data = array();
        if (isset($status)) {
            $data['status'] = $status;
            Zendesk::tickets()->update($id, $data);
            $this->_request->session()->flash('SuccessMessage', 'Ticket  has been updated successfully.');
            return redirect()->back();
        }
    }

}
