@extends('layouts.app')

@section('title', 'Download Donors by Label')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Download Donors by Label</h1>
    <a href="{{ route('donors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('blasting.download.export') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="label_id" class="form-label">Select Label *</label>
                        <select class="form-select @error('label_id') is-invalid @enderror" id="label_id" name="label_id" required>
                            <option value="">Choose a label...</option>
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->label_name }}</option>
                            @endforeach
                        </select>
                        @error('label_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Excel (CSV)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 