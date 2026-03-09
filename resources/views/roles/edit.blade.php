@extends('layouts.dashboard-main')
@section('title', 'Edit Role')

@section('content')
<div class="mx-auto" style="width: 90vw;">
    <div class="row mb-4 mt-4">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Edit Role</h2>
            <a class="btn btn-primary" href="{{ route('roles.index') }}">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('roles.update', $role->role_id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Role Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->role_name) }}"
                        class="form-control" placeholder="Enter role name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Permissions</label>
                    <div class="row">
                        @foreach($permission as $value)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="permission[]" value="{{ $value->permission_id }}"
                                    class="form-check-input" id="perm-{{ $value->permission_id }}"
                                    style="width: auto;"
                                    {{ in_array($value->permission_id, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $value->permission_id }}">
                                    {{ $value->display_name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection