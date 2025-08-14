@extends('layouts.app')

@section('title', 'Download Donors')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Download Donors</h1>
    <a href="{{ route('donors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Donors</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('donors.download') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by name or phone">
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
            <div class="col-md-3">
                <label for="phone_status" class="form-label">Phone Status</label>
                <select class="form-select" id="phone_status" name="phone_status">
                    <option value="">All Status</option>
                    <option value="valid" {{ request('phone_status') == 'valid' ? 'selected' : '' }}>Valid</option>
                    <option value="invalid" {{ request('phone_status') == 'invalid' ? 'selected' : '' }}>Invalid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="limit" class="form-label">Limit Results</label>
                <select class="form-select" id="limit" name="limit">
                    <option value="10" {{ request('limit', '10') == '10' ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('limit', '10') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('limit', '10') == '50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('limit', '10') == '100' ? 'selected' : '' }}>100</option>
                    <option value="250" {{ request('limit', '10') == '250' ? 'selected' : '' }}>250</option>
                    <option value="500" {{ request('limit', '10') == '500' ? 'selected' : '' }}>500</option>
                    <option value="1000" {{ request('limit', '10') == '1000' ? 'selected' : '' }}>1000</option>
                    <option value="all" {{ request('limit', '10') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('donors.download') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
@if(isset($donors) && $donors->count() > 0)
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Results ({{ $donors->count() }} donors found)</h5>
        <div>
            <button type="button" class="btn btn-success" onclick="exportSelected()">
                <i class="fas fa-download"></i> Export Selected
            </button>
            <button type="button" class="btn btn-primary" onclick="exportAll()">
                <i class="fas fa-download"></i> Export All
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="exportForm" method="POST" action="{{ route('donors.download.export') }}">
            @csrf
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="client_id" value="{{ request('client_id') }}">
            <input type="hidden" name="label_id" value="{{ request('label_id') }}">
            <input type="hidden" name="phone_status" value="{{ request('phone_status') }}">
            <input type="hidden" name="limit" value="{{ request('limit', '10') }}">
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label class="form-check-label" for="select-all">
                        Select All
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th width="50">Select</th>
                            <th>Phone Number</th>
                            <th>Phone Status</th>
                            <th>Labels</th>
                            <th>Client</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($donors as $donor)
                        <tr>
                            <td>{{ isset($donors->currentPage) ? (($donors->currentPage() - 1) * $donors->perPage() + $loop->iteration) : $loop->iteration }}</td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input donor-checkbox" type="checkbox" 
                                           name="selected_donors[]" value="{{ $donor->id }}" 
                                           id="donor_{{ $donor->id }}">
                                </div>
                            </td>
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
                                    @foreach($donor->clientDonors->pluck('label.label_name')->unique() as $labelName)
                                        <span class="badge bg-primary me-1">{{ $labelName }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No labels</span>
                                @endif
                            </td>
                            <td>
                                @foreach($donor->clientDonors->pluck('client.client_name')->unique() as $clientName)
                                    <span class="badge bg-info me-1">{{ $clientName }}</span>
                                @endforeach
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="removeDonor({{ $donor->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@elseif(request()->has('search') || request()->has('client_id') || request()->has('label_id'))
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No donors found matching your criteria. Try adjusting your filters.
</div>
@endif

<!-- Export Options Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportOptionsForm">
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Export Format</label>
                        <select class="form-select" id="export_format" name="format">
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel (XLSX)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="export_columns" class="form-label">Columns to Include</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="label" id="col_label" checked>
                            <label class="form-check-label" for="col_label">Label</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="phone" id="col_phone" checked>
                            <label class="form-check-label" for="col_phone">Phone Number</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="col_email">
                            <label class="form-check-label" for="col_email">Email</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="client" id="col_client">
                            <label class="form-check-label" for="col_client">Client</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="phone_status" id="col_phone_status">
                            <label class="form-check-label" for="col_phone_status">Phone Status</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmExport()">Export</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let exportType = 'all'; // 'all' or 'selected'

document.getElementById('select-all').addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.donor-checkbox').forEach(cb => cb.checked = checked);
});

function exportAll() {
    exportType = 'all';
    document.getElementById('select-all').checked = true;
    document.querySelectorAll('.donor-checkbox').forEach(cb => cb.checked = true);
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

function exportSelected() {
    exportType = 'selected';
    const checkedBoxes = document.querySelectorAll('.donor-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one donor to export.');
        return;
    }
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

function confirmExport() {
    const form = document.getElementById('exportForm');
    const optionsForm = document.getElementById('exportOptionsForm');
    
    // Add export options to form
    const format = document.getElementById('export_format').value;
    const columns = Array.from(document.querySelectorAll('#exportOptionsForm input[name="columns[]"]:checked'))
                        .map(cb => cb.value);
    
    // Add hidden inputs
    let formatInput = form.querySelector('input[name="format"]');
    if (!formatInput) {
        formatInput = document.createElement('input');
        formatInput.type = 'hidden';
        formatInput.name = 'format';
        form.appendChild(formatInput);
    }
    formatInput.value = format;
    
    // Remove existing column inputs
    form.querySelectorAll('input[name="columns[]"]').forEach(input => input.remove());
    
    // Add new column inputs
    columns.forEach(column => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'columns[]';
        input.value = column;
        form.appendChild(input);
    });
    
    // Add export type
    let typeInput = form.querySelector('input[name="export_type"]');
    if (!typeInput) {
        typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'export_type';
        form.appendChild(typeInput);
    }
    typeInput.value = exportType;
    
    // Submit form
    form.submit();
}

function removeDonor(donorId) {
    if (confirm('Are you sure you want to delete this donor? This action cannot be undone.')) {
        // Get the button and show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        // Send AJAX request to delete the donor
        fetch(`/donors/${donorId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                const row = button.closest('tr');
                row.remove();
                
                // Update the results count
                const resultsHeader = document.querySelector('.card-header h5');
                const currentCount = parseInt(resultsHeader.textContent.match(/\d+/)[0]);
                resultsHeader.textContent = `Results (${currentCount - 1} donors found)`;
                
                // Show success message
                alert('Donor deleted successfully!');
            } else {
                alert('Error deleting donor: ' + (data.message || 'Unknown error'));
                // Restore button
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting donor. Please try again.');
            // Restore button
            button.innerHTML = originalContent;
            button.disabled = false;
        });
    }
}
</script>
@endpush 