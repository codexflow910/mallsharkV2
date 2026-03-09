@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Store Reports')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{ mix('js/app.js') }}" defer></script>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<link rel="stylesheet" href="{{ URL::asset('assets/css/order-page.css') }}">

<style>
    .bg-light-gray {
        background-color: #f7f7f7;
    }

    .flex-parent-div {
        display: flex;
    }

    .dshbrdin {
        width: 100%;
    }

    .sidebar-admin-burger-menu {
        cursor: pointer;
    }

    #menu-toggle-dashboard span {
        display: block;
        height: 2px;
        background: #000;
        margin: 3px 0;
        width: 20px;
    }

    nav {
        margin-bottom: 0px;
        margin-left: 0px;
        margin-right: 0px;
    }

    .reports-page nav div {
        display: flex;
        gap: 10px;
    }

    .btn-primary {
        border-radius: 3px !important;
    }

    .card {
        margin-bottom: 0px;
        border-radius: 3px !important;
    }

    #dataInfoContainer .card-body {
        padding: 10px 20px;
        border-bottom: 1px solid lightgrey;
    }

    .dataInfo {
        padding: 0px;
        /* border-right: 4px solid lightgrey; */
    }

    .col-md-9 {
        padding: 0px;
    }

    .h-100 {
        height: 100%;
    }

    .card-title {
        margin-bottom: 0px;
    }

    .card-text {
        margin: 0;
    }

    #chart_div {
        height: 505px;
        width: 100%;
    }

    .report-navigation {
        display: flex;
        padding: 5px;
        padding-left: 0px;
        gap: 10px;
    }

    .report-navigation-links {
        display: flex;
        align-items: center;
    }

    .report-navigation-links div {
        margin-right: 10px;
        cursor: pointer;
    }

    .report-navigation-links div a {
        text-decoration: none;
        color: #333;
        display: flex;
        align-items: center;
    }

    .report-navigation-links div a i {
        margin-right: 5px;
    }

    .stock-table {
        display: none;
    }

    .default-link {
        text-decoration: none;
    }

    .active-link {
        color: #0510fa !important;
    }

    .productList {
        padding: 10px;
        border: 1px solid lightgray;
        text-decoration: underline !important;
    }

    .productList:hover {
        background: #f4f8fe;
    }

    .date {
        border: 1px solid lightgray;
        border-radius: 10px;
        background: #f9fbfe;
        padding: 5px;
        outline: none;
    }

    input[type="date"]::placeholder {
        opacity: 0;
    }

    input[type="date"]::-webkit-datetime-edit {
        color: black;
    }

    input[type="date"]::-webkit-clear-button,
    input[type="date"]::-webkit-inner-spin-button {
        display: none;
    }

    input[type="date"]:focus::-webkit-datetime-edit {
        color: initial;
    }

    input[type="date"]:focus:before {
        content: '';
    }


    nav {
        padding: 5px;
    }

    #dataInfoContainer {
        margin-bottom: 20px;
        background: #fff;
    }

    .card:hover {
        background: none !important;
        box-shadow: none !important;
    }

    .card {
        background: none !important;
    }

    .cahrtContainer {
        position: relative;
        background: #fff;
        margin-bottom: 20px;
    }

    #errorMessage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .resetBtn,
    .searchBtn {
        width: 100%;
    }

    .right-border {
        border-right: 4px solid !important;
    }

    #select2Product {
        margin-bottom: 15px !important;
    }

    #select2Category {
        margin-bottom: 15px !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b,
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border: none !important;
    }

    #select2Product>span {
        width: 100% !important;
    }

    #select2Category>span {
        width: 100% !important;
    }

    #loading {
        position: fixed;
        display: block;
        width: 100%;
        height: 100%;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: block;
    }

    #loading-image {
        position: absolute;
        top: 50%;
        z-index: 100;
        width: 150px;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    #dataInfoContainer .card-text {
        color: #a8a8a8 !important;
    }

    #dataInfoContainer .card-body:hover {
        background: #0510fa0f !important;
    }

    #dataInfoContainer .card-body:hover .card-text,
    #dataInfoContainer .card-body:hover .card-title {
        color: #0510fa !important;
    }

    #categorySalesValue {
        display: none;
    }

    .nav-tabs {
        border: none !important;
    }

    .nav-tabs .nav-item.show .nav-link,
    .nav-tabs .nav-link.active {
        border: none !important;
        box-shadow: 0px -2px 3px #00000029;
    }

    .nav-tabs .nav-link {
        border: none !important;
    }

    .nav-tabs .nav-link:focus,
    .nav-tabs .nav-link:hover {
        border-color: none !important;
    }

    #select2Product>span {
        width: 100% !important;
    }

    #select2Category>span {
        width: 100% !important;
    }

    .canvasjs-chart-credit {
        display: none !important;
    }
</style>

