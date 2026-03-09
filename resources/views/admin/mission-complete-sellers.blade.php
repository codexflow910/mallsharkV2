@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Mission Complete Sellers')

<main>
    <section class="admin-dashboard dshbrdin buyer mt-0">
        @include('admin.adminsidebar')

        <div class="dshbrdinadmin list-management">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 mt-40 d-flex justify-content-between align-items-center mb-10">
                        <h3 class="mb-0">Mission Complete Sellers</h3>
                        <div>
                            <span class="text-muted">Total: {{ $stores->total() }}</span>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Store ID</th>
                                        <th>Store Name</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Mission Complete Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($stores as $store)
                                        <tr>
                                            <td>{{ $store->store_id }}</td>
                                            <td>{{ $store->store_name ?? '-' }}</td>
                                            <td>{{ optional($store->user)->name ?? '-' }}</td>
                                            <td>{{ optional($store->user)->email ?? '-' }}</td>
                                            <td>
                                                @if (!empty($store->mission_complete_date))
                                                    {{ \Carbon\Carbon::parse($store->mission_complete_date)->format('Y-m-d') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No mission complete sellers found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $stores->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@endsection
