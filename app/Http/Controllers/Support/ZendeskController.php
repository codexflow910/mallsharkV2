<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zendesk;
use Redirect;

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
        return view('support/zendesk/tickets', ['allTickets' => $allTickets]);
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
        $data = array();
        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            if (isset($postedData['message'])) {
                $data['comment']['body'] = $postedData['message'];
            } else if (isset($postedData['status'])) {
                $data['status'] = $postedData['status'];
            }
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

    public function getTicketById($id) {
        $ticketDetail = Zendesk::tickets()->find($id);
        $ticketDetail = $ticketDetail->ticket;
        $submitterId = $ticketDetail->submitter_id;
        $ticketcomments = Zendesk::tickets($id)->comments()->findAll();
        $userDetail = Zendesk::users()->find($submitterId);
        return view('support/zendesk/ticket-detail', ['ticketDetail' => $ticketDetail, 'ticketcomments' => $ticketcomments->comments, 'userDetail' => $userDetail->user]);
    }

    public function deleteTicket($id) {
        Zendesk::tickets()->delete($id);
    }

    public function createUser() {
//       $pr = Zendesk::users()->findAll();
//       print_rr($pr); exit;
      //  $ticketDetail = Zendesk::tickets(['submitter_id'=>364349324331])->findAll();
        //print_rr($ticketDetail); exit;
        
        $pr = Zendesk::users()->create([
        'name' => 'Asif Islam',
        'email' => 'asifislam@midblazatech.com',
//        'phone' => '+1-954-704-6031',
        'role'  => 'end-user',
        //'details' => 'This user has been created with the API.'
        ]);
        print_rr($pr);
        exit;
    }

}
