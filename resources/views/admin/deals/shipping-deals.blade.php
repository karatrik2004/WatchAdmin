@extends('layouts.admin')
@section('title') Shipping Deals @endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4>Shipping Deals</h4>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style">
                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.shipping-deals') }}" class="bg-light p-4 rounded shadow-sm mb-4">
                    <div class="card p-3 shadow-sm">
                        <div class="row gx-3 gy-2 align-items-end">

                            <!-- Search Box -->
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" name="search_text" id="search_text" class="form-control" placeholder="Search by Model / Serial No." value="{{ request('search_text') }}">
                                    <label for="search_text">Search by Model / Serial No.</label>
                                </div>
                            </div>

                            <!-- Brand -->
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <select name="brand_id" id="brand_id" class="form-select">
                                        <option value="">Select Brand</option>
                                        @foreach($brands as $id => $name)
                                            <option value="{{ $id }}" {{ request('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="brand_id">Brand</label>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="date" name="date_from" id="date_from" class="form-control" placeholder="Select Start Date" value="{{ request('date_from') }}">
                                    <label for="date_from">Start Date</label>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="date" name="date_to" id="date_to" class="form-control" placeholder="Select End Date" value="{{ request('date_to') }}">
                                    <label for="date_to">End Date</label>
                                </div>
                            </div>

                            <!-- Filter & Reset Buttons -->
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i data-feather="filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.shipping-deals') }}" class="btn btn-dark btn-sm">
                                    <i data-feather="refresh-ccw"></i> Reset
                                </a>
                            </div>

                        </div>
                    </div>
                </form>

                @if(count($deals))
                <form id="shipping-deals-form" method="POST" action="{{ route('admin.shipping-deals.bulk-download') }}">
                    @csrf
                    <div class="mb-3 d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label" for="select-all"><strong>Select All</strong></label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-download">
                            <i class="fa fa-download"></i> Download Shipping Invoices
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width:40px;">
                                        <input type="checkbox" id="select-all-top" class="form-check-input select-all-toggle">
                                    </th>
                                    <th>#</th>
                                    <th>Watch ID</th>
                                    <th>Invoice No.</th>
                                    <th>Model No.</th>
                                    <th>Serial No.</th>
                                    <th>Brand</th>
                                    <th>Year</th>
                                    <th>Buyer</th>
                                    <th>Sale Price</th>
                                    <th>Deal Status</th>
                                    <th>Sold Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = ($deals->currentPage() - 1) * $deals->perPage(); @endphp
                                @foreach($deals as $deal)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="deal_ids[]" value="{{ $deal->id }}" class="form-check-input deal-checkbox">
                                    </td>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $deal->watch_id ?? $deal->id }}</td>
                                    <td>{{ optional($deal->dealBuyerDetail)->invoice_number ?? (100000 + $deal->id) }}</td>
                                    <td>{{ $deal->model_number }}</td>
                                    <td>{{ $deal->serial_number }}</td>
                                    <td>{{ $deal->watchBrandDetail->name ?? '' }}</td>
                                    <td>{{ $deal->year }}</td>
                                    <td>{{ optional($deal->dealBuyerDetail)->buyer_name ?? '-' }}</td>
                                    <td>
                                        @if($deal->dealBuyerDetail)
                                            {{ strtoupper($deal->dealBuyerDetail->buyer_currency ?? 'USD') }}
                                            ${{ number_format($deal->dealBuyerDetail->buyer_sale_price ?? 0, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ config('constants.SEARCH_DEAL_STATUS_STYLE')[$deal->deal_status] ?? 'bg-secondary' }}">
                                            {{ config('constants.DEAL_STATUS')[$deal->deal_status] ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>{{ date('d M Y', strtotime($deal->updated_at)) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.shipping.invoice.template.download', $deal->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" data-toggle="tooltip" title="Download Shipping Invoice">
                                            <i class="fa fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                <div class="d-flex justify-content-center mt-3">
                    {{ $deals->links() }}
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-muted">No sold deals found.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('inline-js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('select-all');
    var selectAllTop = document.getElementById('select-all-top');
    var checkboxes = document.querySelectorAll('.deal-checkbox');

    function toggleAll(checked) {
        checkboxes.forEach(function(cb) { cb.checked = checked; });
        if (selectAll) selectAll.checked = checked;
        if (selectAllTop) selectAllTop.checked = checked;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() { toggleAll(this.checked); });
    }
    if (selectAllTop) {
        selectAllTop.addEventListener('change', function() { toggleAll(this.checked); });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var allChecked = document.querySelectorAll('.deal-checkbox:checked').length === checkboxes.length;
            if (selectAll) selectAll.checked = allChecked;
            if (selectAllTop) selectAllTop.checked = allChecked;
        });
    });

    document.getElementById('shipping-deals-form').addEventListener('submit', function(e) {
        var checked = document.querySelectorAll('.deal-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Please select at least one deal.');
        }
    });
});
</script>
@endsection
