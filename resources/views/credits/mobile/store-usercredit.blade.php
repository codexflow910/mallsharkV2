@extends('layouts.mobile-main')
@section('content')
@section('title', 'User credit')

<main class="purchase-credits-container">
    <!-- Dashboard Starts -->
    @include('Store.mobile.sidebar')
    <!-- Dashboard Ends -->
    <div class="dshbrdin buyer">
        <div class="container">
            <div class="row"> 
                <div class="col-md-12">
                    <div class="wdgtbx border">
                        <h4 class="text-center">Credits Available:<span class="txt-blue"><?php echo $userAvailableCredits; ?></span></h4> 
                        <div class="text-center  small"><span class="red">Note:</span> You can add more credits to feature your products from packages we offer</div>
                        <?php if (count($pointHistory) > 0) { ?>
                            <button type="button" class="btn mt-10 default-mobile-btn" data-toggle="modal" data-target="#myModal">
                                Purchase History
                            </button>
                        <?php } ?>
                    </div>  
                </div>  
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h3 style="margin-bottom: 0px;">Purchase history</h3>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <br/>
                </div>
                <div class="modal-body">
                    <?php
                    if (count($pointHistory) > 0) {
                        foreach ($pointHistory as $pr) {
                            echo 'Points:' . $pr->plan_name;
                            echo '<br />';
                            echo 'amount:<span class="zit-price">USDC' . number_format($pr->amount,2) . '</span>';
                            echo '<br />';
                            echo 'date:' . $pr->created_at;
                            echo '<br />';
                            echo '<hr / style="margin-top: 1rem;margin-bottom: 1rem">';
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
    <!--Credit Packages Starts-->
    <?php
    if (!empty($credits)) {
        ?>
        <div class="container  ">
            <h3>Credit Packages</h3>
            <table class="table table-hover" style="border:1px solid #e9ecef; padding:10px"> 
                <tbody>
                    <?php foreach ($credits as $cs) { ?>
                        <tr>
                            <td scope="row" style="line-height:38px; font-size:14px"><?php echo $cs['plan_points']; ?> Credits</td>
                            <td style="line-height:38px;  font-size:14px">USDC <?php echo $cs['plan_price']; ?></td> 
                            <td class="text-center" style=" background-color:#195efa; font-size:14px" ><a class="txt-white btn btn-primary" style="display:block; font-size:14px" href="<?php echo URL::to('/purchase-store-user-credits/' . $cs['credit_plan_id']); ?>">Buy Credits</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>    
        </div>    
    <?php } ?>
    <!--Credit Packages Ends-->    


</main>
@endsection