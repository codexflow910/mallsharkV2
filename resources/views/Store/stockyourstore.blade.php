@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Start Selling')
@section('description', 'Explore creative projects and help bring bold new ideas into the world with the Mallshark app')

<div class="seller-name-page stock-your-page">
    <div class="container">
        <div class="progress">
            <div class="progress-bar w-75" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
          </div>

          <div class="name-your-shop-wrapper stock-yor-store-wrapper">
            <h2>Stock Your Store</h2>
            <div class="row">
                <div class="col-md-12">
                    <div class="description">
                        <p>How would you like to start adding items to your store?</p>
                    </div>
                    <div class="buttons">
                      <a href="/add-product" > <button class="btn btn-primary">Manually Add Items</button></a>
                      <a href="/importshopify" > <button class="btn btn-warning">Upload from Shopify</button> </a>

                    </div>
                </div>

            </div>
          </div>
          <hr>
        </div>
</div>


@endsection