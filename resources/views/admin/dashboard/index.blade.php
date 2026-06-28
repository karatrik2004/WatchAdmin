@extends('layouts.admin')
@section('title') Admin Dashboard @endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-4">
            <!-- Total Deals -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('admin.deals.index') }}" class="text-decoration-none">
                    <div class="card shadow-lg border-0 bg-total text-white h-100 hover-lift">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-briefcase-fill fs-2 mb-3 d-block"></i>
                            <h6 class="card-title fw-semibold text-uppercase">Total Deals</h6>
                            <h2 class="fw-bold mb-0">{{ $totalDeals }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Status-wise Cards -->
            @foreach ($statusLabels as $key => $label)
                @if ($key !== '')
                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('admin.deals.index', ['deal_status' => $key]) }}" class="text-decoration-none">
                            <div class="card shadow-lg border-0 {{ $statusStyles[$key] ?? 'bg-secondary' }} text-white h-100 hover-lift">
                                <div class="card-body text-center py-4">
                                    <h6 class="card-title fw-semibold text-uppercase">{{ $label }}</h6>
                                    <h2 class="fw-bold mb-0">{{ $statusCounts[$key] ?? 0 }}</h2>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Chart Section -->
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <h5 class="card-title text-center text-uppercase fw-bold mb-4">Deal Status Distribution</h5>
                        <canvas id="dealStatusChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('inline-css')
    <style>
        .hover-lift:hover {
            transform: translateY(-10px);
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
        }

        /* Enhanced Background Gradients */
        .bg-total {
            background: linear-gradient(135deg, #4e79b0, #7a4dff); /* Electric Blue to Purple */
        }

        .bg-pending {
            background: linear-gradient(135deg, #FF9F00, #FF5722); /* Warm Orange to Deep Red */
        }

        .bg-processing {
            background: linear-gradient(135deg, #4caf50, #388e3c); /* Lush Green to Deep Teal */
        }

        .bg-sold {
            background: linear-gradient(135deg, #66bb6a, #81c784); /* Soft Green to Mint Green */
        }

        .bg-canceled {
            background: linear-gradient(135deg, #f44336, #e57373); /* Bright Red to Soft Pink */
        }

        /* Common Styling */
        .card-title {
            font-size: 1rem;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .card i {
            opacity: 0.9;
        }

        .card h1 {
            font-size: 2.4rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .card-body {
            padding: 1.5rem 1rem;
        }
    </style>
@endsection
@section('inline-js')
    <script type="text/javascript" src="{{ asset('backend/js/chart.js') }}"></script>
    <script>


        // Always use full statusColors (even if data is empty)
        const ctx = document.getElementById('dealStatusChart').getContext('2d');

        // Create gradient colors manually
        const gradientPending = ctx.createLinearGradient(0, 0, 0, 300);
        gradientPending.addColorStop(0, '#FF9F00');
        gradientPending.addColorStop(1, '#FF5722');

        const gradientProcessing = ctx.createLinearGradient(0, 0, 0, 300);
        gradientProcessing.addColorStop(0, '#4CAF50');
        gradientProcessing.addColorStop(1, '#388E3C');

        const gradientSold = ctx.createLinearGradient(0, 0, 0, 300);
        gradientSold.addColorStop(0, '#66BB6A');
        gradientSold.addColorStop(1, '#81C784');

        const gradientCanceled = ctx.createLinearGradient(0, 0, 0, 300);
        gradientCanceled.addColorStop(0, '#F44336');
        gradientCanceled.addColorStop(1, '#E57373');

        const gradientOthers = ctx.createLinearGradient(0, 0, 0, 300);
        gradientOthers.addColorStop(0, '#7A4DFF');
        gradientOthers.addColorStop(1, '#9575CD');

        // Use gradient colors
        const statusColors = [
            gradientPending,
            gradientProcessing,
            gradientSold,
            gradientCanceled,
            gradientOthers,
        ];

        // Chart data
        const statusLabels = @json(array_values($statusLabels));
        console.log('statusLabels',statusLabels);


        const statusData = @json(array_values($statusCounts));
        console.log('statusData',statusData);
        const total = statusData.reduce((a, b) => a + b, 0);

        // Chart setup
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10,
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#333',
                            font: {
                                size: 14,
                                weight: '500'
                            },
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 6,
                        callbacks: {
                            label: function (context) {
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return ` ${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });




    </script>
@endsection