<main>
    <!-- <a href="{{ route('paypal.payment') }}" class="btn btn-primary">Pay with PayPal</a> -->

    <div class="flex-parent-div bg-light-gray brand-orders-ai">
        <div class="change-password-page-cmt">
            @include('buyer.customsidebar')
        </div>
        <div class="dshbrdin dashprdlst storeproducts-page home-section cm-edit-ja reports-page">
            <div id="loading" class="hide-setion">
                <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashprdlst">
                            <div class="d-flex align-items-center gap-3">
                                <a href="javascript:void(0)"
                                    class="d-flex align-items-center gap-10px sidebar-admin-burger-menu"
                                    id="open-sidebar-admin-panel">
                                    <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu">
                                        <span></span><span></span><span></span><span></span>
                                    </span>
                                </a>
                                <span>Reports</span>
                                <br><br><br>
                            </div>
                            <div class="report-navigation">
                                <button type="button" class="btn btn-primary" onclick="showOrder()">Sales</button>
                                <button type="button" class="btn btn-primary" onclick="showStock()">Stock</button>
                            </div>
                            <div class="order-chart">
                                <nav style="margin: 15px -20px 0px -20px;">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="sales_by_date-tab" data-bs-toggle="tab"
                                                href="#sales_by_date_content" role="tab"
                                                aria-controls="sales_by_date_content" aria-selected="true"
                                                onclick="updateChartByDates();">
                                                <i class="bi bi-calendar-event-fill"></i> Sales by Date
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="sales_by_product-tab" data-bs-toggle="tab"
                                                href="#sales_by_product_content" role="tab"
                                                aria-controls="sales_by_product_content" aria-selected="false"
                                                onclick="salesByProduct();">
                                                <i class="bi bi-basket3-fill"></i> Sales by Product
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="sales_by_category-tab" data-bs-toggle="tab"
                                                href="#sales_by_category_content" role="tab"
                                                aria-controls="sales_by_category_content" aria-selected="false"
                                                onclick="salesByCategory();">
                                                <i class="bi bi-tags-fill"></i> Sales by Category
                                            </a>
                                        </li>
                                    </ul>
                                    <div
                                        style="display: flex;justify-content:space-between;flex-wrap:wrap;background: #fff; padding: 20px;box-shadow:0px 3px 6px #00000029;border-top-right-radius:12px;">
                                        <div class="report-navigation-links"
                                            style="display: flex;gap:5px;align-items:center;">
                                            <div>
                                                <a href="javascript:void(0)"
                                                    onclick="handleFilterClick('today');setActiveClass(this);"
                                                    class="default-link" id="todays-link">
                                                    Today
                                                </a>
                                            </div>|
                                            <div>
                                                <a href="javascript:void(0)"
                                                    onclick="handleFilterClick('last_7_days');setActiveClass(this);"
                                                    class="default-link" id="last-7-days-link">
                                                    Last 7 days
                                                </a>
                                            </div>|
                                            <div>
                                                <a href="javascript:void(0)"
                                                    onclick="handleFilterClick('this_month');setActiveClass(this);"
                                                    class="default-link">
                                                    This month
                                                </a>
                                            </div>|
                                            <div>
                                                <a href="javascript:void(0)"
                                                    onclick="handleFilterClick('last_month');setActiveClass(this);"
                                                    class="default-link">
                                                    Last month
                                                </a>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: baseline;">
                                            <div class="report-navigation-links"
                                                style="display: flex; gap: 10px; align-items: center;">
                                                <label class="lbed-tm">Custom:</label>
                                            </div>
                                            <div
                                                style="display: flex; justify-content: center; gap: 20px; align-items: center;">
                                                <input type="date" id="start-date" name="start_date"
                                                    class="date" />
                                                __
                                                <input type="date" id="end-date" name="end_date"
                                                    class="date" />
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-primary"
                                                    onclick="updateCustomDateRange()"
                                                    style="padding-right: 25px; padding-left: 25px;">
                                                    Go
                                                </button>
                                            </div>
                                        </div>


                                    </div>

                                </nav>


                                <div class="row "
                                    style="box-shadow: 0px 3px 6px #00000029;border-bottom-left-radius:12px;border-bottom-right-radius:12px;background:#fff;">
                                    <div class="col-md-3 dataInfo" id="dataInfoContainer">

                                    </div>
                                    <div class="col-md-9 cahrtContainer">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div id="errorMessage"></div>
                                                <div id="chart_div"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="stock-table">
                                <nav style="margin: 15px 0px 25px 0px;">
                                    <div class="report-navigation-links">
                                        <div><a href="javascript:void(0)"
                                                onclick="filterTable('low_in_stock');setActiveClass(this);"><i
                                                    class="bi bi-exclamation-circle-fill"></i> Low in stock</a></div> |
                                        <div><a href="javascript:void(0)"
                                                onclick="filterTable('out_of_stock');setActiveClass(this);"><i
                                                    class="bi bi-basket3-fill"></i> Out of stock</a></div> |
                                        <div><a href="javascript:void(0)"
                                                onclick="filterTable('most_stocked');setActiveClass(this);"><i
                                                    class="bi bi-cart-fill"></i> Most stocked</a></div> |
                                        <div><a class="all-filter" href="javascript:void(0)"
                                                onclick="filterTable('');setActiveClass(this);"><i
                                                    class="bi bi-list"></i> All</a></div>
                                    </div>
                                </nav>

                                <div
                                    style="margin-left: 0px; margin-rigt:0px;box-shadow:0 0 10px #00000040;border-radius:12px;">
                                    <table id="datatable"
                                        class=" tblish table table-hover table-borderless table-thead-bordered table-striped table-hover table-nowrap table-align-middle card-table table table-striped"
                                        style="width: 100%">
                                        <thead class="thead-light">
                                            <tr class="tblishrw">
                                                <th style="font-weight: bold;" class="text-center">Product</th>
                                                <th style="font-weight: bold;" class="text-center">Parent</th>
                                                <th style="font-weight: bold;" class="text-center">Units in Stock</th>
                                                <th style="font-weight: bold;" class="text-center">Stock status</th>
                                                <th style="font-weight: bold;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="custom-pagination col-sm-12 mt-15">
                                        <ul class="pagination pull-right" id="pagination">
                                            <!-- Pagination links will be generated here by JavaScript -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    //Stock related script
    function showOrder() {
        $(".order-chart").show();
        $(".stock-table").hide();
    }
