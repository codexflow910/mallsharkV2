@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Article Listing')



<div class="articleListingHeader">
    <div class="articleListingHeaderContainer">
        <div class="articleListingHeding">
            <h2>Collection of sharktastic Articles</h2>
        </div>
        <div class="searchForm">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search Articles">
                <div class="input-group-append">
                  <button class="btn btn-primary" type="button" style="border-top-left-radius:40px;border-bottom-left-radius: 40px;">
                  Search
                  </button>
                </div>
              </div>
        </div>
        
    </div>
</div>
<div class="article-listing-nav">
    <nav class="navbar navbar-expand-lg text-dark">
        <div class="cata-sub-nav">
            <ul>
              <li><a class="nav-item active">Popular Articles</a></li>
              <li><a class="nav-item ">Latest Articles</a></li>
              <li><a class="nav-item ">Cryptocurrency</a></li>
              <li><a class="nav-item ">NFT</a></li>
              <li><a class="nav-item ">News</a></li>
              <li><a class="nav-item ">Blockchain</a></li>
              <li><a class="nav-item ">MallShark</a></li>
            </ul>
            
          </div>   
</div>

<div class="mainContainer">

    <div class="container">
        <div class="row masonaryContainer" data-masonry='{"percentPosition": true }'>
            @foreach ($products as $item)
        
            <div class="listing-blocks {{$item['color_code']}}" style="padding-top: 30px;padding-bottom: 14px;" >
                <h2 class="listing-blocksheading">
        {{$item['heading']}}
                </h2>
                <p class="listing-blocksContent">
                    {{$item['paragraph']}}
                </p>
                <button > <b>READ MORE  &#x2192;</b></button>
                @isset($item['img'])
                    
                <div class="imageBlock">
                    <img src={{$item['img']}} alt="" srcset="">
                </div>
                @endisset
            </div>
        
            @endforeach    

        </div>
    </div>

   
   
</div>
<nav aria-label="Page navigation example" class="paination">
    <ul class="pagination justify-content-center">
      {{-- Previous Page Link --}}
      @if ($posts->onFirstPage())
        <li class="page-item disabled">
          <span class="page-link" tabindex="-1">&lt;</span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link" href="{{ $posts->previousPageUrl() }}" tabindex="-1">&lt;</a>
        </li>
      @endif

      {{-- Pagination links --}}
      @for ($i = 1; $i <= $posts->lastPage(); $i++)
        <li class="page-item{{ $posts->currentPage() === $i ? ' active' : '' }}">
          <a class="page-link" href="{{ $posts->url($i) }}">{{ $i }}</a>
        </li>
      @endfor

      {{-- Next Page Link --}}
      @if ($posts->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $posts->nextPageUrl() }}">&gt;</a>
        </li>
      @else
        <li class="page-item disabled">
          <span class="page-link">&gt;</span>
        </li>
      @endif
    </ul>
</nav>

<div class="container articleListingBoxContainer">
    <div class="row">
        <div class="col-md-6">
          <div class="imageContainerBox">
            <img src="../assets/images/Sharkflip.png" alt="" srcset="">
          </div>
        </div>
        <div class="col-md-6">
            <div class="container">
                <h3 class="articleListingBoxh3">Sign Up Today</h3>
                <h1 class="articleListingBoxh1">Want to be part of the future?</h1>
                <p class="articleListingBoxpara">Sign up for mallshark today and experience the wave of the future in e-commerce, when the power of blockchain technology and cyrptocurrency payments converge to provide a secure decentralized and rewarding shopping experience</p>
                <div>
                  <div style="display: flex">
                      
                        <button type="button" class="btn btn-primary articleListingBoxBtn">Sign Up</button>
                        <button type="button" class="btn btn-outline-primary articleListingBoxBtn">Sign In</button>
                      
                  </div>
                </div>
            </div>
         
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Add a click event handler for each category link
        $('.nav-link').on('click', function(event) {
            event.preventDefault();
            var slug = $(this).data('slug');

            // Remove active class from all category links and add it to the clicked link
            $('.nav-link').removeClass('active');
            $(this).addClass('active');

            // Send AJAX request to get associated posts
            $.ajax({
                url: '/your-api-url', // Replace this with your actual API URL
                method: 'POST',
                data: { slug: slug },
                success: function(data) {
                    // Clear the post container before appending new posts
                    $('#postContainer').empty();

                    // Append new posts to the post container
                    data.forEach(function(post) {
                        var postHtml = '<div class="post">' +
                            '<h2>' + post.title + '</h2>' +
                            '<p>' + post.content + '</p>' +
                            '</div>';

                        $('#postContainer').append(postHtml);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        });
    });
</script>
@endsection