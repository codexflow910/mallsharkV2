@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'campaign')


<section class=" terms-condition-page ftrset">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>Dive into the Depths of Savings with MallShark’s Exclusive Promotion!</h1> 
                        <h4>Create or upload your store on MallShark.net and we’ll give you <b>$250!</b></h4>
                    </div>
                    <div class=" terms-wrapper faqsec  mt-40"> 
                        <div class="container">
        <div class="row">
        <div class="col-sm-12">   
        <h1 style="color:#073ba5!important"><i>That’s right, you heard it here first! We’re on the hunt for the pioneers, the go-getters, and the visionaries who are ready to transform the shopping scene. Unleash your inner entrepreneur with MallShark.net’s electrifying deal: be one of the FIRST 50 to craft your dream store and seize a whopping $250 as your launchpad!</i></h1>
        <h2>Promotion Details</h2>
        <p>MallShark is thrilled to offer a special promotion for our dedicated users. The first 50 users who successfully upload or create a store on MallShark will be rewarded with $250.</p>
        <h2>Eligibility:</h2>
        <ul>
            <li>This promotion is open to all registered users of MallShark. </li>
            <li>Only the first 50 users who satisfy the requirements as outlined below will qualify for the reward.<li>
        </ul>
        <h2>Requirements:</h2>
                    <ul>
                    <li><strong>Store Creation:</strong> Users must either upload or establish a new store on MallShark.</li>
                    <li><strong>Inventory:</strong> The store should feature a minimum of 10 items. Each item must have a comprehensive description and a corresponding image.</li>
                    <li><strong>Store Profile:</strong> The store should have:
                        <ol>
                            <li>A detailed store bio explaining the nature and purpose of the store.</li>
                            <li>A store profile image that epitomizes the store’s brand or theme.</li>
                            <li>A captivating store banner to enhance the visual appeal of the store’s page.</li>
                        </ol>
                    </li>
                    <li><strong>Maintenance:</strong> Users are required to maintain their store for a minimum duration of 3 months from the date of its creation or uploading.</li>
                    </ul>

                    <h2>Reward Distribution: </h2>
                    <ul>
                        <li>The reward of $250 will be disbursed to the eligible users after a thorough verification process ensuring all requirements have been met.</li>
                        <li>The reward will be processed through the payment method registered with MallShark within 30 days of verification.</li>
                        <li>Users will be notified via email once the reward has been processed.</li> 
                    </ul>
                    <h2>General Terms:</h2>
                    <ul>
                    <li>MallShark reserves the right to modify, terminate, or amend this promotion at any time, with or without prior notice.</li>
                    <li>Any attempt to manipulate, exploit, or misuse the promotion will result in immediate disqualification and may lead to further legal action.</li>
                    <li>MallShark’s decision in all matters related to this promotion will be final and indisputable.</li>
                    <li>By participating in this promotion, users agree to abide by all the terms and conditions set forth in this agreement.</li>
                    <li>MallShark is not responsible for any technical issues, system failures, or other interruptions that may affect participation in the promotion.</li>
                    </ul>
                    <h2>Promotional Disclaimer:</h2>
                    <ul>
                    <li>This promotion is subject to all applicable federal, state, and local laws and regulations.</li>
                    <li>The reward is non-transferable, non-exchangeable, and cannot be redeemed for cash or other alternatives.</li>
                    <li>MallShark is not responsible for any taxes, fees, or other charges that may arise from the receipt or use of the reward.</li>
                    <li>By participating, users release MallShark and its affiliates from any and all liability arising out of or related to this promotion.</li>
                    </ul>
                    <p>Contact:For any queries, concerns, or clarifications related to this promotion, please reach out to our support team at support@mallshark.net.</p>
                    <br>
                    <?php if (Session::has('userData')) {
                        $userid = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                        $storeData = getStoreDataByUserId($userid);
                        if (!empty($storeData)) {  
                            $url = URL::to('/store-dashboard');
                        } else {
                            $url = URL::to('/store-name?campaign=picvendor');
                        }
                    } else { 
                        if ($compaign !== null) {
                            $url = URL::to('/signup?campaign='.$compaign->slug);
                            $id = "check-email-exist";
                           }
                    } ?>
                <div class="setup-store-button">
                    <a href="{{$url}}" class="btn btn-primary">Setup My Store!</a>
                </div>
 
</div>
         
    </div>
                      
 
                       
                    </div>
                </div>
            </div> 
        </div>
    </section> 


 

 
    

@endsection