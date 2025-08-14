@extends('layouts.app')

@section('title', 'Donor Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Donor Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('donors.edit', $donor) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('donors.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div class="alert alert-warning mb-3">
    <i class="fas fa-exclamation-triangle"></i> <strong>Reminder:</strong> Use <strong>Remove from Client</strong> to remove this donor from a specific client/label only. Use <strong>Delete Donor</strong> to remove the donor from all clients and labels.
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Donor Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $donor->donor_name }}</p>
                        <p><strong>Phone:</strong> {{ $donor->donor_phonenum }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> {{ $donor->donor_email ?: 'Not provided' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $donor->donor_status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($donor->donor_status) }}
                            </span>
                        </p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created:</strong> {{ $donor->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Last Updated:</strong> {{ $donor->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Client Assignments</h5>
            </div>
            <div class="card-body">
                @if($donor->clientDonors->count() > 0)
                    @foreach($donor->clientDonors as $clientDonor)
                        <div class="mb-3 p-3 border rounded d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-info me-1">{{ $clientDonor->client->client_name }}</span>
                                <span class="badge bg-primary me-2">{{ $clientDonor->label->label_name }}</span>
                                <small class="text-muted">Assigned: {{ $clientDonor->added_at->format('M d, Y') }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="removeFromClient({{ $donor->id }}, {{ $clientDonor->client_id }}, {{ $clientDonor->label_id }}, '{{ $clientDonor->client->client_name }}', '{{ $clientDonor->label->label_name }}')">
                                <i class="fas fa-times"></i> Remove from Client
                            </button>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No client assignments yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('donors.edit', $donor) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit Donor
                </a>
                <button type="button" class="btn btn-info w-100 mb-2" onclick="assignLabels({{ $donor->id }})">
                    <i class="fas fa-tags"></i> Assign Labels
                </button>
                <form action="{{ route('donors.destroy', $donor) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this donor?')" 
                      style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Delete Donor
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <p><strong>Total Clients:</strong> {{ $donor->clientDonors->groupBy('client_id')->count() }}</p>
                <p><strong>Total Labels:</strong> {{ $donor->clientDonors->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Label Assignment Modal -->
<div class="modal fade" id="labelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Labels</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="labelForm">
                    <div class="mb-3">
                        <label for="modal_client_id" class="form-label">Select Client</label>
                        <select class="form-select" id="modal_client_id" name="client_id" required>
                            <option value="">Choose a client...</option>
                            @foreach($clients ?? [] as $client)
                                <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Labels</label>
                        <div id="labelCheckboxes">
                            @foreach($labels ?? [] as $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="label_ids[]" 
                                       value="{{ $label->id }}" id="label_{{ $label->id }}">
                                <label class="form-check-label" for="label_{{ $label->id }}">
                                    {{ $label->label_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveLabels()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentDonorId = {{ $donor->id }};

function assignLabels(donorId) {
    currentDonorId = donorId;
    // Reset form
    document.getElementById('modal_client_id').value = '';
    document.querySelectorAll('#labelForm input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    new bootstrap.Modal(document.getElementById('labelModal')).show();
}

document.getElementById('modal_client_id').addEventListener('change', function() {
    const clientId = this.value;
    if (!clientId) return;
    
    // Get current labels for this donor and client
    fetch(`/donors/${currentDonorId}/assign-labels?client_id=${clientId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Reset checkboxes
        document.querySelectorAll('#labelForm input[type="checkbox"]').forEach(cb => cb.checked = false);
        
        // Check current labels
        if (data.labels) {
            data.labels.forEach(labelId => {
                const checkbox = document.getElementById(`label_${labelId}`);
                if (checkbox) checkbox.checked = true;
            });
        }
    });
});

function saveLabels() {
    const formData = new FormData(document.getElementById('labelForm'));
    const clientId = document.getElementById('modal_client_id').value;
    
    if (!clientId) {
        alert('Please select a client first.');
        return;
    }
    
    fetch(`/donors/${currentDonorId}/assign-labels`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function removeFromClient(donorId, clientId, labelId, clientName, labelName) {
    if (!confirm(`Are you sure you want to remove this donor from ${clientName} - ${labelName}?`)) {
        return;
    }
    fetch(`/donors/${donorId}/remove-from-client`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ client_id: clientId, label_id: labelId })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    })
    .catch(error => {
        console.error('Error removing from client:', error);
        alert('Failed to remove donor from client.');
    });
}
</script>
@endpush 