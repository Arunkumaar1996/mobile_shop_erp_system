@extends('layouts.app')

@section('title', $title)
@section('module-title', $title)

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-gear-wide-connected text-primary fs-1 mb-3 d-block spinner-border border-0" style="animation: spin 3s linear infinite;"></i>
        <h3 class="fw-bold mb-2">{{ $title }}</h3>
        <p class="text-muted mb-0">This module is under construction. It will be implemented in subsequent phases.</p>
    </div>
</div>

<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endsection
