@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Buy Credits')
@include('buyer.sidebar')

<section>

<!-- Trigger the modal with a button -->

    <style>
        .buy-credits-container .credits-cards-box h2 {
            font-size: 30px;
            color: #6b6b6b;
            font-weight: 700;
        }

    </style>
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <br/>
                    

                </div>
                <div class="modal-body">
                    <?php
                    if(count($pointHistory)>0){
                        foreach($pointHistory as $pr){
                            echo 'Points: <span class="zit-price">USDC '.number_format($pr->plan_points,2).'</span>';
                            echo '<br />';
                            echo 'amount:' .$pr->amount;
                            echo '<br />';
                            echo 'date:' .$pr->created_at;
                            echo '<br />';
                            echo '<hr />';
                        }
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
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
    @if(Session::has('ErrorMessage'))
        <div class="alert alert-danger alert-success-mbt" id="error_message" role="alert">
            {{ Session::get('ErrorMessage') }}
            <script>
                setTimeout(function () {
                    jQuery("#error_message").fadeOut('slow');
                }, 3000);
            </script>
        </div>
    @endif
    <div class="container buy-credits-container">
        <div class="row">
            <div class="col-sm-12">
                <div class="credits-avail br-all mt-70 text-center pt-60 pb-60 br-blue">
                    <h2 class="mb-0">Credits Available: <span
                                class="blue-text"><?php echo $userAvailableCredits; ?></span></h2>


                    <?php  if(count($pointHistory)>0){ ?>
                    <button type="button" class="btn mt-10" data-toggle="modal" data-target="#myModal">
                        Purchase History
                    </button>
                    <?php } ?>

                </div>
            </div>
        </div>

        <?php
        if (!empty($credits)) {
        ?>
        <div class="row mt-30">
            <?php foreach ($credits as $cs) { ?>
            <div class="col-md-4">
                <div class="card credits-cards-box text-center pb-30 pt-20 radious-3">
                    <p class="blue-text mb-0"><strong><?php echo $cs['plan_points']; ?> Credits</strong></p>
                    <h2 class="mt-20 mb-20">USDC <?php echo $cs['plan_price']; ?></h2>
                    <a href="<?php echo URL::to('/purchase-user-credits/' . $cs['credit_plan_id']); ?>"
                       class="btn btn-primary text-white fnt-14">Buy Credits</a>
                </div><!-- credits-cards-box -->
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div><!-- buy-credits-container -->
</section>


<!-- Content Ends-->
@endsection