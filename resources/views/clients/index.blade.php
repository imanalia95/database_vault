@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Clients</h1>
    <a href="{{ route('clients.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Client
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Organization</th>
                        <th>Phone</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>{{ isset($clients->currentPage) ? (($clients->currentPage() - 1) * $clients->perPage() + $loop->iteration) : $loop->iteration }}</td>
                        <td>{{ $client->client_name }}</td>
                        <td>{{ $client->client_org }}</td>
                        <td>{{ $client->client_phonenum }}</td>
                        <td>{{ $client->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this client?')" 
                                      style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($clients->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $clients->links('vendor.pagination.simple') }}
            </div>
        @endif
    </div>
</div>
@endsection 