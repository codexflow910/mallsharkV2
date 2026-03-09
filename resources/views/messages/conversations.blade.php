@extends('layouts.chat')

@section('content')

<div class="chat-history">
    <ul id="talkMessages">
        <?php
        // $userid = Session::get('userData')['user_id'];
        $userid = request()->route('id');
        $userData = getUserByIdchat($userid);
//        if (!empty($storeData)) {
//            $username = $storeData->store_name;
//        } else {
//            $userData = getUserByIdchat($userid);
//            $username = $userData->name;
//        }
         $username = $userData->name;
        ?>
        <?php
        foreach ($messages as $message) {
            if ($message->user_id != $userid) {
                ?>

                <li class="clearfix" id="message-{{$message->id}}">
                    <div class="message-data align-right">
                        <span class="message-data-time" >{{$message->humans_time}} ago</span> &nbsp; &nbsp;
                        <span class="message-data-name" >me</span>
        <!--                            <a href="#" class="talkDeleteMessage" data-message-id="{{$message->id}}" title="Delete Message"><i class="fa fa-close"></i></a>-->
                    </div>
                    <div class="message other-message float-right">
                        {{$message->message}}
                    </div>
                </li>
            <?php } else {
                ?>
                <li id="message-{{$message->id}}">
                    <div class="message-data">
<!--                        <span class="message-data-name">
                            </span>-->
                        <span class="message-data-time">{{$message->humans_time}} ago</span>

                    </div>
                    <div class="message my-message">
                        {{$message->message}}
                    </div>
                </li>

                <?php
            }
        }
        ?>
    </ul>

</div> <!-- end chat-history -->

@endsection
