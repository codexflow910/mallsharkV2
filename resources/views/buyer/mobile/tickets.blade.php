@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'All tickets')
<!-- Content Starts -->
<main>
    @include('buyer.sidebar')
    <section>
        <?php
        if (!empty($allTickets)) {
//            print_rr(($allTickets->requests));
//           exit;
            ?>
            <div class="tickets-listing">

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
                                            <ul class="nav nav-tabs nav-justified">
                                                <li class="nav-item"><a  class="nav-link active" data-toggle="tab" href="#open-tickets" aria-expanded="false" > Open Tickets</a></li>
                                                <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#closed-tickets" aria-expanded="true">Closed Tickets</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div id="open-tickets" class="tab-pane active">

                                                    <ul class="ticketing-listing">
                                                        <?php
                                                        foreach ($allTickets->requests as $at) {
                                                            //print_rr($at);
                                                            if ($at->status == "solved") {
                                                                continue;
                                                            }
                                                            //  if ($at->status = 'open') {
                                                            ?>    
                                                            <li>
                                                                <div class="flex-container ticket-box">
                                                                    <div class="flex-box width10">
                                                                        <div class="ticket-category">
                                                                            <span> <?php
                                                                                if (!empty($at->type)) {
                                                                                    echo $at->type;
                                                                                }
                                                                                ?></span>
                                                                        </div>
                                                                        <div class="ticket-logo Aligner-item">
                                                                            <span>
                                                                                <i class="fa fa-support" aria-hidden="true"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-box width75 Aligner-item">
                                                                        <h4><a href="<?php echo URL::to('user/ticket-detail/' . $at->id); ?>"><span>Ticket #<?php echo $at->id; ?>:</span><?php echo $at->subject; ?></a></h4>
                                                                        <h5><?php
                                                                            $timestamp = strtotime($at->created_at);
                                                                            $date = date('d-M-Y', $timestamp);
                                                                            echo $date;
                                                                            ?></h5>
                                                                    </div>
                                                                    <div class="flex-box width15">
                                                                        <a href="<?php echo URL::to('/user/mark-ticket/' . $at->id . "/solved"); ?>" class="btn btn-primary text-white" id="">
                                                                            <span class="small-screen-btn">Mark as  resolve</span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <?php
                                                            //}
                                                        }
                                                        ?>
                                                    </ul>
                                                </div><!-- open-tickets -->
                                                <div id="closed-tickets" class="tab-pane ">
                                                    <ul class="ticketing-listing">
                                                        <?php
                                                        foreach ($allTickets->requests as $at) {
                                                            if ($at->status == "solved") {
                                                                ?>    
                                                                <li>
                                                                    <div class="flex-container ticket-box">
                                                                        <div class="flex-box width10">
                                                                            <div class="ticket-category">
                                                                                <span> Technical</span>
                                                                            </div>
                                                                            <div class="ticket-logo Aligner-item">
                                                                                <span>
                                                                                    <i class="fa fa-support" aria-hidden="true"></i>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex-box width75 Aligner-item">
                                                                            <h4><a href="javascript:;"><span>Ticket #<?php echo $at->id; ?>:</span><?php echo $at->subject; ?></a></h4>
                                                                            <h5><?php
                                                                                $timestamp = strtotime($at->created_at);
                                                                                $date = date('d-M-Y', $timestamp);
                                                                                echo $date;
                                                                                ?></h5>
                                                                        </div>
                                                                        <div class="flex-box width15">
                                                                            <a href="<?php echo URL::to('/user/mark-ticket/' . $at->id . "/open"); ?>" class="btn btn-primary text-white" id="">
                                                                                <span class="small-screen-btn">Reopen</span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="cant-find-answer col-md-4 col-md-offset-4">
                                                <h5>
                                                    <a href="javascript:;" id="file-ticket1" data-toggle="modal" data-target="#submit-ticketform" class="btn btn-default topbtn1">Add new Ticket</a>
                                                </h5>
                                            </div>
                                        </div>
                                        <!-- /.ticket-panel -->
                                    </div>
                                    <!-- /.support-box -->
                                </div>
                            </div>
                        </div>
                        <!-- Modal -->
                        <div id="submit-ticketform" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                        <h4 class="modal-title">Your Ticket</h4>
                                        <h5 class="response-div"></h5>
                                    </div>
                                    <div class="modal-body">
                                        <form id="create_ticket" action="<?php echo URL::to('user/ticket/create'); ?>" method="POST" id="submit_ticket_form">
                                            <div class="form-group">
                                                {{ csrf_field() }}
                                                <label for="email">Ticket type:</label>
                                                <select name="type" id="type" class="form-control">
                                                    <option value="">Please select</option>
                                                    <option value="problem">Problem</option>
                                                    <option value="incident">incident</option>
                                                    <option value="question">Question</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" name="action" value="zendesk_add_ticket">
                                                <label for="email">Subject:</label>
                                                <input name="subject" type="text" class="form-control" id="subject">
                                            </div>
                                            <div class="form-group">
                                                <label for="pwd">Your Ticket Description:</label>
                                                <textarea name="message" class="form-control large-field" id="message"></textarea>
                                            </div>
                                            <div class="submit-btn-pop clearfix">
                                                <button type="submit" class="btn btn-default">Submit Your Ticket</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.support-landing -->
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- tickets-listing -->
    </section>
    <script>
        $(document).ready(function () {
            jQuery.validator.addMethod("lettersonly", function (e, r) {
                return this.optional(r) || /^[a-z ]+$/i.test(e)
            }, "Only letters are allowed."),
                    $("#create_ticket").validate({
                errorElement: "span",
                rules: {
                    subject: {required: true, maxlength: 50},
                    message: {required: true, maxlength: 500},
                    type: {required: true}
                },
                messages: {
                    subject: {required: "Subject  is required."},
                    message: {required: "Description  is required."},
                    type: {required: "please select at least one type."},
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.closest('.form-group'));
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });
        });
    </script>


    @endsection