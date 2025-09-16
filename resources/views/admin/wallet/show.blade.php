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

<!-- Enhanced Wallet Overview -->
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
                            <div>
                                <span class="badge bg-{{ $wallet->is_active ? 'success' : 'danger' }}">
                                    {{ $wallet->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($wallet->balance >= 50 && $wallet->user->farmOwnerBankAccount)
                                <span class="badge bg-success ms-1">
                                    <i class="mdi mdi-check-circle me-1"></i>Payment Ready
                                </span>
                                @elseif($wallet->balance < 50)
                                <span class="badge bg-warning ms-1">
                                    <i class="mdi mdi-alert me-1"></i>Below Minimum
                                </span>
                                @else
                                <span class="badge bg-danger ms-1">
                                    <i class="mdi mdi-bank-off me-1"></i>No Bank Account
                                </span>
                                @endif
                            </div>
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

                <!-- Enhanced Balance Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-0 bg-success-subtle">
                            <div class="card-body text-center p-3">
                                <i class="mdi mdi-cash-check text-success" style="font-size: 20px;"></i>
                                <h4 class="text-success mb-1 mt-2">AED {{ number_format($wallet->balance, 2) }}</h4>
                                <p class="text-muted mb-0 small">Confirmed Balance</p>
                                <small class="text-success">
                                    <i class="mdi mdi-check-circle me-1"></i>Available for Payment
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-warning-subtle">
                            <div class="card-body text-center p-3">
                                <i class="mdi mdi-clock-outline text-warning" style="font-size: 20px;"></i>
                                <h4 class="text-warning mb-1 mt-2">AED {{ number_format($wallet->pending_balance, 2) }}</h4>
                                <p class="text-muted mb-0 small">Pending Balance</p>
                                <small class="text-warning">
                                    <i class="mdi mdi-clock-outline me-1"></i>Awaiting Completion
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-info-subtle">
                            <div class="card-body text-center p-3">
                                <i class="mdi mdi-calculator text-info" style="font-size: 20px;"></i>
                                <h4 class="text-info mb-1 mt-2">AED {{ number_format($wallet->getTotalAvailableBalance(), 2) }}</h4>
                                <p class="text-muted mb-0 small">Total Available</p>
                                <small class="text-info">
                                    <i class="mdi mdi-calculator me-1"></i>Confirmed + Pending
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-secondary-subtle">
                            <div class="card-body text-center p-3">
                                <i class="mdi mdi-percent text-secondary" style="font-size: 20px;"></i>
                                <h4 class="text-secondary mb-1 mt-2">{{ $wallet->platform_commission_rate }}%</h4>
                                <p class="text-muted mb-0 small">Commission Rate</p>
                                <small class="text-secondary">
                                    <i class="mdi mdi-percent me-1"></i>Platform Fee
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Balance Breakdown -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-2">
                                        <i class="mdi mdi-information me-2"></i>Balance System Explanation
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="mb-2 d-block">
                                                <strong class="text-success">Confirmed Balance:</strong> Ready for immediate payment
                                                <br><strong class="text-warning">Pending Balance:</strong> From active bookings, confirmed when booking completes
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="mb-0">
                                                <strong>Total Earned:</strong> AED {{ number_format($wallet->total_earned, 2) }} |
                                                <strong>Total Paid Out:</strong> AED {{ number_format($wallet->total_paid_out, 2) }}
                                                <br><strong>Retention Rate:</strong> {{ $wallet->total_earned > 0 ? number_format((($wallet->getTotalAvailableBalance() / $wallet->total_earned) * 100), 1) : 0 }}%
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @if($wallet->pending_balance > 0)
                                <div class="text-end">
                                    @php
                                        $pendingCount = \App\Models\FarmBooking::needsEarningsConfirmation()
                                            ->whereHas('farm', fn($q) => $q->where('user_id', $wallet->user_id))
                                            ->count();
                                    @endphp
                                    <span class="badge bg-warning">
                                        {{ $pendingCount }} bookings pending completion
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Bank Account Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-bank me-2"></i>Bank Account
                </h5>
                @if($wallet->user->farmOwnerBankAccount)
                <div class="mb-3">
                    <label class="text-muted small">Account Type</label>
                    <p class="mb-2">{{ $wallet->user->farmOwnerBankAccount->getAccountTypeLabel() }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Account Details</label>
                    <div class="bg-light p-3 rounded small">
                        @foreach($wallet->user->farmOwnerBankAccount->formatted_account_details as $key => $value)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ $key }}:</span>
                            <span class="fw-bold">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <span class="badge bg-success">
                    <i class="mdi mdi-check-circle me-1"></i> Verified & Ready
                </span>
                @else
                <div class="text-center py-4">
                    <i class="mdi mdi-bank-off text-muted" style="font-size: 32px;"></i>
                    <h6 class="mt-3">No Bank Account Setup</h6>
                    <p class="text-muted mb-3">Farm owner needs to add bank account details for payments</p>
                    <span class="badge bg-danger">Payment Blocked</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Enhanced Pending Earnings Summary -->
        @if($wallet->pending_balance > 0)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-warning">
                    <i class="mdi mdi-clock-outline me-2"></i>Pending Earnings Details
                </h5>
                <p class="text-muted mb-3">
                    Earnings from active bookings that will move to confirmed balance when bookings complete.
                </p>
                
                @php
                    $pendingBookings = \App\Models\FarmBooking::where('earnings_processed', true)
                        ->where('earnings_confirmed', false)
                        ->whereHas('farm', fn($q) => $q->where('user_id', $wallet->user_id))
                        ->with(['farm'])
                        ->orderBy('end_date', 'asc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($pendingBookings->count() > 0)
                <div class="mb-3">
                    <small class="text-muted fw-bold">Active bookings with pending earnings:</small>
                    @foreach($pendingBookings as $booking)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-bold">{{ $booking->booking_reference }}</div>
                            <small class="text-muted">{{ $booking->farm->name_en }}</small>
                            <br><small class="text-info">
                                <i class="mdi mdi-calendar me-1"></i>
                                Ends: {{ $booking->end_date->format('M d, Y') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="text-warning fw-bold">AED {{ number_format($booking->farm_owner_earning, 2) }}</div>
                            <small class="text-muted">{{ $booking->getEarningStatusLabel() }}</small>
                        </div>
                    </div>
                    @endforeach
                    
                    @if($pendingBookings->count() >= 5)
                    <div class="text-center mt-2">
                        <small class="text-muted">And more bookings with pending earnings...</small>
                    </div>
                    @endif
                </div>
                @endif
                
                <div class="text-center p-3 bg-warning-subtle rounded">
                    <div class="fw-bold text-warning">Total Pending</div>
                    <h4 class="text-warning mb-0">AED {{ number_format($wallet->pending_balance, 2) }}</h4>
                </div>
            </div>
        </div>
        @endif

        <!-- Wallet Statistics -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-chart-line me-2"></i>Wallet Statistics
                </h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Transaction:</span>
                        <span class="fw-bold">
                            {{ $wallet->last_transaction_at ? $wallet->last_transaction_at->diffForHumans() : 'Never' }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Payment:</span>
                        <span class="fw-bold">
                            {{ $wallet->last_payment_at ? $wallet->last_payment_at->diffForHumans() : 'Never' }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Transactions:</span>
                        <span class="fw-bold">{{ $wallet->transactions()->count() }}</span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Member Since:</span>
                        <span class="fw-bold">{{ $wallet->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Recent Transactions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="mdi mdi-format-list-bulleted me-2"></i>Recent Transactions
                </h5>
                
                @if($wallet->transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance Impact</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Reference</th>
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
                                        $config = $transactionConfig[$transaction->type] ?? [
                                            'color' => 'secondary', 
                                            'label' => ucfirst(str_replace('_', ' ', $transaction->type)),
                                            'icon' => 'help-circle'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $config['color'] }}">
                                        <i class="mdi mdi-{{ $config['icon'] }} me-1"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}AED {{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($transaction->type === 'pending_earning')
                                    <small class="text-warning">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        To Pending: +AED {{ number_format($transaction->amount, 2) }}
                                    </small>
                                    @elseif($transaction->type === 'earning_confirmed')
                                    <small class="text-success">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        To Balance: +AED {{ number_format($transaction->amount, 2) }}
                                    </small>
                                    @else
                                    <small class="text-muted">
                                        Balance: AED {{ number_format($transaction->balance_after, 2) }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($transaction->description, 40) }}</span>
                                    @if($transaction->booking)
                                    <br><small class="text-primary">
                                        <i class="mdi mdi-calendar me-1"></i>{{ $transaction->booking->booking_reference }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <code class="small">{{ Str::limit($transaction->reference, 12) }}</code>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('dashboard.wallet.transactions', ['search' => $wallet->user->email]) }}" 
                       class="btn btn-outline-primary">
                        <i class="mdi mdi-format-list-bulleted me-1"></i>View All Transactions
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
                        <br>Available confirmed balance: <strong>AED {{ number_format($wallet->balance, 2) }}</strong>
                        @if($wallet->pending_balance > 0)
                        <br><small class="text-warning">Note: Pending balance (AED {{ number_format($wallet->pending_balance, 2) }}) cannot be paid out</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Amount (AED) *</label>
                        <input type="number" class="form-control" id="paymentAmount" name="amount" 
                               step="0.01" min="1" max="{{ $wallet->balance }}" 
                               value="{{ $wallet->balance }}" required>
                        <div class="form-text">Maximum: AED {{ number_format($wallet->balance, 2) }} (confirmed balance only)</div>
                    </div>
                    <div class="mb-3">
                        <label for="paymentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="paymentNotes" name="notes" rows="3" 
                                  placeholder="Optional notes for this payment..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-triangle me-2"></i>
                        <strong>Important:</strong> Complete the actual bank transfer before processing this payment in the system.
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
                        <br>Current confirmed balance: <strong>AED {{ number_format($wallet->balance, 2) }}</strong>
                        @if($wallet->pending_balance > 0)
                        <br>Current pending balance: <strong>AED {{ number_format($wallet->pending_balance, 2) }}</strong>
                        @endif
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
                        <div class="form-text">
                            Positive values add to confirmed balance, negative values deduct from confirmed balance.
                            <br><small class="text-info">Adjustments affect confirmed balance only (not pending balance)</small>
                        </div>
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
                            @else
                                <br><small class="text-success">Currently using platform default</small>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Rate Buttons -->
                    @if($minRate != $maxRate)
                    <div class="mb-3">
                        <label class="form-label">Quick Set:</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($minRate > 0 && $minRate != $wallet->platform_commission_rate)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-rate-btn" data-rate="{{ $minRate }}">
                                {{ $minRate }}% (Min)
                            </button>
                            @endif
                            
                            @if($defaultRate != $minRate && $defaultRate != $maxRate && $defaultRate != $wallet->platform_commission_rate)
                            <button type="button" class="btn btn-sm btn-outline-primary quick-rate-btn" data-rate="{{ $defaultRate }}">
                                {{ $defaultRate }}% (Default)
                            </button>
                            @endif
                            
                            @if($maxRate < 100 && $maxRate != $wallet->platform_commission_rate)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-rate-btn" data-rate="{{ $maxRate }}">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Process Payment Modal Handler
    const processPaymentModal = document.getElementById('processPaymentModal');
    if (processPaymentModal) {
        const form = processPaymentModal.querySelector('form');
        const amountInput = processPaymentModal.querySelector('#paymentAmount');
        
        // Form validation
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const maxAmount = {{ $wallet->balance }};
            
            if (amount > maxAmount) {
                e.preventDefault();
                alert('Amount cannot exceed confirmed balance. Pending balance is not available for payment.');
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
            const minRate = {{ $minRate }};
            const maxRate = {{ $maxRate }};
            
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