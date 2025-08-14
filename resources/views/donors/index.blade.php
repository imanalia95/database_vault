@extends('layouts.app')

@section('title', 'Donors')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Donors</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('donors.import.form') }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel"></i> Import Excel
            </a>
            <a href="{{ route('donors.download') }}" class="btn btn-sm btn-info">
                <i class="fas fa-download"></i> Export Excel
            </a>
            <a href="{{ route('donors.bulkDeleteForm') }}" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i> Bulk Delete
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('donors.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by phone number">
            </div>
            <div class="col-md-3">
                <label for="client_id" class="form-label">Filter by Client</label>
                <select class="form-select" id="client_id" name="client_id">
                    <option value="">All Clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->client_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="label_id" class="form-label">Filter by Label</label>
                <select class="form-select" id="label_id" name="label_id">
                    <option value="">All Labels</option>
                    @foreach($labels as $label)
                        <option value="{{ $label->id }}" {{ request('label_id') == $label->id ? 'selected' : '' }}>
                            {{ $label->label_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">Filter</button>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <a href="{{ route('donors.index') }}" class="btn btn-secondary d-block">Clear</a>
            </div>

        </form>

    </div>
</div>

<!-- Donors Table -->
<div class="card">
    <div class="card-body">
        <form id="bulkDeleteForm" action="{{ route('donors.bulk-delete') }}" method="POST">
            @csrf
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-danger btn-sm" id="ajax-bulk-delete">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th> <!-- Row number column -->
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Phone Number</th>
                            <th>Phone Status</th>
                            <th>Client/Label Assignments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donors as $donor)
                        <tr>
                            <td>{{ ($donors->currentPage() - 1) * $donors->perPage() + $loop->iteration }}</td>
                            <td><input type="checkbox" name="donor_ids[]" value="{{ $donor->id }}" class="row-checkbox"></td>
                            <td>{{ $donor->donor_phonenum }}</td>
                            <td>
                                @if($donor->phone_validation_status === 'valid')
                                    <span class="badge bg-success">Valid</span>
                                @else
                                    <span class="badge bg-danger">Invalid</span>
                                @endif
                            </td>
                            <td>
                                @if($donor->clientDonors->count() > 0)
                                    @foreach($donor->clientDonors as $clientDonor)
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-info me-1">{{ $clientDonor->client->client_name }}</span>
                                            <span class="badge bg-primary me-2">{{ $clientDonor->label->label_name }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">No assignments</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('donors.show', $donor) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('donors.edit', $donor) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="assignLabels({{ $donor->id }})">
                                        <i class="fas fa-tags"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No donors found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        @if($donors->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $donors->links('vendor.pagination.simple') }}
            </div>
        @endif
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
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Labels</label>
                        <div id="labelCheckboxes">
                            @foreach($labels as $label)
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
let currentDonorId = null;

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
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

document.getElementById('select-all').addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
});

document.getElementById('ajax-bulk-delete').addEventListener('click', function() {
    if (!confirm('Delete selected donors?')) return;
    const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    if (ids.length === 0) {
        alert('No donors selected.');
        return;
    }
    fetch('{{ route('donors.bulk-delete-ajax') }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ donor_ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
@endpush 