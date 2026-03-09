@extends('layouts.frontend-main')

@section('content')


<main>
<style>
    

@media only screen and (max-width: 767px){
    body {
        padding-top: 0px;
    }    
}


</style>

<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script>-->
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <script src="{{ URL::asset('assets/js/fone-format.js',env('HTTPS_STATUS')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
    <h1 style="display: none;">Calculate Shipping</h1>
    <div class="signbx">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                   
                    <!-- Sign Up Box -->
                    <div class="signbxin border radius">
          
                        <form id="easypost_ship" name="easypost" method="POST" action="{{Url::to('/shipping_post')}}">
                            {{ csrf_field() }}
                            <div class="form-group ">
                            
                                <label for="States">Origin States*</label>
                                <select name="origin_state" id="origin_state" class="form-control">
                                <?php
                                 $states = getAllStates();
                                foreach($states as $state){
                                ?>
                                <option value="<?php echo $state['code']; ?>"><?php echo $state['state_name']; ?></option>
                                <?php } ?>
                                </select>
                               

                                @if ($errors->has('state'))
                                <div class="error">{{ $errors->first('state') }}</div>
                                @endif
                            </div>
                            <div class="form-group ">
                                <label for="city">Origin City*</label>
                                <input type="text" class="form-control" id="origin_city" name="origin_city" placeholder="Enter Your Origin City">

                                @if ($errors->has('city'))
                                <div class="error">{{ $errors->first('city') }}</div>
                                @endif
                            </div>

                            <div class="form-group ">
                                <label for="city">Origin Street*</label>
                                <input type="text" class="form-control" id="origin_street" name="origin_street" placeholder="Enter Your Origin Street">

                                @if ($errors->has('city'))
                                <div class="error">{{ $errors->first('city') }}</div>
                                @endif
                            </div>

                            <div class="form-group ">
                            
                                <label for="States">Destination States*</label>
                                <select name="destination_state" id="destination_state" class="form-control">
                                <?php
                                 $states = getAllStates();
                                foreach($states as $state){
                                ?>
                                <option value="<?php echo $state['code']; ?>"><?php echo $state['state_name']; ?></option>
                                <?php } ?>
                                </select>
                               

                                @if ($errors->has('state'))
                                <div class="error">{{ $errors->first('state') }}</div>
                                @endif
                            </div>
                            <div class="form-group ">
                                <label for="city">Destination City*</label>
                                <input type="text" class="form-control" id="destination_city" name="destination_city" placeholder="Enter Your Destination City">

                                @if ($errors->has('city'))
                                <div class="error">{{ $errors->first('city') }}</div>
                                @endif
                            </div>
                            <div class="form-group ">
                                <label for="city">Destination Street*</label>
                                <input type="text" class="form-control" id="destination_street" name="destination_street" placeholder="Enter Your Destination Street">

                                @if ($errors->has('city'))
                                <div class="error">{{ $errors->first('city') }}</div>
                                @endif
                            </div>
                            <div class="form-group ">
                                <label for="email">length*</label>
                                <input type="text" class="form-control" id="length" name="length" placeholder="Enter Your length">

                                @if ($errors->has('length'))
                                <div class="error">{{ $errors->first('length') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="pwd">width*</label>
                                <input type="text" class="form-control" id="width" name="width" placeholder="Enter Your width">
                                @if ($errors->has('width'))
                                <div class="error">{{ $errors->first('width') }}</div>
                                @endif
                                <p class="error" id="result"></p>
                            </div>
                            <div class="form-group">
                                <label for="pwd">height*</label>
                                <input class="form-control" id="height" name="height" placeholder="Enter Your height">
                                @if ($errors->has('height'))
                                <div class="error">{{ $errors->first('height') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="pwd">weight*</label>
                                <input type="text" class="form-control" id="weight" name="weight" placeholder="Enter Your weight">
                                @if ($errors->has('weight'))
                                <div class="error">{{ $errors->first('weight') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="pwd">origin*</label>
                                <input type="text" class="form-control" id="origin" name="origin" placeholder="Enter Your origin zip">
                                @if ($errors->has('origin'))
                                <div class="error">{{ $errors->first('origin') }}</div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="pwd">destination*</label>
                                <input type="text" class="form-control" id="destination" name="destination" placeholder="Enter Your destination zip">
                                @if ($errors->has('destination'))
                                <div class="error">{{ $errors->first('destination') }}</div>
                                @endif
                            </div>
                           
                            <div class="form-check">
                                <div  class="form-group mt-10 cat-contan " >
                                    
                                <p><button type="submit" class="btn btn-primary w100p">Create </button></p>
                               
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
  <script>

$("#easypost_ship").validate({
        errorElement: "span",
        rules: {
            length: {required: true},
            width: {required: true},
            height: {required: true},
            weight: {required: true},
            origin: {required: true},
            destination: {required: true},
        },
        messages: {
            length: {required: "length is required."},
            width: {required: "width is required."},
            height: {required: "height is required."},
            weight: {required: "weight is required."},
            origin: {required: "origin is required."},
            destination: {required: "destination is required."},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
  </script> 

@endsection