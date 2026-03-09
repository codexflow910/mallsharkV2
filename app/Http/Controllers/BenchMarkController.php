<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\BenchMark\Benchmarkaccess;

class BenchMarkController extends Controller
{
    private $bmobj;
    private $_request;

    public function __construct(Request $request)
    {

        $this->bmobj = new Benchmarkaccess();
        $this->_request = $request;
    }

    // Get Subscribed Users
    public function getEmailSubscribedUser()
    {
        $users = $this->bmobj->getEmailSubscriptionUsers();

        try {
            foreach ($users as $user) {

                $name = $user['name'];
                $nameParts = explode(' ', $name);

                if (count($nameParts) == 1) {
                    $first_name = $nameParts[0];  // "name"
                    $last_name = "";  // No last name provided
                } else {
                    $first_name = $nameParts[0];
                    $last_name = $nameParts[1];
                }

                $response =  $this->bmobj->addBenchmarkContactDetails($user['email'], $first_name, $last_name);
            }
        } catch (\Throwable $th) {
            $th->getMessage();
        }

        echo 'Data: <pre>' . print_r($response, true) . '</pre>';
        die();
    }



    public function createContentListBenchMark($name)
    {
        $description = "This list is created for subscribed users of MallShark.";
        $response  = $this->bmobj->createContactListBenchMark($name, $description);
        $responseData = json_decode($response, true);
        $id = $responseData['Response']['Data']['ID'];
        echo 'Content list created with id ' . $id;
        return $id;
    }

    public function getContentList()
    {
        $response  = $this->bmobj->getBenchMarkContacts('ACTIVE', 1, 10, 'Name', 'ASC');
        echo 'Data: <pre>' . print_r($response, true) . '</pre>';
        die();
    }

    public function deteleBenchMarkContentList($listId)
    {
        //$listId = '20385611';
        try {
            $response  = $this->bmobj->deleteContactList($listId);
            return 'Content List Deleted Successfully';
        } catch (\Throwable $th) {
            $th->getMessage();
        }
    }

    public function getContentListAll()
    {
        try {
            $response = $this->bmobj->getContentLists();
        } catch (\Throwable $th) {
            $th->getMessage();
        }
        echo 'Data: <pre>' . print_r($response, true) . '</pre>';
        die();
    }

    public function sendEmailToUsers(){
        try {
            $name = 'Noman Mustafa';
            $subject = 'Mallshark email';
            $fromName = 'NOMAN';
            $fromEmail = 'noman@mindblazetech.com';
            $replyEmail = 'noman@mindblazetech.com';
            $version = '1.0';
            $contactLists = 'nomandeveloper6636@gmail.com';
            $response = $this->bmobj->sendBenchmarkEmail($name, $subject, $fromName, $fromEmail, $replyEmail, $version, $contactLists);
        } catch (\Throwable $th) {
            $th->getMessage();
        }
        echo 'Data: <pre>' .print_r($response,true). '</pre>'; die();
    }

    public function emailSchedule(){

        try {
            $id = '23547293';
            $scheduleDate = '22 june 2023';
            $timeZone = 'IST';

            $response = $this->bmobj->scheduleBenchmarkEmail($id, $scheduleDate,$timeZone);
        } catch (\Throwable $th) {
            $th->getMessage();
        }
        echo 'Data: <pre>' .print_r($response,true). '</pre>'; die();
    }

    public function testEmailSend($email)
    {
        $email = 'noman@mindblazetech.com';
        try {
            $response = $this->bmobj->sendTestEmail($email);
        } catch (\Throwable $th) {
            $th->getMessage();
        }
        echo '$response: <pre>' . print_r($response, true) . '</pre>';
        die();
    }

    public function getEmailDetailById($id)
    {
        $id = '23545884';
        try {
            $response = $this->bmobj->getBenchmarkEmail($id);
        } catch (\Throwable $th) {
            $th->getMessage();
        }
        echo '$response: <pre>' . print_r($response, true) . '</pre>';
        die();
    }
}
