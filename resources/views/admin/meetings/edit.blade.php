@extends('layouts.app')
@section('title', 'Edit Meeting')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-calendar-event icon-gradient bg-warning"></i>
            </div>
            <div>
                Edit Meeting
                <div class="page-title-subheading">{{ $meeting->title }}</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('admin.meetings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="bi bi-pencil me-2"></i> Meeting Details
    </div>
    <div class="card-body">
        <form action="{{ route('admin.meetings.update', $meeting) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="title" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $meeting->title) }}" required maxlength="40">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="meeting_at" class="form-label">Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control @error('meeting_at') is-invalid @enderror" id="meeting_at" name="meeting_at" value="{{ old('meeting_at', $meeting->meeting_at->format('Y-m-d\TH:i')) }}" required>
                @error('meeting_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <input type="hidden" name="meeting_with" value="{{ $meeting->lead_id ? 'lead' : ($meeting->customer_id ? 'customer' : 'other') }}">
            <div class="mb-3">
                <label for="purpose_id" class="form-label">Purpose</label>
                <select class="form-select @error('purpose_id') is-invalid @enderror" id="purpose_id" name="purpose_id">
                    <option value="">Select Purpose</option>
                    @foreach($meetingPurposes as $purpose)
                        <option value="{{ $purpose->id }}" {{ old('purpose_id', $meeting->purpose_id) == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                    @endforeach
                </select>
                @error('purpose_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $meeting->location) }}" maxlength="20">
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Notes</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="150">{{ old('description', $meeting->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.meetings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Meeting
                </button>
            </div>
        </form>
        <hr>
        <div class="d-flex justify-content-start">
            <form action="{{ route('admin.meetings.deactivate', $meeting) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this meeting?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-eye-slash me-1"></i> Delete Meeting
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
