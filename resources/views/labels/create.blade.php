@extends('layouts.app')

@section('title', 'Add Label')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Label</h1>
    <a href="{{ route('labels.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Labels
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('labels.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="label_name" class="form-label">Label Name *</label>
                        <input type="text" class="form-control @error('label_name') is-invalid @enderror" 
                               id="label_name" name="label_name" value="{{ old('label_name') }}" required>
                        @error('label_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter a unique name for this label (e.g., "VIP", "Regular", "Premium")</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('labels.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Label</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('donors.create') }}" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-plus"></i> Add Donor
                </a>
                <a href="{{ route('clients.create') }}" class="btn btn-info w-100">
                    <i class="fas fa-building"></i> Add Client
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 