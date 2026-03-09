<div class="superDeailInnerWrapper">
    <h3 class="d-flex align-items-center secHeadingTm">
        <img src="{{ URL::asset('assets/images/energy_flash_icon.svg')}}" alt="" style="max-width: 30px;" />
        <span class="dealTxtFl">Christmas Deals</span>
    </h3>
    <?php
    use App\Models\Discount;
    use Carbon\Carbon;
    $cutoffDate = Carbon::create(2024, 12, 25);
         $flashDeal = Discount::where(['status'=>'active','state'=>'live'])->whereDate('end_time', '=', $cutoffDate)->first();
         ?>
    <div class="row">
        <div class="col-md-12">
            <div class="deailsBoxWrap radius10 missoutBox"  style="background-color:#fbe7e7;">
            <div class="row">
                <div class="col-md-8">
                    <div class="flashDealsContent">
                        <div class="heading-sec-lrg">
                            Get It Now or <strong style="color:#c0113f;" class="weight-800">Miss Out!</strong>
                        </div>
                        <p style="color:#c0113f;" class="weight-800">Christmas Deals<br>
                            Ending Soon ></p>
                            @if(!is_null($flashDeal) && $flashDeal->count() > 0)
                            <div id="timer" class="d-flex align-items-center"></div>
                            @else
                            <div id="timer" class="d-none align-items-center"></div>
                            
                            @endif

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dealsSecOne">
                            <div class="flashDealSlide">
                                <div class="slideItem">
                                    <?php
                                        
                                            $products = flashDeal();
                                            foreach ($products as $key => $product) {
                                    ?>
                                     <a href="{{route('category.landing',['slug' => 'christmas-deal'])}}">
                                    <div class="productImage">
                                        <img src="<?php echo getAttachmentUrlById($product->feature_image) ?>" title="{{$product->title}}">
                                    </div> 
                                </a>   
                                    <?php 
                                    }
                                ?>          
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>


</div>



<script>
document.addEventListener('DOMContentLoaded', (event) => {
    function startTimer(targetDate) {
        let timer = setInterval(function () {
            let now = new Date().getTime();
            let distance = targetDate.getTime() - now;

            // Calculate time left
            let days = Math.floor(distance / (1000 * 60 * 60 * 24)); // Full days
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)); // Full hours
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the timer
            document.getElementById("timer").innerHTML = 
                `<span class="days">${days}</span>d 
                 <span class="hours">${hours}</span>h 
                 <span class="minutes">${minutes}</span>m 
                 <span class="seconds">${seconds}</span>s`;

            // If the countdown is over
            if (distance < 0) {
                clearInterval(timer);
                document.getElementById("timer").innerHTML = "Merry Christmas!";
                console.log("Countdown ended!");
            }
        }, 1000);
    }

    // Set the target date to December 25, 2024, 12:00 AM
    const targetDate = new Date("2024-12-25T00:00:00");

    // Start the countdown timer
    startTimer(targetDate);
});




</script>