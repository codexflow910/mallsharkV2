@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Reports')

    @include('Store.mobile.sidebar')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<main class="reports-container-mbt">
    @include('Store.sidebar')
    <div class="dshbrdin store-reports ">
        <div class="container">
            <div class="col-sm-12">              
                <div class="d-flex justify-content-between">
                    <div class="hd-bt">  <h1>Sales Reports </h1></div>  
                    <div class="add-pr-lnk">
                        <a class="btn btn-primary btn-simple small-btn-mbt" href="{{URL::to('/store-dashboard')}}">
                            Back to Dashboard
                        </a>                                        
                    </div>                        
                </div>            
            </div>
            <div class="col-sm-12 mt-25 mb-20 hide-mobile ">
                <!--                <div id="columnchart_material" style="height: 500px;"></div>-->
                <form method="get" action="<?php echo URL::to('store/reports'); ?>">
                    <div class="bxst">
                        <div class="year-mnth-filter row no-gutters">
                            <div class="form-group col-md-2 mb-2">
                                <select class="form-control" name="type" id="">
                                    <option value="yearly" <?php
                                    if (isset($_GET['type']) && $_GET['type'] == 'yearly')
                                        echo "selected";
                                    else
                                        echo "";
                                    ?>>Yearly</option>
                                    <option value="monthly" <?php
                                    if (isset($_GET['type']) && $_GET['type'] == 'monthly')
                                        echo "selected";
                                    else
                                        echo "";
                                    ?>>This Month</option>
                                    <option value="lastmonth" <?php
                                    if (isset($_GET['type']) && $_GET['type'] == 'lastmonth')
                                        echo "selected";
                                    else
                                        echo "";
                                    ?>>Last Month</option>
                                    <option value="weekly" <?php
                                    if (isset($_GET['type']) && $_GET['type'] == 'weekly')
                                        echo "selected";
                                    else
                                        echo "";
                                    ?>>Last 7 Days</option>
                                    <option value="daily" <?php
                                    if (isset($_GET['type']) && $_GET['type'] == 'daily')
                                        echo "selected";
                                    else
                                        echo "";
                                    ?>>Daily</option>
                                </select>
                            </div>                            
                            <div class="form-group col-md-2">
                                <select class="form-control  " name="product_id" id="">
                                    <option value="">All Product</option>
                                    <?php
                                    if (!empty($products)) {
                                        foreach ($products as $ps) {
                                            $sle = "";
                                            if (isset($_GET['product_id']) && $_GET['product_id'] == $ps->product_id) {
                                                $sle = "selected";
                                            }
                                            ?>
                                            <option value="<?php echo!empty($ps->product_id) ? $ps->product_id : ""; ?>" <?php echo $sle; ?>><?php echo!empty($ps->title) ? $ps->title : ""; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="text-right align-self-center cstm-field-mbt">
                                <div class="row mobpad ">
                                    <div class="col-sm-2 pr-5px mobmar">
                                        <label class="lbed-tm">Custom:</label>
                                    </div>
                                    <div class="form-group col-md-4 pl-5px pr-5px mobmar">
                                        <div class="fmr-lbt">
                                            <span class="grbe-fied"><input id="garbage_dpFrom" autocomplete="off" type="text" name="date_to" class="form-control" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ""; ?>" data-calendar="date-from" /></span>
                                            <span class="calendar-date-select"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 pl-5px pr-5px mobmar">
                                        <div class="fmr-lbt">
                                            <span class="grbe-fied"><input id="garbage_dpTo" type="text" name="date_from" class="form-control js-hook--date-to" autocomplete="off"  value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ""; ?>" data-calendar="date-to" /></span>
                                            <span class="calendar-date-select"><i class="fa fa-calendar" aria-hidden="true"></i></span>                                        
                                        </div>
                                    </div> 
                                    <div class="col-md-2 pl-5px pr-5px submit-btn-filter-mbt">
                                        <input type="submit" value="Go" id="sale_submit" class="btn btn-primary btn-simple small-btn-mbt" >
                                        <a href="<?php echo URL::to('store/reports'); ?>" class="clear-filter-mbt">
                                            Reset 
                                        </a>                                        
                                    </div>

                                </div>   
                            </div>
                        </div>
                    </div>
                </form>
                <?php
                $parms = array();
                $parms['type'] = isset($_GET['type']) ? $_GET['type'] : "yearly";
                $parms['date_to'] = isset($_GET['date_to']) ? $_GET['date_to'] : "";
                $parms['date_from'] = isset($_GET['date_from']) ? $_GET['date_from'] : "";
                $parms['product_id'] = isset($_GET['product_id']) ? $_GET['product_id'] : "";
                $userId = Session::get('userData')['user_id'];
                if(empty($userId)){
                    $userId=Request::segment(2);
                }
                $storeData = getStoreDataByUserId($userId);
                $productName = "";
                $textPro = "";
                ?>
                <p style="font-size:18px; font-weight: bold;  margin-top: 24px; margin-bottom: 0px; text-align: center;">
                    <?php
                    if (isset($_GET['product_id']) && $_GET['product_id'] != "") {
                        $storeTotalSale = getProductTotalSale($parms);
                        $productData = getProductById($_GET['product_id']);
                        $productName = !empty($productData[0]->title) ? $productData[0]->title : "";
                        $textPro = " of ";
                    } else {
                        $storeTotalSale = getStoreSale($storeData->store_id, $parms);
                    }
                    ?>
                    <?php
                    if (!empty($_GET['date_to']) && !empty($_GET['date_from'])) {
                        echo $_GET['date_to'] . " to " . $_GET['date_from'] . " ";
                    } else {
                        echo isset($_GET['type']) ? ucfirst($_GET['type']) : "Yearly";
                    }
                    ?>
                    Sale <?php echo $productName . $textPro; ?>: <?php echo isset($storeTotalSale['store_sale']) ? number_format($storeTotalSale['store_sale']) : 0; ?>USDC</p>
                <div class="col-md-12">


                </div>
                <div class="col-sm-12-lrge mt-4">
                    <div id="chart_div" style="height: 320px; width: 100%;" class="brdr-all"></div>
                    <div style="width: 100%;padding: 5px;background: #fff;margin: auto;margin-top: -13px;position: relative;border-left: 1px solid #dee2e6;border-right: 1px solid #dee2e6;"></div>
                    <div id="no-record" class="no-record-foundd"></div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    var
            dates = $("#garbage_dpFrom,#garbage_dpTo ").datepicker({
        showButtonPanel: true,
        closeText: 'Clear', // Text to show for "close" button  
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        onClose: function (dateText, inst) {
            if ($(window.event.srcElement).hasClass('ui-datepicker-close')) {
                document.getElementById(this.id).value = '';
            }

        },
        onSelect: function (dateText, inst) {
            var type = $('#' + inst.id).data('calendar');
            if (type === 'date-from') {
                setTimeout(function () {
                    $('.js-hook--date-to').focus();
                }, 50)

            }
        }

    });
    var _gotoToday = jQuery.datepicker._gotoToday;
    jQuery.datepicker._gotoToday = function (a) {
        var target = jQuery(a);
        var inst = this._getInst(target[0]);
        _gotoToday.call(this, a);
        jQuery.datepicker._selectDate(a, jQuery.datepicker._formatDate(inst, inst.selectedDay, inst.selectedMonth, inst.selectedYear));
    };
//store_id   
    $("#store_select").on('change', function () {
        var currentVal = $(this).val();
        if (currentVal != "") {
            $(".form-check").show();
        } else {
            $(".form-check").show();
        }
        console.log($(this).val());
    });
    $("#sale_submit").on("click", function () {
        if ($("#garbage_dpFrom").val() !== "" && $("#garbage_dpTo").val() == "") {
            $("#garbage_dpTo").css('border-color', 'red');
            return false;
        } else {
            $("#garbage_dpTo").css('border-color', '#c5c5c5');
        }
    });
</script>
<?php
if (!empty($storeSales)) {
    $text = "Yearly Report (" . date("Y") . ")";
    if (!empty($_GET['product_id']) && $_GET['date_to'] != "" && $_GET['date_from'] != "") {
        $text = $_GET['date_to'] . " to  " . $_GET['date_from'] . " Report of " . $productName;
    } else if (!empty($_GET['product_id'])) {
        $text = ucwords($_GET['type']) . " Report of " . $productName;
    } else if (isset($_GET['type']) && $_GET['type'] == 'yearly' && $_GET['date_to'] == "" && $_GET['date_from'] == "") {
        $text = "Yearly Report (" . date("Y") . ")";
    } else if (isset($_GET['type']) && $_GET['type'] == 'monthly' && $_GET['date_to'] == "" && $_GET['date_from'] == "") {
        $text = "This Month Report (" . date("M,Y") . ")";
    } else if (isset($_GET['type']) && $_GET['type'] == 'lastmonth') {
        $date = date("Y-m-d");
        $text = "Last Month (" . date('M,Y', strtotime("-1 month", strtotime($date))) . ")";
    } else if (isset($_GET['type']) && $_GET['type'] == 'weekly' && $_GET['date_to'] == "" && $_GET['date_from'] == "") {
        $text = "Weekly";
    } else if (isset($_GET['type']) && $_GET['type'] == 'daily') {
        $text = "Daily";
    } else if (isset($_GET['date_to']) && isset($_GET['date_from'])) {
        $text = $_GET['date_to'] . " to  " . $_GET['date_from'] . " Report";
    }
    ?>
    <script src="{{ URL::asset('assets/js/canvasjs.min.js',env('HTTPS_STATUS')) }}"></script>
    <script>
    window.onload = function () {

        var chart = new CanvasJS.Chart("chart_div", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "<?php echo $text; ?>",
                fontSize: 20,
                padding: 10,
                fontColor:'#2f2f2f',

            },
            axisX: {
                valueFormatString: "DD MMM",
                fontSize: 50,
                crosshair: {
                    enabled: true,
                    snapToDataPoint: true
                }
            },
            axisY: {
                title: "Sales in USDC",
                crosshair: {
                    enabled: true
                }
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                verticalAlign: "bottom",
                horizontalAlign: "left",
                dockInsidePlotArea: true,
                itemclick: toogleDataSeries
            },
            data: [{
                    type: "line",
                    showInLegend: true,
                    name: "USDC",
                    markerType: "square",
                    xValueFormatString: "DD MMM YYYY",
                    color: "#195dfa",
                    dataPoints: [
    <?php
    for ($i = 0; $i < count($storeSales); $i++) {
        $to = date('Y,m,d', strtotime("-1 month", strtotime($storeSales[$i]['created_at'])));
        if (!isset($storeSales[$i]['total_amount'])) {
            $totalsale = $storeSales[$i]['quantity'] * $storeSales[$i]['price'];
        } else {
            $totalsale = $storeSales[$i]['total_amount'];
        }
        ?>

                            {x: new Date(<?php echo isset($to) ? $to : 0; ?>), y:<?php echo $totalsale; ?>},
    <?php } ?>
                        //                                {x: new Date(2017, 0, 3), y: 9800}
                    ]
                }]
        });
        chart.render();

        function toogleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            chart.render();
        }

    }

    </script>
<?php } else {
    ?>
    <script>
        $("#no-record").html("No record found.");
    </script>
<?php } ?>


@endsection