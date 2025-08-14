@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Blasts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBlasts }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Donors</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDonors }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Clients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClients }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Blasts -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Blasts</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Blast Name</th>
                                <th>Client</th>
                                <th>Sentiment</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBlasts as $blast)
                            <tr>
                                <td>{{ $blast->blast_name }}</td>
                                <td>{{ $blast->client->client_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $blast->blast_sentiment == 'positive' ? 'success' : ($blast->blast_sentiment == 'negative' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($blast->blast_sentiment) }}
                                    </span>
                                </td>
                                <td>
                                    @if($blast->blastResult)
                                        <span class="badge bg-{{ $blast->blastResult->blast_status == 'completed' ? 'success' : ($blast->blastResult->blast_status == 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($blast->blastResult->blast_status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">No Result</span>
                                    @endif
                                </td>
                                <td>{{ $blast->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No blasts found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // No specific DataTable initialization needed as per new_code
    });
</script>
@endpush 