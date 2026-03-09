@extends('layouts.new-main')

@section('content')
<div class="container">
  <h2>Rates</h2>
        <?php 

echo 'Data: <pre>' .print_r($rates,true). '</pre>'; die();
?>
  <table class="table">
    <thead>
      <tr>
        <th>Service</th>
        <th>Rates</th>
        <th>Carrier</th>
      </tr>
    </thead>
    <tbody>
        @foreach($rates as $rate) 
      <tr>
        <td>{{$rate->service}}</td>
        <td>{{$rate->rate}}</td>
        <td>{{$rate->carrier}}</td>
      </tr>
      @endforeach
      
    </tbody>
  </table>
</div>

@endsection