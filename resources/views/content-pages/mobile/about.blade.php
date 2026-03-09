@extends('layouts.mobile-main')
@section('content')
@section('title', 'About Us')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
 

    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {

        }
    </style>
    <section class="aboutus-container">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-white-sec pos-rel mt-40 pt-30">
                        <h3 class="mb-10">About Us</h3>
                        <p>
                            Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn. 

                        </p>
                        <h3 class="mt-25 mb-10">
                            Address
                        </h3>
                        <p>
                            25th Lake Avenue, Advent Road,<br>
                            LosVegas, 56555, US
                        </p>

                        <h3 class="mt-25 mb-10">
                            Contact
                        </h3>
                        <p>
                            bass@gmail.com<br>
                            +1 4446 4460054
                        </p>                       
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
<!-- Content Ends-->




