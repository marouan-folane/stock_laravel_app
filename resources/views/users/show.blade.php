@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Details: {{ $user->name }}</h1>
    <div>
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-rounded">
            <i class="fas fa-edit fa-sm me-2"></i> Edit User
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-rounded">
            <i class="fas fa-arrow-left fa-sm me-2"></i> Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&size=128" 
                         alt="{{ $user->name }}" class="img-profile rounded-circle img-thumbnail" style="width: 150px; height: 150px;">
                    <h4 class="mt-3">{{ $user->name }}</h4>
                    <span class="badge 
                        @if($user->role == 'admin') bg-danger 
                        @elseif($user->role == 'employee') bg-primary 
                        @else bg-success @endif">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                
                <hr>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Email</div>
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </div>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Phone Number</div>
                            @if($user->phone_number)
                                <a href="tel:{{ $user->phone_number }}">{{ $user->phone_number }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Created At</div>
                            {{ $user->created_at->format('F d, Y') }}
                        </div>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Last Updated</div>
                            {{ $user->updated_at->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-edit me-1"></i> Edit User
                </a>
                
                <button type="button" class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash me-1"></i> Delete User
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="30%">User ID</th>
                                <td>{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td>
                                    <span class="badge 
                                        @if($user->role == 'admin') bg-danger 
                                        @elseif($user->role == 'employee') bg-primary 
                                        @else bg-success @endif py-2 px-3">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ $user->phone_number ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <th>Email Verified</th>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i> 
                                            Verified on {{ $user->email_verified_at->format('F d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i> 
                                            Not verified
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Account Created</th>
                                <td>{{ $user->created_at->format('F d, Y \a\t h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $user->updated_at->format('F d, Y \a\t h:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 