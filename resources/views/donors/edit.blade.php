@extends('layouts.app')

@section('title', 'Edit Donor')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Donor</h1>
    <a href="{{ route('donors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('donors.update', $donor) }}" method="POST" id="donorForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="donor_name" class="form-label">Donor Name *</label>
                        <input type="text" class="form-control @error('donor_name') is-invalid @enderror" 
                               id="donor_name" name="donor_name" value="{{ old('donor_name', $donor->donor_name) }}" required>
                        @error('donor_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="donor_phonenum" class="form-label">Phone Number *</label>
                        <input type="text" class="form-control @error('donor_phonenum') is-invalid @enderror" 
                               id="donor_phonenum" name="donor_phonenum" value="{{ old('donor_phonenum', $donor->donor_phonenum) }}" required>
                        @error('donor_phonenum')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="donor_email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('donor_email') is-invalid @enderror" 
                               id="donor_email" name="donor_email" value="{{ old('donor_email', $donor->donor_email) }}">
                        @error('donor_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="donor_status" class="form-label">Status *</label>
                        <select class="form-select @error('donor_status') is-invalid @enderror" 
                                id="donor_status" name="donor_status" required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('donor_status', $donor->donor_status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('donor_status', $donor->donor_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('donor_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Client & Label Assignments</label>
                        <div id="clientAssignments">
                            @if($clientAssignments->count() > 0)
                                @foreach($clientAssignments as $index => $assignment)
                                <div class="client-assignment mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Client</label>
                                            <select class="form-select" name="client_assignments[{{ $index }}][client_id]" required>
                                                <option value="">Select Client</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ $assignment['client_id'] == $client->id ? 'selected' : '' }}>
                                                        {{ $client->client_name }}
                                                    </option>
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
                                                               name="client_assignments[{{ $index }}][label_ids][]" 
                                                               value="{{ $label->id }}" 
                                                               id="label_{{ $index }}_{{ $label->id }}"
                                                               {{ in_array($label->id, $assignment['label_ids']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="label_{{ $index }}_{{ $label->id }}">
                                                            {{ $label->label_name }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @if($index > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeClientAssignment(this)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                    @endif
                                </div>
                                @endforeach
                            @else
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
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addClientAssignment()">
                            <i class="fas fa-plus"></i> Add Another Client
                        </button>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('donors.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Donor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Current Assignments</h6>
            </div>
            <div class="card-body">
                @if($donor->clientDonors->count() > 0)
                    @foreach($donor->clientDonors->groupBy('client_id') as $clientId => $assignments)
                        @php
                            $client = $assignments->first()->client;
                            $clientLabels = $assignments->pluck('label.label_name');
                        @endphp
                        <div class="mb-3">
                            <h6 class="text-primary">{{ $client->client_name }}</h6>
                            @foreach($clientLabels as $labelName)
                                <span class="badge bg-primary me-1">{{ $labelName }}</span>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No client assignments yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let assignmentIndex = {{ $clientAssignments->count() }};

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