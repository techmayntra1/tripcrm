@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-pencil-square icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Edit Customer: {{ $customer->name }}
                
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-pencil me-2"></i> Customer Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            @include('admin.customers._form_fields', ['customer' => $customer])
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
