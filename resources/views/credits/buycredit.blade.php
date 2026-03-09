@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Buy Credits')
@include('vendor.sidebar')
<main>
<section>
    <div class="container buy-credits-container">
        <div class="row">
            <div class="col-sm-12">
                <div class="credits-avail br-all mt-70 text-center pt-60 pb-60 br-blue">
                    <h2 class="mb-0">Credits Available: <span class="blue-text"><?php echo $userAvailableCredits; ?></span> </h2>
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
                            <p class="blue-text mb-0"><strong><?php echo $cs['plan_points']; ?> Credits</strong> </p>
                            <h2 class="mt-20 mb-20">USDC <?php echo $cs['plan_price']; ?></h2>
                            <a href="<?php echo URL::to('/buy-credits/'.$cs['credit_plan_id']); ?>" class="btn btn-primary text-white fnt-14">Buy Credits</a>
                        </div><!-- credits-cards-box -->
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div><!-- buy-credits-container -->
</section>
</main>

<!-- Content Ends-->
@endsection