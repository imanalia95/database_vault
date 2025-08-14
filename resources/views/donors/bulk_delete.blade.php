@extends('layouts.app')

@section('title', 'Bulk Delete Donors')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Bulk Delete Donors</h1>
    <a href="{{ route('donors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
</div>

<div class="alert alert-warning mb-3">
    <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> When removing donors from a specific client, you must select which client they should be removed from. The donor will only be removed from that client's assignments, not from other clients they may be associated with.
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Bulk Delete by Phone Numbers</h5>
    </div>
    <div class="card-body">
        <form id="bulkDeleteForm" onsubmit="submitBulkDelete(event)">
            <div class="mb-3">
                <label for="phone_numbers" class="form-label">Phone Numbers (one per line, comma, or space separated)</label>
                <textarea class="form-control" id="phone_numbers" name="phone_numbers" rows="6" required placeholder="Paste phone numbers here..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Delete Type</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="delete_type" id="delete_type_full" value="full" checked>
                    <label class="form-check-label" for="delete_type_full">
                        <strong>Delete donor(s) entirely</strong> - Removes donor from ALL clients and deletes the donor record completely
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="delete_type" id="delete_type_relationship" value="relationship">
                    <label class="form-check-label" for="delete_type_relationship">
                        <strong>Remove donor(s) from specific client only</strong> - Keeps donor but removes them from selected client
                    </label>
                </div>
            </div>
            <div class="row mb-3" id="relationship-filters" style="display:none;">
                <div class="col-md-6">
                    <label for="client_id" class="form-label"><strong>Select Client *</strong></label>
                    <select class="form-select" id="client_id" name="client_id" required>
                        <option value="">Choose a client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">This is the client from which the donor(s) will be removed</small>
                </div>
                <div class="col-md-6">
                    <label for="label_id" class="form-label">Specific Label (Optional)</label>
                    <select class="form-select" id="label_id" name="label_id">
                        <option value="">All Labels for Selected Client</option>
                        @foreach($labels as $label)
                            <option value="{{ $label->id }}">{{ $label->label_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">If selected, removes donor only from this specific label within the client</small>
                </div>
            </div>
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Bulk Delete
            </button>
        </form>
        <div id="bulkDeleteResults" class="mt-4" style="display:none;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="delete_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const relationshipFilters = document.getElementById('relationship-filters');
        const clientSelect = document.getElementById('client_id');
        
        if (this.value === 'relationship') {
            relationshipFilters.style.display = '';
            clientSelect.required = true;
        } else {
            relationshipFilters.style.display = 'none';
            clientSelect.required = false;
        }
    });
});

function submitBulkDelete(event) {
    event.preventDefault();
    const form = document.getElementById('bulkDeleteForm');
    const deleteType = document.querySelector('input[name="delete_type"]:checked').value;
    const clientId = document.getElementById('client_id').value;
    
    // Validate client selection for relationship deletion
    if (deleteType === 'relationship' && !clientId) {
        alert('Please select a client when removing donors from a specific client.');
        return;
    }
    
    const formData = new FormData(form);
    const resultsDiv = document.getElementById('bulkDeleteResults');
    resultsDiv.style.display = 'none';
    resultsDiv.innerHTML = '';

    fetch("{{ route('donors.bulkDeletePhones') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let html = '';
        if (data.success) {
            html += `<div class='alert alert-success'><strong>Bulk delete completed successfully.</strong></div>`;
            if (data.deleted && data.deleted.length) {
                html += `<div class='mb-2'><strong>Deleted entirely:</strong> <span class='text-danger'>${data.deleted.join(', ')}</span></div>`;
            }
            if (data.removed && data.removed.length) {
                const clientName = document.getElementById('client_id').options[document.getElementById('client_id').selectedIndex].text;
                html += `<div class='mb-2'><strong>Removed from client (${clientName}):</strong> <span class='text-warning'>${data.removed.join(', ')}</span></div>`;
            }
            if (data.not_found && data.not_found.length) {
                html += `<div class='mb-2'><strong>Not found:</strong> <span class='text-muted'>${data.not_found.join(', ')}</span></div>`;
            }
            if (data.errors && data.errors.length) {
                html += `<div class='alert alert-danger'><strong>Errors:</strong><br>${data.errors.join('<br>')}</div>`;
            }
        } else {
            html += `<div class='alert alert-warning'><strong>${data.message}</strong></div>`;
            if (data.not_found && data.not_found.length) {
                html += `<div class='mb-2'><strong>Phone numbers not found:</strong> <span class='text-muted'>${data.not_found.join(', ')}</span></div>`;
            }
            if (data.debug) {
                html += `<div class='alert alert-info'><strong>Debug Info:</strong><br>`;
                html += `<strong>Searched phones:</strong> ${data.debug.searched_phones.join(', ')}<br>`;
                html += `<strong>Total donors in DB:</strong> ${data.debug.total_donors_in_db}<br>`;
                html += `<strong>Sample DB phones:</strong> ${data.debug.sample_db_phones.join(', ')}<br>`;
                html += `</div>`;
            }
            if (data.errors && data.errors.length) {
                html += `<div class='alert alert-danger'><strong>Errors:</strong><br>${data.errors.join('<br>')}</div>`;
            }
        }
        resultsDiv.innerHTML = html;
        resultsDiv.style.display = '';
    })
    .catch(error => {
        resultsDiv.innerHTML = `<div class='alert alert-danger'><strong>Error:</strong> ${error}</div>`;
        resultsDiv.style.display = '';
    });
}
</script>
@endpush 