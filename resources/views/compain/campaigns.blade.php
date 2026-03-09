@extends('layouts.dashboard-main')
@section('content')
<style>
    .reward-notification{
        margin-top: 40px;
    }
</style>

<div class="flex-parent-div">
<div class="change-password-page-cmt">
    @include('buyer.customsidebar')
</div>
<div class=" dshbrdin store-dashboard pro-dsb-cntainer home-section cm-edit-ja overflow-y-none mobile-adjust-scroll campaigns-page">
<div class="container">
    <div class="row mt-15">
    <div class="col-sm-12 col-12">
        <h6 class="store-status-mbt">
        <div class="d-flex align-items-center ">                        
            <a href="javascript:void(0)" class="d-flex align-items-center gap-10px sidebar-admin-burger-menu" id="open-sidebar-admin-panel">                                        
            <span id="menu-toggle-dashboard" class="shopzit-menu mt-0 ml-0 bx-menu" style="margin-right: 5px;">
                <span></span><span></span><span></span> <span></span>
            </span>
        </a>
        <span class="campaign-dashboard-title">Campaigns</span> 
                                                </div>   

        </h6>
    </div>
</div>
@include('Store.sidebar')
    @if (!empty($camaignStoreData))
        @php $campaignFound = false; @endphp
        @foreach ($camaignStoreData as $camapign)
            @if ($camapign && !empty($camapign->campaign_id) && $store->store_id == $camapign->store_id && $totalActiveProducts >= 10)

                <div class="campaign-reward-wrapper text-center">
                    <div class="campaigns-heading">
                        <h1>Congratulations</h1>
                        <p>You have joined the campaign for</p> 
                        <h2>MallShark Exclusive</h2>
                    </div>
                    <div class="campaign-reward-img">
                        <img src="{{ URL::asset('assets/images/campaign.png')}}" alt="campaign" srcset="">
                    </div>
                    <div class="reward-text">
                        <h2>Reward</h2>
                    </div>
                </div>
                @php $campaignFound = true; @endphp 
                @break 
            @endif
        @endforeach

        @if(!$campaignFound)
        
            <div class="reward-notification text-center in-foreach">
                <p>
                    <img src="{{ URL::asset('assets/images/warning.png')}}" alt="not found any campaign" srcset="">
                    <span>You have not joined any campaign yet.</span>
                </p>
            </div>
        @endif

    @else
        <div class="reward-notification text-center">
            <p>
                <img src="{{ URL::asset('assets/images/warning.png')}}" alt="not found any campaign" srcset="">
                <span>You have not joined any campaign yet.</span>
            </p>
        </div>
    @endif
</div>
</div>
</div>
@endsection