@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h4 class="text-center text-white mb-4 fw-600">Choose New Password</h4>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" class="form-control @error('email') is-invalid @enderror" required readonly>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus autocomplete="new-password" placeholder="Min. 8 characters">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="Confirm password">
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-primary shadow-sm mb-2">
            Reset Password
        </button>
    </div>
</form>
@endsection