</script>

<script>
    let currentPage = 1;
    let currentFilter = '';

    function showStock() {
        $(".order-chart").hide();
        $(".stock-table").show();
        $(".all-filter").addClass('active-link');
        fetchStockData();
    }

    function fetchStockData(filter = '', page = 1) {
        $.ajax({
            url: '/stock-reports',
            type: 'GET',
            data: {
                filter: filter,
                page: page
            },
            success: function(response) {
                updateStockTable(response.data.reportData);
                updatePagination(response.data.pagination);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function updateStockTable(data) {
        var tbody = $("#datatable tbody");
        tbody.empty();
        if (data.length == 0) {
            document.getElementById("pagination").style.display = "none";
            var noDataRow = `<tr>
            <td class="table-column-pe-0 text-center" colspan="5"><b>No record found</b></td>
        </tr>`;
            tbody.append(noDataRow);
        } else {
            const paginations = document.getElementById("pagination");
            paginations.style.display = 'inline-flex';

            data.forEach(function(item) {
                var parentCategory = item.parent_category_name ? item.parent_category_name : '-';
                var row = `<tr>
            <td class="table-column-pe-0 text-center">${item.product_name}</td>
            <td class="table-column-pe-0 text-center">${parentCategory}</td>
            <td class="table-column-pe-0 text-center">${item.units_in_stock}</td>
            <td class="table-column-pe-0 text-center">${item.stock_status}</td>
            <td class="table-column-pe-0 text-center">
                <a href="/edit-product/${item.productId}" style="color:#0510fa;font-size: 18px;"><i class="bi bi-pen-fill"></i></a>&nbsp;
                <a href="product/${item.product_slug}"><i class="bi bi-eye-fill" style="color:#0510fa;font-size: 18px;"></i></a>
            </td>
        </tr>`;

                tbody.append(row);
            });
        }
    }

    function updatePagination(pagination) {
        let paginationHtml = '';
        const totalPages = pagination.last_page;
        const currentPage = pagination.current_page;
        const delta = 2; // Number of pages to show around the current page

        if (pagination.prev_page_url) {
            paginationHtml +=
                `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>`;
        }

        if (totalPages <= 1 + 2 * delta) {
            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += generatePageItem(i, currentPage);
            }
        } else {
            paginationHtml += generatePageItem(1, currentPage);

            let start = Math.max(2, currentPage - delta);
            let end = Math.min(totalPages - 1, currentPage + delta);

            if (start > 2) {
                paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
            }

            for (let i = start; i <= end; i++) {
                paginationHtml += generatePageItem(i, currentPage);
            }

            if (end < totalPages - 1) {
                paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
            }

            paginationHtml += generatePageItem(totalPages, currentPage);
        }

        if (pagination.next_page_url) {
            paginationHtml +=
                `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>`;
        }

        $('#pagination').html(paginationHtml);
    }

    function generatePageItem(page, currentPage) {
        if (page === currentPage) {
            return `<li class="page-item active"><a class="page-link" href="#">${page}</a></li>`;
        } else {
            return `<li class="page-item"><a class="page-link" href="#" data-page="${page}">${page}</a></li>`;
        }
    }

    function filterTable(filter) {
        currentPage = 1;
        currentFilter = filter;
        fetchStockData(currentFilter, currentPage);
    }

    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            fetchStockData(currentFilter, currentPage);
        }
    });
</script>

<script>
    function setActiveClass(element) {
        const links = document.querySelectorAll('.report-navigation-links a');
        links.forEach(link => link.classList.remove('active-link'));
        if (element === '') {
            var todays = document.querySelector('#todays-link');
            todays.classList.add('active-link');

        } else {
            element.classList.add('active-link');

        }
    }

    function setTopSellerActive(element) {
        document.querySelectorAll('.productList').forEach(function(item) {
            item.classList.remove('active-link');
        });

        element.querySelector('.productList').classList.add('active-link');
    }

    function selectAllCategories() {
        let allValues = [];
        $('.js-example-basic-multiple option').each(function() {
            allValues.push($(this).val());
        });
        $('.js-example-basic-multiple').val(allValues).trigger('change');
    }
</script>

