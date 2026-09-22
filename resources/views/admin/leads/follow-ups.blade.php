@extends('layouts.app')
@section('title', 'Lead Follow Ups')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-clock-history icon-gradient bg-mean-fruit"></i>
            </div>
            <div title="{{ $lead->name }}">
                Follow Ups - {{ Str::limit($lead->name, 25) }}
            </div>
        </div>
        <div class="page-title-actions">
            @can('leads.edit')
            <button class="btn btn-primary me-2" type="button" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
                <i class="bi bi-plus-lg me-1"></i> Add Update
            </button>
            @endcan
            <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Lead
            </a>
        </div>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="row mb-3">
    <div class="col-md-12">
        <div class="main-card card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Lead Name</small>
                        <strong>{{ $lead->name }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Mobile</small>
                        <strong>{{ $lead->mobile ? $lead->full_mobile : '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $lead->status_color }}">{{ ucfirst($lead->status) }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Updates</small>
                        <strong>{{ $updates->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="main-card mb-3 card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i> Follow Up History
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="150" style="white-space: nowrap">Follow-up Date</th>
                            <th width="150">Type</th>
                            <th>Notes</th>
                            <th width="120">Added By</th>
                            <th width="150" style="white-space: nowrap">Created At</th>
                            @can('leads.edit')
                            <th width="80">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($updates as $update)
                        <tr>
                            <td style="white-space: nowrap">
                                @if($update->follow_up_date)
                                    {{ formatDate($update->follow_up_date, true) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $update->updateType->color ?? 'primary' }}">
                                    <i class="bi {{ $update->updateType->icon ?? 'bi-sticky' }} me-1"></i>{{ $update->updateType->name ?? 'Update' }}
                                </span>
                            </td>
                            <td>{{ $update->notes }}</td>
                            <td>{{ $update->user->name ?? 'System' }}</td>
                            <td style="white-space: nowrap">{{ $update->created_at->format('d-m-Y H:i') }}</td>
                            @can('leads.edit')
                            <td>
                                <form action="{{ route('admin.leads.updates.destroy', [$lead, $update]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this update?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('leads.edit') ? 6 : 5 }}" class="text-center py-4 text-muted">
                                No follow-up updates recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
@can('leads.edit')
<div class="modal fade" id="addUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leads.updates.store', $lead) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="update_type_id" class="form-label">Update Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="update_type_id" id="update_type_id" required>
                                <option value="">Select Type</option>
                                @foreach($updateTypes as $type)
                                    <option value="{{ $type->id }}" data-icon="{{ $type->icon }}" data-color="{{ $type->color }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Lead Status</label>
                            @php
                                $currentStatusOrder = $leadStatuses->firstWhere(fn($s) => strtolower($s->name) === $lead->status)?->sort_order ?? 0;
                                $isAdmin = auth()->user()->isAdmin();
                            @endphp
                            <select class="form-select" name="status" id="status">
                                <option value="">Keep Current</option>
                                @foreach($leadStatuses as $status)
                                    @if($isAdmin || $status->sort_order >= $currentStatusOrder)
                                        <option value="{{ strtolower($status->name) }}">{{ $status->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="follow_up_date" class="form-label">Next Follow-up Date</label>
                        <input type="datetime-local" class="form-control" name="follow_up_date" id="follow_up_date" placeholder="Select date and time">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes / Update Details <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Enter update details..." required maxlength="150"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endpush
