@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Reports')
<main>
    @include('Store.mobile.sidebar')

    <div class="dshbrdin store-dashboard store-reports">
        <div class="container">


            <div class="col-sm-12"><h1>Store Reports</h1></div>
            <div class="col-sm-12 mt-25 mb-20 hide-mobile " style="overflow: auto; ">
                <div id="columnchart_material" style="height: 300px; width: 500px;"></div>
            </div>
        </div>
    </div>
    <?php
    $arr = array();
    $storeSales = getYearlyOrdersOfStore();
    echo 'Data: <pre>' .print_r($storeSales,true). '</pre>'; die();
    for ($i = 0; $i <= 12; $i++) {
        if (array_key_exists($i, $storeSales)) {
            $arr[$i] = $storeSales[$i];
        } else {
            $arr[$i] = 0;
        }
    }
    ?>
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
            width: 400,
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


@endsection