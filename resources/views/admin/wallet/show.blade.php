@extends('admin.layout')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.index') }}">Wallet Management</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.wallet.wallets') }}">Wallets</a></li>
                    <li class="breadcrumb-item active">{{ $wallet->user->name }}</li>
                </ol>
            </div>
            <h4 class="page-title">Wallet Details - {{ $wallet->user->name }}</h4>
        </div>
    </div>
</div>

<!-- Wallet Overview -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-lg">
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary" style="font-size: 24px;">
                                    {{ strtoupper(substr($wallet->user->name, 0, 2)) }}
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $wallet->user->name }}</h5>
                            <p class="text-muted mb-1">{{ $wallet->user->email }}</p>
                            <span class="badge badge-soft-{{ $wallet->is_active ? 'success' : 'danger' }}">
                                {{ $wallet->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if($wallet->balance >= 50 && $wallet->user->farmOwnerBankAccount)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
                            <i class="mdi mdi-bank-transfer-out me-1"></i> Process Payment
                        </button>
                        @endif
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                            <i class="mdi mdi-plus-minus-variant me-1"></i> Add Adjustment
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#commissionModal">
                            <i class="mdi mdi-percent me-1"></i> Update Commission
                        </button>
                    </div>
                </div>

                <!-- Balance Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-0 bg-primary-subtle">
                            <div class="card-body text-center p-3">
                                <h4 class="text-primary mb-1">AED {{ number_format($wallet->balance, 2) }}</h4>
                                <p class="text-muted mb-0 small">Current Balance</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-success-subtle">
                            <div class="card-body text-center p-3">
                                <h4 class="text-success mb-1">AED {{ number_format($wallet->total_earned, 2) }}</h4>
                                <p class="text-muted mb-0 small">Total Earned</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-info-subtle">
                            <div class="card-body text-center p-3">
                                <h4 class="text-info mb-1">AED {{ number_format($wallet->total_paid_out, 2) }}</h4>
                                <p class="text-muted mb-0 small">Total Paid Out</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-warning-subtle">
                            <div class="card-body text-center p-3">
                                <h4 class="text-warning mb-1">{{ $wallet->platform_commission_rate }}%</h4>
                                <p class="text-muted mb-0 small">Commission Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Bank Account Information</h5>
                @if($wallet->user->farmOwnerBankAccount)
                <div class="mb-3">
                    <label class="text-muted small">Account Type</label>
                    <p class="mb-2">{{ $wallet->user->farmOwnerBankAccount->getAccountTypeLabel() }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Account Details</label>
                    <div class="bg-light p-2 rounded">
                        @foreach($wallet->user->farmOwnerBankAccount->formatted_account_details as $key => $value)
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">{{ $key }}:</span>
                            <span class="small">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-0">
                    <span class="badge badge-soft-success">
                        <i class="mdi mdi-check-circle me-1"></i> Verified
                    </span>
                </div>
                @else
                <div class="text-center py-3">
                    <i class="mdi mdi-bank-off text-muted" style="font-size: 32px;"></i>
                    <p class="text-muted mt-2 mb-0">No bank account setup</p>
                    <p class="small text-muted">Farm owner needs to add bank account details</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">This Month Statistics</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">AED {{ number_format($statistics['this_month']['earnings'] ?? 0, 2) }}</h4>
                            <p class="text-muted mb-0 small">Earnings</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-warning">AED {{ number_format($statistics['this_month']['payments'] ?? 0, 2) }}</h4>
                            <p class="text-muted mb-0 small">Payments</p>
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                <div class="text-center">
                    <h5 class="text-primary">{{ $statistics['this_month']['transactions_count'] ?? 0 }}</h5>
                    <p class="text-muted mb-0 small">Total Transactions</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Status</h5>
                @if($wallet->user->farmOwnerBankAccount)
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Eligible for Payment:</span>
                        <span class="badge badge-soft-{{ $statistics['payments']['is_eligible_for_payment'] ? 'success' : 'warning' }}">
                            {{ $statistics['payments']['is_eligible_for_payment'] ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Ready for Payment:</span>
                        <span class="badge badge-soft-{{ $statistics['payments']['is_ready_for_payment'] ? 'success' : 'info' }}">
                            {{ $statistics['payments']['is_ready_for_payment'] ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Days Since Last Payment:</span>
                        <span class="fw-bold">{{ $statistics['payments']['days_since_last_payment'] ?? 0 }}</span>
                    </div>
                </div>
                @if($statistics['payments']['last_payment'])
                <div class="mb-0">
                    <span class="text-muted small">Last Payment:</span>
                    <p class="mb-0">{{ \Carbon\Carbon::parse($statistics['payments']['last_payment'])->format('M d, Y') }}</p>
                </div>
                @endif
                @else
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert-triangle me-2"></i>
                    Bank account required for payments
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions and Manual Payments Tabs -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-bordered" id="walletTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="transactions-tab" data-bs-toggle="tab" 
                                data-bs-target="#transactions" type="button" role="tab">
                            Recent Transactions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" 
                                data-bs-target="#payments" type="button" role="tab">
                            Manual Payments
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="earnings-tab" data-bs-toggle="tab" 
                                data-bs-target="#earnings" type="button" role="tab">
                            Booking Earnings
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="walletTabsContent">
                    <!-- Recent Transactions Tab -->
                    <div class="tab-pane fade show active" id="transactions" role="tabpanel">
                        @if($wallet->transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wallet->transactions->take(10) as $transaction)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'earning' => 'success',
                                                    'manual_payment' => 'warning',
                                                    'commission' => 'info',
                                                    'refund' => 'danger',
                                                    'adjustment' => 'secondary'
                                                ];
                                                $typeColor = $typeColors[$transaction->type] ?? 'light';
                                            @endphp
                                            <span class="badge badge-soft-{{ $typeColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $transaction->description }}</span>
                                            @if($transaction->booking)
                                            <br><small class="text-primary">{{ $transaction->booking->booking_reference }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('dashboard.wallet.transactions', ['search' => $wallet->user->email]) }}" 
                               class="btn btn-outline-primary">
                                View All Transactions
                            </a>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-format-list-bulleted text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2">No transactions yet</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Manual Payments Tab -->
                    <div class="tab-pane fade" id="payments" role="tabpanel">
                        @if($wallet->manualPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Processed By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wallet->manualPayments as $payment)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $payment->payment_date->format('M d, Y') }}</span>
                                            <br><small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">AED {{ number_format($payment->amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-primary">{{ $payment->getPaymentMethodLabel() }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $payment->processedBy->name }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $payment->notes ?: 'No notes' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-bank-transfer-out text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2">No manual payments processed yet</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Booking Earnings Tab -->
                    <div class="tab-pane fade" id="earnings" role="tabpanel">
                        @if($bookingsWithEarnings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking</th>
                                        <th>Farm</th>
                                        <th>Booking Date</th>
                                        <th>Total Amount</th>
                                        <th>Commission</th>
                                        <th>Your Earning</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingsWithEarnings as $booking)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $booking->booking_reference }}</span>
                                            <br><small class="text-muted">{{ $booking->customer_name }}</small>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $booking->farm->name_en ?: $booking->farm->name_ar }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</span>
                                            @if($booking->start_date !== $booking->end_date)
                                            <br><small class="text-muted">to {{ \Carbon\Carbon::parse($booking->end_date)->format('M d') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">AED {{ number_format($booking->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-info">{{ $booking->platform_commission_rate }}%</span>
                                            <br><small class="text-muted">AED {{ number_format($booking->platform_commission_amount, 2) }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">AED {{ number_format($booking->farm_owner_earning, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'confirmed' => 'success',
                                                    'completed' => 'primary',
                                                    'cancelled' => 'danger'
                                                ];
                                                $statusColor = $statusColors[$booking->booking_status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-soft-{{ $statusColor }}">
                                                {{ ucfirst($booking->booking_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for bookings -->
                        @if($bookingsWithEarnings->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $bookingsWithEarnings->links() }}
                        </div>
                        @endif
                        @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-cash-multiple text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2">No booking earnings yet</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Process Payment Modal -->
<div class="modal fade" id="processPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.wallet.process-payment', $wallet->user_id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Process Manual Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Processing payment for <strong>{{ $wallet->user->name }}</strong>
                        <br>Available balance: <strong>AED {{ number_format($wallet->balance, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Amount (AED) *</label>
                        <input type="number" class="form-control" id="paymentAmount" name="amount" 
                               step="0.01" min="1" max="{{ $wallet->balance }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="paymentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="paymentNotes" name="notes" rows="3" 
                                  placeholder="Optional notes for this payment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="mdi mdi-bank-transfer-out me-1"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.wallet.wallets.adjustment', $wallet->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Wallet Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-triangle me-2"></i>
                        Making adjustment for <strong>{{ $wallet->user->name }}</strong>
                        <br>Current balance: <strong>AED {{ number_format($wallet->balance, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentType" class="form-label">Type *</label>
                        <select class="form-select" id="adjustmentType" name="type" required>
                            <option value="adjustment">Adjustment</option>
                            <option value="bonus">Bonus</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentAmount" class="form-label">Amount (AED) *</label>
                        <input type="number" class="form-control" id="adjustmentAmount" name="amount" 
                               step="0.01" required placeholder="Use negative values to deduct">
                        <div class="form-text">Use positive values to add funds, negative values to deduct funds</div>
                    </div>
                    <div class="mb-3">
                        <label for="adjustmentDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="adjustmentDescription" name="description" rows="3" 
                                  required placeholder="Reason for this adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-plus-minus-variant me-1"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Commission Rate Modal -->
<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.wallet.wallets.commission-rate', $wallet->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Commission Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        Updating commission rate for <strong>{{ $wallet->user->name }}</strong>
                        <br>Current rate: <strong>{{ $wallet->platform_commission_rate }}%</strong>
                    </div>
                    
                    @php
                        $minRate = \App\Models\PlatformSetting::getMinimumCommissionRate();
                        $maxRate = \App\Models\PlatformSetting::getMaximumCommissionRate();
                        $defaultRate = \App\Models\PlatformSetting::getDefaultCommissionRate();
                    @endphp
                    
                    <div class="mb-3">
                        <label for="commissionRate" class="form-label">Commission Rate (%) *</label>
                        <input type="number" class="form-control" id="commissionRate" name="commission_rate" 
                               step="0.01" min="{{ $minRate }}" max="{{ $maxRate }}" 
                               value="{{ $wallet->platform_commission_rate }}" required>
                        <div class="form-text">
                            Rate must be between {{ $minRate }}% and {{ $maxRate }}%
                            @if($defaultRate != $wallet->platform_commission_rate)
                                <br><small class="text-muted">Platform default: {{ $defaultRate }}%</small>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Rate Buttons -->
                    @if($minRate != $maxRate)
                    <div class="mb-3">
                        <label class="form-label">Quick Set:</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($minRate > 0)
                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                    onclick="document.getElementById('commissionRate').value = {{ $minRate }}">
                                {{ $minRate }}% (Min)
                            </button>
                            @endif
                            
                            @if($defaultRate != $minRate && $defaultRate != $maxRate)
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="document.getElementById('commissionRate').value = {{ $defaultRate }}">
                                {{ $defaultRate }}% (Default)
                            </button>
                            @endif
                            
                            @if($maxRate < 100)
                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                    onclick="document.getElementById('commissionRate').value = {{ $maxRate }}">
                                {{ $maxRate }}% (Max)
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="commissionReason" class="form-label">Reason for Change</label>
                        <textarea class="form-control" id="commissionReason" name="reason" rows="3" 
                                  placeholder="Optional reason for this change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-percent me-1"></i> Update Commission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection