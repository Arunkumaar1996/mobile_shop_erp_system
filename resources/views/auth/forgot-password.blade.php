@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h4 class="text-center text-white mb-3 fw-600">Reset Password</h4>
<p class="text-center text-light mb-4 fs-7">Enter your email and we'll send you a password reset link.</p>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show border-0" role="alert" style="background-color: rgba(16, 185, 129, 0.15); color: #34d399;">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus placeholder="name@example.com">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-primary shadow-sm mb-2">
            Send Reset Link
        </button>
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="auth-link"><i class="bi bi-arrow-left me-1"></i> Back to Sign In</a>
        </div>
    </div>
</form>
@endsection
