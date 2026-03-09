@extends('layouts.dashboard-main')
@section('title', 'Role Management')

@section('content')
<div class="mx-auto" style="width: 90vw;">
    <div class="row mb-4 mt-4">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Role Management</h2>
            <div>
                <a class="btn btn-primary addtransporterbtn btn-simple" href="{{ URL('admin/dashboard') }}">Dashboard</a>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a class="btn btn-primary mr-2" href="{{ route('roles.create') }}">
            <i class="bi bi-plus-circle"></i> Create New Role
        </a>
        <a class="btn btn-secondary" href="{{ route('permissions.index') }}">
            <i class="bi bi-gear"></i> Manage Permissions
        </a>
    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <table class="table table-bordered align-middle mx-auto">
                <thead class="table-light">
                    <tr>
                        <th width="50px">No</th>
                        <th>Name</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $key => $role)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $role->role_name }}</td>
                        <td class="w-auto">
                            <a class="btn btn-info btn-sm" href="{{ route('roles.show', $role->role_id) }}">
                                <i class="bi bi-eye"></i> Show
                            </a>

                            <a class="btn btn-primary btn-sm" href="{{ route('roles.edit', $role->role_id) }}">
                                <i class="bi bi-pencil"></i> Edit
                            </a>



                            <form action="{{ route('roles.destroy', $role->role_id) }}" method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this role?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-3">
                {!! $roles->links('pagination::bootstrap-4') !!}
            </div>
        </div>
    </div>
</div>
<script>
    setTimeout(() => {
        const alert = document.getElementById('success-alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
</script>
@endsection