<script>
    var chart;
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth();
    var currentDay = currentDate.getDate();


    window.onload = function() {
        updateChartByDates();
    }

    function getActiveFilter() {
        const activeElement = document.querySelector('.default-link.active-link');
        if (activeElement) {
            return activeElement.getAttribute('onclick').match(/handleFilterClick\('(\w+)'\)/)[1];
        }
        return null;
    }
    let currentProductId = null;

    function resetHandler() {
        currentProductId = null;
        var productDetailDiv = document.querySelector('#productDetail');
        setActiveClass('');
        productDetailDiv.innerHTML = '';
        var chartDiv = document.getElementById('chart_div');
        chartDiv.style.display = "none";
        $('#selectProduct').val(null).trigger('change');
        errorMessage.style.display = "block";
        errorMessage.innerHTML = "Choose a Product to view Stats";
    }

    function handleProductItemClick(productId, filter = null, startDate = '', endDate = '') {
        if (!filter) {
            filter = getActiveFilter() || 'today';
        }

        if (filter && filter == 'custom') {
            if (startDate == '' && endDate == '') {
                alert('Please select dates properly.');
                return;
            }
            const links = document.querySelectorAll('.report-navigation-links a');
            links.forEach(link => link.classList.remove('active-link'));
        }

        if (filter == 'today') {
            var todays = document.querySelector('#todays-link');
            todays.classList.add('active-link');
        }

        if (productId !== null) {
            currentProductId = productId;
        }
        if (!currentProductId || currentProductId.length === 0 || currentProductId == null) {
            alert('Please select at least one product.');
            return;
        }

        var productDetailDiv = document.querySelector('#productDetail')
        var url = `/sales-by-product?product_id`;
        var requestData = {
            product_id: currentProductId,
        }

        if (filter) {
            ``
            if (filter === 'custom') {
                url += `&filter=${filter}&start_date=${startDate}&end_date=${endDate}`;
            } else {
                url += `&filter=${filter}`;
            }
        }
        $.ajax({
            url: url,
            method: 'GET',
            data: requestData,
            success: function(response) {
                const reportData = response.data.reportData;

                productDetailDiv.innerHTML = `
                <div>
                    <div style="max-height:160px; overflow:auto;">
                        <div id="totalSalesCard" style="padding:15px;background:#fff;border:1px solid lightgray;margin-bottom:7px;">
                            <h3 style="margin:0px;">$${reportData.totalSales.toFixed(2)}</h3>
                            <p style="margin-bottom:0px;">Total Sales</p>
                        </div>
                        <div id="ordersPlacedCard" style="padding:15px;background:#fff;border:1px solid lightgray;margin-bottom:7px;">
                            <h3 style="margin:0px;">${reportData.ordersPlaced}</h3>
                            <p style="margin-bottom:0px;">Total Purchases</p>
                        </div>
                    </div>
                    <h4 class="accordion-header" id="flush-headingOne" style="padding: 10px; background: #e9ecef; padding: 0px; border-radius: 8px;">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="true" aria-controls="flush-collapseOne" style="background: #e9ecef; border: none; padding: 10px; width: 100%;">
                            <div style="display: flex; justify-content:space-between;">
                                <b>Showing report for &nbsp;&nbsp;&nbsp; </b>
                                <i class="bi bi-arrow-down-circle-fill"></i>
                            </div>
                        </button>
                    </h4>
                    <div id="flush-collapseOne" class="accordion-collapse collapse show" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body" style="background: #fff; padding: 10px;">
                            <p> ${reportData.productName}</p>
                            <button class="btn btn-primary mb-3 resetBtn" onClick="resetHandler();">Reset</button>
                        </div>
                    </div>
                </div>
            `;
                updateChartDataForProduct(reportData.chartData);
            },
            error: function(error) {
                console.error('Error fetching product details:', error);
                productDetailDiv.innerHTML =
                    '<p>Error loading product details. Please try again later.</p>';
            }
        });
    }

    async function updateChartDataForProduct(chartData) {
        const chartTypes = ['totalSales', 'totalSales'];
        const colors = {
            totalSales: "orange",
            ordersPlaced: "#6569cd",
        };

        // Set border colors for cards
        for (const type in colors) {
            const card = document.getElementById(`${type}Card`);
            if (card) {
                card.classList.add('right-border');
                card.style.setProperty('border-right-color', colors[type], 'important');
            }
        }

        const totalSalesDataPoints = chartData.map(data => ({
            x: new Date(data.date),
            y: data.totalSales
        }));

        const ordersPlacedDataPoints = chartData.map(data => ({
            x: new Date(data.date),
            y: data.ordersPlaced
        }));

        const chart = new CanvasJS.Chart("chart_div", {
            theme: "light2",
            animationEnabled: true,
            interactivityEnabled: true,
            zoomEnabled: true,
            zoomType: "xy",
            legend: {
                cursor: "pointer",
                verticalAlign: "bottom",
                horizontalAlign: "center",
                // itemclick: toogleDataSeries
            },
            axisX: {
                valueFormatString: "DD MMM"
            },
            axisY: {
                includeZero: true,
                minimum: 0
            },
            axisY2: {
                includeZero: true,
                minimum: 0
            },
            data: [{
                    type: "column",
                    name: "Orders Placed",
                    showInLegend: false,
                    legendText: " ",
                    legendMarkerType: "square",
                    axisYType: "secondary",
                    dataPoints: ordersPlacedDataPoints,
                    color: colors.ordersPlaced,
                    dataPointWidth: 5
                },
                {
                    type: "spline",
                    name: "Total Sales",
                    showInLegend: false,
                    legendText: " ",
                    legendMarkerType: "square",
                    dataPoints: totalSalesDataPoints,
                    color: colors.totalSales
                }
            ]
        });

        if (totalSalesDataPoints.length === 0 && ordersPlacedDataPoints.length === 0) {
            document.getElementById('chart_div').style.display = "none";
            document.getElementById('errorMessage').style.display = "block";
            document.getElementById('errorMessage').innerHTML = "No data available for the selected Product.";
        } else {
            document.getElementById('chart_div').style.display = "block";
            document.getElementById('errorMessage').style.display = "none";
            chart.render();
        }
    }
    let chartInstance = null;

    function updateChartData(chartData) {
        const chartTypes = [
            'grossSales', 'netSales', 'ordersPlaced', 'itemsPurchased',
            'averageGrossSales', 'averageNetSales', 'refundedOrders', 'chargedForShipping',
            'worthOfCoupons'
        ];
        const colors = {
            grossSales: "blue",
            netSales: "green",
            ordersPlaced: "red",
            itemsPurchased: "purple",
            averageGrossSales: "orange",
            averageNetSales: "brown",
            refundedOrders: "pink",
            chargedForShipping: "black",
            worthOfCoupons: "violet",
        };

        for (const type in colors) {
            const card = document.getElementById(`${type}Card`);
            if (card) {
                card.classList.add('right-border');
                card.style.setProperty('border-right-color', colors[type], 'important');

                // Add hover event listeners
                card.addEventListener('mouseenter', () => highlightLine(type, true));
                card.addEventListener('mouseleave', () => highlightLine(type, false));
            }
        }

        const chartSeries = chartTypes.map(type => ({
            type: "spline",
            name: type,
            showInLegend: true,
            legendText: " ",
            legendMarkerType: "square",
            xValueFormatString: "DD MMM YYYY",
            yValueFormatString: "#,##0",
            dataPoints: chartData[type].map(dataPoint => ({
                x: new Date(dataPoint.date),
                y: dataPoint.value || 0
            })),
            color: colors[type],
            lineThickness: 2 // Default line thickness

        }));

        chartInstance = new CanvasJS.Chart("chart_div", {
            zoomEnabled: true,
            animationEnabled: true,
            interactivityEnabled: true,
            zoomType: "xy",
            theme: "light2",
            legend: {
                cursor: "pointer",
                verticalAlign: "bottom",
                horizontalAlign: "center",
                itemclick: toogleDataSeries
            },
            axisX: {
                valueFormatString: "DD MMM",
            },
            axisY: {
                includeZero: true,
                minimum: 0,
                gridThickness: 1,
            },
            data: chartSeries
        });

        if (chartSeries.every(series => series.dataPoints.length === 0)) {
            document.getElementById('chart_div').style.display = "none";
            document.getElementById('errorMessage').style.display = "block";
            document.getElementById('errorMessage').innerHTML = "No data available for the selected filter.";
        } else {
            document.getElementById('chart_div').style.display = "block";
            document.getElementById('errorMessage').style.display = "none";
            chartInstance.render();

        }
    }

    function toogleDataSeries(e) {
        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        } else {
            e.dataSeries.visible = true;
        }
        chartInstance.render();
    }

    function highlightLine(type, highlight) {
        if (!chartInstance) return;

        chartInstance.options.data.forEach(series => {
            if (series.name === type) {
                series.lineThickness = highlight ? 5 : 2; // Thicker line on hover
                series.opacity = highlight ? 1 : 0.5; // Highlight the line
            } else {
                series.opacity = highlight ? 0.3 : 1; // Dim other lines
            }
        });
        chartInstance.render();
    }
</script>

<script>
    //report charts by date
    async function fetchReportData(filterType = '', startDate = '', endDate = '') {
        try {
            let url = `/store-reports/?filter=${filterType}`;
            if (filterType === 'custom') {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            return data.data.reportData;
        } catch (error) {
            console.error('Error fetching report data:', error);
            return null;
        }
    }

    async function updateChartByDates(filter = 'today') {
        let startDate = '';
        let endDate = '';
        if (filter === 'custom') {
            startDate = document.getElementById('start-date').value;
            endDate = document.getElementById('end-date').value;
        }
        if (filter && filter == 'custom') {
            const links = document.querySelectorAll('.report-navigation-links a');
            if (startDate != '' && endDate != '') {
                links.forEach(link => link.classList.remove('active-link'));
            }
        }
        if (filter == 'today') {
            const links = document.querySelectorAll('.report-navigation-links a');
            links.forEach(link => link.classList.remove('active-link'));
            var todays = document.querySelector('#todays-link');
            todays.classList.add('active-link');
        }
        var targetDiv = document.querySelector(
            'body > main > div > div.dshbrdin.dashprdlst.storeproducts-page.home-section.cm-edit-ja > div.container-fluid > div > div > div > div.order-chart > nav > div'
        );
        if (targetDiv) {
            targetDiv.style.borderTopLeftRadius = "0px";
        }

        const reportData = await fetchReportData(filter, startDate, endDate);

        document.getElementById("loading").style.display = 'none';
        if (!reportData) {
            return;
        }

        const dataInfoContainer = document.getElementById("dataInfoContainer");
        dataInfoContainer.innerHTML = `
                <div id="grossSalesCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.grossSales.toFixed(2)}</h5>
                        <p class="card-text">Gross sales in this period</p>
                    </div>
                </div>
                <div id="averageGrossSalesCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.averageGrossSales.toFixed(2)}</h5>
                        <p class="card-text">Average gross daily sales</p>
                    </div>
                </div>
                <div id="netSalesCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.netSales.toFixed(2)}</h5>
                        <p class="card-text">Net sales in this period</p>
                    </div>
                </div>
                <div id="averageNetSalesCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.averageNetSales.toFixed(2)}</h5>
                        <p class="card-text">Average net daily sales</p>
                    </div>
                </div>
                <div id="ordersPlacedCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">${reportData.sales.ordersPlaced}</h5>
                        <p class="card-text">Order${reportData.sales.ordersPlaced === 1 ? '' : 's'} placed</p>
                    </div>
                </div>
                <div id="itemsPurchasedCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">${reportData.sales.itemsPurchased}</h5>
                        <p class="card-text">Item${reportData.sales.itemsPurchased === 1 ? '' : 's'} purchased</p>
                    </div>
                </div>
                <div id="refundedOrdersCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.refundedOrders.toFixed(2)}</h5>
                        <p class="card-text">Refunded in ${reportData.sales.refundedOrdersCount} order${reportData.sales.refundedOrdersCount === 1 ? '' : 's'}</p>
                    </div>
                </div>
                <div id="chargedForShippingCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.chargedForShipping.toFixed(2)}</h5>
                        <p class="card-text">Charged for shipping</p>
                    </div>
                </div>
                <div id="worthOfCouponsCard" class="card">
                    <div class="card-body">
                        <h5 class="card-title">$${reportData.sales.worthOfCoupons.toFixed(2)}</h5>
                        <p class="card-text">Worth of coupons used</p>
                    </div>
                </div>
            `;

        updateChartData(reportData.chartData);
    }
</script>

<script>
    async function salesByProduct(filter = 'last_7_days') {

        var targetDiv = document.querySelector(
            'body > main > div > div.dshbrdin.dashprdlst.storeproducts-page.home-section.cm-edit-ja > div.container-fluid > div > div > div > div.order-chart > nav > div'
        );
        if (targetDiv) {
            targetDiv.style.borderTopLeftRadius = "12px";
        }
        var errorMessage = document.getElementById('errorMessage');
        var chartDiv = document.getElementById('chart_div');

        chartDiv.style.display = "none";
        errorMessage.style.display = "block";
        errorMessage.innerHTML = "Choose a Product to view Stats";

        var dataInfoContainer = document.getElementById("dataInfoContainer");
        dataInfoContainer.innerHTML = "";
        setActiveClass('');

        updateChartByProduct();
        dataInfoContainer.innerHTML += `
           <div class="card" style="padding: 10px; background: #fefefe78;">
                <div id="productDetail" class="mb-3"></div>
                    <div class="accordion accordion-flush" id="accordionFlushExample" style="padding: 10px; padding: 0px;">
                        <div class="accordion-item" style="border: 1px solid lightgrey;">
                            <h4 class="accordion-header" id="flush-headingTwo" style="padding: 10px; background: #e9ecef; padding: 0px; border-radius: 8px;">
                                <button class="accordion-button collapsed " type="button"   data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo" style="background: #e9ecef; border: none; padding: 10px; width: 100%;">
                                    <div style="display: flex; justify-content:space-between;">
                                        <b>Product Search &nbsp;&nbsp;&nbsp; </b>
                                  <i class="bi bi-arrow-down-circle-fill"></i>
                                    </div>

                                </button>
                            </h4>
                            <div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body" style="background: #fff; padding: 10px;">
                                     <div id="select2Product"></div>
                                     <button class="btn btn-primary mb-3 searchBtn" onclick="handleSelectedProductClick()">Show</button>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" style="border: 1px solid lightgrey;">
                            <h4 class="accordion-header" id="flush-headingThree" style="padding: 10px; background: #e9ecef; padding: 0px; border-radius: 8px;">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree" style="background: #e9ecef; width: 100%; border: none; padding: 10px;">
                                   <div style="display: flex; justify-content:space-between;">
                                        <b>Top seller &nbsp;&nbsp;&nbsp; </b>
                                   <i class="bi bi-arrow-down-circle-fill"></i>
                                    </div>
                                </button>
                            </h4>
                            <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    <ul style="max-height:350px;overflow:auto;width:100%;">

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>


        `;
    }
    async function getTopSellingProducts() {
        $.ajax({
            url: '/get-store-products?type=top',
            method: 'GET',
            success: function(data) {
                const topSellers = data.data.topSellers;
                const productList = $('#flush-collapseThree .accordion-body ul');
                productList.empty(); // Clear the existing list

                if (topSellers.length === 0) {
                    productList.append(
                        '<br><li style="text-align: center;" class="no-record">No Record found</li><br>'
                    );
                } else {
                    topSellers.forEach(function(product) {
                        productList.append(`
                        <a href="#" onclick="handleProductItemClick('${product.product_id}'); setTopSellerActive(this);">
                            <li class="productList" id="${product.product_id}">${product.title}</li>
                        </a>
                    `);
                    });
                }
            }
        });
    }
    async function getAllProductList() {
        $.ajax({
            url: '/get-store-products?type=all',
            method: 'GET',
            success: function(data) {
                const allProducts = data.data.topSellers;
                console.log(allProducts);
                const selectOptions = allProducts.map(product => `
                <option value="${product.product_id}">${product.title}</option>
                    `).join('');

                $('#select2Product').html(`
                    <select class="js-example-basic-single" name="product[]" multiple="multiple" id="selectProduct">
                        ${selectOptions}
                    </select>
                    `);

                // Initialize the select2 plugin
                $('.js-example-basic-single').select2();
            }
        });
    }

    async function updateChartByProduct() {
        getTopSellingProducts();
        getAllProductList();
    }

    function handleSelectedProductClick() {
        const selectedProductId = $('.js-example-basic-single').val();
        handleProductItemClick(selectedProductId);
    }
</script>
<script></script>
<script>
    function addCategorySearchSelect() {
        $.ajax({
            url: 'get-store-categories/',
            method: 'GET',
            success: function(response) {
                console.log(response)
                if (response.data.categorySales) {
    // Generate the dynamic select HTML
    let selectHTML = '<select class="js-example-basic-multiple" name="categories[]" multiple="multiple">';

    // Loop through each category in the categorySales array
    for (const category of response.data.categorySales) {
        // Only consider parent categories (parent === 0)
        if (category) {
            selectHTML += `<option value="${category.categoryId}">${category.categoryName}</option>`;

        }
    }

    selectHTML += '</select>';

    // Insert the generated HTML into the #select2Category div
    $('#select2Category').html(selectHTML);

    // Initialize select2 plugin
    $('.js-example-basic-multiple').select2();
}

            },
            error: function(error) {
                console.error('Error fetching categories:', error);
            }
        });
    }

    function showAllCategoryChart(filter = '') {
        let requestData = {
            filter: filter
        };
        $.ajax({
            url: '/store-reports-by-category',
            method: 'GET',
            data: requestData,
            success: function(response) {
                if (response.data.categorySales) {
                    let dataInfoContainer = document.getElementById("dataInfoContainer");
                    dataInfoContainer.style.display = "none";

                    const filteredChartData = filterZeroRecords(response.data.chartData);

                    applyUniqueColors(filteredChartData.datasets);
                    displaySalesCards(response.data.categorySales, filteredChartData);
                    displayChart(filteredChartData);
                } else {
                    console.error('No sales data found.');
                }
            },
            error: function(error) {
                console.error('Error fetching sales data:', error);
            }
        });
    }

    var categoriesGlobal = null;
    var requestType = null;

    function resetCategoryHandler() {
        categoriesGlobal = null;
        var categoryDetailDiv = document.querySelector('#categorySalesValue');
        setActiveClass('');
        categoryDetailDiv.innerHTML = '';
        var chartDiv = document.getElementById('chart_div');
        chartDiv.style.display = "none";
        $('.js-example-basic-multiple').val(null).trigger('change');
        errorMessage.style.display = "block";
        errorMessage.innerHTML = "Choose a Category to view Stats";
    }

    function showSalesCategory(RequestType = '', filter = 'today', startDate = '', endDate = '') {
        let selectedCategories;
        let requestData
        if (RequestType != null) {
            requestType = RequestType;
        }
        console.log(filter);
        console.log(RequestType);

        if (requestType == 'all') {

            requestData = {
                filter: filter
            };

        } else {

            if (filter === 'today') {
                const links = document.querySelectorAll('.report-navigation-links a');
                links.forEach(link => link.classList.remove('active-link'));
                var todays = document.querySelector('#todays-link');
                selectedCategories = $('.js-example-basic-multiple').val();
                categoriesGlobal = selectedCategories;
            } else {
                selectedCategories = categoriesGlobal;
            }
            if (filter === 'custom') {
                if (startDate == '' && endDate == '') {
                    alert('Please select dates properly.');
                    return;
                }
            }
            if (!selectedCategories || selectedCategories.length === 0) {
                alert('Please select at least one category.');
                return;
            }
            requestData = {
                category_ids: selectedCategories,
                filter: filter
            };
        }
        if (filter === 'custom') {
            requestData.start_date = startDate;
            requestData.end_date = endDate;
        }

        $.ajax({
            url: '/store-reports-by-category',
            method: 'GET',
            data: requestData,
            success: function(response) {
                if (response.data.categorySales) {
                    let dataInfoContainer = document.getElementById("dataInfoContainer");
                    dataInfoContainer.style.display = "none";

                    const filteredChartData = filterZeroRecords(response.data.chartData);
                    console.log('filterd data', response.data.chartData);

                    applyUniqueColors(filteredChartData.datasets);
                    displaySalesCards(response.data.categorySales, filteredChartData);
                    displayChart(filteredChartData);
                } else {
                    console.error('No sales data found.');
                }
            },
            error: function(error) {
                console.error('Error fetching sales data:', error);
            }
        });
    }

    function filterZeroRecords(chartData) {
        const nonZeroIndexes = chartData.labels.map((date, index) => {
            return chartData.datasets.some(dataset => dataset.data[index] !== 0);
        });

        const filteredLabels = chartData.labels.filter((date, index) => nonZeroIndexes[index]);
        const filteredDatasets = chartData.datasets.map(dataset => {
            return {
                ...dataset,
                data: dataset.data.filter((value, index) => nonZeroIndexes[index])
            };
        });

        return {
            labels: filteredLabels,
            datasets: filteredDatasets
        };
    }

    function applyUniqueColors(datasets) {
        const colors = [
            'rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)', 'rgba(199, 199, 199, 0.2)', 'rgba(83, 102, 255, 0.2)',
            'rgba(255, 159, 128, 0.2)', 'rgba(155, 159, 64, 0.2)'
        ];
        const borderColors = [
            'rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)', 'rgba(199, 199, 199, 1)', 'rgba(83, 102, 255, 1)',
            'rgba(255, 159, 128, 1)', 'rgba(155, 159, 64, 1)'
        ];

        datasets.forEach((dataset, index) => {
            dataset.backgroundColor = colors[index % colors.length];
            dataset.borderColor = borderColors[index % borderColors.length];
        });
    }

    function displaySalesCards(categorySales, chartData) {
        let grossSalesCard = document.getElementById("dataInfoContainer");
        let categorySalesValue = document.getElementById("categorySalesValue");
        categorySalesValue.style.display = "block";
        categorySalesValue.innerHTML = '';
        grossSalesCard.style.display = "block";
        // grossSalesCard.innerHTML = '';

        categorySales.forEach((category, index) => {
            const dataset = chartData.datasets.find(ds => ds.label === category.category_name);
            const borderColor = dataset ? dataset.borderColor : 'black';

            categorySalesValue.innerHTML += `
            <div class="card" style="border-right: 4px solid ${borderColor} !important;">
                <div class="card-body">
                    <h5 class="card-title">$${category.sales.toFixed(2)}</h5>
                    <p class="card-text">Sales In ${category.category_name}</p>
                </div>
            </div>
        `;
        });
    }

    function displayChart(chartData) {
        const chartSeries = chartData.datasets.map(dataset => ({
            dataPointMaxWidth: 20,
            type: "column",
            name: dataset.label,
            showInLegend: false,
            dataPoints: dataset.data.map((value, index) => ({
                x: new Date(chartData.labels[index]),
                y: value
            })),
            color: dataset.borderColor,
            barThickness: 20
        }));

        const chart = new CanvasJS.Chart("chart_div", {
            zoomEnabled: true,
            zoomType: "xy",
            animationEnabled: true,
            theme: "light2",
            axisX: {
                valueFormatString: "DD MMM",
                intervalType: "day",
            },
            axisY: {
                includeZero: false
            },
            data: chartSeries
        });

        if (chartSeries.every(series => series.dataPoints.length === 0)) {
            document.getElementById('chart_div').style.display = "none";
            document.getElementById('errorMessage').style.display = "block";
            document.getElementById('errorMessage').innerHTML = "No data available for the selected filter.";
        } else {
            document.getElementById('chart_div').style.display = "block";
            document.getElementById('errorMessage').style.display = "none";
            chart.render();
        }
    }

    function salesByCategory() {
        addCategorySearchSelect();
        var targetDiv = document.querySelector(
            'body > main > div > div.dshbrdin.dashprdlst.storeproducts-page.home-section.cm-edit-ja > div.container-fluid > div > div > div > div.order-chart > nav > div'
        );
        if (targetDiv) {
            targetDiv.style.borderTopLeftRadius = "12px";
        }
        var errorMessage = document.getElementById('errorMessage');
        var chartDiv = document.getElementById('chart_div');

        chartDiv.style.display = "none";
        errorMessage.style.display = "block";
        errorMessage.innerHTML = "Select Category to view Stats";

        var dataInfoContainer = document.getElementById("dataInfoContainer");
        dataInfoContainer.innerHTML = "";
        setActiveClass('');
        dataInfoContainer.innerHTML += `
               <div class="card" style="padding: 10px; background: #fefefe78;">
                <div id="categorySalesValue" style="margin-bottom:8px;"></div>
        <div class="accordion accordion-flush" id="accordionFlushExample" style="padding: 10px; padding: 0px;">
            <div class="accordion-item" style="border: 1px solid lightgrey;">
                <h4 class="accordion-header" id="flush-headingOne" style="padding: 10px; background: #e9ecef; padding: 0px; border-radius: 8px;">
                    <button class="accordion-button collapsed" type="button"  data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne" style="background: #e9ecef; border: none; padding: 10px; width: 100%;">
                      <div style="display: flex; justify-content:space-between;">
                            <b>Categories &nbsp;&nbsp;&nbsp; </b>
                        <i class="bi bi-arrow-down-circle-fill"></i>
                        </div>
                    </button>
                </h4>
                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                    <div class="accordion-body" style="background: #fff; padding: 10px;">
                          <div id="select2Category"></div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <button class="btn btn-primary" onclick="showSalesCategory('show')">Show</button>
                            <div>
                            <a  href="#" onclick="selectAllCategories()" style="text-decoration:underline!important;">All</a>
                            <a  href="#" onclick="resetCategoryHandler()" style="text-decoration:underline!important;">None</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            `;
    }
</script>

<script>
    // Function to handle filter clicks
    function handleFilterClick(filter) {

        var activeTabId = $('#myTab .nav-link.active').attr('id');

        if (activeTabId === 'sales_by_date-tab') {
            updateChartByDates(filter);
        } else if (activeTabId === 'sales_by_product-tab') {
            handleProductItemClick(null, filter);
        } else if (activeTabId === 'sales_by_category-tab') {
            showSalesCategory(null, filter);
        }
    }

    // Function to handle custom date range
    function updateCustomDateRange() {
        var startDate = $('#start-date').val();
        var endDate = $('#end-date').val();
        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }
        var activeTabId = $('#myTab .nav-link.active').attr('id');
        if (activeTabId === 'sales_by_date-tab') {
            updateChartByDates('custom', startDate, endDate);
        } else if (activeTabId === 'sales_by_product-tab') {
            handleProductItemClick(null, 'custom', startDate, endDate);
        } else if (activeTabId === 'sales_by_category-tab') {
            showSalesCategory(null, 'custom', startDate, endDate);
        }
    }

    $(document).ready(function() {
        // Get today's date in yyyy-mm-dd format
        var today = new Date().toISOString().split('T')[0];

        $('#start-date').on('change', function() {
            var startDate = $(this).val();
            var endDate = $('#end-date').val();

            if (!startDate) {
                $('#end-date').val('');
                $('#end-date').attr('min', ''); // Set min value for end date
                $('#end-date').attr('max', ''); // Set max value to today
                return;
            }
            // If end date is empty, set it to the next day of the start date
            if (!endDate) {
                var nextDay = new Date(startDate);
                nextDay.setDate(nextDay.getDate() + 1); // Add one day
                var formattedNextDay = nextDay.toISOString().split('T')[0];
                $('#end-date').val(formattedNextDay);
            }

            $('#end-date').attr('min', startDate); // Set min value for end date
            $('#end-date').attr('max', today); // Set max value to today

            // Clear end date if it's invalid
            if (new Date(endDate) < new Date(startDate)) {
                $('#end-date').val('');
            }
        });

        $('#end-date').on('change', function() {
            var endDate = $(this).val();
            var startDate = $('#start-date').val();

            if (!endDate) {
                $('#start-date').val('');
                return;
            }
            // If start date is empty, set it to the previous day of the end date
            if (!startDate) {
                var previousDay = new Date(endDate);
                previousDay.setDate(previousDay.getDate() - 1); // Subtract one day
                var formattedPreviousDay = previousDay.toISOString().split('T')[0];
                $('#start-date').val(formattedPreviousDay);
            }

            // Check if the end date is before the start date
            if (new Date(endDate) < new Date(startDate)) {
                alert('End date must be greater than or equal to the start date.');
                $(this).val(''); // Clear the end date input
            }
        });
    });
</script>

@endsection
