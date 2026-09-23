@extends('layouts.auth')
@section('title', 'Login')
@section('content')
<div class="app-container">
    <div class="h-100">
        <div class="h-100 g-0 row">
            <div class="d-none d-lg-block col-lg-4">
                <div class="position-relative h-100 d-flex flex-column justify-content-center align-items-center text-center px-4" style="background: linear-gradient(135deg, #2c3e50 0%, #4a6278 100%); color: #fff;">
                    <div style="background: #fff; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                        <img src="{{ asset('logo.png') }}?v=2" alt="{{ config('app.name') }}" style="max-width: 160px; width: 100%; height: auto; display: block; margin: 0 auto;">
                    </div>
                    <h3 class="mb-0" style="color: #fff; font-weight: 500;">Welcome to {{ config('app.name') }}</h3>
                </div>
            </div>
            <div class="h-100 d-flex bg-white justify-content-center align-items-center col-md-12 col-lg-8">
                <div class="mx-auto app-login-box col-sm-12 col-md-10 col-lg-9">
                    <div class="app-logo"></div>
                    <h4 class="mb-0">
                        <span>Please sign in to your account.</span>
                    </h4>
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="divider row"></div>
                    <div>
                        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input name="email" id="email" placeholder="Enter your email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input name="password" id="password" placeholder="Enter your password" type="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                                            <span class="input-group-text toggle-password" style="cursor: pointer; background: var(--primary-color) !important; border-color: var(--primary-color) !important;" data-target="#password">
                                                <i class="bi bi-eye" style="color: #fff !important;"></i>
                                            </span>
                                            <div class="invalid-feedback">Please enter your password</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="ms-auto">
                                    <button type="submit" class="btn btn-primary btn-lg" style="color: #fff !important; font-weight: 400 !important;">Login to Dashboard</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
