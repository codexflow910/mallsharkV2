@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'All tickets')
<!-- Content Starts -->
<script src="{{ URL::asset('assets/js/jquery.validate.js')}}"></script>
<main>
    @include('admin.adminsidebar')
    <section>
        <?php
        if (!empty($allTickets)) {
            //print_rr(($allTickets));
            ?>
            <div class="tickets-listing">
                <div class="container">
                    <div class="site-inner">
                        <div class="support-landing">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h2 class="support-heading text-center colr-orange">Support &amp; Help</h2>
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
                                                        foreach ($allTickets->tickets as $at) {

                                                            if ($at->status == "closed") {
                                                                continue;
                                                            }
                                                            //  if ($at->status = 'open') {
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
                                                                    <!--                                                                        <div class="flex-box width15">
                                                                                                                                                <button type="submit" class="btn btn-primary text-white" id="">
                                                                                                                                                    <span class="small-screen-btn">Reopen</span>
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                    -->
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
                                                        foreach ($allTickets->tickets as $at) {
                                                            if ($at->status == "closed") {
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
                                                                        <!--                                                                        <div class="flex-box width15">
                                                                                                                                                    <button type="submit" class="btn btn-primary text-white" id="">
                                                                                                                                                        <span class="small-screen-btn">Reopen</span>
                                                                                                                                                    </button>
                                                                                                                                                </div>-->
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
                                        <form id="create_ticket" action="<?php echo URL::to('/admin/addticket'); ?>" method="POST" id="submit_ticket_form">
                                            <div class="form-group">

                                                <label for="email">Ticket type:</label>
                                                <select name="type" id="type" class="form-control">
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
    $("#create_ticket").validate({
        errorElement: "span",
        rules: {
            subject: {required: true},
            message: {required: true},
        },
        messages: {
            subject: {required: "Subject  is required."},
            message: {required: "Description  is required."},
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