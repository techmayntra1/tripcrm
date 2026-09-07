@extends('layouts.app')
@section('title', 'Passenger Types')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-people icon-gradient bg-info"></i>
            </div>
            <div>Passenger Types</div>
        </div>
        <div class="page-title-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="main-card mb-3 card">
    <div class="card-header has-tabs d-flex justify-content-between align-items-center">
        <ul class="nav lead-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.masters.passenger-types') }}">
                    <i class="bi bi-people me-1"></i> Active ({{ $items->where('is_active', true)->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.masters.passenger-types.trashed') }}">
                    <i class="bi bi-archive me-1"></i> Inactive ({{ $inactiveCount }})
                </a>
            </li>
        </ul>
        <form method="GET" action="{{ route('admin.masters.passenger-types') }}" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" class="form-control" name="search" placeholder="Search passenger types..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </div>
            @if(request('search'))
            <a href="{{ route('admin.masters.passenger-types') }}" class="btn btn-sm btn-danger" title="Clear"><i class="bi bi-x-lg"></i></a>
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
                @forelse($items->where('is_active', true) as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="openEditModal({{ json_encode($item) }})" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.masters.passenger-types.toggle', $item) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                        No passenger types found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        Showing {{ $items->where('is_active', true)->count() }} {{ Str::plural('item', $items->where('is_active', true)->count()) }}
        @if(request('search')) (filtered) @endif
    </div>
</div>
@push('modals')
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add Passenger Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.masters.passenger-types.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Passenger Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection
@push('styles')
<style>
    .card-header .form-control {
        background-color: #fff !important;
        color: #333 !important;
    }
</style>
@endpush
@push('scripts')
<script>
function openEditModal(item) {
    document.getElementById('editForm').action = '/admin/masters/passenger-types/' + item.id;
    document.getElementById('edit_name').value = item.name;
}
</script>
@endpush
