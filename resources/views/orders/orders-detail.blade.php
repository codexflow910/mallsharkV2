

@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'S6')
@include('vendor.sidebar')

<main>
	<section class="order-detail">
		<div class="container">
			<div class="order-detail-head row">
				<div class="col-sm-6">
					<h3>Order # 345 Detail</h3>
					<p>Paymen via Paypal, Jun 152017</p>
				</div>
				<div class="col-sm-6">
					<div class="search-detail">
						<span class="search-heading-order">Search Orders:</span>
						
						<span class="input-field-order"> <input type="text"  name="" class="form-control" placeholder="Order ID, email or name"></span>
					</div>
				</div>
			</div><!-- order-detail-head -->
			<div class="detail-list-section display-table-cotnainer">
				<div class="display-table-cell left-content-order">
					 <p>Order By:</p>
					 <div class="about-order-person">
					 	<span class="img-orderer">
					 		<img src="http://via.placeholder.com/40x40" class="img-fluid" alt="icon" title="icon">
					 	</span>
					 	<span class="name-orderer">Johan Smith</span>
					 </div><!-- about-order-person -->
					 <div class="order-status-sec">
					 	<p>Order Status:</p>
					 	<span class="order-status-icon"><i class="fa fa-hourglass-1" aria-hidden="true"></i></span>
					 	<span class="status-dropdown">
						<div class="form-group">
                            <select class="form-control" id="" name="">
                                <option value="0">Pending</option>
                                <option value="1">Approved</option>
                                <option value="2">Delivered</option> 
                            </select>
                        </div>					 		
					 	</span>
					 </div>
					<div class="row shipping-detail-section">
						<div class="col-sm-6">
							<div class="shipping-info-sec">
								<h3>Shipping Details:</h3>
								<p>25th Avenue Garden, street 44, 23444 wordstone ohio</p>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="shipping-info-sec">
								<h3>Email:</h3>
								<p>johansmith@gmail.com</p>
							</div>
						</div>

					</div>
					<!-- shipping-detail-section -->
					<div class="row shipping-detail-section">
						<div class="col-sm-6">
							<div class="shipping-info-sec">
								<h3>Billing Details:</h3>
								<p>25th Avenue Garden, street 44, 23444 wordstone ohio</p>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="shipping-info-sec">
								<h3>Phone:</h3>
								<p>+92300-000000</p>
							</div>
						</div>

					</div>
					<!-- shipping-detail-section -->
					<div class="items-ordered-list">
						<h5>Items Ordered</h5>
						<div class="row list-row-spacing">

							<div class="col-sm-5">
								<div class="row">
									<div class="col-sm-3 less-spcing-right">
										<img src="http://via.placeholder.com/60x60" class="img-fluid" alt="icon" title="icon">
									</div>
									<div class="col-sm-6 less-spcing-left">
										<h4>Product Name</h4>
										<span class="items-select">1 item</span>
										<span class="items-prise">$25</span>
									</div>
								</div>
							</div>
							<div class="col-sm-5">
								<div class="row">
									<div class="col-sm-3 less-spcing-right">
										<img src="http://via.placeholder.com/60x60" class="img-fluid" alt="icon" title="icon">
									</div>
									<div class="col-sm-6 spcing-left">
										<h4>Product Name</h4>
										<span class="items-select">1 item</span>
										<span class="items-prise">$25</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row list-row-spacing">
							<div class="col-sm-5">
								<div class="row">
									<div class="col-sm-3 less-spcing-right">
										<img src="http://via.placeholder.com/60x60" class="img-fluid" alt="icon" title="icon">
									</div>
									<div class="col-sm-6 spcing-left">
										<h4>Product Name</h4>
										<span class="items-select">1 item</span>
										<span class="items-prise">$25</span>
									</div>
								</div>
							</div>
							<div class="col-sm-5">
								<div class="row">
									<div class="col-sm-3 less-spcing-right">
										<img src="http://via.placeholder.com/60x60" class="img-fluid" alt="icon" title="icon">
									</div>
									<div class="col-sm-6 spcing-left">
										<h4>Product Name</h4>
										<span class="items-select">1 item</span>
										<span class="items-prise">$25</span>
									</div>
								</div>
							</div>
						</div>

					</div>
					<!-- items-ordered-list -->

				</div>
				<div class="display-table-cell right-content-order">
					<div class="buyer-note-container">

							<div class="row">

								<div class="col-sm-12">
									<div class="description-option">
										<h6>Buyer Note:</h6>
										<!-- <p>
											Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ultricies faucibus ligula. Aliquam suscipit porta consequat. Curabitur vitae viverra libero. Aliquam lacinia mauris lacinia quam vulputate tincidunt.
										</p> -->
									</div>

								</div>
								<div class="col-sm-12">
									<div class="description-option">
										<h6>Seller Note:</h6>
										<!-- <p>
											Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ultricies faucibus ligula. Aliquam suscipit porta consequat. Curabitur vitae viverra libero. Aliquam lacinia mauris lacinia quam vulputate tincidunt.
										</p> -->
									</div>
									<div class="remove-note">
										<a href="javascript:;">Remove</a>
									</div>

								</div>


							</div>		

					</div><!-- buyer-note-container -->
					<div class="display-table">
						<div class="messageto-buyer-box">
						<label>Add Note:</label>
						<form>
							<textarea class="form-control"> </textarea>
							<div class="form-group">
                            <select class="form-control" id="" name="">
                                <option value="0">Note To</option>
                                <option value="1">Approved</option>
                                <option value="2">Delivered</option> 
                            </select>
                        </div>
                        	<div class="text-right">
								<button class="btn btn-primary btn-simple">Add Note</button>
							</div>
						</form>
					</div>
					</div><!-- messageto-buyer-box -->
				</div>
				<!-- right-content-order -->


			</div>
			<!-- detail-list-section -->
		</div>
	</section><!-- order-detail -->

</main>



@endsection