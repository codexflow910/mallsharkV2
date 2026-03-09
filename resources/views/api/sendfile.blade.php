
@extends('layouts.frontend-main')
@section('content')
@section('title', 'file upload')

<form method="post" action="{{ URL::to('/') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" >
    <input type="submit" name="submit"  value="submit">
</form>

@endsection