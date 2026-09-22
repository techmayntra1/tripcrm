@extends('layouts.app')
@section('title', 'Add New Customer')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-person-plus-fill icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>
                Add New Customer
                
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
        <i class="bi bi-person-plus me-2"></i> Customer Information
    </div>
    <div class="card-body">
        <form action="{{ route('admin.customers.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @include('admin.customers._form_fields')
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
