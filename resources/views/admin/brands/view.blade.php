@extends('layouts.admin')
@section('title') View Brand @endsection
@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header py-4 px-4 bg-light border-bottom position-relative rounded-top">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 2rem;">
                        <i class="fa fa-info-circle"></i>
                    </span>
                    <div>
                        <h3 class="mb-0 text-primary fw-bold">Brand Details Overview</h3>
                        <div class="text-muted small">Comprehensive summary and actions for this brand</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Brand Name</label>
                    <div class="fw-bold">{{ $brand->name }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <div>{{ $brand->description }}</div>
                </div>
            </div>
            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-info">Edit</a>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection
