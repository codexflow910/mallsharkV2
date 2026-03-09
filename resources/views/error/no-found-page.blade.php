
@extends('layouts.dashboard-main')
@section('content')

<div class="container">
    <div class="error-wrapper 404error">
        <div class="error-detail">
            <div class="error-title">
                <h1>404</h1>
            </div>

            <div class="error-description">
                <h6><i class="fa fa-warning"></i>Sorry but we couldnt find this page</h6>
                <p>We could not find the page you were looking for. Meanwhile, you may return to Home Page or try using the search form.</p>
            </div>

            <div class="error-image">
                <img src="{{ URL::asset('assets/images/crossbones.png') }}" alt="image" srcset="">
 
            </div>

            <form method="get" action="{{ route('home.search') }}" class="search-autocomplete-form">

                <div class="error-input-field">
                    <div class="error-input-field">
                        <div class="input-group  mt-3 mb-4">
                            <input type="text" name="keyword" value="<?php if (isset($_GET['keyword'])) {
                                                                            echo $_GET['keyword'];
                                                                        } ?>" class="form-control autocomplete-field" placeholder="Search for something else" aria-label="Recipient's username" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <span class="input-group-text" id="basic-addon2">
                                    <button class="search-button-icons" type="submit"><i class="fa fa-search"></i></span></button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <div class="error-button">
              <a href="/"><button class="btn btn-primary">Go to Home Page</button></a>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(".autocomplete-field").autocomplete({
        minLength: 2,
        source: function(request, response) {
            requestt = request.term;
            jQuery.ajax({
                url: "/esearch-keyword",
                method: "POST",
                data: {
                    keyword: request.term
                },
                success: function(data) {

                    response(data);
                },
                focus: function(event, ui) {

                },
            });
        },
        select: function(event, ui) {
            console.log(event);
            if (ui.item) {
                val2 = ui.item.value;
                var res = val2.split(" in category ");
                //ParsedElements = $(htmlToParse);
                if (res.length > 1) {
                    $('.autocomplete-field').val(res[0]);
                    jQuery('#category_parentjs').val(res[1]);
                } else {
                    var res = val2.split(" in brands");
                    if (res.length > 1) {

                        window.location.href = "/brands?keyword=" + requestt;
                        return false;
                    }
                }
                $('#category_parentjs').removeAttr("disabled");
            }
            jQuery(".search-autocomplete-form").submit();
        }

    })
</script>
@endsection