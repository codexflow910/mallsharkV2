@extends('layouts.new-main')
@section('content')
@section('title', 'Store Dashboard')
<main>
    @include('Store.sidebar')
    <style>
        .noteactive {
            position: absolute;
            z-index: 9999;
            width: 100%;
            height: 100%;
            left: 0px;
            right: 0px;
        }

        html body .switch-buttonk input {
            position: static !important;
            z-index: 88;
        }

        .alert {
            margin-top: 0px;
            margin-bottom: 30px;
            position: absolute;
            width: auto;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
            z-index: 999;
            top: 142px;
        }

    </style>
    <?php
    // print_rr($storeData);
    ?>
    <div class="dshbrdin store-dashboard">
        <div class="container">
            <div class="row">
                
                <?php
                if (!$stripeConnectedAccount) {
                    $activateMessage = false;
                    ?>
                    <div style="
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    padding: 20px;
                    float: none;
                    clear: both;
                    display: block;
                    width: 100%;
                    margin-bottom: 40px;
                    text-align: CENTER;
                    ">Registering with stripe is necessary for you to receive payments and take your store online. <br /> Please visit <a href="https://dashboard.stripe.com/register" target="_blank">stripe</a> to create your account. </div>
                <div class="view-al static-position mb-15 mt-10" style="margin:auto">
                    <a href="{{$connectAccount}}" class="button btn" style="background: #34a08d;
                       color: #fff;">Connect Your stripe Account</a>
                </div>
                <?php
            }
            if ($stripeConnectedAccount != false) {
                echo '<div style="
                     border: 1px solid #ddd;
                     border-radius: 6px;
                     padding: 20px;
                     float: none;
                     clear: both;
                     display: block;
                     width: 100%;
                     margin-bottom: 40px;
                     text-align: CENTER;
                     ">Account Status : connected<br> You must fully activate your account to start receving  payments for details <br /> please consult <a href="https://stripe.com/docs/account" target="_blank">https://stripe.com/docs/account</a></div>';
            }
            ?>
        </div>




    </div>
</div>

</main>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load('current', {'packages': ['bar']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    var data = google.visualization.arrayToDataTable([
        ['Month', 'Sales'],
        ['Jan', <?php echo isset($arr[1]) ? $arr[1] : 0; ?>],
        ['Feb', <?php echo isset($arr[2]) ? $arr[2] : 0; ?>],
        ['Mar', <?php echo isset($arr[3]) ? $arr[3] : 0; ?>],
        ['Apr', <?php echo isset($arr[4]) ? $arr[4] : 0; ?>],
        ['May', <?php echo isset($arr[5]) ? $arr[5] : 0; ?>],
        ['June', <?php echo isset($arr[6]) ? $arr[6] : 0; ?>],
        ['Jul', <?php echo isset($arr[7]) ? $arr[7] : 0; ?>],
        ['Aug', <?php echo isset($arr[8]) ? $arr[8] : 0; ?>],
        ['Sep', <?php echo isset($arr[9]) ? $arr[9] : 0; ?>],
        ['Oct', <?php echo isset($arr[10]) ? $arr[10] : 0; ?>],
        ['Nov', <?php echo isset($arr[11]) ? $arr[11] : 0; ?>],
        ['Dec', <?php echo isset($arr[12]) ? $arr[12] : 0; ?>]
    ]);

    var options = {
        width: 1120,
        is3D: true,
        // colors: ['#34a08d'],
        colors: ['#34a08d'],
        chart: {
            title: 'Yearly Sales Report',
            // subtitle: 'Sales, Expenses, and Profit: 2016-2017',
        }
    };

    var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

    chart.draw(data, google.charts.Bar.convertOptions(options));
}
</script>
<script>
    var message = "";
    $('.checked-store').on('click', function () {

        checked = $('input', this).is(':checked');
        res = {}

        if (checked == true) {
            res['store_status'] = "2";
            message = "Store has been activated successfully";
        } else {
//            var rep = confirm("Are you want to sure to deactivate your store.");
//            if (rep == false)
//                return false;
            res['store_status'] = "0";
            message = "Store has been deactivated successfully";
        }


        sendreq(res, 'store-status', 'change', 'callbacksutatus');
        $('span.check-status').text(checked ? 'Activeted' : 'Deactive');
    });
    //    $(".noteactive").on('click',function(){
    //         $("#successMessage").html(message);
    //        $("#successMessage").show();
    //    });
    function callbacksutatus() {
        $("#successMessage").html(message);
        $("#successMessage").show();
        setTimeout(function () {
            jQuery("#successMessage").fadeOut('slow')
        }, 3000);
    }

</script>
@endsection
