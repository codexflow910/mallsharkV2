@extends('layouts.frontend-main')
@section('content')
<style>
    .search-icon-commingsoon{
        font-size: 20px;
    }
</style>

<div class="container" style="min-height:450px;">
    <div class="error-wrapper coming-soon mt-50">
        <div class="error-detail">
            <div class="error-title">
                <h1><b>COMING SOON</b></h1>
            </div>

            <div class="error-description">
                <h6>Something Sharktastic is in the works!</h6>
              
            </div>
 
         
             
             
        </div>
    </div>
</div>
<script>
    // jQuery(".autocomplete-field").autocomplete({
    //     minLength: 2,
    //     source: function(request, response) {
    //         requestt = request.term;
    //         jQuery.ajax({
    //             url: "/esearch-keyword",
    //             method: "POST",
    //             data: {
    //                 keyword: request.term
    //             },
    //             success: function(data) {

    //                 response(data);
    //             },
    //             focus: function(event, ui) {

    //             },
    //         });
    //     },
    //     select: function(event, ui) {
    //         console.log(event);
    //         if (ui.item) {
    //             val2 = ui.item.value;
    //             var res = val2.split(" in category ");
    //             //ParsedElements = $(htmlToParse);
    //             if (res.length > 1) {
    //                 $('.autocomplete-field').val(res[0]);
    //                 jQuery('#category_parentjs').val(res[1]);
    //             } else {
    //                 var res = val2.split(" in brands");
    //                 if (res.length > 1) {

    //                     window.location.href = "/brands?keyword=" + requestt;
    //                     return false;
    //                 }
    //             }
    //             $('#category_parentjs').removeAttr("disabled");
    //         }
    //         jQuery(".search-autocomplete-form").submit();
    //     }

    // })
</script>
@endsection