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
            <h4 class="page-title">{{ $wallet->user->name }}'s Wallet</h4>
        </div>
    </div>
</div>

<!-- Wallet Overview -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                            <span class="text-primary fw-bold" style="font-size: 24px;">
                                {{ strtoupper(substr($wallet->user->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $wallet->user->name }}</h5>
                            <p class="text-muted mb-1">{{ $wallet->user->email }}</p>
                            <span class="badge bg-{{ $wallet->is_active ? 'success' : 'danger' }}">
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
                            <i class="mdi mdi-plus-minus-variant me-1"></i> Adjustment
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#commissionModal">
                            <i class="mdi mdi-percent me-1"></i> Commission
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
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Bank Account</h5>
                @if($wallet->user->farmOwnerBankAccount)
                <div class="mb-3">
                    <label class="text-muted small">Account Type</label>
                    <p class="mb-2">{{ $wallet->user->farmOwnerBankAccount->getAccountTypeLabel() }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Account Details</label>
                    <div class="bg-light p-2 rounded small">
                        @foreach($wallet->user->farmOwnerBankAccount->formatted_account_details as $key => $value)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ $key }}:</span>
                            <span>{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <span class="badge bg-success">
                    <i class="mdi mdi-check-circle me-1"></i> Verified
                </span>
                @else
                <div class="text-center py-3">
                    <i class="mdi mdi-bank-off text-muted" style="font-size: 32px;"></i>
                    <p class="text-muted mt-2 mb-0">No bank account setup</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Recent Transactions</h5>
                
                @if($wallet->transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                    <span class="fw-bold">{{ $transaction->created_at->format('M d, Y') }}</span>
                                    <br><small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
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
                                    <span class="badge bg-{{ $typeColor }}">
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
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
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
        </div>
    </div>
</div>

<!-- Modals -->
@include('admin.wallet.modals.process-payment')
@include('admin.wallet.modals.adjustment')  
@include('admin.wallet.modals.commission')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Process Payment Modal Handler
    const processPaymentModal = document.getElementById('processPaymentModal');
    if (processPaymentModal) {
        const form = processPaymentModal.querySelector('form');
        const amountInput = processPaymentModal.querySelector('#paymentAmount');
        
        // Set max amount when modal opens
        processPaymentModal.addEventListener('show.bs.modal', function() {
            amountInput.max = {{ $wallet->balance }};
            amountInput.value = {{ $wallet->balance }};
        });
        
        // Form validation
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const maxAmount = {{ $wallet->balance }};
            
            if (amount > maxAmount) {
                e.preventDefault();
                alert('Amount cannot exceed wallet balance');
                return false;
            }
            
            if (amount < 1) {
                e.preventDefault();
                alert('Amount must be at least AED 1.00');
                return false;
            }
            
            return confirm('Are you sure you want to process this payment?');
        });
    }

    // Commission Modal Handler
    const commissionModal = document.getElementById('commissionModal');
    if (commissionModal) {
        const commissionInput = commissionModal.querySelector('#commissionRate');
        const quickButtons = commissionModal.querySelectorAll('.quick-rate-btn');
        
        // Quick rate buttons
        quickButtons.forEach(button => {
            button.addEventListener('click', function() {
                const rate = this.dataset.rate;
                commissionInput.value = rate;
            });
        });
        
        // Commission form validation
        const commissionForm = commissionModal.querySelector('form');
        commissionForm.addEventListener('submit', function(e) {
            const rate = parseFloat(commissionInput.value);
            const minRate = {{ \App\Models\PlatformSetting::getMinimumCommissionRate() }};
            const maxRate = {{ \App\Models\PlatformSetting::getMaximumCommissionRate() }};
            
            if (rate < minRate || rate > maxRate) {
                e.preventDefault();
                alert(`Commission rate must be between ${minRate}% and ${maxRate}%`);
                return false;
            }
            
            return confirm('Are you sure you want to update the commission rate?');
        });
    }

    // Adjustment Modal Handler  
    const adjustmentModal = document.getElementById('adjustmentModal');
    if (adjustmentModal) {
        const adjustmentForm = adjustmentModal.querySelector('form');
        const amountInput = adjustmentModal.querySelector('#adjustmentAmount');
        
        adjustmentForm.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            
            if (amount === 0) {
                e.preventDefault();
                alert('Amount cannot be zero');
                return false;
            }
            
            const action = amount > 0 ? 'add funds to' : 'deduct funds from';
            return confirm(`Are you sure you want to ${action} this wallet?`);
        });
    }
});
</script>
@endpush