<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Mailgun\Mailgun;
class MailgunController extends Controller
{
    private $mailgun;
    private $domain;

    public function __construct() {
        $mailgunSecret = config('services.mailgun.secret');
        $mailgunDomain = config('services.mailgun.domain');
        if (!$mailgunSecret || !$mailgunDomain) {
            throw new \Exception('Mailgun API credentials are not set in the .env file.');
        }
        $this->mailgun = Mailgun::create($mailgunSecret);
        $this->domain = $mailgunDomain;
    }

    public function SendMailgunMail($params) {
        try{
            return $this->mailgun->messages()->send($this->domain, $params);
        }
        catch(Exception $e){
            throw new Exception($e->getMessage());
        }            
    }
    public function sendActivateTemplate() {
        return view('mail.activeteProduct');

    }
    public function sendAproveTemplate(){
        return view('mail.approvedProduct');
    }
    public function sendRestoredTemplate(){
        return view('mail.restoredProduct');

    }
    public function sendDeleteTemplate(){
        return view('mail.deletedProduct');
    }
    public function sendRejectTemplate(){
        return view('mail.rejectProduct');

    }
    public function sendDeactivateTemplate(){
        return view('mail.deactivateProduct');

    }
    public function sendOTP($params){
        try{

            return $this->mailgun->messages()->send($this->domain, $params);
        }
        catch(Exception $e){
            // dd($e->getMessage());
        } 
    }
}
