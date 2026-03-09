<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zendesk;
use Redirect;
use Session;
use DB;
class ZendeskController extends Controller {

    private $_request;

    public function __construct(Request $request) {
        $this->_request = $request;
    }

    public function getAlltickets() {
//        $ip = "202.163.113.240";
//        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
//        print_rr($details); //  exit;
//exit;
        $allTickets = Zendesk::tickets()->findAll();
        return view('admin/zendesk/tickets', ['allTickets' => $allTickets]);
    }

    public function getAllUsers() {
        $pr = Zendesk::users()->findAll();
        print_rr($pr);
        exit;
    }

    public function createTicket() {
        $postedData = $this->_request->input();
        $newTicket = Zendesk::tickets()->create([
            'subject' => $postedData['subject'],
            'comment' => [
                'body' => $postedData['message']
            ],
            'priority' => 'normal',
            'type' => $postedData['type']
        ]);
        $this->_request->session()->flash('SuccessMessage', 'Ticket  has been created successfully.');
        return redirect()->back();
    }

    public function updateTicket($id) {
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $zendeskId = $userData->zendesk_user_id;
            $data = array();
            if ($this->_request->isMethod('post')) {
                $postedData = $this->_request->input();
                if (isset($postedData['message'])) {
                    if (empty($zendeskId)) {
                        $zendeskResult = Zendesk::users()->create([
                            'name' => $userData->name,
                            'email' => $userData->email,
                            'role' => 'end-user',
                        ]);
                        if (isset($zendeskResult)) {
                            $zendeskResult = $zendeskResult->user;
                            $zendId = $zendeskResult->id;
                            $zendIdd = strval($zendId);
                            $zendeskId = $zendIdd;
                            $q = DB::table('users')->where('user_id', '=', $userid)->update(['zendesk_user_id' => $zendIdd]);
                        }
                    }

                    $data['comment']['body'] = $postedData['message'];
                    $data['comment']['author_id'] = $zendeskId;
                }
                //else if (isset($postedData['status'])) {
                $data['status'] = "open";
                //}
                Zendesk::tickets()->update($id, $data);
                $this->_request->session()->flash('SuccessMessage', 'Ticket  has been updated successfully.');
                return redirect()->back();
            }
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

    public function getTicketById($id) {
        $ticketDetail = Zendesk::tickets()->find($id);
        $ticketcomments = Zendesk::tickets($id)->comments()->findAll();
        if (empty($ticketcomments) || empty($ticketDetail)) {
            exit("<div class='container d-flex h-100 something-wrong' style='text-align: center;margin-top: 10%;color: #b7b6b6;border: 1px solid #b7b6b6;width: 60%;margin-left: auto;margin-right: auto;'><div class='row justify-content-center align-self-center'><h2>Sorry! Some thing went wrong please try again later</h2></div></div>");
        }
        $ticketDetail = $ticketDetail->ticket;
        $submitterId = $ticketDetail->requester_id;
        //$userDetail = Zendesk::users()->find($submitterId);
        return view('admin/zendesk/ticket-detail', ['ticketDetail' => $ticketDetail, 'ticketcomments' => $ticketcomments->comments]);
    }

    public function deleteTicket($id) {
        Zendesk::tickets()->delete($id);
    }

}
