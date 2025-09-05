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
                               step="0.01" min="1" required>
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