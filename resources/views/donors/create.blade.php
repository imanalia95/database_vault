@extends('layouts.app')

@section('title', 'Add Donor')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Donor</h1>
    <a href="{{ route('donors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('donors.store') }}" method="POST" id="donorForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="donor_name" class="form-label">Donor Name *</label>
                        <input type="text" class="form-control @error('donor_name') is-invalid @enderror" 
                               id="donor_name" name="donor_name" value="{{ old('donor_name') }}" required>
                        @error('donor_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="donor_phonenum" class="form-label">Phone Number *</label>
                        <input type="text" class="form-control @error('donor_phonenum') is-invalid @enderror" 
                               id="donor_phonenum" name="donor_phonenum" value="{{ old('donor_phonenum') }}" required>
                        @error('donor_phonenum')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="donor_email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('donor_email') is-invalid @enderror" 
                               id="donor_email" name="donor_email" value="{{ old('donor_email') }}">
                        @error('donor_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="donor_status" class="form-label">Status *</label>
                        <select class="form-select @error('donor_status') is-invalid @enderror" 
                                id="donor_status" name="donor_status" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('donor_status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('donor_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('donor_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Client & Label Assignments</label>
                        <div id="clientAssignments">
                            <div class="client-assignment mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Client</label>
                                        <select class="form-select" name="client_assignments[0][client_id]" required>
                                            <option value="">Select Client</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Labels</label>
                                        <div class="row">
                                            @foreach($labels as $label)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="client_assignments[0][label_ids][]" 
                                                           value="{{ $label->id }}" id="label_0_{{ $label->id }}">
                                                    <label class="form-check-label" for="label_0_{{ $label->id }}">
                                                        {{ $label->label_name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addClientAssignment()">
                            <i class="fas fa-plus"></i> Add Another Client
                        </button>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('donors.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Donor</button>
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
                <a href="{{ route('donors.import.form') }}" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-file-excel"></i> Import from Excel
                </a>
                <a href="{{ route('labels.create') }}" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-plus"></i> Create New Label
                </a>
                <a href="{{ route('clients.create') }}" class="btn btn-warning w-100">
                    <i class="fas fa-building"></i> Add New Client
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let assignmentIndex = 1;

function addClientAssignment() {
    const container = document.getElementById('clientAssignments');
    const newAssignment = document.createElement('div');
    newAssignment.className = 'client-assignment mb-3 p-3 border rounded';
    newAssignment.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Client</label>
                <select class="form-select" name="client_assignments[${assignmentIndex}][client_id]" required>
                    <option value="">Select Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Labels</label>
                <div class="row">
                    @foreach($labels as $label)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="client_assignments[${assignmentIndex}][label_ids][]" 
                                   value="{{ $label->id }}" id="label_${assignmentIndex}_{{ $label->id }}">
                            <label class="form-check-label" for="label_${assignmentIndex}_{{ $label->id }}">
                                {{ $label->label_name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeClientAssignment(this)">
            <i class="fas fa-trash"></i> Remove
        </button>
    `;
    container.appendChild(newAssignment);
    assignmentIndex++;
}

function removeClientAssignment(button) {
    button.closest('.client-assignment').remove();
}
</script>
@endpush 