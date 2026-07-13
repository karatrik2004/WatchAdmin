@extends('layouts.admin')
@section('title') Vendors @endsection
@section('inline-css')
    <link rel="stylesheet" href="{{ asset('backend/css/flatpickr.min.css') }}" />
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h4>All Vendors</h4>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.vendors.index', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-dark btn-sm custom_btn float-right"><i class="fa fa-download" aria-hidden="true"></i>
                            Download CSV</a>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style mb-2">
                {{ html()->modelForm($search ?? null, 'get', route('admin.vendors.index'))
    ->class('bg-light p-4 rounded shadow-sm')
    ->id('filter-form')
    ->open()
}}

                <div class="card p-3 shadow-sm">
                    <div class="row gx-3 gy-2 align-items-end">

                        <div class="col-md-4">
                            <div class="form-floating">
                                {{ html()->text('search_text')
                                    ->class('form-control')
                                    ->placeholder('Search by name or email')
                                    ->value(old('search_text', $search->search_text ?? request('search_text')))
                                }}
                                <label for="search_text">Search by name or email</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating">
                                {{ html()->text('start_date')
                                    ->class('form-control flatpickr')
                                    ->id('start_date')
                                    ->placeholder('Start Date')
                                    ->value(old('start_date', $search->start_date ?? request('start_date')))
                                }}
                                <label for="start_date">Start Date</label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating">
                                {{ html()->text('end_date')
                                    ->class('form-control flatpickr')
                                    ->id('end_date')
                                    ->placeholder('End Date')
                                    ->value(old('end_date', $search->end_date ?? request('end_date')))
                                }}
                                <label for="end_date">End Date</label>
                            </div>
                        </div>

                        <div class="col-md-2 text-md-end">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold">Per Page:</span>
                                @php $showrecord = Config::get('constants.SHOW_RECORD'); @endphp
                                {{ html()->select('showrecord', $showrecord)
                                    ->class('form-select w-auto')
                                    ->id('showrecord')
                                }}
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i data-feather="filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.vendors.index') }}" class="btn btn-dark btn-sm">
                                <i data-feather="refresh-ccw"></i> Reset
                            </a>
                            <a href="{{ route('admin.vendors.create') }}" class="btn btn-success btn-sm ms-auto">Add Vendor</a>
                        </div>

                    </div>
                </div>

                {{ html()->form()->close() }}


                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendor Type</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                              
                                <th>Created</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(count($vendors))
                            @php $i = ($vendors->currentPage() - 1) * $vendors->perPage(); @endphp
                            @foreach($vendors as $vendor)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ ucfirst($vendor->vendor_type ?? '') }}</td>
                                    <td>
                                        @if(optional($vendor)->vendor_type === 'company')
                                            {{ $vendor->company_name }}
                                        @else
                                            {{ $vendor->first_name }} {{ $vendor->last_name }}
                                        @endif
                                    </td>
                                    <td>{{ $vendor->email }}</td>
                                    <td>{{ $vendor->contact_number }}</td>
                                  
                                    <td>{{ date('D, M d, Y', strtotime($vendor->created_at)) }}</td>
                                    <td class="noselect text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-success btn-sm" data-toggle="tooltip" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-info btn-sm" data-toggle="tooltip" title="Edit">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this vendor?');">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="noselect text-center" colspan="8">No vendors found.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    @if (count($vendors))
                        {!! $vendors->withQueryString()->links('pagination::bootstrap-5') !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('inline-js')
    <script type="text/javascript" src="{{ asset('backend/js/flatpickr.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#start_date", { dateFormat: "Y-m-d" });
            flatpickr("#end_date", { dateFormat: "Y-m-d" });
        });
    </script>
@endsection
