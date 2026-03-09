@extends('layouts.dashboard-main')

@section('title', 'Permissions Management')

@section('content')
<div class="mx-auto" style="width: 90vw;">
    <div class="row mb-4 mt-4">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-4">Permissions Management</h2>

            <div>
                <a class="btn btn-primary addtransporterbtn btn-simple" href="{{ route('roles.index') }}">Back</a>
            </div>

        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success') || session()->has('error'))
        <script>
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 3000);
        </script>
    @endif


    {{-- Create New Permission --}}
    <form method="POST" action="{{ route('permissions.store') }}" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <input type="text" name="display_name" placeholder="Display Name (e.g. Edit User)" class="form-control" placeholder="Display Name (e.g. Edit User)" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="group_name" placeholder="Group Name (optional)" class="form-control" placeholder="Group Name (optional)">
            </div>
            <div class="col-md-2">
                <button class="btn btn-success" type="submit">Add Permission</button>
            </div>
        </div>
    </form>

    {{-- Permissions Table --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Display Name</th>
                <th>Name (slug)</th>
                <th>Group</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($permissions as $key => $permission)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $permission->display_name }}</td>
                <td>{{ $permission->name }}</td>
                <td>{{ $permission->group_name ?? '-' }}</td>
                <td>
                    {{-- Edit --}}
                    <form method="POST" 
                        action="{{ route('permissions.update', $permission->permission_id) }}" 
                        class="d-inline">
                        @csrf
                        @method('PUT')

                        <div class="mb-1">
                            <label class="form-label small">Display Name</label>
                            <input type="text" 
                                class="form-control form-control-sm" 
                                placeholder="Display Name (e.g. Edit User)" 
                                name="display_name" 
                                value="{{ $permission->display_name }}" 
                                required>
                        </div>

                        <div class="mb-1">
                            <label class="form-label small">Group Name</label>
                            <input type="text" 
                                class="form-control form-control-sm" 
                                placeholder="Group Name (optional)" 
                                name="group_name" 
                                value="{{ $permission->group_name }}">
                        </div>

                        <button class="btn btn-primary btn-sm">Update</button>
                    </form>

                    {{-- Delete --}}
                    <form method="POST" 
                        action="{{ route('permissions.destroy', $permission->permission_id) }}" 
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this permission?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No permissions found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">
        {!! $permissions->links('pagination::bootstrap-4') !!}
    </div>
</div>
@endsection
