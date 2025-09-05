@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Platform Settings</li>
                </ol>
            </div>
            <h4 class="page-title">Platform Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <!-- Payment Settings -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Payment Settings</h5>
                
                <form method="POST" action="{{ route('dashboard.settings.payment-settings.update') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transfer Frequency (Days)</label>
                                <input type="number" name="transfer_frequency_days" class="form-control" 
                                       value="{{ $settings['transfer_frequency_days'] }}" 
                                       required min="1" max="365">
                                <small class="form-text text-muted">How often farm owners become eligible for payment</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Transfer Amount (AED)</label>
                                <input type="number" name="minimum_transfer_amount" class="form-control" 
                                       value="{{ $settings['minimum_transfer_amount'] }}" 
                                       required min="1" max="10000" step="0.01">
                                <small class="form-text text-muted">Minimum balance required for payment</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>Update Payment Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Commission Settings -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Commission Settings</h5>
                
                <form method="POST" action="{{ route('dashboard.settings.commission-settings.update') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Default Rate (%)</label>
                                <input type="number" name="default_commission_rate" class="form-control" 
                                       value="{{ $settings['default_commission_rate'] }}" 
                                       required min="0" max="100" step="0.01">
                                <small class="form-text text-muted">Rate for new farm owners</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Minimum Rate (%)</label>
                                <input type="number" name="minimum_commission_rate" class="form-control" 
                                       value="{{ $settings['minimum_commission_rate'] }}" 
                                       required min="0" max="100" step="0.01">
                                <small class="form-text text-muted">Lowest allowed rate</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Maximum Rate (%)</label>
                                <input type="number" name="maximum_commission_rate" class="form-control" 
                                       value="{{ $settings['maximum_commission_rate'] }}" 
                                       required min="0" max="100" step="0.01">
                                <small class="form-text text-muted">Highest allowed rate</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Default rate must be between minimum and maximum rates. These settings affect new farm owners and rate updates.
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-percent me-1"></i>Update Commission Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Current Settings & Statistics -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Statistics</h5>
                
                <div class="text-center mb-4">
                    <h3 class="text-primary">{{ $paymentStats['total_payments'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Total Payments Processed</p>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-success">{{ $paymentStats['this_month_payments'] ?? 0 }}</h5>
                        <p class="text-muted mb-0 small">This Month</p>
                    </div>
                    <div class="col-6">
                        <h5 class="text-info">{{ number_format($paymentStats['this_month_amount'] ?? 0, 2) }}</h5>
                        <p class="text-muted mb-0 small">Amount (AED)</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Paid:</span>
                        <span class="fw-bold">AED {{ number_format($paymentStats['total_amount'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Average Payment:</span>
                        <span class="fw-bold">AED {{ number_format($paymentStats['average_payment'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Current Settings</h6>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Transfer Frequency:</span>
                        <span class="badge bg-primary">{{ $settings['transfer_frequency_days'] }} days</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Minimum Amount:</span>
                        <span class="badge bg-success">AED {{ number_format($settings['minimum_transfer_amount'], 2) }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Commission Range:</span>
                        <span class="badge bg-info">{{ $settings['minimum_commission_rate'] }}% - {{ $settings['maximum_commission_rate'] }}%</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Default Rate:</span>
                        <span class="badge bg-warning">{{ $settings['default_commission_rate'] }}%</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('dashboard.wallet.pending-payments') }}" class="btn btn-warning btn-sm">
                            <i class="mdi mdi-bank-transfer-out me-1"></i>Pending Payments
                        </a>
                        <a href="{{ route('dashboard.wallet.export.payments') }}" class="btn btn-success btn-sm">
                            <i class="mdi mdi-download me-1"></i>Export Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection