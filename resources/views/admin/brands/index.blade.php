@extends('layouts.admin')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-10">
                            <h4>All Brands</h4>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary float-right">Add Brand</a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style mb-2">
                    <form method="GET" action="{{ route('admin.brands.index') }}" class="mb-3">
                        <div class="row gx-3 gy-2 align-items-end">
                            <div class="col-md-4">
                                <input type="text" name="name" class="form-control" placeholder="Filter by name"
                                    value="{{ request('name') }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="description" class="form-control"
                                    placeholder="Filter by description" value="{{ request('description') }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-info">Filter</button>
                                <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td>{{ $brand->name }}</td>
                                    <td>{{ $brand->description }}</td>
                                    <td>
                                        <a href="{{ route('admin.brands.show', $brand->id) }}" class="btn btn-sm btn-success" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.brands.edit', $brand->id) }}"
                                            class="btn btn-sm btn-warning" title="Edit">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this brand?')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($brands->count())
                        {!! $brands->withQueryString()->links('pagination::bootstrap-5') !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
