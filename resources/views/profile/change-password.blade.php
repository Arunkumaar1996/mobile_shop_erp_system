@extends('layouts.app')

@section('title', 'Change Password')
@section('module-title', 'User Settings')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('profile.index') }}" class="text-decoration-none">Profile</a></li>
<li class="breadcrumb-item active" aria-current="page">Change Password</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-key me-2"></i>Change Password
            </div>
            <div class="card-body">
                @if(session('success'))
                    <script>
                        window.onload = function() {
                            toastr.success("{{ session('success') }}");
                        }
                    </script>
                @endif
                
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required placeholder="••••••••">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="••••••••">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="••••••••">
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock me-2"></i>Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
