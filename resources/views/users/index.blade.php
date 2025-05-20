@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Management</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-rounded">
        <i class="fas fa-plus fa-sm me-2"></i> Add New User
    </a>
</div>

<!-- Search and Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search & Filter Users</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('users.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, email or phone" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Filter by Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                    <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Client</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('users.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone_number ?? 'N/A' }}</td>
                        <td>
                            @if($user->role == 'admin')
                                <span class="badge bg-danger">Admin</span>
                            @elseif($user->role == 'employee')
                                <span class="badge bg-primary">Employee</span>
                            @else
                                <span class="badge bg-success">Client</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="action-buttons">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush 