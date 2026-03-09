@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')


<section class="admin-dashboard dshbrdin buyer mt-0">
	 @include('support.supportsidebar')
	<div class="dshbrdinadmin list-management">

        <div class="container">
            <div class="row">
                <div class="col-sm-12 mt-40">
                    <h3>Listing Filters</h3>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="search-field-ability">
                        <input type="search" name="" class="form-control" placeholder="Search">
                    </div>
                </div>

                <div class="col-sm-6 col-md-6">
                    <div class="edit-active buttons-feature-group clearfix">
                        <a href="javascript:;" class="btn btn-primary btn-simple f-left-md">Add Mover</a>
                    </div>
                </div>



        </div>

                <div class="row">
                    <div class="custom-pagination col-sm-12 mt-15">
<nav aria-label="Page navigation example ">
  <ul class="pagination pull-right">
    <li class="page-item"><a class="page-link" href="#">Previous</a></li>
    <li class="page-item"><a class="page-link" href="#">1</a></li>
    <li class="page-item"><a class="page-link" href="#">2</a></li>
    <li class="page-item"><a class="page-link" href="#">3</a></li>
    <li class="page-item"><a class="page-link" href="#">Next</a></li>
  </ul>
</nav>                        
                    </div>

            </div>

            <div class="row mt-60 justify-content-center ">
                    <div class="col-xs-10 col-md-5">
                        <div class="card all-shahdow bx-pnl-box pl-15 pr-15">
                            <h5>Change Status</h5>

                            <div class="active-deactive-panel mt-20">   

                            <div class="mb-30">
                            <span class="display-inline-block">
                                <input type="radio" class="describe-check" id="active" name="chk_describe" value="1" > 
                                <label for="active">Activate</label>
                            </span>                                
                            <span class="display-inline-block">
                                <input type="radio" class="describe-check" id="deactive" name="chk_describe" value="1" > 
                                <label for="deactive">Deactivate</label>
                            </span>   
                        </div>

                            <div class="edit-active buttons-feature-group clearfix">
                                <a href="javascript:;" class="btn btn-primary btn-simple f-left-md">Cancel</a>  
                                    <button type="submit" class="btn btn-primary text-white f-right-md" id="">
                                        <span class="small-screen-btn">Save</span>
                                    </button>                                                       
                            </div> 

                            </div>
 
                        </div>
                    </div>
            </div>

	</div><!-- dshbrdinadmin -->

</section>


@endsection