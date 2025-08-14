@extends('layouts.app')

@section('title', 'Edit Client')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Client</h1>
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Clients
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('clients.update', $client) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Client Name *</label>
                        <input type="text" class="form-control @error('client_name') is-invalid @enderror" 
                               id="client_name" name="client_name" value="{{ old('client_name', $client->client_name) }}" required>
                        @error('client_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="client_org" class="form-label">Organization *</label>
                        <input type="text" class="form-control @error('client_org') is-invalid @enderror" 
                               id="client_org" name="client_org" value="{{ old('client_org', $client->client_org) }}" required>
                        @error('client_org')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="client_phonenum" class="form-label">Phone Number *</label>
                        <input type="text" class="form-control @error('client_phonenum') is-invalid @enderror" 
                               id="client_phonenum" name="client_phonenum" value="{{ old('client_phonenum', $client->client_phonenum) }}" required>
                        @error('client_phonenum')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('clients.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Client Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> {{ $client->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Last Updated:</strong> {{ $client->updated_at->format('M d, Y H:i') }}</p>
                
                <hr>
                
                <h6>Related Data:</h6>
                <p><strong>Donors:</strong> {{ $client->donors->count() }}</p>
                <p><strong>Blasts:</strong> {{ $client->blasts->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection 