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