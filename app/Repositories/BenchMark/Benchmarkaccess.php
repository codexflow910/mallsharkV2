<?php

namespace App\Repositories\BenchMark;

use App\BaseAccess;
use App\Models\User;

/**
 * Description of Benchmarkaccess
 *
 * @author Noman
 */
class Benchmarkaccess extends BaseAccess
{

    private $benchmark_api;
    private $benchmark_listId;

    public function __construct()
    {
        $this->benchmark_api = env('BenchMark_API'); 
        $this->benchmark_listId = env('BenchMark_listId'); 
    }

     /**
     * Check email exist or not for subscription.
     */
    public function checkEmailSubscription($email)
    {
        try {
                $result = User::where('email', '=', $email)->count();
                if($result == '1'){
                    $checkSubscribed = User::where('email', '=', $email)->where('subscription', '=', '1')->count();
                    if($checkSubscribed > 0 ){
                        return '0';
                    }
                }
               
                if($result > 0){
                    User::where('email', '=', $email)->update(['subscription'=> '1']);
                    return $result;
                }else{
                    
                    return '1';
                }
        } catch (\Throwable $th) {
            $th->getMessage();
        }
    }

    public function getEmailSubscriptionUsers(){
        $result = User::select('name', 'email', 'subscription')->where('subscription', '1')->get()->toArray();
        return $result;
    }

    /**
     * Display a listing of BenchMark Email Contant Details.
     */
    public function addBenchmarkContactDetails($email, $firstName, $lastName) {
        $url = 'https://clientapi.benchmarkemail.com/Contact/'.$this->benchmark_listId.'/ContactDetails';
        $authToken = $this->benchmark_api;
        
        $data = array(
            'Data' => array(
                'Email' => $email,
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'EmailPerm' => '1'
            )
        );
        
        $headers = array(
            'AuthToken: ' . $authToken,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            // Request failed
            $error = curl_error($ch);
            curl_close($ch);
            return "Error: $error";
        } else {
            // Request successful
            curl_close($ch);
            return $response;
        }
    }

    /**
     * Create a list of BenchMark Email Contant.
     */
    public function createContactListBenchMark($name, $description) {
        $url = 'https://clientapi.benchmarkemail.com/Contact/';
    
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
    
        $data = array(
            'Data' => array(
                'Name' => $name,
                'Description' => $description
            )
        );
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }

    /**
     * Get All lists of BenchMark Email Contant.
     */
     public function getBenchMarkContacts($status, $pageNumber, $pageSize, $orderBy, $sortOrder) {
        $url = 'https://clientapi.benchmarkemail.com/Contact/?Status=' . $status . '&PageNumber=' . $pageNumber . '&PageSize=' . $pageSize  . '&OrderBy=' . $orderBy . '&SortOrder=' . $sortOrder;
    
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }
    /**
     * Delete lists of BenchMark Email Contant.
     */
    public function deleteContactList($listIds) {
        $url = 'https://clientapi.benchmarkemail.com/Contact/DeleteList/' . $listIds;
    
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }
    /**
     * Get content list of BenchMark.
     */
    public function getContentLists() {
        $url = 'https://clientapi.benchmarkemail.com/Emails/?PageNumber=' . 1 . '&PageSize=' . 10 ;
    
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }
    /**
     * Send Test Email of BenchMark.
     */
    public function sendTestEmail($emailAddresses) {
        $url = 'https://clientapi.benchmarkemail.com/Emails/23545884/Test';
    
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
    
        $data = array(
            'Emails' => $emailAddresses,
            'Message' => ""
        );
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }

    /**
     * Send Email of BenchMark.
     */
    public function sendBenchmarkEmail($name, $subject, $fromName, $fromEmail, $replyEmail, $version, $contactLists) {
        $url = 'https://clientapi.benchmarkemail.com/Emails/';
        
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
        
        $data = array(
            'Detail' => array(
                'Name' => $name,
                'Subject' => $subject,
                'FromName' => $fromName,
                'FromEmail' => $fromEmail,
                'ReplyEmail' => $replyEmail,
                'Version' => $version,
                'ContactLists' => $contactLists
            )
        );
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        return $response;
    }
    /**
     * Get Send Email Details of BenchMark.
     */
    public function getBenchmarkEmail($id) {
        $url = 'https://clientapi.benchmarkemail.com/Emails/' . $id;
        
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        return $response;
    }
    /**
     * Email Schedule of BenchMark.
     */
    public function scheduleBenchmarkEmail($id, $scheduleDate, $timeZone) {
        $url = 'https://clientapi.benchmarkemail.com/Emails/' . $id . '/Schedule';
        
        $headers = array(
            'AuthToken: ' . $this->benchmark_api,
            'Content-Type: application/json'
        );
        
        $data = array(
            'ScheduleDate' => $scheduleDate,
            'TimeZone' => $timeZone
        );
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        return $response;
    }
    
    
    
    
    
    
}
