@extends('layouts.dashboard-main')
@section('title', 'Role Details')

@section('content')
<div class="mx-auto" style="width: 90vw;">
    <div class="row mb-4 mt-4">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Role Details</h2>
            <a class="btn btn-primary" href="{{ route('roles.index') }}">Back</a>
        </div>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <div class="mb-3">
                <h5 class="fw-bold">Name:</h5>
                <p class="mb-0">{{ $role->role_name }}</p>
            </div>
            
            <div class="mb-3">
                <h5 class="fw-bold">Permissions:</h5>
                @if(!empty($rolePermissions) && $rolePermissions->count())
                <div class="d-flex flex-wrap gap-2">
                    @foreach($rolePermissions as $permission)
                    <span class="badge bg-success mr-2 mb-2">{{ $permission->display_name }}</span>
                    @endforeach
                </div>
                @else
                <p class="text-muted">No permissions assigned</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection