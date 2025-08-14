@extends('layouts.app')

@section('title', 'Labels')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Labels</h1>
    <a href="{{ route('labels.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Label
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Label Name</th>
                        <th>Donor Count</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labels as $label)
                    <tr>
                        <td>{{ isset($labels->currentPage) ? (($labels->currentPage() - 1) * $labels->perPage() + $loop->iteration) : $loop->iteration }}</td>
                        <td>
                            <span class="badge bg-primary fs-6">{{ $label->label_name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $label->donors_count }} donors</span>
                        </td>
                        <td>{{ $label->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('labels.edit', $label) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('labels.destroy', $label) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this label? This will remove it from all donors.')" 
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
        
        @if($labels->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $labels->links('vendor.pagination.simple') }}
            </div>
        @endif
    </div>
</div>
@endsection 