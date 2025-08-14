@extends('layouts.app')

@section('title', 'Label Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Label Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('labels.edit', $label) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('labels.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Label Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $label->label_name }}</p>
                        <p><strong>Code:</strong> {{ $label->label_code ?: 'Not set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created:</strong> {{ $label->created_at->format('M d, Y H:i') }}</p>
                        <p><strong>Last Updated:</strong> {{ $label->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('labels.edit', $label) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit Label
                </a>
                <form action="{{ route('labels.destroy', $label) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this label?')" 
                      style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Delete Label
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 