@extends('layouts.app')

@section('title', 'Import Donors')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Import Donors from Excel</h1>
    <a href="{{ route('donors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Import Donors</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success card mb-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger card mb-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if (
                    $errors->any())
                    <div class="alert alert-danger card mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('donors.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="import_type" value="full">
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Select Client *</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">Choose a client...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->client_name }} ({{ $client->client_org }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Excel/CSV File *</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('donors.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import Donors
                        </button>
                    </div>
                </form>
                <div id="full-format" class="mt-4">
                    <h6>Full Database Import Format (multi-column):</h6>
                    <ul class="list-unstyled">
                        <li><strong>Column 1:</strong> Label (required)</li>
                        <li><strong>Column 2:</strong> Phone Number (required)</li>
                        <li><strong>Column 3:</strong> Contact Name (optional, will be ignored)</li>
                        <li><strong>Column 4:</strong> WhatsApp Name (will be used as donor name)</li>
                        <li><strong>Column 5:</strong> Country (optional, will be ignored)</li>
                    </ul>
                    <h6>Example Format:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Phone Number</th>
                                    <th>Contact Name</th>
                                    <th>WhatsApp Name</th>
                                    <th>Country</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Premium</td>
                                    <td>+60123456789</td>
                                    <td>John Doe</td>
                                    <td>John</td>
                                    <td>Malaysia</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-2">
                        <small>
                            <strong>How it works:</strong> Each row must have a label and phone number. Labels must exist in the database. The first row should be a header and will be skipped.
                        </small>
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
                <a href="{{ route('donors.create') }}" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-plus"></i> Add Single Donor
                </a>
                <a href="{{ route('clients.create') }}" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-building"></i> Create New Client
                </a>
                <a href="{{ route('labels.create') }}" class="btn btn-warning w-100">
                    <i class="fas fa-tags"></i> Create New Label
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fullImport = document.getElementById('full_import');
    const fullFormat = document.getElementById('full-format');

    // No need for updateDisplay function as there's only one import type
    // fullImport.addEventListener('change', updateDisplay);
    
    // Initialize display
    // updateDisplay(); // This line is no longer needed
});
</script>
@endpush
@endsection 