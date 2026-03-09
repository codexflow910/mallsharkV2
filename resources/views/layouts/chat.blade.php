<!DOCTYPE html>
<html >
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{csrf_token()}}">
        <title>Support</title>
        <link rel="stylesheet" href="{{asset('assets/css/reset.css')}}">
        <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css'>
        <link rel="stylesheet" href="{{asset('assets/css/style-chat.css')}}">
        <style>
            body {
                background: #ffffff;
            }
            .container {
                margin: 0 auto;
                width: 490px;
                border-radius: 5px;
            }

            @media only screen and (max-width: 767px) and (min-width: 320px) and (orientation: portrait){
                .container.clearfix.body{
                    margin: 0 auto;
                    width: 320px;
                    border-radius: 5px;                    
                } 
                .chat {
                    width: 320px;

                }



            </style>
        </head>
        <?php
        if (collect(request()->segments())->last() == 'buyer') {
            $userid = request()->route('id');
            if (isset($userid)) {
                $userData = getUserById($userid);
                $username = $userData->name;
            }
        } else if (collect(request()->segments())->last() == 'seller') {
            $userid = request()->route('id');
            if (isset($userid)) {
                $userData = getStoreDataByUserId($userid);
                $username = $userData->store_name;
            }
        }
        ?>
        <body>
            <!--        <div class="header">
                        <div class="container header-brand">
                            <a href="{{url('/home')}}" class="brand">Talk Message</a>
                        </div>
                    </div>-->
            <div class="container clearfix body">
                <div class="chat">
                    <div class="chat-header clearfix">

                        @if(isset($user))
                        <img src="{{@$user->avatar}}" alt="avatar" title="avatar" class="hide-div" />

                        @endif
                        <div class="chat-about">
                            @if(isset($user))
                            <div class="chat-with">{{'Chat with ' . @$username}}</div>
                            @else
                            <div class="chat-with">No Thread Selected</div>
                            @endif
                        </div>
                        <i class="fa fa-star"></i>
                    </div> <!-- end chat-header -->

                    @yield('content')

                    <div class="chat-message clearfix">
                        <form action="" method="post" id="talkSendMessage">
                            <textarea name="message-data" id="message-data" placeholder ="Type your message" rows="3"></textarea>
                            <input type="hidden" name="_id" value="{{@request()->route('id')}}">
                            <button type="submit">Send</button>
                        </form>

                    </div> <!-- end chat-message -->
                </div> <!-- end chat -->
            </div> <!-- end container -->
            <script>
                var __baseUrl = "{{url('/')}}"
            </script>
            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
   <!--        <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>--> 
            <script src='https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/3.0.0/handlebars.min.js'></script>
            <script src='https://cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js'></script>
            <script src="{{asset('assets/js/talk.js')}}"></script>
            <script>
                var show = function (data) {
                    alert(data.sender.name + " - '" + data.message + "'");
                }
                var msgshow = function (data) {
                    var html = '<li id="message-' + data.id + '">' +
                            '<div class="message-data">' +
                            '<span class="message-data-time">1 Second ago</span>' +
                            '</div>' +
                            '<div class="message my-message">' +
                            data.message +
                            '</div>' +
                            '</li>';
                    $('#talkMessages').append(html);
                }
            </script>
            {!! talk_live(['user'=>["id"=>$userid, 'callback'=>['msgshow']]]) !!}
        </body>
    </html>
