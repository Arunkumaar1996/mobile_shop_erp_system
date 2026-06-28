@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<h4 class="text-center text-white mb-4 fw-600">Sign In to ERP</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="background-color: rgba(16, 185, 129, 0.15); color: #34d399;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
    @csrf

    <!-- Username / Email Address -->
    <div class="mb-3">
        <label for="login" class="form-label">Email Address or Username</label>
        <input id="login" type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" required autofocus placeholder="Enter email or username">
        @error('login')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label mb-0">Password</label>
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">Forgot Password?</a>
            @endif
        </div>
        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="••••••••">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="mb-4 form-check">
        <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
        <label class="form-check-label text-light fs-7" for="remember_me">Remember me</label>
    </div>

    <div>
        <button type="submit" class="btn btn-primary shadow-sm mb-3">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </button>
    </div>
</form>
@endsection
