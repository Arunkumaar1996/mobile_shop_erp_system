@extends('layouts.app')

@section('title', 'My Profile')
@section('module-title', 'User Settings')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Profile</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card text-center">
            <div class="card-body">
                <div class="mb-3 text-center">
                    @if($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Avatar" class="rounded-circle border" width="120" height="120" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 2.5rem; font-weight: 600;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ '@' . $user->username }}</p>
                <div class="mb-3">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary px-3 py-2 fs-7">{{ $role->display_name }}</span>
                    @endforeach
                </div>
                <div class="text-start border-top pt-3 mt-3">
                    <p class="mb-2"><i class="bi bi-envelope-at me-2 text-muted"></i>{{ $user->email }}</p>
                    <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i>{{ $user->phone ?? 'N/A' }}</p>
                    <p class="mb-0"><i class="bi bi-building-check me-2 text-muted"></i>Branch: {{ $user->branch->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Edit Profile Form -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-gear me-2"></i>Edit Profile Details
            </div>
            <div class="card-body">
                @if(session('success'))
                    <script>
                        window.onload = function() {
                            toastr.success("{{ session('success') }}");
                        }
                    </script>
                @endif
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="profile_image" class="form-label">Update Profile Image</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror">
                        <small class="text-muted d-block mt-1">Accepted formats: JPG, PNG, GIF. Max file size: 2MB.</small>
                        @error('profile_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
