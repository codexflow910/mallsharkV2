@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'General FAQ')
@section('description', 'Frequently Asked Questions (FAQs).General FAQs for customers who are purchasing from Mallshark.')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>


    <style>
          .circule_wallet_gernal{
          color:#0000a7;
        }
    .faqs-container .h4,
    .faqs-container h4 {
        font-size: 1.1rem;
    

    @media only screen and (min-width : 767px) and (max-width : 10000px) {}
    </style>

    <div class="col-sm-12 faqs-container ">
        <div itemscope="" itemtype="https://schema.org/FAQPage">
            <div class="data-div pos-rel mt-0 pt-30">
                <p>
                 

                </p>
                <div class="panel-group" id="accordion">
                <h1 class="faqHeader">General questions</h1>
                <?php
                        $id = 0;
                        foreach($faqs as $faq){
                            //echo 'Data: <pre>' .print_r($faq,true). '</pre>';
                        $id++;
                            if($faq->type == 'general-faqs'){
                        ?>
                    <div class="accordian">
                        <div class="card">
                        <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                            <div class="card-header" id="heading_{{$id}}">
                        
                                    <a class="btn btn-header-link collapsed" data-toggle="collapse"
                                        data-parent="#accordion" data-target="#collapse{{$id}}" aria-expanded="true" aria-controls="collapse{{$id}}" itemprop="name">{{$faq->question}}</a>
                               
                            </div>
                            <div id="collapse{{$id}}"  aria-labelledby="heading_{{$id}" class=" collapse" data-parent="#accordion"
                                style="height: 0px;">
                                <div class="card-body">
                                    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                        <div itemprop="text">
                                        <?php echo stripslashes($faq->answer); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <?php 
                             }
                            }
                            ?>
                    
                </div>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection
<!-- Content Ends-->