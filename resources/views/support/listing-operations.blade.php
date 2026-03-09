@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')


<section class="admin-dashboard dshbrdin buyer mt-0">
  @include('support.supportsidebar')
    <div class="dshbrdinadmin list-management">

        <div class="container">
            <div class=" ">
                <div class="col-sm-12 mt-40">
                    <h3>Manage Listing Filters</h3>
                </div>
                
                <!-- Search Data Bar -->
                <div class="col-md-12">
                
                <div class="row">
                    <div class="col srchpr">
                        <input class="form-control" type="text" placeholder="Search">
</div>
                    <div class="col text-right "><a href="" class="btn btn-primary btn-simple  ">Add More</a></div>
                </div>
                <!-- Search Data Bar Ends -->
              <!-- Head Bar Starts -->
              <div class="rdbr  ">
                  <div class="row">
                  <div class="col">Filters</div>
                   <div class="col text-right">Action</div>
                   </div>
              </div>
              <!-- Head Bar Ends -->
              <br>
              <!-- Active Filters Starts -->
              
              
                <!-- Filter Segment Starts -->
                
                
                
                <div class="accordion fltrseg" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      
        <a class=" "  data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
         Conditions
        </a>
        <span class="acbx float-right ">
             <a href="#">Add Value</a> &nbsp;&nbsp;&nbsp;
             <a href="#">Remove</a>
        </span>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
          <div class="row fltopt">
              <div class="col">New</div>
              <div class="col text-right"><a href="#">Add</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="#">Remove</a></div>
          </div>
          <div class="row fltopt">
              <div class="col">Used</div>
              <div class="col text-right"><a href="#">Add</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="#">Remove</a></div>
          </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <a class=" collapsed"  data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
         Categories
        </a>
        <span class="acbx float-right ">
             <a href="#">Add Value</a> &nbsp;&nbsp;&nbsp;
             <a href="#">Remove</a>
        </span>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
          <div class="row fltopt">
              <div class="col">New</div>
              <div class="col text-right"><a href="#">Add</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="#">Remove</a></div>
          </div>
          <div class="row fltopt">
              <div class="col">Used</div>
              <div class="col text-right"><a href="#">Add</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href="#">Remove</a></div>
          </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingThree">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Collapsible Group Item #3
        </button>
      </h5>
    </div>
    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
      <div class="card-body">
        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
      </div>
    </div>
  </div>
</div>
                
                
                
                <!-- Filter Segment Ends -->
              
              
              
              
              
              <!-- Active Filters Ends -->





            </div>
        </div>
    </div><!-- dshbrdinadmin -->

</section>


@endsection