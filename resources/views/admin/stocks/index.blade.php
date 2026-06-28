@extends('layouts.admin')
@section('title')
    Manage Stocks
@endsection

@section('inline-css')
    <style>
        .brand-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            height: 100%;
        }

        .brand-header {
            background: linear-gradient(to right, #004c7f, #ffd700);
            color: #fff;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .stock-table th,
        .stock-table td {
            padding: 0.6rem 0.75rem;
            font-size: 0.9rem;
        }

        .stock-table thead th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-weight: 600;
        }

        .value {
            color: #059669;
            font-weight: 600;
        }

        .bought {
            color: #2563eb;
            font-weight: 600;
        }

        .sold {
            color: #dc2626;
            font-weight: 600;
        }

        .no-data {
            background: #f9fafb;
            padding: 1.5rem;
            text-align: center;
            border-radius: 0.5rem;
            border: 1px dashed #cbd5e1;
            font-weight: 500;
            color: #64748b;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <h4 class="mb-4 fw-semibold">Stock Summary by Brand</h4>

        <div class="row">
            @forelse($brand_stocks as $b_stock)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card brand-card">
                        <div class="brand-header">
                            {{ $b_stock->watchBrandDetail->name ?? "N/A" }}
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-borderless stock-table mb-0">
                                <thead>
                                <tr>
                                    <th>Stock on Hand</th>
                                    <th>Bought</th>
                                    <th>Sold</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="value">{{ number_format($b_stock->value_on_hand, 2) }}(A$)</td>
                                    <td class="bought">{{ $b_stock->bought_stock }} units</td>
                                    <td class="sold">{{ $b_stock->sold_stock }} units</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="no-data">No stock data available.</div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="container-fluid">
        <h4 class="mb-4 fw-semibold">Stock Summary by Location</h4>

        <div class="row">
            @forelse($state_stocks as $s_stock)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card brand-card">
                        <div class="brand-header">
                            {{ $s_stock->state->state_name ?? "N/A" }}
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-borderless stock-table mb-0">
                                <thead>
                                <tr>
                                    <th>Stock on Hand</th>
                                    <th>Bought</th>
                                    <th>Sold</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="value">{{ number_format($s_stock->value_on_hand, 2) }}(A$)</td>
                                    <td class="bought">{{ $s_stock->bought_stock }} units</td>
                                    <td class="sold">{{ $s_stock->sold_stock }} units</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="no-data">No stock data available.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('inline-js')
@endsection
