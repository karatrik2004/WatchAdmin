@extends('layouts.admin')
@section('title') Shipping Invoices @endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Shipping Invoices</h4>
                <a href="{{ route('admin.shipping-invoices.create') }}" class="btn btn-primary btn-sm">Create</a>
            </div>
            <div class="card-body table-border-style">

                <form method="GET" class="mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by invoice number…" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('admin.shipping-invoices.index') }}" class="btn btn-dark btn-sm">Reset</a>
                        </div>
                    </div>
                </form>

                @if($invoices->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No.</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = ($invoices->currentPage() - 1) * $invoices->perPage(); @endphp
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td>{{ $invoice->items_count }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.shipping-invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                        <i data-feather="eye"></i>
                                    </a>
                                    <a href="{{ route('admin.shipping-invoices.download', $invoice->id) }}" class="btn btn-sm btn-outline-primary" title="Download PDF">
                                        <i data-feather="download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.shipping-invoices.destroy', $invoice->id) }}" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $invoices->links() }}
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <p>No shipping invoices found. <a href="{{ route('admin.shipping-invoices.create') }}">Create</a></p>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
