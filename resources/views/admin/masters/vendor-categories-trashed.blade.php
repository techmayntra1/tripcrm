@extends('layouts.app')
@section('title', 'Deleted Vendor Categories')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-tags-fill icon-gradient bg-warning"></i>
            </div>
            <div>Vendor Categories</div>
        </div>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.masters.vendor-categories') }}">
                    <i class="bi bi-tags me-1"></i> Active ({{ $activeCount }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.masters.vendor-categories.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Deleted ({{ $items->count() }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.masters.vendor-categories.trashed') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" name="search" placeholder="Search categories..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </div>
            @if(request('search'))
            <a href="{{ route('admin.masters.vendor-categories.trashed') }}" class="btn btn-sm btn-danger" title="Clear"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Name</th>
                    <th width="120" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-center">
                        <form action="{{ route('admin.masters.vendor-categories.toggle', $item) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                        <i class="bi bi-archive fs-1 d-block mb-2"></i>
                        No deleted vendor categories.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        Showing {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
        @if(request('search')) (filtered) @endif
    </div>
</div>
@endsection
@push('styles')
<style>
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
