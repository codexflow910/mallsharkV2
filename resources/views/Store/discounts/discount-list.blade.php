<?php 
use Carbon\Carbon;

?>
    @forelse ($discounts as $discount)
        <div class="list-group-item d-flex justify-content-between align-items-center {{ $discount->status == 'active' ? 'active-discount' : '' }}">
            <div>
                {{ $discount->title ?? $discount->code }}
                @if ($discount->status == 'active' && $discount->state == 'live')
                    <span class="badge badge-success badge-active">Live</span>
                    <br><small class="text-secondary">Start since : {{Carbon::parse($discount->updated_at)->format('d-M-Y H:i:s')}}</small>
                @elseif ($discount->status == 'active' && $discount->state == 'scheduled')
                    <br><small class="text-secondary">Starts at : {{Carbon::parse($discount->start_time)->format('d-M-Y H:i:s')}}</small>
                @elseif ($discount->status == 'inActive' && $discount->state == 'expired')
                    <br><small class="text-danger">Expired at : {{Carbon::parse($discount->end_time)->format('d-M-Y H:i:s')}}</small>
                @endif
            </div>
            <div>
                @if ($discount->status == 'active')
                    <button class="btn btn-link btn-disable" data-id="{{ $discount->id }}">Disable</button>
                @else
                    <button class="btn btn-link btn-activate" data-id="{{ $discount->id }}">Activate</button>
                @endif
                <button class="btn btn-link edit-button" data-id="{{ $discount->id }}">Edit Discount</button>
                <button class="btn btn-link archive-button" data-id="{{ $discount->id }}" data-status="{{$discount->status}}">Archive</button>
{{--                                                <button class="btn btn-link btn-delete" data-id="{{ $discount->id }}">Delete</button>--}}
            </div>
        </div>
    @empty
        <div class="list-group-item">No discounts available.</div>
    @endforelse
