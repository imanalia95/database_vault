@extends('layouts.app')

@section('title', 'Edit Label')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Label</h1>
    <a href="{{ route('labels.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Labels
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('labels.update', $label) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="label_name" class="form-label">Label Name *</label>
                        <input type="text" class="form-control @error('label_name') is-invalid @enderror" 
                               id="label_name" name="label_name" value="{{ old('label_name', $label->label_name) }}" required>
                        @error('label_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter a unique name for this label</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('labels.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Label</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Label Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> {{ $label->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Last Updated:</strong> {{ $label->updated_at->format('M d, Y H:i') }}</p>
                
                <hr>
                
                <h6>Current Usage:</h6>
                <p><strong>Donors with this label:</strong> {{ $label->donors->count() }}</p>
                
                @if($label->donors->count() > 0)
                <div class="mt-3">
                    <h6>Sample Donors:</h6>
                    <ul class="list-unstyled">
                        @foreach($label->donors->take(5) as $donor)
                        <li class="mb-1">
                            <small>{{ $donor->donor_name }} ({{ $donor->donor_phonenum }})</small>
                        </li>
                        @endforeach
                        @if($label->donors->count() > 5)
                        <li class="text-muted">
                            <small>... and {{ $label->donors->count() - 5 }} more</small>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 