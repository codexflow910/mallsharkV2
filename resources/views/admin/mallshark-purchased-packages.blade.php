@extends('layouts.dashboard-main')

@section('title', 'Purchased Packages')

@section('content')

<link rel="stylesheet" href="{{ URL::asset('assets/css/order-page.css') }}">

<div class="flex-parent-div bg-light-gray d-flex">
@include('admin.adminsidebar')

    <div class="order-product-page home-section p-3">
        <div class="content container py-4">
            <div class="d-flex align-items-center justify-content-between py-2 mb-3">
                <h2 class="buyer-order-heading m-0">Purchased Packages</h2>

                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.purchased.packages') }}" class="d-flex align-items-center">
                    <input type="text" name="search" class="form-control" placeholder="Search seller, store, or package..." value="{{ request('search') }}" style="width: 250px; margin-right: 8px;">

                    <select name="status" class="form-select form-control me-2" style="width: 90px; margin-right: 8px;">
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="all" selected>All</option>
                    </select>
                    <select name="sort" class="form-select form-control me-2" style="width: 160px; margin-right: 8px;">
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Newest to Oldest</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Oldest to Newest</option>
                    </select>

                    <button type="submit" class="btn btn-outline-primary me-2 mr-2">Search</button>
                    <a href="{{ route('admin.purchased.packages') }}" class="btn btn-outline-danger">Clear</a>
                </form>

            </div>
            <div class="table-responsive card">
                <table class="table table-hover align-middle bg-white shadow-sm rounded">
                    <thead class="table-light text-center">
                        <tr>
                            <th scope="col" class="font-weight-bold">#</th>
                            <th class="text-center font-weight-bold" scope="col">Seller</th>
                            <th class="text-center font-weight-bold" scope="col">Store</th>
                            <th class="text-center font-weight-bold" scope="col">Package</th>
                            <th class="text-center font-weight-bold" scope="col">Amount</th>
                            <th class="text-center font-weight-bold" scope="col">Status</th>
                            <th class="text-center font-weight-bold" scope="col">Start</th>
                            <th class="text-center font-weight-bold" scope="col">Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($packages as $index => $package)
                        <tr>
                            <td class="text-center">{{ $packages->firstItem() + $index }}</td>
                            <input type="hidden" name="package-id" value="{{ $package->id }}">
                            <td class="text-center">
                                <strong>{{ ucwords($package->store->user->name ?? 'N/A') }}</strong><br>
                                <small class="text-muted">{{ $package->store->user->email ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                <a href="{{ env('FRONT_END_URL') . '/brand/' . $package->store->store_slug }}" target="_blank" class="text-decoration-none">
                                    <strong>{{ ucwords($package->store->store_name) }}</strong>
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ ucwords($package->package_name) }}</span>
                            </td>
                            <td class="text-success fw-bold text-center">
                                ${{ number_format($package->amount, 2) }}
                            </td>
                            <td class="text-success fw-bold text-center">
                                <span class="badge {{ $package->status == 'expired' ? 'bg-secondary' : ($package->status == 'active' ? 'bg-success' : ($package->status == 'inactive' ? 'bg-danger' : 'bg-warning')) }}">
                                    {{ ucfirst($package->status) }}
                                </span>
                            </td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($package->started_at)->format('M d, Y') }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($package->expires_at)->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No purchased packages found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>



            <div class="mt-4">
                {{ $packages->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection