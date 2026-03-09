<?php

namespace App\Http\Controllers;

use Mandrill;

class MandrillT extends Controller {

    public function __construct() {

        //$this->_homeobj = new Homeaccess();
    }


    public function sendMandrillEmail($subject, $templateSlug, $toEmail, $toCompletename, $varsArray) {

        $mandrill = new Mandrill(env('MANDRILL_SECRET'));
        $message = array(
            'subject' => $subject,
            'from_email' => 'John@shopzit.io',
            'to' => array(array('email' => $toEmail, 'name' => $toCompletename)),
            'global_merge_vars' => array(
                $varsArray
            ),
            'merge_vars' => array(array(
                     'rcpt' => $toEmail,
                    'vars' =>
                    $varsArray
//                        array(
//                            'name' => 'FNAME',
//                            'content' => 'Kamran1'),
//                        array(
//                            'name' => 'LNAME',
//                            'content' => 'Ashraf'
//                        )
        )));




        return $result = $mandrill->messages->sendTemplate($templateSlug, [], $message);
    }

}
