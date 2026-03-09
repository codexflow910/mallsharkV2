@extends('layouts.new-main')
@section('content')
@section('title', 'Admin dashboard')
<?php 
foreach ($listingresults as $lr){
    echo $lr->product_id."<br>";
    echo $lr->user_id."<br>";
    echo $lr->store_id."<br>";
    echo $lr->title."<br>";
    echo $lr->title."<br>";
    echo $lr->categoryTitle."<br>";
}
?>
{{ $listingresults->links() }}
@endsection