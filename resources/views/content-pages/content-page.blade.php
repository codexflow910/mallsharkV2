<?php
if (isset($content[0]['page_name'])) {
    $page_name = $content[0]['page_name'];
    $page_content = $content[0]['page_content'];
} else {
    echo 'No Record Foun';
    die();
}
?>
@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title',$page_name)
<?php $desciption = substr($page_content, 0, 100); ?>
@section('description',$desciption)
<main>
   

    <style>
        /* @media only screen and (min-width : 767px) and (max-width : 10000px) {
            .search-icon-header{ display: none; }
        } */
    </style>
    <section>
        <div class="container product-detail-mbt mt-55">
            <div class="white-background cart-sec-opt shahdow-all w100p">
                <h1>{{$page_name}}</h1>
                <br/>
                <?php echo $page_content; ?>
            </div>

        </div>
    </section>






    <style>.show-more-results{
            display:none;
        }
    </style>

</main>
@endsection
<!-- Content Ends-->




