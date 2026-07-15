@extends('layouts.admin')

@section('title')
    Customers
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h4 class="mb-0">All Customers</h4>
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-success btn-sm">Add Customer</a>
                </div>
            </div>
            <div class="card-body table-border-style mb-2">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-3">
                    <div class="row gx-3 gy-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Search</label>
                            <input type="text" name="search_text" class="form-control" value="{{ request('search_text', $search ?? '') }}"
                                placeholder="Search name, email, address, city, state, country, zipcode">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Per Page</label>
                            @php $showrecord = Config::get('constants.SHOW_RECORD'); @endphp
                            <select name="showrecord" class="form-control">
                                @foreach($showrecord as $key => $label)
                                    <option value="{{ $key }}" {{ (string) request('showrecord', $perPage) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-dark btn-sm">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Buyer Name</th>
                                <th>Buyer Email</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Country</th>
                                <th>Zipcode</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($customers->count())
                                @php $i = ($customers->currentPage() - 1) * $customers->perPage(); @endphp
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td>{{ $customer->buyer_name }}</td>
                                        <td>{{ $customer->buyer_email }}</td>
                                        <td>{{ $customer->buyer_address }}</td>
                                        <td>{{ $customer->buyer_city }}</td>
                                        <td>{{ $customer->buyer_state }}</td>
                                        <td>{{ $customer->buyer_country }}</td>
                                        <td>{{ $customer->buyer_zipcode }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-success btn-sm" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-info btn-sm" title="Edit">
                                                    <i class="far fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this customer?');">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="text-center" colspan="9">No customers found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($customers->count())
                    {!! $customers->links('pagination::bootstrap-5') !!}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
