@extends('layouts.app')

@section('title', 'Company Settings')
@section('module-title', 'Administration')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Company Settings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 mx-auto">
        <h4 class="mb-3 fw-bold"><i class="bi bi-gear me-2"></i>Global Company Settings</h4>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header">
                ERP Configuration Profile
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-600">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name', $company->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tax_number" class="form-label fw-600">Tax Number / GSTIN</label>
                            <input type="text" name="tax_number" id="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number', $company->tax_number) }}">
                            @error('tax_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-600">Contact Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $company->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-600">Contact Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $company->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label fw-600">Company Website</label>
                        <input type="text" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $company->website) }}">
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label fw-600">Physical Address</label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $company->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="currency" class="form-label fw-600">Currency Code <span class="text-danger">*</span></label>
                            <input type="text" name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required value="{{ old('currency', $company->currency) }}" placeholder="e.g. USD, EUR, INR">
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="currency_symbol" class="form-label fw-600">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="currency_symbol" id="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" required value="{{ old('currency_symbol', $company->currency_symbol) }}" placeholder="e.g. $, €, ₹">
                            @error('currency_symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600">Company Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="logo-preview-container border border-color rounded p-2" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; background-color: var(--bg-body);">
                                @if($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="img-fluid rounded" style="max-height: 80px;">
                                @else
                                    <i class="bi bi-image text-muted fs-1"></i>
                                @endif
                            </div>
                            <div>
                                <input type="file" name="logo" id="logo-input" class="form-control @error('logo') is-invalid @enderror">
                                <small class="text-muted d-block mt-1">Formats: JPEG, PNG, JPG, GIF. Max file size: 2MB.</small>
                                @error('logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-color mb-4">

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Save Configurations</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
