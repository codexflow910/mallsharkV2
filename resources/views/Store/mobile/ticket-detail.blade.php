@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Ticket')
<!-- Content Starts -->
<main>
    @include('Store.mobile.sidebar')
    <?php
    // print_rr($ticketcomments);
    ?>
    <section>
        <div class="ticket-detail">
            <div class="container">
                <div class="site-inner">
                    <div class="support-landing">
                        <div class="row">
                            <div class="col-sm-12">
                                <h2 class="support-heading text-center colr-orange">Support &amp; Help</h2>
                                @if(Session::has('SuccessMessage'))
                                <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                                    {{ Session::get('SuccessMessage') }}
                                    <script>
                                        setTimeout(function () {
                                            jQuery("#success_message").fadeOut('slow');
                                        }, 3000);
                                    </script>
                                </div>
                                @endif
                                <div class="support-box">
                                    <div class="ticket-panel">
                                        <div class="tab-content">
                                            <div  class="support-help-container">
                                                <div class="original-ticket">

                                                    <div class="flex-container ticket-box">
                                                        <div class="flex-box width75 Aligner-item">
                                                            <div class="ticket-body">

                                                                <h4><span>
                                                                        <i class="fa fa-support" aria-hidden="true"></i>
                                                                    </span><span>Ticket #<?php echo $ticketDetail->id; ?>:</span> <?php echo $ticketDetail->subject; ?>.</h4>
                                                            </div>
                                                        </div>
                                                        <div class="flex-box width15 text-center">
                                                            <a href="{{URL::to('/store/tickets')}}">Back to List</a>
                                                        </div>
                                                    </div>


                                                </div>
                                                <ul class="ticketing-listing">
                                                    <?php
                                                    if (!empty($ticketcomments)) {
                                                        foreach ($ticketcomments as $tc) {
                                                            ?>
                                                            <li>
                                                                <div class="flex-container ticket-box">
                                                                    <div class="flex-box width10">
                                                                        <div class="ticket-logo Aligner-item">

                                                                            <span class="support-logo">
                                                                                <?php
                                                                                $imgUrl = "";
                                                                                $userDetail = getAendeskUserInfo($tc->author_id);
                                                                                if (!empty($userDetail->photo)) {
                                                                                $userDetail = $userDetail->photo;
                                                                                $imgUrl = $userDetail->content_url;
                                                                                ?>

                                                                                <img src="<?php echo $imgUrl; ?>"
                                                                                     class="img-fluid" alt="{{$userDetail->name}}" title="{{$userDetail->name}}" >
                                                                                <?php } else{ ?>
                                                                                <span><?php echo $userDetail->name; ?></span>
                                                                                <?php } ?>

                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-box width75 Aligner-item">
                                                                        <div class="ticket-body">
                                                                            <h4><?php
                                                                        $userDetail = getAendeskUserInfo($tc->author_id);
                                                                      //  echo $userDetail->name;
                                                                                ?> <span class="ticket-date"><?php
                                                                                $timestamp = strtotime($tc->created_at);
                                                                                $date = date('d-M-Y', $timestamp);
                                                                                echo $date;
                                                                                ?></span></h4>
                                                                            <p><?php
                                                                        echo $tc->body;
                                                                                ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-box width15 text-center">
                                                                        <a href="javascript:;" class="replay-button">Reply</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                            </div><!-- tab-content -->
                                            <form action="<?php echo URL::to('store/update-ticket/' . $ticketDetail->id) ?>" id="update_ticket" method="Post">
                                                <div class="replay-comment-box">
                                                    {{ csrf_field() }}
                                                    <textarea name="message" id="message" class="form-control"></textarea> 
                                                    <div class="text-right mt-10">
                                                        <button type="submit"> Send</button>
                                                    </div>
                                                </div><!-- replay-comment-box -->
                                            </form>

                                            <div class="original-ticket-box hide-div">
                                                <div class="ticket-box-mbt">
                                                    <div class="strp-head-mbt"><span>Original Ticket</span></div>
                                                    <div class="flex-container ticket-box">
                                                        <div class="flex-box width10">
                                                            <div class="ticket-logo Aligner-item">
                                                                <span class="support-logo">
                                                                    <img src=" {{ asset('assets/images/tman-1.png',env('HTTPS_STATUS')) }}" alt="image" class="img-fluid">
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-box width75 Aligner-item">

                                                            <h4>To S6Net Support, <span class="ticket-date">12 March 2017</span></h4>
                                                            <!-- <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec fringilla turpis egestas mauris scelerisque porttitor. Sed in fringilla augue. Integer congue, tellus at feugiat tincidunt, ligula nibh vehicula ex, nec ornare sapien tortor quis dolor</p> -->

                                                        </div>

                                                    </div>
                                                </div>                                
                                            </div><!-- original-ticket-box -->
                                        </div>
                                    </div>
                                    <!-- /.ticket-panel -->
                                </div>
                                <!-- /.support-box -->
                            </div>
                        </div>
                    </div>
                    <!-- Modal -->

                </div>
            </div>
        </div>
        <!-- tickets-listing -->
    </section>
    <script>

        $(".replay-button").click(function () {
            $('html,body').animate({
                scrollTop: $(".replay-comment-box").offset().top},
                    'slow');
        });
        $(document).ready(function () {
            jQuery.validator.addMethod("lettersonly", function (e, r) {
                return this.optional(r) || /^[a-z ]+$/i.test(e)
            }, "Only letters are allowed."),
                    $("#update_ticket").validate({
                errorElement: "span",
                rules: {
                    message: {required: true, maxlength: 500},

                },
                messages: {
                    message: {required: "Description is required."},
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.closest('.replay-comment-box'));
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });
        });
    </script>


    @endsection