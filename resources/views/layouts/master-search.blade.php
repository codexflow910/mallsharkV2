@include('layouts.header')

<main>
      @yield('content-form-area')  

<div class="container-fluide rslts">
          <div class="row">
              <!-- Filter Box Starts -->
            <div class="col-md-12  ">

 
      
        @yield('content')
   
</div>
</div>
</div>
</main>
@include('layouts.footer')