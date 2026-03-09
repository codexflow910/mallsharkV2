@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Store Reports')
<!-- <link rel="stylesheet" href="{{ URL::asset('assets/css/bootstrap-datetimepicker.min.css' ,env('HTTPS_STATUS')) }}" type="text/css">-->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://harvesthq.github.io/chosen/chosen.jquery.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" integrity="sha256-p6xU9YulB7E2Ic62/PX+h59ayb3PBJ0WFTEQxq0EjHw=" crossorigin="anonymous" />

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function () {
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({allow_single_deselect: true});
    });
</script>
<main class="reports-container-mbt d-flex">
    @include('admin.adminsidebar')

    <div class="dshbrdin store-reports w-100">
        <div class="container">
            <div class="col-sm-12">              
                <div class="d-flex justify-content-between">
                    <div class="hd-bt">  <h1>Sales Report </h1></div> 
                </div>            
            </div>
            <?php
            $text = "Yearly Report (" . date("Y") . ")";
            $saleText = "This Year";
            if (isset($_GET['type']) && $_GET['type'] == 'yearly' && $_GET['date_to'] == "" && $_GET['date_from'] == "") {
                $text = "Yearly Report (" . date("Y") . ")";
                $saleText = "This Year";
            } else if (isset($_GET['type']) && $_GET['type'] == 'monthly' && $_GET['date_to'] == "" && $_GET['date_from'] == "") {
                $text = "This Month Report (" . date("M,Y") . ")";
                $saleText = "This Month";
            } else if (isset($_GET['type']) && $_GET['type'] == 'lastmonth') {
                $date = date("Y-m-d");
                $text = "Last Month (" . date('M,Y', strtotime("-1 month", strtotime($date))) . ")";
                $saleText = "Last Month";
            } else if (isset($_GET['type']) && $_GET['type'] == 'weekly' && $_GET['date_to'] == "" && $_GET['date_from'] == "") {
                $text = "Weekly";
                $saleText = "This Week";
            } else if (isset($_GET['type']) && $_GET['type'] == 'daily') {
                $text = "Daily";
                $saleText = "Today";
            } else if (isset($_GET['date_to']) && isset($_GET['date_from'])) {
                $text = $_GET['date_to'] . " to  " . $_GET['date_from'] . " Report";
                $saleText = $_GET['date_to'] . " to " . $_GET['date_from'];
            } else if (isset($_GET['flashsale']) && $_GET['flashsale'] != "" && isset($_GET['date_to']) && isset($_GET['date_from'])) {
                $text = $_GET['date_to'] . " to  " . $_GET['date_from'] . " Flash Sale Report";
            } else if (isset($_GET['flashsale']) && $_GET['flashsale'] != "") {
                $text = "Flash sale" . $_GET['type'] . " Report";
            }
            ?>  
            <div class="col-sm-12 mt-25 mb-20 hide-mobile ">
                <form method="get" id="report_form" class="row no-gutters" action="<?php echo URL::to('admin/sale-report'); ?>">
                    <div class="bxst w-100">
                        <div class="year-mnth-filter row no-gutters">
                            <div class="form-group col-md-2 right-spacng-cmt">
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
                            <div class="col-md-1 pl-5px">
                                <div></div>
                            </div>
                            <div class="text-right align-self-center cstm-field-mbt col-md-6">
                                <div class="row ">
                                    <div class="col-sm-2 pr-5px">
                                        <label class="lbed-tm">Custom:</label>
                                    </div>
                                    <div class="form-group col-md-3 pl-5px pr-5px">
                                        <div class="fmr-lbt">
                                            <span class="grbe-fied"><input id="garbage_dpFrom" autocomplete="off" type="text" name="date_to" class="form-control" data-calendar="date-from" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ""; ?>"  /></span>
                                            <span class="calendar-date-select"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3 pl-5px pr-5px">
                                        <div class="fmr-lbt">
                                            <span class="grbe-fied"><input id="garbage_dpTo" type="text" name="date_from" class="form-control js-hook--date-to" autocomplete="off" data-calendar="date-to"  value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ""; ?>" /></span>
                                            <span class="calendar-date-select"><i class="fa fa-calendar" aria-hidden="true"></i></span>                                        
                                        </div>
                                    </div> 
                                    <input type="hidden" name="store_ids"  value="<?php echo!empty($_GET['store_ids']) ? $_GET['store_ids'] : ""; ?>">
                                    <input type="hidden" name="store_id" id="store_id" value="<?php echo!empty($_GET['store_id']) ? $_GET['store_id'] : ""; ?>">
                                    <input type="hidden" name="store_name" id="store_name" value="<?php echo!empty($_GET['store_name']) ? $_GET['store_name'] : ""; ?>">
                                    <div class="col-md-2 pl-5px pr-5px submit-btn-filter-mbt">
                                        <input type="submit" id="sale_submit" value="Go" class="btn btn-primary btn-simple small-btn-mbt text-light" >  
                                    </div>
                                    <div class="col-md-2 pl-5px pr-5px submit-btn-filter-mbt text-center d-flex align-items-center">
                                        <a href="<?php echo URL::to('admin/sale-report'); ?>" style="font-size:12px;">Reset Filters</a>
                                    </div>
                                </div>   
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">

                    <div class="col-sm-4-lrge col-md-3 mt-4" >
                        
                        <!-- stores section -->
                        <div  style="width: 100%; height: auto; padding:10px; margin-top:42px" class="brdr-all pad10" id="sales_store_div">
                            <p style="margin-bottom:5px">Sales From: <b><?php echo!empty($saleText) ? $saleText : ""; ?></b></p>
                            <div class="" style="width:100%; padding:3px" >
                                <p style="font-size:14px; margin-top:0px; font-weight:normal; margin-bottom:0px">Total Sales of <b><?php echo isset($_GET['store_ids']) ? count(explode(',',$_GET['store_ids'])) : count($storeData); ?></b> Stores</p> 

                                <p style="font-size:14px; margin-top:0px; font-weight:normal; margin-bottom:0px;  "><b style="color:#1e73f8">$<?php echo!empty($totalSales) ? number_format($totalSales, 2, '.', '') : 0; ?> </b></p> 
                            </div>    
                            <hr>
                            <form class="mb-10"><div class="input-group ">
                                    <input style="font-size: 14px;" class="form-control py-2 border-right-0 border" type="text" placeholder="Find Store(s)" id="search_store" value="<?php echo!empty($_GET['store_name']) ? $_GET['store_name'] : ""; ?>">
                                    <span class="input-group-append">
                                        <button class="btn btn-outline-secondary border-left-0 border" id="btn_search" type="button">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div></form>  
                                <form action="{{URL::to('admin/sale-report')}}" id="saleForm">
                                    <input type="hidden" name="type" value="{{ request()->get('type', 'yearly') }}">
                                    <input type="hidden" name="date_from" value="{{ request()->get('date_from', '') }}">
                                    <input type="hidden" name="date_to" value="{{ request()->get('date_to', '') }}">
                                    <input type="hidden" name="store_ids" id="storeIds">  
                                    <div class="strscrl">
                                        <?php
                                        if (!empty($storeData)) {
                                            foreach ($storeData as $sd) {
                                                $sty="";
                                                if(!empty($_GET['store_id']) && $_GET['store_id']==$sd->store_id){
                                                    $sty="active";
                                                }
                                                ?>
                                                <a style="text-decoration:none;" href="/admin/sale-report?type=<?php echo isset($_GET['type']) ? $_GET['type'] : 'yearly'; ?>&date_to=<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>&date_from=<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>&store_id=<?php echo!empty($sd->store_id) ? $sd->store_id : ""; ?>" class="" data-store-id="<?php echo!empty($sd->store_id) ? $sd->store_id : ""; ?>">
                                                    <div class="row rptstr <?php echo $sty; ?>">
                                                        <div class="col-md-3 pr-0 pl-0 position-relative">
                                                            <input type="checkbox" class="img-checkbox" style="position:absolute; top:0px; left:0px;" value="{{$sd->store_id ? $sd->store_id : ''}}"  {{ isset($_GET['store_ids']) ? (in_array($sd->store_id, explode(',',$_GET['store_ids'])) ? 'checked' : '') : '' }}>
                                                            <img src="<?php echo getAttachmentUrlById((int) $sd->store_logo, 'small'); ?>" alt="<?php echo!empty($sd->store_name) ? $sd->store_name : ""; ?>"  title="<?php echo!empty($sd->store_name) ? $sd->store_name : ""; ?>"  onerror="this.onerror=null;this.src='<?php echo URL::asset('assets/images/default-store.png'); ?>';"  class="img-fluid" />
                                                        </div>
                                                        <div class="col-md-9 pl-0 pr-0">
                                                            <p  class="mb-0">&nbsp; <?php echo!empty($sd->store_name) ? str_limit($sd->store_name,15) : ""; ?></p>
                                                            <p class="txt-clr-blue mb-0" style="font-size:12px; padding-left: 4px;">&nbsp;Sales: $<?php echo!empty($sd['total_sale']) ? number_format($sd['total_sale'], 2, '.', '') : 0; ?> </p></div>
                                                    </div>
                                                </a>
                                        <?php } ?>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <input id="showAllCheck" type="checkbox" class="d-flex justify-content-center align-items-center">
                                            <label for="showAllCheck" class="small font-weight-normal m-0 pl-1">Show selected</label>
                                        </div>
                                        <button type="submit" class="text-danger small btn">Apply filter</button>
                                    </div>
                                    <?php } else { ?>
                                        No record found..
                                    <?php } ?>
                                </form>
                            </div>
                    </div>
                    <script>
                        document.getElementById('saleForm').addEventListener('submit', function(e) {
                            let ids = [];
                            document.querySelectorAll('.img-checkbox:checked').forEach(cb => {
                                ids.push(cb.value);
                            });
                            document.getElementById('storeIds').value = ids.join(','); // encode as "3,7,10"
                        });
                    </script>
                    <div class="col-sm-8-lrge col-md-9 mt-4">
                        <div class="d-flex justify-content-between align-items-center" style="height:42px;">
                            <div class="d-flex">
                                <h6 style="">All Store Sale</h6>
                                <?php
                                if (!empty($_GET['store_id'])) {
                                    $storeData = gatStoreDataById($_GET['store_id']);
                                    $storename = !empty($storeData->store_name) ? $storeData->store_name : "";
                                    ?>
                                    <h6>
                                        <span class="px-2 fx-bold">></span>
                                        <!-- <img style="height: 35px; margin-right: 5px;" src="<?php echo getAttachmentUrlById((int) $storeData->store_logo, 'small'); ?>" alt="<?php echo!empty($storeData->store_name) ? $storeData->store_name : ""; ?>"  title="<?php echo!empty($storeData->store_name) ? $storeData->store_name : ""; ?>" onerror="this.onerror=null;this.src='<?php echo URL::asset('assets/images/default-store.png'); ?>';"  class="img-fluid" /> -->
                                        <?php echo!empty($storename) ? $storename : ""; ?>
                                        <a style="padding: 5px; color: #d3d3d3; text-decoration:none; font-weight: normal;" href="/admin/sale-report?type=<?php echo isset($_GET['type']) ? $_GET['type'] : 'yearly'; ?>&product_id=&date_to=<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>&date_from=<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">&#10005;</a>
                                    </h6>
                                <?php } ?>
                            </div>
                            <div>
                                <?php
                                if (!empty($_GET['store_id'])) {
                                    $storeData = gatStoreDataById($_GET['store_id']);
                                    $storename = !empty($storeData->store_name) ? $storeData->store_name : "";
                                    ?>
                                    <h6>
                                        <a href="{{ url('admin/product-report') }}?type={{ request('type') }}&date_to={{ request('date_to') }}&date_from={{ request('date_from') }}&store_id={{ request('store_id') }}&" class="btn btn-primary" type="button">View Product Reports</a>
                                    </h6>
                                <?php } ?>
                            </div>
                        </div>
                        <div id="chart_div" style="width: 100%; height: 550px" class="brdr-all"></div> 
                        <div style="width: 100%;padding: 5px;background: #fff;margin: auto;margin-top: -13px;position: relative;border-left: 1px solid #dee2e6;border-right: 1px solid #dee2e6;"></div>
                        <div id="no-record" class="no-record-foundd"></div>
                    </div>
                </div>     
            </div>
        </div>
    </div>
