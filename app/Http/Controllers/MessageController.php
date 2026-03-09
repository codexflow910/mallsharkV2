<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Nahid\Talk\Facades\Talk;
use Auth;
use View;
use Session;
use DB;

class MessageController extends Controller {

    public function __construct() {

//        Talk::setAuthUserId();
//        View::composer('partials.peoplelist', function($view) {
//            $threads = Talk::threads();
//            $view->with(compact('threads'));
        //});
    }

    public function authUser() {
        $userId = Session::get('userData')['user_id'];
        Talk::setAuthUserId($userId);
        View::composer('partials.peoplelist', function($view) {
            $threads = Talk::threads();
            $view->with(compact('threads'));
        });
    }

    public function chatHistory($id) {
        $receiverId = $id;
        $senderId = (int) Session::get('userData')['user_id'];
        $Userid = (int)$this->authUser();
        $conversations = Talk::getMessagesByUserId($id, 0, 500);
        $user = '';
        $messages = [];
        if (!$conversations) {
            $user = User::find($id);
        } else {
            $user = $conversations->withUser;
            $messages = $conversations->messages;
        }
        //DB::enableQuerylog();
        DB::table('messages')->where('receiver_id', '=', $senderId)->where('user_id', '=', $receiverId)->update(['is_seen' => 1]);
//        dd(DB::enableQuerylog()); exit;
        return view('messages.conversations', compact('messages', 'user'));
    }

    public function ajaxSendMessage(Request $request) {
        if ($request->ajax()) {
            $rules = [
                'message-data' => 'required',
                '_id' => 'required'
            ];

            $this->validate($request, $rules);

            $body = $request->input('message-data');
            $userId = $request->input('_id');

            if ($message = Talk::sendMessageByUserId($userId, $body)) {
                $html = view('ajax.newMessageHtml', compact('message'))->render();
                return response()->json(['status' => 'success', 'html' => $html], 200);
            }
        }
    }

    public function ajaxDeleteMessage(Request $request, $id) {
        if ($request->ajax()) {
            if (Talk::deleteMessage($id)) {
                return response()->json(['status' => 'success'], 200);
            }

            return response()->json(['status' => 'errors', 'msg' => 'something went wrong'], 401);
        }
    }

    public function makeSeen(Request $request) {
        if ($request->ajax()) {
            $receiverId = $request->input('receiver_id');
            $senderId = $request->input('user_id');
            DB::table('messages')->where('receiver_id', '=', $receiverId)->where('user_id', '=', $senderId)->update(['is_seen' => 1]);
        }
    }

    public function tests() {
        dd(Talk::channel());
    }

}
