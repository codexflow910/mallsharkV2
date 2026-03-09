@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Google Analytics')

<main>
    <?php
    //print_rr($top_countries);
    $countries=array();
    $labels=array();
    $values=array();
    $values2=array();
    foreach ($most_visited_pages as $obj) {
        $page_title = Str::words($obj['pageTitle'], '2');
        $page_views = $obj['pageViews'];
        //$visitors = $obj['visitors'];
        $labels[]=$page_title;
        $values[]=$page_views;
        //die();
    }
    foreach ($top_countries as $cont) {
        $country_title_title = $cont['country'];
        $page_views = Str::words($cont['sessions'], '2');
        //$visitors = $obj['visitors'];
        $countries[]=$country_title_title;
        $values2[]=$page_views;
        //die();
    }
   //print_rr($countries);
   // print_rr($values2);
    $labels= json_encode($labels);
    $values= json_encode($values);
    $values2= json_encode($values2);
    $countries= json_encode($countries);

    //die();
    ?>
    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section">
        @include('admin.adminsidebar')
        <div class="dshbrdinadmin list-management">

            <div class="container">
                <div class="row">
                    <div class="col-sm-12 mt-40">
                        <h3>Google Analytics</h3>
                        
                    </div>
                    @if (Session::has('message_error'))
                    <div class="col-sm-12">
                        <div class="alert alert-danger">
                            <ul>
                                <li>{!! Session::get('message_error') !!}</li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if (Session::has('message_success'))
                    <div class="col-sm-12">

                        <div class="alert alert-success">
                            <ul>
                                <li>{!! Session::get('message_success') !!}</li>
                            </ul>
                        </div>
                    </div>
                    @endif

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>

                    <!-- Inside the body tag -->
                    
                    <canvas id="pie-chart" width="800" height="450"></canvas>
                    <script>

                        new Chart(document.getElementById("pie-chart"), {
                            type: 'line',
                            data: {
                                labels: <?php echo $labels;?>,
                                datasets: [{
                                        label: "Most Visited Pages",
                                        backgroundColor: ["#3e95cd", "#8e5ea2", "#3cba9f", "#e8c3b9", "#c45850"],
                                        data: <?php echo $values;?>
                                    }]
                            },
                            options: {
                                title: {
                                    display: false,
                                    text: <?php echo $values;?>
                                }
                            }
                        });
                    </script>
                    
                    <canvas id="pie-chart-countries" width="800" height="450"></canvas>
                    <script>

                        new Chart(document.getElementById("pie-chart-countries"), {
                            type: 'line',
                            data: {
                                labels: <?php echo $countries;?>,
                                datasets: [{
                                        label: "Top countries",
                                        backgroundColor: ["#3e95cd", "#8e5ea2", "#3cba9f", "#e8c3b9", "#c45850"],
                                        data: <?php echo $values2;?>
                                    }]
                            },
                            options: {
                                title: {
                                    display: false,
                                    text: <?php echo $values2;?>
                                }
                            }
                        });
                    </script>


                </div>


            </div>



        </div>
       

    </section>

    @endsection