</main>
<script>

    //    $('#datepicker').datepicker({uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd', showClear: true});
    //    $('#datepicker1').datepicker({uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd', clearBtn: true});

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
//print_rr($storeSales); exit;
if (!empty($storeSales)) {
    ?>
    <script src="{{ URL::asset('assets/js/canvasjs.min.js',env('HTTPS_STATUS')) }}"></script>
    <script>
        window.onload = function () {

            var chart = new CanvasJS.Chart("chart_div", {

                animationEnabled: true,
                theme: "light2",

                title: {
                    text: "<?php echo $text; ?>",
                    fontSize: 17,
                    padding: 10,
                    fontColor: '#2f2f2f',

                },
                axisX: {
                    labelFontSize: 12,
                    valueFormatString: "DD MMM",
                    crosshair: {
                        enabled: true,
                        snapToDataPoint: true
                    }
                },
                axisY: {
                    labelFontSize: 12,
                    title: "Sales in $",
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
                        name: "$",
                        markerType: "square",

                        // xValueFormatString: "DD MMM YYYY",
                        color: "#195dfa",
                        dataPoints: [
    <?php
    for ($i = 0; $i < count($storeSales); $i++) {
        $to = date('Y,m,d', strtotime("-1 month", strtotime($storeSales[$i]['created_at'])));
        if (!isset($storeSales[$i]['total_amount'])) {
            //$totalsale = $storeSales[$i]['quantity'] * $storeSales[$i]['price'];
            $totalsale = $storeSales[$i]['sale'];
        } else {
            $totalsale = $storeSales[$i]['total_amount'];
        }
        ?>

                                {x: new Date(<?php echo isset($to) ? $to : 0; ?>), y: <?php echo $totalsale; ?>},
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
        $(document).on("click", ".store-detail", function () {
            var storeId = $(this).attr('data-store-id');
            console.log(storeId);
            $("#store_id").val(storeId);
            $("#report_form").submit();

        });
    </script>
<?php } else {
    ?>
    <script>
        $("#no-record").html("No record found.");
    </script>
<?php } ?>
<script>
    function getUrlParams() {
        let params = {};
        let queryString = window.location.search.substring(1);
        let pairs = queryString.split("&");
        for (let i = 0; i < pairs.length; i++) {
            let pair = pairs[i].split("=");
            params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || "");
        }
        return params;
    }
    $("#btn_search").on("click", function () {
        var data = {};
        var data = getUrlParams();
        var searchItem = $("#search_store").val();
        data['term'] = searchItem;
        data['search_status'] = "yes";
        $.ajax({
        url: "/admin/search-stores",
        type: "POST",
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            $(".strscrl").html(response);
        }
    });
});
</script>    


@endsection