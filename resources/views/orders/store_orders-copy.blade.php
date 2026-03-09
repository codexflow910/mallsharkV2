@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'S6')
@include('vendor.sidebar')
<?php
/*
  echo '<pre>';
  print_r($orders_processing);
  echo '</pre>';
 * 
 */
//die();
?>
<div class="dshbrdin orders-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="dshodrs">
                    <div class="row srtbx">
                        <div class="col-md-6"><h3 class=" ">Orders</h3></div>
                        <div class="col-md-6 text-right">
                            <div class="  form-inline small pull-right label-inline mobile-floatnone">
                                <form method="get" action="">
                                    <label for="text">Search Orders:</label>&nbsp;&nbsp;
                                    <input type="text" name="search" class="form-control" value="" aria-describedby="" placeholder="Order Id, email or name">  
                                    abc
                                </form>
                            </div>   
                        </div>                                                    
                    </div><!-- srtbx -->

                    <ul class="nav nav-tabs  ">
                        <li class="nav-item">
                            <a data-toggle="tab" href="#home" class="nav-link active">All</a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="tab" href="#pending-tab" class="nav-link">Pending</a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" href="#filled-tab" class="nav-link">Filled</a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="tab" href="#shipped-tab" class="nav-link">Shipped</a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="tab" href="#undelivered-tab" class="nav-link">Undelivered</a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="tab" href="#returned-tab" class="nav-link">Returned</a>
                        </li>                        

                        <li class="nav-item">
                            <a data-toggle="tab" href="#cancelled-tab" class="nav-link">Cancelled</a>
                        </li>

                        <li class="nav-item">
                            <a data-toggle="tab" href="#completed-tab" class="nav-link">Completed</a>
                        </li>


