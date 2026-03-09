@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Seller FAQ: Selling on MallShark')
@section('description', "Explore MallShark's Seller FAQ for valuable insights and support in selling successfully on our platform.")
@section('keyword', "Seller FAQ
MallShark
Selling on MallShark,
Frequently Asked Questions for Sellers,
MallShark.com,
Seller guide,
Seller policies,
Seller support,
Selling tips,
Seller registration,
MallShark marketplace,
Seller account")
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>


    <style>
    @media only screen and (min-width : 320px) and (max-width : 767px) {

        .faqs-container .h4,
        .faqs-container h4 {
            font-size: 1.1rem;
        }
    }
    </style>




<section class=" terms-condition-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>Frequently Asked Questions - Seller</h1>
                        
                    </div>
                    <div class=" terms-wrapper faqsec  mt-40 pt-30"> 
                        <div class="container">
        <div class="row">
        <div class="col-sm-12">            
            <div itemscope="" itemtype="https://schema.org/FAQPage">                                                                                                                                                                                                                                                                                                                                                                                                                                            

                <div class="data-div pos-rel mt-0 pt-30">
                    <!-- <p>
                        Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van
                        een
                        pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem
                        Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot
                        "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop
                        publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard
                        model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw
                        zijn.
                    </p> -->
                    <div class="panel-group" id="accordion"> 
                         
                    <?php
                        $id = 0;
                        foreach($faqs as $faq){
                            //echo 'Data: <pre>' .print_r($faq,true). '</pre>';
                        $id++;
                            if($faq->type == 'seller-faqs'){
                        ?>
                        <div class="accordian">
                            <div class="card">
                            <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                                <div class="card-header" id="heading_{{$id}}">
                                
                                        <a class="btn btn-header-link collapsed" data-toggle="collapse"
                                             data-target="#collapse{{$id}}" aria-expanded="true" aria-controls="collapse{{$id}}"
                                            itemprop="name"> Q: {{$faq->question}}</a>
                                   
                                </div>
                                <div id="collapse{{$id}}" aria-labelledby="heading_{{$id}" class="collapse" data-parent="#accordion">
                                    <div class="card-body">
                                        <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                            <div itemprop="text">
                                            <?php echo  stripslashes($faq->answer); ?>
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
                      
 
                       
                    </div>
                </div>
            </div> 
        </div>
    </section>
 
</main>
@endsection
<!-- Content Ends-->