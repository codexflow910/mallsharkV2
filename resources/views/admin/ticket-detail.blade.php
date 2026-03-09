@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Ticket')
<!-- Content Starts -->
<main>
     @include('admin.adminsidebar')
  
<section>
   <div class="ticket-detail">
      <div class="container">
         <div class="site-inner">
            <div class="support-landing">
               <div class="row">
                  <div class="col-sm-12">
                     <h2 class="support-heading text-center colr-orange">Support &amp; Help</h2>
                     <div class="support-box">
                        <div class="ticket-panel">
                           <div class="tab-content">
                              <div  class="support-help-container">
                                <div class="original-ticket">

                                    <div class="flex-container ticket-box">
                                        <div class="flex-box width75 Aligner-item">
                                            <div class="ticket-body">

                                            <h4><a href="#"><span>
                                            <i class="fa fa-support" aria-hidden="true"></i>
                                          </span><span>Ticket #452666:</span> I cannot log into my account after changed my password.</a></h4>
                                          </div>
                                        </div>
                                      <div class="flex-box width15 text-center">
                                        <a href="{{URL::to('/admin/tickets-list')}}">Back to List</a>
                                      </div>
                                    </div>
                                                                                                      
                                </div>

                                <div class="mark-resolved text-right mb-30 mt-30">
                                          <button type="submit" class="btn btn-primary text-white" id="">
                                              <span class="small-screen-btn">Mark Resolved</span>
                                          </button>                                  
                                </div>

                                 <ul class="ticketing-listing">
                                	<li>
                                		<div class="flex-container ticket-box">
                                			<div class="flex-box width10">
                                				<div class="ticket-logo Aligner-item">
                                					<span class="support-logo">
                                            <img src=" {{ asset('assets/images/logo.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                                					</span>
                                				</div>
                                			</div>
                                			<div class="flex-box width75 Aligner-item">
                                        <div class="ticket-body">
                                				<h4>Hi, Johanson <span class="ticket-date">12 March 2017</span></h4>
                                				<!-- <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec fringilla turpis egestas mauris scelerisque porttitor. Sed in fringilla augue. Integer congue, tellus at feugiat tincidunt, ligula nibh vehicula ex, nec ornare sapien tortor quis dolor</p> -->
                                      </div>
                                			</div>
                                			<div class="flex-box width15 text-center">
                                        <a href="#" class="replay-button">Replay</a>
                                			</div>
                                		</div>
                                	</li>
                                  <li>
                                    <div class="flex-container ticket-box">
                                      <div class="flex-box width10">
                                        <div class="ticket-logo Aligner-item">
                                          <span class="support-logo">
                                            <img src=" {{ asset('assets/images/logo.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                                          </span>
                                        </div>
                                      </div>
                                      <div class="flex-box width75 Aligner-item">
                                        <div class="ticket-body">
                                        <h4>Hi, Johanson <span class="ticket-date">12 March 2017</span></h4>
                                        <!-- <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec fringilla turpis egestas mauris scelerisque porttitor. Sed in fringilla augue. Integer congue, tellus at feugiat tincidunt, ligula nibh vehicula ex, nec ornare sapien tortor quis dolor</p> -->
                                      </div>
                                      </div>
                                      <div class="flex-box width15 text-center">
                                        <a href="#" class="replay-button">Replay</a>
                                      </div>
                                    </div>
                                  </li>
                                 </ul>
                              </div><!-- tab-content -->

                              <div class="replay-comment-box">
                                <textarea class="form-control"></textarea>
                                <div class="text-right mt-10">
                                  <button type="submit"> Send</button>
                                </div>
                              </div><!-- replay-comment-box -->


                              <div class="original-ticket-box">
                                  <div class="ticket-box-mbt">
                                      <div class="strp-head-mbt"><span>Original Ticket</span></div>
                                    <div class="flex-container ticket-box">
                                      <div class="flex-box width10">
                                        <div class="ticket-logo Aligner-item">
                                          <span class="support-logo">
                                            <img src=" {{ asset('assets/images/tman-1.png',env('HTTPS_STATUS')) }}" class="img-fluid">
                                          </span>
                                        </div>
                                      </div>
                                      <div class="flex-box width75 Aligner-item">

                                        <h4>To S6Net Support, <span class="ticket-date">12 March 2017</span></h4>
                                        <!-- <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec fringilla turpis egestas mauris scelerisque porttitor. Sed in fringilla augue. Integer congue, tellus at feugiat tincidunt, ligula nibh vehicula ex, nec ornare sapien tortor quis dolor</p> -->

                                      </div>

                                    </div>
                                  </div>                                
                              </div><!-- original-ticket-box -->



                           </div>

                        </div>
                        <!-- /.ticket-panel -->
                     </div>
                     <!-- /.support-box -->
                  </div>
               </div>
            </div>
            <!-- Modal -->

         </div>
      </div>
   </div>
   <!-- tickets-listing -->
</section>
<script>
  
$(".replay-button").click(function() {
    $('html,body').animate({
        scrollTop: $(".replay-comment-box").offset().top},
        'slow');
});

</script>

    
@endsection