@extends('admin.layout')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.index') }}">Wallet Management</a></li>
                    <li class="breadcrumb-item active">Payment Settings</li>
                </ol>
            </div>
            <h4 class="page-title">Platform Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <!-- Payment Settings Form -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Payment Configuration</h5>
                
                <form method="POST" action="{{ route('dashboard.wallet.payment-settings.update') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transfer Frequency (Days) *</label>
                                <input type="number" name="transfer_frequency_days" class="form-control" 
                                       value="{{ $settings['transfer_frequency_days'] }}" 
                                       required min="1" max="365">
                                <small class="form-text text-muted">
                                    How often farm owners become eligible for payment (in days)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Transfer Amount (AED) *</label>
                                <input type="number" name="minimum_transfer_amount" class="form-control" 
                                       value="{{ $settings['minimum_transfer_amount'] }}" 
                                       required min="1" max="10000" step="0.01">
                                <small class="form-text text-muted">
                                    Minimum wallet balance required for payment eligibility
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline me-2"></i>
                            <strong>How it works:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Farm owners become eligible when their balance reaches the minimum amount</li>
                                <li>They become "ready for payment" after the frequency period has passed since their last payment</li>
                                <li>You can process payments manually at any time through the dashboard</li>
                                <li>All payments are recorded with full audit trails</li>
                            </ul>
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

        <!-- Commission Settings Form -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Commission Rate Configuration</h5>
                
                <form method="POST" action="{{ route('dashboard.wallet.wallet.commission-settings.update') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Default Commission Rate (%) *</label>
                                <input type="number" name="default_commission_rate" class="form-control" 
                                       value="{{ \App\Models\PlatformSetting::getDefaultCommissionRate() }}" 
                                       required min="0" max="100" step="0.01">
                                <small class="form-text text-muted">
                                    Commission rate assigned to new farm owners
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Minimum Rate (%) *</label>
                                <input type="number" name="minimum_commission_rate" class="form-control" 
                                       value="{{ \App\Models\PlatformSetting::getMinimumCommissionRate() }}" 
                                       required min="0" max="100" step="0.01">
                                <small class="form-text text-muted">
                                    Lowest allowed commission rate
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Maximum Rate (%) *</label>
                                <input type="number" name="maximum_commission_rate" class="form-control" 
                                       value="{{ \App\Models\PlatformSetting::getMaximumCommissionRate() }}" 
                                       required min="0" max="100" step="0.01">
                                <small class="form-text text-muted">
                                    Highest allowed commission rate
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-triangle me-2"></i>
                            <strong>Important:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Default rate must be between minimum and maximum rates</li>
                                <li>Individual farm owner rates can be adjusted within these limits</li>
                                <li>Changes affect new farm owners and manual rate updates</li>
                            </ul>
                        </div>
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

    <!-- Statistics -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Statistics</h5>
                
                <div class="text-center mb-4">
                    <h3 class="text-primary">{{ $paymentStats['total_payments'] }}</h3>
                    <p class="text-muted mb-0">Total Payments Processed</p>
                </div>

                <div class="row text-center">
                    <div class="col-6 border-end">
                        <h5 class="text-success">{{ $paymentStats['this_month_payments'] }}</h5>
                        <p class="text-muted mb-0 font-12">This Month</p>
                    </div>
                    <div class="col-6">
                        <h5 class="text-info">AED {{ number_format($paymentStats['this_month_amount'], 2) }}</h5>
                        <p class="text-muted mb-0 font-12">This Month Amount</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Amount Paid</span>
                        <span class="fw-bold">AED {{ number_format($paymentStats['total_amount'], 2) }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Average Payment</span>
                        <span class="fw-bold">AED {{ number_format($paymentStats['average_payment'], 2) }}</span>
                    </div>
                </div>

                <div class="row text-center mt-4">
                    <div class="col-6">
                        <h6 class="text-primary">{{ $paymentStats['iban_payments'] }}</h6>
                        <p class="text-muted mb-0 font-12">IBAN Transfers</p>
                        <small class="text-success">AED {{ number_format($paymentStats['iban_amount'], 2) }}</small>
                    </div>
                    <div class="col-6">
                        <h6 class="text-warning">{{ $paymentStats['cliq_payments'] }}</h6>
                        <p class="text-muted mb-0 font-12">CLIQ Transfers</p>
                        <small class="text-success">AED {{ number_format($paymentStats['cliq_amount'], 2) }}</small>
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
                        <span class="badge bg-info">{{ \App\Models\PlatformSetting::getMinimumCommissionRate() }}% - {{ \App\Models\PlatformSetting::getMaximumCommissionRate() }}%</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Default Rate:</span>
                        <span class="badge bg-warning">{{ \App\Models\PlatformSetting::getDefaultCommissionRate() }}%</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard.wallet.pending-payments') }}" class="btn btn-warning btn-sm">
                            <i class="mdi mdi-bank-transfer-out me-1"></i>Pending Payments
                        </a>
                        <a href="{{ route('dashboard.wallet.export', ['type' => 'payments']) }}" class="btn btn-success btn-sm">
                            <i class="mdi mdi-download me-1"></i>Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection