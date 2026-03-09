@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'User Wallet')
<section class="user-profile-mbt  kingdom-page">
    <div class="tabs-data">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="d-flex justify-content-between">
                        <div class="hd-bt">  <h1></h1></div>
                        <div class="add-pr-lnk">
                            <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/user-dashboard')}}">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-content">
                <?php if ($userType == 'current') { ?>
                    <div id="wallet-tab"  class="tab-pane  in mt-30 active show">
                        <div class="w100p">
                            <div class="bxst">
                                <div class="row">
                                    <div class="col-md-6">
                                        <span style="line-height:38px"><span>USDC Available: {{number_format($currentVolum,2)}}</span> <span class="clrgry">&nbsp;&nbsp;|&nbsp;&nbsp;</span> <span>USDC Value: $0.1</span></span></div>
                                    <div class="col-md-6 text-right"><a class="btn btn-primary" style="color:#fff" data-toggle="modal" data-target="#add-zit">Add More ZIT</a> </div>
                                </div>
                            </div>
                            <div class="table-responsive" style="margin-top:30px">
                                <div class="form-group col-md-3 pull-right">

                                    <label for="sel1">Sort list:</label> <select class="form-control" id="sort_trans">
                                        <option value="">Please select</option>
                                        <option value="purchased Icon" <?php
                                        if (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'purchased Icon') {
                                            echo "selected";
                                        }
                                        ?>>Zanimal purchase</option>
                                        <option value="icon purchased" <?php
                                        if (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'icon purchased') {
                                            echo "selected";
                                        }
                                        ?>>Zanimal sold</option>
                                        <option value="Products purchased" <?php
                                        if (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'Products purchased') {
                                            echo "selected";
                                        }
                                        ?>>Product purchases</option>
                                        <option value="cashback" <?php
                                        if (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'cashback') {
                                            echo "selected";
                                        }
                                        ?>>USDC Back</option>
                                    </select>
                                </div>
                                <h3>Transaction History</h3>

                                <?php
                                if (count($transactions) > 0) {
                                    ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">Transaction Id</th>


                                                <th scope="col">Transaction</th>
                                                <th scope="col">Credit</th>
                                                <th scope="col">Debit</th>
                                                <th scope="col">Current ZIT</th>
                                                <th scope="col">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($transactions as $trans) {
                                                if ($trans->created_at != null) {

                                                    $created_at = $trans->created_at;
                                                    $created_at = explode(' ', $created_at);
                                                    $created_at = date('m/d/Y', strtotime($created_at[0]));
                                                    if ($trans->wtransac_type == 'credit') {
                                                        $credit = $trans->wtrans_amount;
                                                        $credit = number_format($credit, 2);
                                                        $debit = '-';
                                                    }
                                                    if ($trans->wtransac_type == 'debit') {
                                                        $debit = $trans->wtrans_amount;
                                                        $debit = number_format($debit, 2);
                                                        $credit = '-';
                                                    }
                                                    $amount = $trans->wtrans_amount;
                                                    $amount = number_format($amount, 2);
                                                    $current = $trans->wcurrent_amount;
                                                    $user = $trans->wuser_id;
                                                    $trans_id = $trans->wtrans_id;
                                                    $wtransaction_info = $trans->wtransaction_info;
                                                    ?>
                                                    <tr>
                                                        <td>{{$trans_id}}</td>
                                                        <td>{{$wtransaction_info}}</td>
                                                        <td>{{$credit}}</td>
                                                        <td>{{$debit}}</td>
                                                        <td>{{$current}}</td>
                                                        <td>{{$created_at}}</td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <?php
                                    $q = [];
                                    if (isset($_GET['transaction_type'])) {
                                        $q['transaction_type'] = $_GET['transaction_type'];
                                    }
                                    echo $transactions->appends($q)->links('pagination.defaultpagination');
                                    ?>

                                </div>
                            </div>
                        </div><!-- menu2 -->
                    <?php } else { ?>
                        <br>
                        <div class=" alert alert-danger alert-success-mbt col-md-12 mt-30" role="alert"> No record found</div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>


    <div class="modal fade zitt-add" id="add-zit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title zit-title" id="">Add USDC</h5>
                    <span class="success-message-zit">Wallet credited successfully</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="zit-add-container">
                        <form class="zit-form">
                            <div class="form-group">
                                <input type="text" onkeypress=" return isNumber(event);" maxlength="14" name="zit_amount" class="form-control" placeholder="" id="zit-adding-input" >
                            </div>
                            <button type="button" class="btn btn-primary btn-simple sendButton" id="add-zitt-btn">Add</button>
                        </form>

                        <div class="add-more-zitt">
                            <button type="button" class="btn btn-primary btn-simple" id="add-more-zitt-btn" >Add More ZIT</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    jQuery(document).ready(function () {

        jQuery('.sendButton').attr('disabled', true);
        jQuery('#zit-adding-input').keyup(function () {

            if (jQuery(this).val().length == 0 || jQuery(this).val() == 0) {
                jQuery('.sendButton').attr('disabled', true);
            } else
            {
                jQuery('.sendButton').attr('disabled', false);

            }
        })

        jQuery('#add-zitt-btn').on('click', function (event) {
            if (jQuery('#zit-adding-input').val() != '') {
                jQuery('.zit-form,.zit-title').hide();
                jQuery.ajax({
                    url: "/add-zit",
                    method: 'POST',
                    type: 'POST',
                    data: 'amount=' + jQuery('#zit-adding-input').val(),
                    success: function (res) {
                        if (res == 'yes') {
                            jQuery('.add-more-zitt,.success-message-zit').fadeIn(1000);
                            //swal("Wallet credited successfully");
                        }
                    }
                });
            }
        });
        jQuery('#add-more-zitt-btn').on('click', function (event) {
            jQuery('.sendButton').attr('disabled', true);
            jQuery('#zit-adding-input').val('');
            jQuery('.zit-form,.zit-title').fadeIn(1000);
            jQuery('.add-more-zitt,.success-message-zit').hide();
        });
    });
    $(document).on("change", "#sort_trans", function () {
        var currentVal = $(this).val();
        var baseUrl = $("#site_url").val();
        var url = baseUrl + '/wallet?transaction_type=' + currentVal;
        window.location.href = url;
    });
    function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57)) {
            return false;
        }
        return true;
    }
</script>
@endsection