<!--                         <li class="nav-item">
                            <a data-toggle="tab" href="#menu1" class="nav-link">New</a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" href="#menu2" class="nav-link">Processing</a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" href="#menu3" class="nav-link">Shipped</a>
                        </li> -->

                    </ul>                    

                    <div class="tab-content">

                      <div id="home" class="tab-pane  in active">

                        <div class="horizntal-scroll">
                            <div class="ordtbscnt ordtbscnt-head">
                                <div class="row"> 
                                    <div class="col-3 col-md-3"><span>Order ID</span></div>
                                    <div class="col-1 col-md-1"><span class="text-center">Status</span></div>
                                    <div class="col-1 col-md-1"><span class="text-center">Date</span></div>
                                    <div class="col-1 col-md-1"><span class="text-center">Total</span></div>
                                    <div class="col-4 col-md-4"><span>Shipping Address</span></div>
                                    <div class="col-2 col-md-2"><span class="text-center">Action</span></div>
                                </div>
                            </div><!-- ordtbscnt -->
                            <div class="ordtbscnt" id="accordion">
                                    <div class="row"> 
                                        <div class="col-3 col-md-3"><span><a class="card-link" data-toggle="collapse" data-parent="#accordion" href="#collapse4_all">
                                                    <span class="arw"><i style="font-size:18px" class="fa fa-angle-down" aria-hidden="true"></i></span>&nbsp;&nbsp; #4</a> &nbsp;&nbsp; By: <a href="javascript:;">John</a></span></div>
                                        <div class="col-1 col-md-1 text-center" style="font-size:20px">
                                            <span class="text-center"><i class="fa fa-clock-o" aria-hidden="true"></i>                                            </span>
                                        </div>
                                        <div class="col-1 col-md-1 ">
                                            <span class="text-center">June 04, 18</span>
                                        </div>
                                        <div class="col-1 col-md-1 "><span class="text-center">$23</span></div>
                                        <div class="col-3 col-md-3"><span>asdf La Massana Andorra</span></div>
                                        <div class="col-1 col-md-1">
                                            <span class="text-center note-icon">
                                            <a href="javascript:;" title="" data-toggle="popover" data-placement="top" data-content="" data-original-title="">
                                                            <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                                                        </a>
                                            </span>
                                        </div>

                                        <div class="col-2 col-md-2"><span class="text-center"><a href="javascript:;" class="btn btn-primary btn-simple btnauto dropdown-toggle" data-toggle="dropdown"> Change Status</a>
                                                <div class="dropdown-menu">


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-hourglass-3" aria-hidden="true"></i> Pending</a>


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-codepen" aria-hidden="true"></i> Filled</a>


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ship" aria-hidden="true"></i> Shipped</a>

                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Completed</a>


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-history" aria-hidden="true"></i> Undelivered</a>

                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-retweet" aria-hidden="true"></i> Returned</a>


                                                    <a data-status="cancelled" data-order_id="4" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ban" aria-hidden="true"></i> Canceled</a>
                                                </div>
                                            </span></div> 
                                    </div>      
                                            <div id="collapse4_all" class="collapse ordinf">
                                               <div class="card-body">
                                                <div class="row">
                                                    <div class="col-4 col-md-4"><sapn class="padleft"><img src="https://dev.ecommedge.com/uploads/small/HeroD_Transparent-3_53.jpg" onerror="this.src='https://dev.ecommedge.com/assets/images/placeholder.jpg'" alt="image"></sapn><span>Embr Wave Wristband   love it.</span> </div>
                                                    <div class="col-1 col-md-1 text-center"> 
                                                        <a style="font-size:20px; display:inline-block; width:100%;" href="javascript:;" title="" data-toggle="popover" data-placement="top" data-content="" data-original-title="">
                                                            <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                                                        </a>
                                                    </div>

                                                    <div class="col-1 col-md-1"><span class="text-center">Qty:1</span></div>
                                                    <div class="col-4 col-md-4"></div>
                                                    <div class="col-2 col-md-2">
                                                        <span class="text-center">
                                                            <span>Price:$23<br>
                                                                <span class="small grey">Via paypal
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </div>   
                                                </div>
                                            </div>
                                        
                                    </div>




                            </div><!-- ordtbscnt -->

                            <div class="ordtbscnt" id="accordion">
                                    <div class="row"> 
                                        <div class="col-3 col-md-3"><span><a class="card-link" data-toggle="collapse" data-parent="#accordion" href="#collapse5_all">
                                                    <span class="arw"><i style="font-size:18px" class="fa fa-angle-down" aria-hidden="true"></i></span>&nbsp;&nbsp; #4</a> &nbsp;&nbsp; By: <a href="javascript:;">John</a></span></div>
                                        <div class="col-1 col-md-1 text-center" style="font-size:20px">
                                            <span class="text-center"><i class="fa fa-clock-o" aria-hidden="true"></i>                                            </span>
                                        </div>
                                        <div class="col-1 col-md-1 ">
                                            <span class="text-center">June 04,18</span>
                                        </div>
                                        <div class="col-1 col-md-1 "><span class="text-center">$23</span></div>
                                        <div class="col-3 col-md-3"><span>asdf La Massana Andorra</span></div>
                                        <div class="col-1 col-md-1">
                                            <span class="text-center note-icon">
                                            <a href="javascript:;" title="" data-toggle="popover" data-placement="top" data-content="" data-original-title="">
                                                            <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                                                        </a>
                                            </span>
                                        </div>

                                        <div class="col-2 col-md-2"><span class="text-center"><a href="javascript:;" class="btn btn-primary btn-simple btnauto dropdown-toggle" data-toggle="dropdown"> Change Status</a>
                                                <div class="dropdown-menu">


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-hourglass-3" aria-hidden="true"></i> Pending</a>


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-codepen" aria-hidden="true"></i> Filled</a>


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ship" aria-hidden="true"></i> Shipped</a>

                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Completed</a>


                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-history" aria-hidden="true"></i> Undelivered</a>

                                                    <a data-status="" data-order_id="" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-retweet" aria-hidden="true"></i> Returned</a>


                                                    <a data-status="cancelled" data-order_id="4" class="dropdown-item change-order-status" href="javascript:;"><i class="fa fa-ban" aria-hidden="true"></i> Canceled</a>
                                                </div>
                                            </span></div> 
                                    </div>      
                                            <div id="collapse5_all" class="collapse ordinf">
                                               <div class="card-body">
                                                <div class="row">
                                                    <div class="col-4 col-md-4"><sapn class="padleft"><img src="https://dev.ecommedge.com/uploads/small/HeroD_Transparent-3_53.jpg" onerror="this.src='https://dev.ecommedge.com/assets/images/placeholder.jpg'" alt="image"></sapn><span>Embr Wave Wristband   love it.</span> </div>
                                                    <div class="col-1 col-md-1 text-center"> 
                                                        <a style="font-size:20px; display:inline-block; width:100%;" href="javascript:;" title="" data-toggle="popover" data-placement="top" data-content="" data-original-title="">
                                                            <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                                                        </a>
                                                    </div>

                                                    <div class="col-1 col-md-1"><span class="text-center">Qty:1</span></div>
                                                    <div class="col-4 col-md-4"></div>
                                                    <div class="col-2 col-md-2">
                                                        <span class="text-center">
                                                            <span>Price:$23<br>
                                                                <span class="small grey">Via paypal
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </div>   
                                                </div>
                                            </div>
                                        
                                    </div>




                            </div><!-- ordtbscnt -->

                        </div>




                      </div><!-- home -->

                      <div id="pending-tab" class="tab-pane">
                          Pending tab
                      </div>
                      <div id="filled-tab" class="tab-pane">
                          Filled tab
                      </div>
                      <div id="shipped-tab" class="tab-pane">
                          shipped tab
                      </div>
                      <div id="undelivered-tab" class="tab-pane">
                          undelivered tab
                      </div>
                      <div id="returned-tab" class="tab-pane">
                          returned tab
                      </div>
                      <div id="cancelled-tab" class="tab-pane">
                          Cancelled tab
                      </div>
                      <div id="completed-tab" class="tab-pane">
                          completed tab
                      </div>











                    </div><!-- tab-content -->






                </div><!-- dshodrs -->      
            </div>
        </div>
    </div>
</div><!-- dshbrdin -->



<style>
    .trackinginfo {
        margin-bottom: 10px;
    }
    .trackinginfo input {
    display: inline-block;
    width: 74%;
    height: 35px;
}
.trackinginfo strong {
    display: block;
    font-weight: 500;
}

</style>

<script>
  



$(".dropdown-menu  a").click(function(){
  var selText = $(this).text();
  $(this).parents('.text-center').find('.btnauto').html(selText);
});


</script>


@endsection